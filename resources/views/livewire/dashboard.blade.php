<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Forms</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\Form::where('user_id', auth()->id())->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Submissions</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\FormSubmission::query()->join('forms', 'forms.id', '=', 'form_submissions.form_id')->where('forms.user_id', auth()->id())->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">AI Imports</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\ImportJob::where('user_id', auth()->id())->count() }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Recent forms</h3>
                <p class="text-sm text-slate-500">Your most recently created forms are listed here.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('ai.form') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Generate with AI</a>
                <a href="{{ route('imports.create') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Import DOCX/XLSX</a>
                <a href="{{ route('forms.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Build a form</a>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            @forelse ($forms as $form)
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <div>
                        <p class="font-medium text-slate-900">{{ $form->title }}</p>
                        <p class="text-sm text-slate-500">{{ Str::limit($form->description ?: 'No description provided yet.', 90) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('forms.preview', $form) }}" class="text-sm text-slate-600 hover:text-slate-900">Preview</a>
                        <a href="{{ route('forms.edit', $form) }}" class="text-sm text-indigo-600 hover:text-indigo-500">Edit</a>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No forms yet. Start by creating your first AI-powered form.</p>
            @endforelse
        </div>
    </div>
</div>
