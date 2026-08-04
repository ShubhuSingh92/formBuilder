<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-indigo-600">{{ $form->title }}</p>
                <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-900">Form submissions</h2>
            </div>
            <a href="{{ route('forms.edit', $form) }}" class="app-button-secondary">Back to builder</a>
        </div>
    </x-slot>

    @php
        $fieldLabels = collect($form->schema ?? [])->mapWithKeys(fn ($field) => [
            (string) ($field['key'] ?? '') => (string) ($field['label'] ?? ucfirst(str_replace('_', ' ', $field['key'] ?? 'Field'))),
        ]);
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="app-surface p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Total responses</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ $submissions->total() }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Latest response</p>
                    <p class="mt-2 text-sm font-bold text-slate-800">{{ optional($submissions->first()?->created_at)->diffForHumans() ?? 'No responses yet' }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Form status</p>
                    <p class="mt-2 inline-flex items-center gap-2 text-sm font-bold text-slate-800"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Collecting responses</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($submissions as $submission)
                    <article id="submission-{{ $submission->id }}" class="app-surface scroll-mt-24 overflow-hidden">
                        <header class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-xl bg-slate-950 text-xs font-extrabold text-white">#{{ $submission->id }}</span>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-900">Submission {{ $submission->id }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $submission->created_at->format('M d, Y · h:i A') }}</p>
                                </div>
                            </div>
                            <span class="inline-flex w-fit rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">{{ ucfirst($submission->status) }}</span>
                        </header>

                        <div class="divide-y divide-slate-100 px-5 sm:px-6">
                            @foreach ($submission->payload ?? [] as $key => $value)
                                <div class="grid gap-2 py-4 sm:grid-cols-[minmax(0,0.38fr)_minmax(0,0.62fr)] sm:gap-6">
                                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-400">{{ $fieldLabels->get($key, ucfirst(str_replace('_', ' ', $key))) }}</p>
                                    <div class="min-w-0 text-sm text-slate-700">
                                        @if (is_array($value) && ($value['kind'] ?? null) === 'file')
                                            @php
                                                $bytes = (int) ($value['size'] ?? 0);
                                                $fileSize = $bytes >= 1048576
                                                    ? number_format($bytes / 1048576, 1).' MB'
                                                    : number_format(max($bytes, 0) / 1024, 1).' KB';
                                            @endphp
                                            <a href="{{ route('forms.submissions.files.download', [$form, $submission, $key]) }}" class="inline-flex max-w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50/40">
                                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 3.5H11L15 7.5V16.5H5V3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M11 3.5V7.5H15M10 10V14M8 12H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block truncate font-bold text-slate-800">{{ $value['original_name'] ?? 'Uploaded file' }}</span>
                                                    <span class="mt-0.5 block text-xs text-slate-400">{{ $fileSize }} · Download</span>
                                                </span>
                                            </a>
                                        @elseif (is_array($value))
                                            <div class="flex flex-wrap gap-2">
                                                @forelse ($value as $item)
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ is_scalar($item) ? $item : json_encode($item) }}</span>
                                                @empty
                                                    <span class="text-slate-400">No answer</span>
                                                @endforelse
                                            </div>
                                        @elseif ($value !== null && $value !== '')
                                            <p class="whitespace-pre-wrap break-words leading-6">{{ $value }}</p>
                                        @else
                                            <span class="text-slate-400">No answer</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="app-surface flex min-h-72 flex-col items-center justify-center p-8 text-center">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-500">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 4H18C19.1 4 20 4.9 20 6V18C20 19.1 19.1 20 18 20H6C4.9 20 4 19.1 4 18V6C4 4.9 4.9 4 6 4Z" stroke="currentColor" stroke-width="1.6"/><path d="M8 9H16M8 13H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </span>
                        <h3 class="mt-4 text-base font-extrabold text-slate-900">No submissions yet</h3>
                        <p class="mt-1.5 max-w-sm text-sm leading-6 text-slate-500">Share the public form link and responses—including uploaded files—will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $submissions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
