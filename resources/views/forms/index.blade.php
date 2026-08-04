<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800">Forms</h2>
            <a href="{{ route('forms.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Create form</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                @forelse ($forms as $form)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $form->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $form->description ?: 'No description yet.' }}</p>
                            </div>
                            <div class="flex gap-3 text-sm">
                                <a href="{{ route('forms.preview', $form) }}" class="text-slate-600">Preview</a>
                                <a href="{{ route('forms.edit', $form) }}" class="text-indigo-600">Edit</a>
                                <a href="{{ route('forms.submissions', $form) }}" class="text-slate-600">Submissions</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                        No forms created yet.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $forms->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
