<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
    <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/15">
                    <x-application-logo class="h-9 w-9 text-indigo-600 shadow-sm" />
                    <div class="hidden sm:block">
                        <div class="text-sm font-extrabold tracking-tight text-slate-950">{{ config('app.name') === 'Laravel' ? 'FormPilot' : config('app.name', 'FormPilot') }}</div>
                        <div class="-mt-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">AI Form Studio</div>
                    </div>
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} rounded-lg px-3 py-2 text-sm font-semibold transition">Dashboard</a>
                    <a href="{{ route('forms.index') }}" class="{{ request()->routeIs('forms.*') ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} rounded-lg px-3 py-2 text-sm font-semibold transition">Forms</a>
                    <a href="{{ route('submissions.index') }}" class="{{ request()->routeIs('submissions.*') ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} rounded-lg px-3 py-2 text-sm font-semibold transition">Submissions</a>
                    <a href="{{ route('ai.form') }}" class="{{ request()->routeIs('ai.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} rounded-lg px-3 py-2 text-sm font-semibold transition">AI generator</a>
                    <a href="{{ route('imports.create') }}" class="{{ request()->routeIs('imports.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} rounded-lg px-3 py-2 text-sm font-semibold transition">Imports</a>
                </div>
            </div>

            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('forms.create') }}" class="app-button-primary !rounded-lg !px-3.5 !py-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    New form
                </a>

                <x-dropdown align="right" width="w-56" contentClasses="py-1 bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-xl border border-transparent p-1.5 pr-2 text-left transition hover:border-slate-200 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200/70">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-100 text-xs font-bold uppercase text-indigo-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="hidden max-w-32 truncate text-sm font-semibold text-slate-700 xl:block">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile settings') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = !open" class="app-icon-button sm:hidden" aria-label="Toggle navigation" :aria-expanded="open.toString()">
                <svg x-show="!open" class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <svg x-cloak x-show="open" class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>

    <div x-cloak x-show="open" x-transition class="border-t border-slate-200 bg-white px-4 py-4 sm:hidden">
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-950 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
            <a href="{{ route('forms.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('forms.*') ? 'bg-slate-950 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Forms</a>
            <a href="{{ route('submissions.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('submissions.*') ? 'bg-slate-950 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Submissions</a>
            <a href="{{ route('ai.form') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">AI generator</a>
            <a href="{{ route('imports.create') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Imports</a>
        </div>
        <div class="mt-4 border-t border-slate-200 pt-4">
            <a href="{{ route('forms.create') }}" class="app-button-primary w-full">Create new form</a>
            <div class="mt-4 flex items-center justify-between px-1">
                <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-slate-600">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm font-semibold text-rose-600">Log out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
