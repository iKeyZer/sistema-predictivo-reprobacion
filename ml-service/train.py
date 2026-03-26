"""
Módulo de entrenamiento del modelo predictivo de riesgo académico.
Algoritmo: Random Forest Classifier
"""

import numpy as np
import joblib
import logging
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import StandardScaler
from sklearn.pipeline import Pipeline
from sklearn.metrics import accuracy_score, classification_report

logger = logging.getLogger(__name__)

# Labels: 0=bajo, 1=medio, 2=alto
RISK_MAP = {0: 'bajo', 1: 'medio', 2: 'alto'}
RISK_REVERSE = {'bajo': 0, 'medio': 1, 'alto': 2}

FEATURE_NAMES = [
    'avg_grade',          # Promedio actual
    'attendance_pct',     # % asistencia
    'failed_subjects',    # Materias reprobadas anteriormente
    'academic_load',      # Materias cursando este semestre
    'subject_difficulty', # Dificultad histórica de la asignatura (% reprobación)
    'partial1',           # Calificación parcial 1
    'partial2',           # Calificación parcial 2
    'partial3',           # Calificación parcial 3
]


class RiskModel:
    def __init__(self):
        self.pipeline = None
        self.is_trained = False

    def _build_pipeline(self) -> Pipeline:
        return Pipeline([
            ('scaler', StandardScaler()),
            ('clf', RandomForestClassifier(
                n_estimators=100,
                max_depth=10,
                min_samples_split=5,
                min_samples_leaf=2,
                class_weight='balanced',
                random_state=42,
                n_jobs=-1,
            ))
        ])

    def train(self, features: list, labels: list) -> dict:
        X = np.array(features, dtype=float)
        y = np.array(labels, dtype=int)

        # Fill NaN with column means
        col_means = np.nanmean(X, axis=0)
        inds = np.where(np.isnan(X))
        X[inds] = np.take(col_means, inds[1])

        self.pipeline = self._build_pipeline()

        if len(X) >= 20:
            X_train, X_test, y_train, y_test = train_test_split(
                X, y, test_size=0.2, random_state=42, stratify=y
            )
            self.pipeline.fit(X_train, y_train)
            y_pred = self.pipeline.predict(X_test)
            accuracy = accuracy_score(y_test, y_pred)
            report = classification_report(y_test, y_pred,
                                           target_names=['bajo', 'medio', 'alto'],
                                           output_dict=True)
        else:
            self.pipeline.fit(X, y)
            accuracy = 1.0
            report = {}

        self.is_trained = True
        return {
            'success': True,
            'accuracy': float(accuracy),
            'samples': len(X),
            'report': report,
        }

    def predict(self, features: dict) -> dict:
        if self.is_trained and self.pipeline is not None:
            return self._ml_predict(features)
        return self._heuristic_predict(features)

    def _ml_predict(self, features: dict) -> dict:
        X = np.array([[
            features.get('avg_grade', 0),
            features.get('attendance_pct', 100),
            features.get('failed_subjects', 0),
            features.get('academic_load', 5),
            features.get('subject_difficulty', 0),
            features.get('partial1', features.get('avg_grade', 0)),
            features.get('partial2', features.get('avg_grade', 0)),
            features.get('partial3', features.get('avg_grade', 0)),
        ]], dtype=float)

        pred_class = int(self.pipeline.predict(X)[0])
        proba = self.pipeline.predict_proba(X)[0]

        # Get model version from file if available
        return {
            'risk_level':   RISK_MAP[pred_class],
            'probability':  float(max(proba)),
            'model_version': 'ml_random_forest',
        }

    def _heuristic_predict(self, features: dict) -> dict:
        """Fallback heurístico cuando el modelo no está entrenado."""
        avg         = features.get('avg_grade', 0)
        att         = features.get('attendance_pct', 100)
        failed      = features.get('failed_subjects', 0)
        difficulty  = features.get('subject_difficulty', 0)
        partial1    = features.get('partial1', avg)
        partial2    = features.get('partial2', avg)

        score = 0.0

        # Grade factor (weight: 40%)
        if avg < 55:    score += 0.40
        elif avg < 65:  score += 0.28
        elif avg < 70:  score += 0.16
        elif avg < 75:  score += 0.08
        else:           score += 0.02

        # Attendance factor (weight: 30%)
        if att < 70:    score += 0.30
        elif att < 80:  score += 0.18
        elif att < 90:  score += 0.08
        else:           score += 0.01

        # Historical failed subjects (weight: 15%)
        if failed >= 3:   score += 0.15
        elif failed >= 1: score += 0.08
        else:             score += 0.01

        # Subject difficulty (weight: 10%)
        score += (difficulty / 100) * 0.10

        # Partial grades trend (weight: 5%)
        if partial1 and partial2 and partial2 < partial1 - 10:
            score += 0.05

        probability = min(score, 0.99)

        if probability >= 0.55:
            risk_level = 'alto'
        elif probability >= 0.30:
            risk_level = 'medio'
        else:
            risk_level = 'bajo'

        return {
            'risk_level':   risk_level,
            'probability':  round(probability, 4),
            'model_version': 'heuristic',
        }

    def save(self, path: str) -> None:
        joblib.dump(self.pipeline, path)
        logger.info(f"Modelo guardado en {path}")

    def load(self, path: str) -> None:
        self.pipeline = joblib.load(path)
        self.is_trained = True
        logger.info(f"Modelo cargado desde {path}")
