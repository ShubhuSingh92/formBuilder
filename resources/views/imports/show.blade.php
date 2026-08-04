<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Import result</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-medium text-slate-700">Status: {{ ucfirst($job->status) }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $job->message }}</p>
                <pre class="mt-6 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-slate-100">{{ json_encode($job->result_schema, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
</x-app-layout>
