@extends('layouts.app')

@section('title', 'Log Aktivitas - ' . config('app.name'))

@section('page_title', 'Log Aktivitas')
@section('page_subtitle', 'Pantau riwayat perubahan dan aktivitas pengguna dalam sistem.')

@section('content')
<div class="card">
    <div class="card-header" style="flex-direction: column; align-items: stretch; gap: 1.5rem; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h2 class="card-title" style="margin: 0;">Riwayat Aktivitas</h2>
            <div class="tabs">
                <a href="{{ route('activity-logs.index', ['type' => 'all'] + request()->except(['type', 'page'])) }}" class="tab-item {{ $type === 'all' ? 'active' : '' }}">
                    Semua Aktivitas
                </a>
                <a href="{{ route('activity-logs.index', ['type' => 'items'] + request()->except(['type', 'page'])) }}" class="tab-item {{ $type === 'items' ? 'active' : '' }}">
                    Log Perubahan Barang
                </a>
            </div>
        </div>
        
        <form action="{{ route('activity-logs.index') }}" method="GET" class="filter-row">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="filter-group" style="flex: 1; min-width: 200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pesan, aksi, atau nama pengguna..." class="input" style="width: 100%;">
            </div>
            <div class="filter-group" style="width: 180px;">
                <select name="action" class="input" style="width: 100%;">
                    <option value="">Semua Aksi</option>
                    <option value="created" @selected(request('action') === 'created')>Dibuat (Created)</option>
                    <option value="updated" @selected(request('action') === 'updated')>Diubah (Updated)</option>
                    <option value="deleted" @selected(request('action') === 'deleted')>Dihapus (Deleted)</option>
                    <option value="login" @selected(request('action') === 'login')>Masuk (Login)</option>
                    <option value="logout" @selected(request('action') === 'logout')>Keluar (Logout)</option>
                </select>
            </div>
            <div class="button-row" style="gap: 10px;">
                <button type="submit" class="button">Terapkan Filter</button>
                <a href="{{ route('activity-logs.index', ['type' => $type]) }}" class="button-secondary">Reset</a>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Model</th>
                    <th>Deskripsi</th>
                    <th>Alamat IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="text-sm">
                            <div>{{ $log->created_at->translatedFormat('d M Y') }}</div>
                            <div class="text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                        </div>
                    </td>
                    <td>
                        @if($log->user)
                            <div class="user-info">
                                <strong>{{ $log->user->name }}</strong>
                                <span class="text-xs text-muted">{{ $log->user->roleLabel() }}</span>
                            </div>
                        @else
                            <span class="text-muted">Sistem</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $actionClass = match($log->action) {
                                'created' => 'badge-success',
                                'updated' => 'badge-info',
                                'deleted' => 'badge-danger',
                                'login' => 'badge-primary',
                                'logout' => 'badge-secondary',
                                default => 'badge-secondary'
                            };
                            $actionLabel = match($log->action) {
                                'created' => 'Dibuat',
                                'updated' => 'Diubah',
                                'deleted' => 'Dihapus',
                                'login' => 'Login',
                                'logout' => 'Logout',
                                default => ucfirst($log->action)
                            };
                        @endphp
                        <span class="badge {{ $actionClass }}">{{ $actionLabel }}</span>
                    </td>
                    <td>
                        @if($log->model_type)
                            <span class="text-sm">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-sm" style="font-weight: 600;">{{ $log->description }}</span>
                        @if($log->action === 'updated' && isset($log->properties['attributes']) && isset($log->properties['original']))
                            <div class="changes-list" style="margin-top: 10px; display: grid; gap: 4px;">
                                @php
                                    $attributes = $log->properties['attributes'];
                                    $original = $log->properties['original'];
                                    $hasChanges = false;
                                @endphp
                                @foreach($attributes as $key => $value)
                                    @php
                                        $oldValue = $original[$key] ?? null;
                                        // Skip if value is same or is a timestamp
                                        if ($value == $oldValue || in_array($key, ['updated_at', 'created_at', 'id'])) continue;
                                        $hasChanges = true;
                                    @endphp
                                    <div class="change-item" style="font-size: 0.7rem; background: rgba(0,0,0,0.02); padding: 4px 8px; border-radius: 4px; border-left: 3px solid var(--accent-color, #f97316);">
                                        <span class="text-muted" style="text-transform: uppercase; font-weight: 700; font-size: 0.6rem;">{{ str_replace('_', ' ', $key) }}</span>
                                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                            <span style="text-decoration: line-through; color: #ef4444; opacity: 0.7;">{{ is_array($oldValue) ? json_encode($oldValue) : ($oldValue ?? 'null') }}</span>
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 10px; height: 10px; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                            <span style="color: #16a34a; font-weight: 600;">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                                @if(!$hasChanges)
                                    <span class="text-xs text-muted">Tidak ada perubahan field yang terdeteksi (mungkin hanya touch timestamp).</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="text-xs font-mono">{{ $log->ip_address }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.3;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p>Tidak ada log aktivitas ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<style>
    .badge-success { background-color: rgba(34, 197, 94, 0.1); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.2); }
    .badge-info { background-color: rgba(59, 130, 246, 0.1); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.2); }
    .badge-danger { background-color: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
    .badge-primary { background-color: rgba(124, 58, 237, 0.1); color: #7c3aed; border: 1px solid rgba(124, 58, 237, 0.2); }
    .badge-secondary { background-color: rgba(107, 114, 128, 0.1); color: #4b5563; border: 1px solid rgba(107, 114, 128, 0.2); }
    
    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .search-form {
        display: flex;
        gap: 8px;
    }
    
    .input {
        padding: 8px 12px;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 8px;
        background: var(--card-bg, #ffffff);
        color: var(--text-main, #111827);
        font-size: 0.875rem;
        width: 250px;
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
    }
    
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    .text-muted { color: #6b7280; }
    .font-mono { font-family: monospace; }

    /* Tabs & Filters */
    .tabs {
        display: flex;
        gap: 4px;
        background: var(--bg-soft, #f3f4f6);
        padding: 4px;
        border-radius: 10px;
    }

    .tab-item {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-muted, #6b7280);
        transition: all 0.2s;
    }

    .tab-item:hover {
        color: var(--text-main, #111827);
        background: rgba(255, 255, 255, 0.5);
    }

    .tab-item.active {
        background: white;
        color: var(--accent-color, #f97316);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .filter-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group {
        display: flex;
        align-items: center;
    }

    /* Pagination Styles */
    .card-footer {
        padding: 1rem;
        border-top: 1px solid var(--border-color, #e5e7eb);
    }

    nav[role="navigation"] {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    nav[role="navigation"] svg {
        width: 1.25rem;
        height: 1.25rem;
        display: inline-block;
        vertical-align: middle;
    }

    nav[role="navigation"] .flex.justify-between.flex-1 {
        display: none;
    }

    nav[role="navigation"] .hidden {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    nav[role="navigation"] span.relative.inline-flex.items-center,
    nav[role="navigation"] a.relative.inline-flex.items-center {
        padding: 8px 12px;
        font-size: 0.875rem;
        border: 1px solid var(--border-color, #e5e7eb);
        background: var(--card-bg, #ffffff);
        color: var(--text-main, #111827);
        border-radius: 6px;
        margin: 0 2px;
        transition: all 0.2s;
    }

    nav[role="navigation"] a:hover {
        background: var(--bg-soft, #f9fafb);
        border-color: var(--accent-color, #f97316);
        color: var(--accent-color, #f97316);
    }

    nav[role="navigation"] span[aria-current="page"] span {
        background: var(--accent-color, #f97316) !important;
        color: white !important;
        border-color: var(--accent-color, #f97316) !important;
    }

    @media (max-width: 640px) {
        nav[role="navigation"] .hidden {
            display: none !important;
        }
        nav[role="navigation"] .flex.justify-between.flex-1 {
            display: flex;
            gap: 0.5rem;
        }
    }
</style>
@endsection
