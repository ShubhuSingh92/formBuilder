<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Workspace</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Your forms</h2>
                <p class="mt-1 text-sm text-slate-500">Manage forms, copy share links, and review submissions.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('submissions.index') }}" class="app-button-secondary">Submission dashboard</a>
                <a href="{{ route('forms.create') }}" class="app-button-primary">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Create form
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ number_format($forms->total()) }} {{ Str::plural('form', $forms->total()) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Public links can be copied in one click.</p>
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                @forelse ($forms as $form)
                    <article class="app-surface flex flex-col p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-lg font-extrabold text-slate-950">{{ $form->title }}</h3>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $form->is_public ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $form->is_public ? 'Public' : 'Private' }}
                                    </span>
                                </div>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $form->description ?: 'No description yet.' }}</p>
                            </div>
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 3.5H16V16.5H4V3.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M7 7H13M7 10H13M7 13H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Submissions</p>
                                <p class="mt-1 text-lg font-extrabold text-slate-900">{{ number_format($form->submissions_count) }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3.5 py-3">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Updated</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $form->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @include('forms.partials.share-control', ['form' => $form])

                        <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-5">
                            <a href="{{ route('forms.edit', $form) }}" class="app-button-primary !px-3.5 !py-2">Edit</a>
                            <a href="{{ route('forms.preview', $form) }}" class="app-button-secondary !px-3.5 !py-2">Preview</a>
                            <a href="{{ route('forms.submissions', $form) }}" class="app-button-secondary !px-3.5 !py-2">
                                Submissions
                                @if ($form->submissions_count)
                                    <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-600">{{ $form->submissions_count }}</span>
                                @endif
                            </a>
                            @if ($form->is_public)
                                <a href="{{ route('forms.public', $form->slug) }}" target="_blank" rel="noopener" class="ml-auto inline-flex items-center gap-1.5 px-2 py-2 text-xs font-bold text-slate-500 hover:text-indigo-600">
                                    Open live form
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M8 5H5C3.9 5 3 5.9 3 7V15C3 16.1 3.9 17 5 17H13C14.1 17 15 16.1 15 15V12M11 3H17V9M9 11L17 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="app-surface col-span-full flex min-h-80 flex-col items-center justify-center border-dashed p-10 text-center">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 3.5H16V16.5H4V3.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M10 7V13M7 10H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </span>
                        <h3 class="mt-4 text-lg font-extrabold text-slate-900">No forms created yet</h3>
                        <p class="mt-1 max-w-sm text-sm leading-6 text-slate-500">Build your first form and its public share URL will appear here.</p>
                        <a href="{{ route('forms.create') }}" class="app-button-primary mt-5">Create your first form</a>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $forms->links() }}</div>
        </div>
    </div>
</x-app-layout>
