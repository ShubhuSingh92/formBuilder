@php
    $isEditing = isset($form) && $form->exists;
    $formTitle = old('title', $isEditing ? $form->title : '');
    $formDescription = old('description', $isEditing ? $form->description : '');
    $formIsPublic = (bool) old('is_public', $isEditing ? $form->is_public : true);
    $schemaValue = old('schema', $isEditing ? json_encode($form->schema, JSON_PRETTY_PRINT) : '[]');
    if (is_array($schemaValue)) {
        $schemaValue = json_encode($schemaValue, JSON_PRETTY_PRINT);
    }

    $fieldGroups = [
        'Essentials' => [
            ['type' => 'text', 'label' => 'Short text', 'hint' => 'Single-line response'],
            ['type' => 'textarea', 'label' => 'Long text', 'hint' => 'Multi-line response'],
            ['type' => 'number', 'label' => 'Number', 'hint' => 'Numeric response'],
        ],
        'Contact & date' => [
            ['type' => 'email', 'label' => 'Email', 'hint' => 'Email address'],
            ['type' => 'phone', 'label' => 'Phone', 'hint' => 'Phone number'],
            ['type' => 'date', 'label' => 'Date', 'hint' => 'Calendar picker'],
        ],
        'Choices' => [
            ['type' => 'dropdown', 'label' => 'Dropdown', 'hint' => 'Choose one option'],
            ['type' => 'radio', 'label' => 'Radio group', 'hint' => 'Visible single choice'],
            ['type' => 'checkbox', 'label' => 'Checkboxes', 'hint' => 'Choose multiple'],
            ['type' => 'rating', 'label' => 'Rating', 'hint' => 'Score from 1 to 5'],
        ],
        'Structure & files' => [
            ['type' => 'section_heading', 'label' => 'Section heading', 'hint' => 'Organize your form'],
            ['type' => 'file', 'label' => 'File upload', 'hint' => 'Collect an attachment'],
        ],
    ];
@endphp

<div
    class="min-h-[calc(100vh-4rem)]"
    data-form-builder
    data-validate-url="{{ route('schema.validate') }}"
    data-draft-key="form_builder_draft_{{ $isEditing ? $form->id : 'new' }}"
    data-is-editing="{{ $isEditing ? 'true' : 'false' }}"
    data-server-updated-at="{{ $isEditing && $form->updated_at ? $form->updated_at->timestamp * 1000 : 0 }}"
>
    <form method="POST" action="{{ $isEditing ? route('forms.update', $form) : route('forms.store') }}" id="form-builder-form">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <textarea id="json-schema" name="schema" class="hidden" aria-hidden="true">{{ $schemaValue }}</textarea>

        <div class="border-b border-slate-200/80 bg-white">
            <div class="mx-auto flex max-w-[1600px] flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                        <a href="{{ route('forms.index') }}" class="transition hover:text-slate-700">Forms</a>
                        <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none"><path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="truncate text-slate-600">{{ $isEditing ? $form->title : 'Untitled form' }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">{{ $isEditing ? 'Edit form' : 'Create a new form' }}</h1>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Draft
                        </span>
                        <span id="autosave-status" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none"><path d="M3 8.5L6.2 11.5L13 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Ready
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('ai.form') }}" class="app-button-secondary !border-indigo-200 !text-indigo-700 hover:!bg-indigo-50">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 2L11.2 6.8L16 8L11.2 9.2L10 14L8.8 9.2L4 8L8.8 6.8L10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M15.5 13L16.1 15.4L18.5 16L16.1 16.6L15.5 19L14.9 16.6L12.5 16L14.9 15.4L15.5 13Z" fill="currentColor"/></svg>
                        Generate with AI
                    </a>
                    @if ($isEditing)
                        @if ($form->is_public)
                            <button type="button" class="app-button-secondary" data-copy-url="{{ route('forms.public', $form->slug) }}">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M7.5 10.5L12.5 5.5M6.5 6.5L8 5C9.4 3.6 11.6 3.6 13 5C14.4 6.4 14.4 8.6 13 10L11.5 11.5M13.5 13.5L12 15C10.6 16.4 8.4 16.4 7 15C5.6 13.6 5.6 11.4 7 10L8.5 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Share
                            </button>
                        @endif
                        <a href="{{ route('forms.preview', $form) }}" target="_blank" class="app-button-secondary">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M2.5 10C4.4 6.8 6.9 5.2 10 5.2C13.1 5.2 15.6 6.8 17.5 10C15.6 13.2 13.1 14.8 10 14.8C6.9 14.8 4.4 13.2 2.5 10Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="10" r="2" stroke="currentColor" stroke-width="1.5"/></svg>
                            Preview
                        </a>
                    @endif
                    <button type="submit" class="app-button-primary" id="save-form-button">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 3.5H14.5L17 6V16.5H4V3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7 3.5V8H14V3.5M7 13H14" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                        {{ $isEditing ? 'Save changes' : 'Create form' }}
                    </button>
                </div>
            </div>
        </div>

        @if (session('status') || session('public_url'))
            <div class="mx-auto max-w-[1600px] px-4 pt-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 10.5L8 14L16 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold">{{ session('status') ?? 'Form saved successfully.' }}</p>
                            @if (session('public_url'))
                                <p class="mt-0.5 truncate text-emerald-800/80">{{ session('public_url') }}</p>
                            @endif
                        </div>
                    </div>
                    @if (session('public_url'))
                        <button type="button" class="app-button-secondary !border-emerald-200 !bg-white !py-2 !text-emerald-800" data-copy-url="{{ session('public_url') }}">Copy public URL</button>
                    @endif
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-auto max-w-[1600px] px-4 pt-5 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 6V10.5M10 14H10.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <div>
                            <p class="font-bold">Please fix the following before saving:</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-rose-800">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div id="ai-schema-alert" class="mx-auto hidden max-w-[1600px] px-4 pt-5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-100 text-indigo-700">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 2L11.2 6.8L16 8L11.2 9.2L10 14L8.8 9.2L4 8L8.8 6.8L10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                    </span>
                    <div><span class="font-bold">AI schema loaded.</span> Review the generated fields and customize anything that needs a human touch.</div>
                </div>
                <button type="button" id="clear-ai-schema" class="text-left text-sm font-bold text-indigo-700 hover:text-indigo-950">Clear generated fields</button>
            </div>
        </div>

        <div class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 lg:px-8">
            <div class="grid gap-5 xl:grid-cols-[260px_minmax(0,1fr)_320px]">
                <aside class="app-surface self-start overflow-hidden xl:sticky xl:top-[5.25rem] xl:max-h-[calc(100vh-6.5rem)]">
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-extrabold text-slate-950">Fields</h2>
                                <p class="mt-0.5 text-xs text-slate-500">Drag or click to add</p>
                            </div>
                            <span id="field-library-count" class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">12</span>
                        </div>
                        <div class="relative mt-3">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none"><circle cx="8.5" cy="8.5" r="5" stroke="currentColor" stroke-width="1.5"/><path d="M12.5 12.5L16.5 16.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            <input id="field-search" type="search" class="app-input !py-2 !pl-9" placeholder="Search fields..." autocomplete="off">
                        </div>
                    </div>

                    <div id="field-library" class="builder-scrollbar max-h-[calc(100vh-15.5rem)] overflow-y-auto p-3">
                        @foreach ($fieldGroups as $group => $fields)
                            <section class="field-library-group {{ !$loop->first ? 'mt-5' : '' }}" data-field-group>
                                <h3 class="px-2 text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ $group }}</h3>
                                <div class="mt-1.5 space-y-0.5">
                                    @foreach ($fields as $fieldItem)
                                        <button
                                            type="button"
                                            class="library-field group"
                                            data-field-type="{{ $fieldItem['type'] }}"
                                            data-field-label="{{ strtolower($fieldItem['label'].' '.$fieldItem['hint'].' '.$group) }}"
                                            draggable="true"
                                        >
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 transition group-hover:bg-indigo-50 group-hover:text-indigo-600">
                                                @switch($fieldItem['type'])
                                                    @case('text') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 5H16M10 5V15M7 15H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg> @break
                                                    @case('textarea') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 8H14M6 11H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> @break
                                                    @case('number') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M7 3L5 17M15 3L13 17M3 8H17M2 13H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> @break
                                                    @case('email') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M4 6L10 11L16 6" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg> @break
                                                    @case('phone') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M6.5 3.5L8.5 7L6.7 8.4C7.7 10.6 9.4 12.3 11.6 13.3L13 11.5L16.5 13.5L15.5 16.2C15.2 17 14.4 17.4 13.6 17.2C8.1 15.8 4.2 11.9 2.8 6.4C2.6 5.6 3 4.8 3.8 4.5L6.5 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg> @break
                                                    @case('date') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="3" y="4.5" width="14" height="12.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 3V6M14 3V6M3 8H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> @break
                                                    @case('dropdown') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="3" y="4" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M7 8H13M8 11L10 13L12 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> @break
                                                    @case('radio') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><circle cx="6" cy="6" r="2.5" stroke="currentColor" stroke-width="1.5"/><circle cx="6" cy="14" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="M11 6H17M11 14H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> @break
                                                    @case('checkbox') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="3.5" y="3.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M4.8 6L5.8 7L7.3 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/><rect x="3.5" y="11.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M11 6H17M11 14H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> @break
                                                    @case('rating') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 2.8L12.1 7L16.8 7.7L13.4 11L14.2 15.7L10 13.5L5.8 15.7L6.6 11L3.2 7.7L7.9 7L10 2.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg> @break
                                                    @case('section_heading') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 5H16M4 9H13M4 14H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg> @break
                                                    @case('file') <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M5 3.5H11L15 7.5V16.5H5V3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M11 3.5V7.5H15M10 10V14M8 12H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg> @break
                                                @endswitch
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-bold text-slate-800">{{ $fieldItem['label'] }}</span>
                                                <span class="block truncate text-[11px] text-slate-400">{{ $fieldItem['hint'] }}</span>
                                            </span>
                                            <svg class="h-4 w-4 shrink-0 text-slate-300" viewBox="0 0 20 20" fill="none"><path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                        </button>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                        <p id="field-search-empty" class="hidden px-3 py-8 text-center text-sm text-slate-400">No field types match your search.</p>
                    </div>
                </aside>

                <main class="min-w-0 space-y-5">
                    <section class="app-surface overflow-hidden">
                        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M4 4H16V16H4V4Z" stroke="currentColor" stroke-width="1.5"/><path d="M7 8H13M7 11H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </span>
                                    <div>
                                        <h2 class="text-sm font-extrabold text-slate-950">Form details</h2>
                                        <p class="text-xs text-slate-500">This is what respondents see first.</p>
                                    </div>
                                </div>
                            </div>
                            <label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <span class="text-xs font-bold text-slate-600">Public form</span>
                                <span class="relative inline-flex h-5 w-9 items-center">
                                    <input type="hidden" name="is_public" value="0">
                                    <input id="form-is-public" type="checkbox" name="is_public" value="1" class="peer sr-only" {{ $formIsPublic ? 'checked' : '' }}>
                                    <span class="absolute inset-0 rounded-full bg-slate-300 transition peer-checked:bg-indigo-600 peer-focus:ring-4 peer-focus:ring-indigo-500/15"></span>
                                    <span class="absolute left-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition peer-checked:translate-x-4"></span>
                                </span>
                            </label>
                        </div>
                        <div class="grid gap-4 p-5 lg:grid-cols-[1.2fr_0.8fr]">
                            <div>
                                <label for="form-title" class="text-xs font-bold text-slate-600">Form title</label>
                                <input id="form-title" type="text" name="title" value="{{ $formTitle }}" class="app-input mt-1.5" placeholder="e.g. Client intake form" required maxlength="255">
                            </div>
                            <div>
                                <label for="form-description" class="text-xs font-bold text-slate-600">Short description</label>
                                <textarea id="form-description" name="description" rows="1" class="app-input mt-1.5 min-h-[42px] resize-none" placeholder="Tell people what this form is for">{{ $formDescription }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="app-surface overflow-hidden">
                        <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-sm font-extrabold text-slate-950">Form canvas</h2>
                                        <span id="field-count" class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500">0 fields</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500">Select a field to edit its settings.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-1">
                                <button type="button" id="undo-builder" class="app-icon-button" title="Undo" aria-label="Undo" disabled>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M7 6L3.5 9.5L7 13M4 9.5H11.5C14.3 9.5 16.5 11.7 16.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <button type="button" id="redo-builder" class="app-icon-button" title="Redo" aria-label="Redo" disabled>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M13 6L16.5 9.5L13 13M16 9.5H8.5C5.7 9.5 3.5 11.7 3.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <span class="mx-1 h-5 w-px bg-slate-200"></span>
                                <button type="button" id="validate-canvas" class="app-button-secondary !border-transparent !px-2.5 !py-2 !shadow-none" title="Validate form">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 2.8L16 5.2V9.8C16 13.5 13.5 16.5 10 17.5C6.5 16.5 4 13.5 4 9.8V5.2L10 2.8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7 10L9 12L13 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span class="hidden sm:inline">Validate</span>
                                </button>
                                <button type="button" id="toggle-json" class="app-button-secondary !border-transparent !px-2.5 !py-2 !shadow-none" title="Edit JSON">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M7 4L3 10L7 16M13 4L17 10L13 16M11 3L9 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span class="hidden sm:inline">JSON</span>
                                </button>
                            </div>
                        </div>

                        <div class="builder-grid-bg p-3 sm:p-6">
                            <div class="mx-auto max-w-3xl rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] sm:p-8">
                                <div class="border-b border-slate-100 pb-6">
                                    <div class="flex items-start gap-4">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M5 4H15V16H5V4Z" stroke="currentColor" stroke-width="1.5"/><path d="M8 8H12M8 11H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <h3 id="canvas-form-title" class="break-words text-xl font-extrabold tracking-tight text-slate-950">Untitled form</h3>
                                            <p id="canvas-form-description" class="mt-1.5 break-words text-sm leading-6 text-slate-500">Add a short description to help respondents understand this form.</p>
                                        </div>
                                    </div>
                                </div>

                                <div id="field-canvas" class="mt-6 min-h-[340px] space-y-4" aria-live="polite"></div>

                                <div id="canvas-submit-preview" class="mt-6 hidden border-t border-slate-100 pt-6">
                                    <button type="button" disabled class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white opacity-80">Submit response</button>
                                    <p class="mt-2 text-[11px] text-slate-400">Preview only — responses are not submitted in the builder.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="app-surface self-start overflow-hidden xl:sticky xl:top-[5.25rem] xl:max-h-[calc(100vh-6.5rem)]">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4">
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-950">Properties</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Configure the selected field</p>
                        </div>
                        <span id="selected-field-type" class="hidden rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-extrabold uppercase tracking-wide text-indigo-600"></span>
                    </div>
                    <div id="field-details" class="builder-scrollbar max-h-[calc(100vh-11rem)] overflow-y-auto p-4">
                        <div id="inspector-empty" class="flex min-h-[300px] flex-col items-center justify-center px-5 text-center">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M4 4H16V16H4V4Z" stroke="currentColor" stroke-width="1.5" stroke-dasharray="2.5 2.5"/><path d="M7 8H13M7 11H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                            </span>
                            <h3 class="mt-4 text-sm font-extrabold text-slate-800">Select a field</h3>
                            <p class="mt-1.5 text-xs leading-5 text-slate-500">Click any field on the canvas to edit its label, placeholder, options, and validation.</p>
                        </div>
                        <div id="field-properties" class="hidden space-y-5"></div>
                    </div>
                </aside>
            </div>
        </div>
    </form>

    <dialog id="json-dialog" class="w-[min(920px,calc(100vw-2rem))] rounded-3xl bg-transparent p-0 backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm">
        <div class="dialog-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-slate-950 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M7 4L3 10L7 16M13 4L17 10L13 16M11 3L9 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-950">JSON schema</h2>
                            <p class="text-xs text-slate-500">Advanced mode. Changes apply to the visual canvas.</p>
                        </div>
                    </div>
                </div>
                <button type="button" id="close-json-dialog" class="app-icon-button" aria-label="Close JSON editor">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none"><path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="p-5 sm:p-6">
                <textarea id="json-editor" rows="20" spellcheck="false" class="block w-full rounded-2xl border border-slate-200 bg-slate-950 p-4 font-mono text-xs leading-6 text-slate-100 shadow-inner focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15"></textarea>
                <div id="json-feedback" class="mt-3 flex items-center gap-2 text-xs font-medium text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                    Edit the JSON directly, then apply it to the canvas.
                </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <button type="button" id="format-json" class="app-button-secondary">Format JSON</button>
                <div class="flex gap-2">
                    <button type="button" id="cancel-json" class="app-button-secondary flex-1 sm:flex-none">Cancel</button>
                    <button type="button" id="apply-json" class="app-button-primary flex-1 sm:flex-none">Apply changes</button>
                </div>
            </div>
        </div>
    </dialog>

    <div id="builder-toast" class="pointer-events-none fixed bottom-5 right-5 z-50 hidden max-w-sm rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 text-sm font-semibold text-white shadow-2xl" role="status" aria-live="polite"></div>
</div>
