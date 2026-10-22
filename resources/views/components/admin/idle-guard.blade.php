{{-- Peringatan sebelum admin keluar otomatis karena tidak aktif; logikanya di Alpine.data('idleGuard') (app.js). --}}
<div
    x-data="idleGuard({
        timeout: {{ (int) config('admin.idle_timeout') }},
        warning: {{ (int) config('admin.idle_warning') }},
        pingUrl: @js(route('admin.keep-alive')),
        warningUrl: @js(route('admin.idle-warning')),
        logoutUrl: @js(route('admin.idle-logout')),
        loginUrl: @js(route('login')),
    })"
>
    <div
        x-show="remaining !== null"
        x-cloak
        x-transition.opacity
        role="alert"
        aria-live="assertive"
        class="fixed bottom-4 right-4 z-[90] w-80 max-w-[calc(100vw-2rem)] rounded-sm border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 shadow-lg"
    >
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <div>
                <p class="font-semibold">Sesi akan berakhir</p>
                <p class="mt-1 leading-5">Anda tidak aktif. Anda akan keluar otomatis dalam <span class="font-semibold tabular-nums" x-text="clock"></span>.</p>
                <button type="button" @click="stay()" class="mt-3 rounded-sm bg-primary px-3.5 py-1.5 text-[13px] font-semibold text-white ring-1 ring-inset ring-black/10 transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">Tetap masuk</button>
            </div>
        </div>
    </div>
</div>
