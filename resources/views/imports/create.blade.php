<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Import from Word or Excel</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Upload .docx or .xlsx</label>
                        <input type="file" name="file" class="mt-2 block w-full text-sm text-slate-500" accept=".docx,.xlsx,.xls" required>
                    </div>
                    <p class="text-sm text-slate-500">Word documents are parsed into section-like draft fields, while spreadsheets expect a simple header or first-column prompt layout.</p>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
