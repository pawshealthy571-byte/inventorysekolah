<div class="auth-switcher" style="margin-bottom: 24px; background: white; padding: 10px; border-radius: var(--radius-lg); border: 1px solid var(--border); width: fit-content;">
    <a class="auth-switcher-link {{ request()->routeIs('settings.index') ? 'active' : '' }}" href="{{ route('settings.index') }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        Identitas Web
    </a>
    
    @if(auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_ACCOUNTS_MANAGE))
        <a class="auth-switcher-link {{ request()->routeIs('settings.accounts') ? 'active' : '' }}" href="{{ route('settings.accounts') }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Manajemen Akun
        </a>
    @endif

    @if(auth()->user()->hasPermission(\App\Models\RolePermission::PERMISSION_ACCESS_MANAGE))
        <a class="auth-switcher-link {{ request()->routeIs('settings.access') ? 'active' : '' }}" href="{{ route('settings.access') }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            Manajemen Akses
        </a>
    @endif
</div>
