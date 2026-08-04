<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Overview</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Form builder dashboard</h2>
                <p class="mt-1 text-sm text-slate-500">Your forms, share links, and latest responses at a glance.</p>
            </div>
            <a href="{{ route('forms.create') }}" class="app-button-primary">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                Create form
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <livewire:dashboard />
        </div>
    </div>
</x-app-layout>
