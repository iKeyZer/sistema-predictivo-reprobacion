@extends('layouts.app')
@section('title', 'Mis Alertas')
@section('page-title', 'Mis Alertas Académicas')

@section('content')
<div class="card">
    @forelse($alerts as $alert)
        <div class="p-4 border-bottom {{ $alert->status === 'activa' ? 'bg-danger bg-opacity-5' : '' }}">
            <div class="d-flex gap-3">
                <div class="flex-shrink-0 mt-1">
                    <span class="badge rounded-circle p-2 {{ $alert->type === 'riesgo_alto' ? 'bg-danger' : ($alert->type === 'riesgo_medio' ? 'bg-warning text-dark' : 'bg-info text-dark') }}"
                          style="width:36px;height:36px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-exclamation"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="fw-semibold small">{{ $alert->getTypeLabel() }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $alert->getStatusBadgeClass() }}">{{ ucfirst($alert->status) }}</span>
                            <span class="text-muted" style="font-size:0.75rem">{{ $alert->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <p class="small mb-0">{{ $alert->message }}</p>
                    @if($alert->status === 'activa')
                        <div class="mt-2 p-2 bg-light rounded small">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            <strong>¿Qué hacer?</strong> Contacta a tu tutor académico o acude a asesoría con tu docente.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="p-5 text-center">
            <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
            <h5>¡Sin alertas activas!</h5>
            <p class="text-muted">Tu desempeño académico está en orden.</p>
        </div>
    @endforelse
</div>
@if($alerts->hasPages())
    <div class="mt-3">{{ $alerts->links() }}</div>
@endif
@endsection
