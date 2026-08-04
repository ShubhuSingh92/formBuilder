@php
    $answerableFields = collect($form->schema ?? [])->reject(fn ($field) => in_array(strtolower((string) ($field['type'] ?? '')), ['section_heading', 'section', 'heading'], true));
    $requiredCount = $answerableFields->filter(fn ($field) => (bool) ($field['required'] ?? false))->count();
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm text-rose-800" role="alert">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-white text-rose-600 shadow-sm">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M10 6.5V10.5M10 13.5V13.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </span>
            <div>
                <p class="font-bold">A few answers need your attention.</p>
                <p class="mt-0.5 text-xs leading-5 text-rose-700">Review the highlighted fields, then submit the form again.</p>
            </div>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('forms.submit', $form) }}" enctype="multipart/form-data" class="space-y-4 sm:space-y-5" data-respondent-form>
    @csrf

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-form-progress-wrap>
        <div class="flex items-center justify-between gap-4 text-xs font-semibold">
            <span class="text-slate-500"><span data-progress-answered>0</span> of {{ $answerableFields->count() }} answered</span>
            <span class="text-slate-700" data-progress-percent>0%</span>
        </div>
        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
            <div class="h-full w-0 rounded-full bg-indigo-600 transition-all duration-300" data-progress-bar></div>
        </div>
    </div>

    @foreach ($form->schema as $field)
        @include('forms.partials.respondent-field', ['field' => $field, 'index' => $loop->index])
    @endforeach

    <div class="pt-3">
        <div class="flex flex-col-reverse gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs leading-5 text-slate-400">
                @if ($requiredCount)
                    <span class="font-semibold text-rose-500">*</span> {{ $requiredCount }} required {{ \Illuminate\Support\Str::plural('field', $requiredCount) }}
                @else
                    All fields are optional
                @endif
            </p>
            <button type="submit" class="app-button-primary min-h-12 w-full px-6 sm:w-auto sm:min-w-40" data-submit-button>
                <span data-submit-label>{{ $submitLabel ?? 'Submit response' }}</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10H16M11 5L16 10L11 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg class="hidden h-4 w-4 animate-spin" viewBox="0 0 20 20" fill="none" data-submit-spinner aria-hidden="true"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-opacity=".25" stroke-width="2"/><path d="M17 10A7 7 0 0010 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</form>
