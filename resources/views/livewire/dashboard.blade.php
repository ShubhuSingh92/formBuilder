<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('forms.index') }}" class="app-surface group p-5 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Forms</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['forms']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 3.5H16V16.5H4V3.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M7 7H13M7 10H13M7 13H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs font-semibold text-indigo-600">Manage forms →</p>
        </a>

        <a href="{{ route('submissions.index') }}" class="app-surface group p-5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Submissions</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['submissions']) }}</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 4H15C16.1 4 17 4.9 17 6V14C17 15.1 16.1 16 15 16H5C3.9 16 3 15.1 3 14V6C3 4.9 3.9 4 5 4Z" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 8H13.5M6.5 11H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </span>
            </div>
            <p class="mt-3 text-xs font-semibold text-emerald-600">Open response center →</p>
        </a>

        <div class="app-surface p-5">
            <p class="text-sm font-semibold text-slate-500">Responses today</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['today']) }}</p>
            <p class="mt-3 text-xs text-slate-400">New since midnight</p>
        </div>

        <div class="app-surface p-5">
            <p class="text-sm font-semibold text-slate-500">AI imports</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['imports']) }}</p>
            <a href="{{ route('imports.create') }}" class="mt-3 inline-block text-xs font-semibold text-indigo-600 hover:text-indigo-800">Start an import →</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.55fr)]">
        <section class="app-surface overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-950">Recent forms</h3>
                    <p class="mt-1 text-sm text-slate-500">Copy a live link directly from the form list.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('ai.form') }}" class="app-button-secondary !py-2">Generate with AI</a>
                    <a href="{{ route('forms.create') }}" class="app-button-primary !py-2">Build a form</a>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($forms as $form)
                    <article class="px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="truncate font-extrabold text-slate-900">{{ $form->title }}</h4>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $form->is_public ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $form->is_public ? 'Public' : 'Private' }}</span>
                                </div>
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ Str::limit($form->description ?: 'No description provided yet.', 100) }}</p>
                                <div class="mt-2 flex items-center gap-3 text-xs font-semibold text-slate-400">
                                    <span>{{ $form->submissions_count }} {{ Str::plural('submission', $form->submissions_count) }}</span>
                                    <span>Updated {{ $form->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2 text-sm">
                                <a href="{{ route('forms.preview', $form) }}" class="app-button-secondary !px-3 !py-2">Preview</a>
                                <a href="{{ route('forms.edit', $form) }}" class="app-button-primary !px-3 !py-2">Edit</a>
                            </div>
                        </div>

                        @include('forms.partials.share-control', ['form' => $form, 'compact' => true])
                    </article>
                @empty
                    <div class="p-10 text-center">
                        <p class="text-sm font-bold text-slate-800">No forms yet</p>
                        <p class="mt-1 text-sm text-slate-500">Create your first form and its share URL will appear here.</p>
                    </div>
                @endforelse
            </div>

            @if ($forms->isNotEmpty())
                <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-4 text-right">
                    <a href="{{ route('forms.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">View all forms →</a>
                </div>
            @endif
        </section>

        <aside class="app-surface self-start overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5">
                <div>
                    <h3 class="font-extrabold text-slate-950">Latest submissions</h3>
                    <p class="mt-1 text-xs text-slate-500">Newest responses across all forms</p>
                </div>
                <a href="{{ route('submissions.index') }}" class="text-xs font-bold text-indigo-600">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentSubmissions as $submission)
                    <a href="{{ route('forms.submissions', $submission->form) }}#submission-{{ $submission->id }}" class="flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-950 text-[11px] font-extrabold text-white">#{{ $submission->id }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-slate-800">{{ $submission->form->title }}</span>
                            <span class="mt-0.5 block text-xs text-slate-400">{{ $submission->created_at->diffForHumans() }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-slate-300" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @empty
                    <div class="p-8 text-center">
                        <p class="text-sm font-bold text-slate-800">No responses yet</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Share a public form link to start collecting answers.</p>
                    </div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
