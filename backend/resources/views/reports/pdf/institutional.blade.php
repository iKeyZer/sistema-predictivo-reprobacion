<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Institucional — {{ $period }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; color: #1a3c6e; margin-bottom: 4px; }
        h2 { font-size: 14px; color: #1a3c6e; margin: 20px 0 8px; border-bottom: 2px solid #1a3c6e; padding-bottom: 4px; }
        .meta { color: #666; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1a3c6e; color: #fff; padding: 8px 6px; text-align: left; font-size: 11px; }
        td { padding: 7px 6px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .badge-alto  { background: #dc3545; color: #fff; padding: 2px 8px; border-radius: 10px; }
        .badge-medio { background: #ffc107; color: #212529; padding: 2px 8px; border-radius: 10px; }
        .badge-bajo  { background: #198754; color: #fff; padding: 2px 8px; border-radius: 10px; }
        .summary-box { display: inline-block; width: 30%; margin-right: 2%; padding: 12px; border-radius: 6px; text-align: center; }
        .box-danger  { background: #fff5f5; border: 1px solid #dc3545; }
        .box-warning { background: #fffbf0; border: 1px solid #ffc107; }
        .box-success { background: #f0fff4; border: 1px solid #198754; }
        .box-num { font-size: 28px; font-weight: bold; }
        footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>

<h1>Reporte Institucional &mdash; ITSC Inform&aacute;tica</h1>
<div class="meta">
    Periodo: <strong>{{ $period }}</strong> &nbsp;|&nbsp;
    Total estudiantes activos: <strong>{{ $totalStudents }}</strong> &nbsp;|&nbsp;
    Generado: <strong>{{ date('d/m/Y H:i') }}</strong>
</div>

<h2>Distribución de Riesgo</h2>
<div>
    <div class="summary-box box-danger">
        <div class="box-num" style="color:#dc3545">{{ $riskSummary['alto'] }}</div>
        <div>Riesgo Alto</div>
    </div>
    <div class="summary-box box-warning">
        <div class="box-num" style="color:#856404">{{ $riskSummary['medio'] }}</div>
        <div>Riesgo Medio</div>
    </div>
    <div class="summary-box box-success">
        <div class="box-num" style="color:#198754">{{ $riskSummary['bajo'] }}</div>
        <div>Riesgo Bajo</div>
    </div>
</div>

<h2>Estadísticas por Asignatura</h2>
<table>
    <thead>
        <tr>
            <th>Clave</th>
            <th>Asignatura</th>
            <th>Semestre</th>
            <th>Dificultad Histórica (%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjectStats as $subject)
        <tr>
            <td>{{ $subject->code }}</td>
            <td>{{ $subject->name }}</td>
            <td>{{ $subject->semester }}</td>
            <td>{{ number_format($subject->historical_difficulty, 1) }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

<footer>
    ITSC &mdash; Sistema Predictivo de Reprobaci&oacute;n &nbsp;|&nbsp; Documento generado autom&aacute;ticamente
</footer>
</body>
</html>
