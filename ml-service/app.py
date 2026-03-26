"""
Sistema Predictivo de Reprobación — Microservicio ML
Microservicio Flask con scikit-learn para predecir riesgo académico.

Endpoints:
  GET  /health   → Estado del servicio y modelo
  POST /predict  → Predecir nivel de riesgo para un estudiante
  POST /train    → Entrenar/reentrenar el modelo con datos históricos
"""

import os
import json
import joblib
import logging
from pathlib import Path
from flask import Flask, request, jsonify
from datetime import datetime

from train import RiskModel

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)

MODEL_PATH = Path(__file__).parent / 'model' / 'risk_model.pkl'
MODEL_VERSION_PATH = Path(__file__).parent / 'model' / 'version.json'

# ─── Load model on startup ────────────────────────────────────────────────────
risk_model = RiskModel()

if MODEL_PATH.exists():
    try:
        risk_model.load(str(MODEL_PATH))
        logger.info(f"Modelo cargado desde {MODEL_PATH}")
    except Exception as e:
        logger.warning(f"No se pudo cargar el modelo: {e}. Usando modo heurístico.")
else:
    logger.info("No hay modelo entrenado. Usando modo heurístico.")


# ─── Health check ─────────────────────────────────────────────────────────────
@app.route('/health', methods=['GET'])
def health():
    version_info = {}
    if MODEL_VERSION_PATH.exists():
        with open(MODEL_VERSION_PATH) as f:
            version_info = json.load(f)

    return jsonify({
        'status': 'ok',
        'model_loaded': risk_model.is_trained,
        'model_version': version_info.get('version', 'heuristic'),
        'trained_at': version_info.get('trained_at'),
        'timestamp': datetime.utcnow().isoformat(),
    })


# ─── Predict endpoint ─────────────────────────────────────────────────────────
@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json(silent=True)
    if not data:
        return jsonify({'error': 'No se recibieron datos JSON'}), 400

    required = ['avg_grade', 'attendance_pct', 'failed_subjects', 'academic_load', 'subject_difficulty']
    missing = [f for f in required if f not in data]
    if missing:
        return jsonify({'error': f'Campos requeridos faltantes: {missing}'}), 422

    features = {
        'avg_grade':          float(data.get('avg_grade', 0)),
        'attendance_pct':     float(data.get('attendance_pct', 100)),
        'failed_subjects':    int(data.get('failed_subjects', 0)),
        'academic_load':      int(data.get('academic_load', 5)),
        'subject_difficulty': float(data.get('subject_difficulty', 0)),
        'partial1':           float(data.get('partial1') or data.get('avg_grade', 0)),
        'partial2':           float(data.get('partial2') or data.get('avg_grade', 0)),
        'partial3':           float(data.get('partial3') or data.get('avg_grade', 0)),
    }

    result = risk_model.predict(features)
    logger.info(f"Predicción: {result['risk_level']} ({result['probability']:.3f})")

    return jsonify(result)


# ─── Train endpoint ───────────────────────────────────────────────────────────
@app.route('/train', methods=['POST'])
def train():
    data = request.get_json(silent=True)
    if not data or 'data' not in data or 'labels' not in data:
        return jsonify({'error': 'Se requieren "data" (features) y "labels"'}), 400

    features = data['data']
    labels   = data['labels']

    if len(features) < 10:
        return jsonify({'error': 'Se necesitan al menos 10 muestras para entrenar'}), 422

    if len(features) != len(labels):
        return jsonify({'error': 'data y labels deben tener la misma longitud'}), 422

    try:
        result = risk_model.train(features, labels)

        os.makedirs(MODEL_PATH.parent, exist_ok=True)
        risk_model.save(str(MODEL_PATH))

        version = f"v{datetime.now().strftime('%Y%m%d%H%M')}"
        with open(MODEL_VERSION_PATH, 'w') as f:
            json.dump({'version': version, 'trained_at': datetime.utcnow().isoformat(),
                       'samples': len(features), 'accuracy': result['accuracy']}, f)

        result['model_version'] = version
        logger.info(f"Modelo entrenado. Precisión: {result['accuracy']:.3f}")
        return jsonify(result)

    except Exception as e:
        logger.error(f"Error al entrenar: {e}")
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
