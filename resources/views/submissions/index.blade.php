<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Response center</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">Submission dashboard</h2>
                <p class="mt-1 text-sm text-slate-500">Review responses from every form in one place.</p>
            </div>
            <a href="{{ route('forms.index') }}" class="app-button-secondary">View all forms</a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-surface p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total submissions</p>
                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['total']) }}</p>
                        </div>
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 3.5H16V16.5H4V3.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M7 7H13M7 10H13M7 13H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </span>
                    </div>
                </div>
                <div class="app-surface p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Received today</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['today']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Since midnight</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">This week</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['this_week']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Monday through Sunday</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Active forms</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ number_format($stats['responding_forms']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Forms with at least one response</p>
                </div>
            </div>

            <div class="app-surface mt-6 overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">All responses</h3>
                            <p class="mt-1 text-sm text-slate-500">Filter by form or status, then open the full response.</p>
                        </div>
                        <form method="GET" action="{{ route('submissions.index') }}" class="grid gap-2 sm:grid-cols-[minmax(12rem,1fr)_minmax(9rem,0.6fr)_auto]">
                            <label class="sr-only" for="submission-form-filter">Filter by form</label>
                            <select id="submission-form-filter" name="form_id" class="app-input py-2">
                                <option value="">All forms</option>
                                @foreach ($ownedForms as $ownedForm)
                                    <option value="{{ $ownedForm->id }}" @selected((string) request('form_id') === (string) $ownedForm->id)>{{ $ownedForm->title }}</option>
                                @endforeach
                            </select>
                            <label class="sr-only" for="submission-status-filter">Filter by status</label>
                            <select id="submission-status-filter" name="status" class="app-input py-2">
                                <option value="">All statuses</option>
                                <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                            </select>
                            <div class="flex gap-2">
                                <button type="submit" class="app-button-primary !py-2">Filter</button>
                                @if (request()->hasAny(['form_id', 'status']))
                                    <a href="{{ route('submissions.index') }}" class="app-button-secondary !py-2">Reset</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="px-6 py-3.5">Submission</th>
                                <th class="px-6 py-3.5">Form</th>
                                <th class="px-6 py-3.5">Answers</th>
                                <th class="px-6 py-3.5">Submitted</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($submissions as $submission)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-slate-950 text-xs font-extrabold text-white">#{{ $submission->id }}</span>
                                            <span class="text-sm font-bold text-slate-800">Response</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="max-w-xs truncate text-sm font-bold text-slate-900">{{ $submission->form->title }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ count(array_filter($submission->payload ?? [], fn ($value) => $value !== null && $value !== '' && $value !== [])) }} answered</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <p class="text-sm font-semibold text-slate-700">{{ $submission->created_at->format('M d, Y') }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $submission->created_at->format('h:i A') }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4"><span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ ucfirst($submission->status) }}</span></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <a href="{{ route('forms.submissions', $submission->form) }}#submission-{{ $submission->id }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">Open response</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <p class="text-sm font-bold text-slate-800">No submissions found</p>
                                        <p class="mt-1 text-sm text-slate-500">Share a public form link to start collecting responses.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-slate-100 md:hidden">
                    @forelse ($submissions as $submission)
                        <article class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-950 text-xs font-extrabold text-white">#{{ $submission->id }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-slate-900">{{ $submission->form->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $submission->created_at->format('M d, Y · h:i A') }}</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ ucfirst($submission->status) }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-xs font-semibold text-slate-500">{{ count(array_filter($submission->payload ?? [], fn ($value) => $value !== null && $value !== '' && $value !== [])) }} fields answered</span>
                                <a href="{{ route('forms.submissions', $submission->form) }}#submission-{{ $submission->id }}" class="text-sm font-bold text-indigo-600">Open response</a>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">No submissions found.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-6">{{ $submissions->links() }}</div>
        </div>
    </div>
</x-app-layout>
