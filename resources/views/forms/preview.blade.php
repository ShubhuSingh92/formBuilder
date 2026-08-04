<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-indigo-600">Respondent experience</p>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-900">Preview form</h2>
            </div>
            <a href="{{ route('forms.edit', $form) }}" class="app-button-secondary">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 14.5V16H5.5L14.7 6.8L13.2 5.3L4 14.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M12.7 5.8L14.2 4.3C14.6 3.9 15.2 3.9 15.6 4.3L15.7 4.4C16.1 4.8 16.1 5.4 15.7 5.8L14.2 7.3" stroke="currentColor" stroke-width="1.5"/></svg>
                Back to builder
            </a>
        </div>
    </x-slot>

    <div class="relative overflow-hidden py-8 sm:py-12">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-indigo-50 to-transparent"></div>
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-amber-600 shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M10 9V14M10 6V6.1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <p><span class="font-bold">Preview mode:</span> submissions made here are recorded as real responses.</p>
            </div>

            @if (session('status'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-slate-950/5">
                <header class="border-b border-slate-100 bg-slate-950 px-6 py-8 text-white sm:px-10 sm:py-10">
                    <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] text-indigo-200">Form preview</span>
                    <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $form->title }}</h1>
                    @if ($form->description)
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">{{ $form->description }}</p>
                    @endif
                </header>
                <div class="p-5 sm:p-8 lg:p-10">
                    @include('forms.partials.respondent-form', ['submitLabel' => 'Submit test response'])
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
