@extends('layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Total: <strong>{{ $users->total() }}</strong></p>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
    </a>
</div>

<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Buscar por nombre o email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm">
                <option value="">Todos los roles</option>
                @foreach(['estudiante','docente','tutor','coordinador','admin'] as $role)
                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">Buscar</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary w-100">Limpiar</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleColors = ['admin'=>'danger','docente'=>'primary','tutor'=>'success','coordinador'=>'info','estudiante'=>'secondary'];
                            @endphp
                            <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $user->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('usuarios.destroy', $user) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar usuario {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-3">{{ $users->links() }}</div>
    @endif
</div>
@endsection
