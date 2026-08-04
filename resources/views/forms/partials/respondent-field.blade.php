@php
    $type = strtolower((string) ($field['type'] ?? 'text'));
    $key = (string) ($field['key'] ?? 'field_'.$index);
    $label = (string) ($field['label'] ?? ucfirst(str_replace('_', ' ', $key)));
    $placeholder = (string) ($field['placeholder'] ?? '');
    $helpText = (string) ($field['help_text'] ?? '');
    $required = (bool) ($field['required'] ?? false);
    $options = array_values(array_filter(array_map('strval', $field['options'] ?? []), fn ($option) => $option !== ''));
    $fieldId = 'field-'.$index.'-'.\Illuminate\Support\Str::slug($key);
    $error = $errors->first($key);
    $baseControl = 'respondent-control'.($error ? ' respondent-control-error' : '');
    $fileTypes = $field['accepted_file_types'] ?? $field['accept'] ?? '';
    $fileAccept = is_array($fileTypes) ? implode(',', $fileTypes) : (string) $fileTypes;
    $maxUploadMb = max(1, min(50, (int) ($field['max_file_size_mb'] ?? 10)));
@endphp

@if (in_array($type, ['section_heading', 'section', 'heading'], true))
    <section class="respondent-section-heading" aria-labelledby="{{ $fieldId }}">
        <div class="flex items-center gap-3">
            <span class="h-px flex-1 bg-slate-200"></span>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] text-indigo-600">Section</span>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>
        <h2 id="{{ $fieldId }}" class="mt-5 text-xl font-extrabold tracking-tight text-slate-950">{{ $label }}</h2>
        @if ($helpText)
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $helpText }}</p>
        @endif
    </section>
@else
    <div class="respondent-field" data-response-field data-field-type="{{ $type }}">
        <div class="mb-3 flex items-start justify-between gap-4">
            <div>
                <label @if (!in_array($type, ['radio', 'checkbox', 'rating'], true)) for="{{ $fieldId }}" @endif class="block text-sm font-bold leading-6 text-slate-800">
                    {{ $label }}
                    @if ($required)
                        <span class="ml-0.5 text-rose-500" aria-label="required">*</span>
                    @endif
                </label>
                @if ($helpText && !in_array($type, ['file', 'upload', 'file_upload'], true))
                    <p id="{{ $fieldId }}-help" class="mt-1 text-xs leading-5 text-slate-500">{{ $helpText }}</p>
                @endif
            </div>
            <span class="shrink-0 text-[11px] font-semibold text-slate-400">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        @switch($type)
            @case('textarea')
                <textarea id="{{ $fieldId }}" name="{{ $key }}" rows="5" class="{{ $baseControl }} resize-y" placeholder="{{ $placeholder ?: 'Type your answer' }}" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>{{ old($key, $field['default'] ?? '') }}</textarea>
                @break

            @case('number')
                <input id="{{ $fieldId }}" type="number" name="{{ $key }}" value="{{ old($key, $field['default'] ?? '') }}" class="{{ $baseControl }}" placeholder="{{ $placeholder ?: '0' }}" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                @break

            @case('email')
                <input id="{{ $fieldId }}" type="email" name="{{ $key }}" value="{{ old($key, $field['default'] ?? '') }}" class="{{ $baseControl }}" placeholder="{{ $placeholder ?: 'name@example.com' }}" autocomplete="email" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                @break

            @case('phone')
            @case('tel')
                <input id="{{ $fieldId }}" type="tel" name="{{ $key }}" value="{{ old($key, $field['default'] ?? '') }}" class="{{ $baseControl }}" placeholder="{{ $placeholder ?: '+1 (555) 000-0000' }}" autocomplete="tel" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                @break

            @case('date')
                <input id="{{ $fieldId }}" type="date" name="{{ $key }}" value="{{ old($key, $field['default'] ?? '') }}" class="{{ $baseControl }}" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                @break

            @case('dropdown')
            @case('select')
                <div class="relative">
                    <select id="{{ $fieldId }}" name="{{ $key }}" class="{{ $baseControl }} appearance-none pr-11" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                        <option value="">{{ $placeholder ?: 'Select an option' }}</option>
                        @foreach ($options as $option)
                            <option value="{{ $option }}" @selected((string) old($key, $field['default'] ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                @break

            @case('radio')
                <fieldset @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                    <legend class="sr-only">{{ $label }}</legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @forelse ($options as $option)
                            <label class="respondent-choice">
                                <input type="radio" name="{{ $key }}" value="{{ $option }}" class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked((string) old($key, $field['default'] ?? '') === $option) @if ($required) required @endif>
                                <span>{{ $option }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-400">No options have been configured.</p>
                        @endforelse
                    </div>
                </fieldset>
                @break

            @case('checkbox')
                @php $selectedOptions = (array) old($key, (array) ($field['default'] ?? [])); @endphp
                <fieldset @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
                    <legend class="sr-only">{{ $label }}</legend>
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @forelse ($options as $option)
                            <label class="respondent-choice">
                                <input type="checkbox" name="{{ $key }}[]" value="{{ $option }}" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(in_array($option, $selectedOptions, true))>
                                <span>{{ $option }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-400">No options have been configured.</p>
                        @endforelse
                    </div>
                </fieldset>
                @break

            @case('rating')
                <fieldset>
                    <legend class="sr-only">{{ $label }}</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($options ?: ['1', '2', '3', '4', '5'] as $option)
                            <label class="group cursor-pointer">
                                <input type="radio" name="{{ $key }}" value="{{ $option }}" class="peer sr-only" @checked((string) old($key, $field['default'] ?? '') === $option) @if ($required) required @endif>
                                <span class="grid h-11 min-w-11 place-items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-extrabold text-slate-600 shadow-sm transition group-hover:-translate-y-0.5 group-hover:border-indigo-300 group-hover:text-indigo-600 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-indigo-500/20">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                @break

            @case('file')
            @case('upload')
            @case('file_upload')
                <label class="respondent-file-drop {{ $error ? 'border-rose-300 bg-rose-50/40' : '' }}" data-file-drop>
                    <input id="{{ $fieldId }}" type="file" name="{{ $key }}" class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0" @if ($fileAccept) accept="{{ $fileAccept }}" @endif @if ($required) required @endif data-file-input>
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 15V4M7.5 8.5L12 4L16.5 8.5M5 14V19C5 19.6 5.4 20 6 20H18C18.6 20 19 19.6 19 19V14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="min-w-0 text-left">
                        <span class="block text-sm font-bold text-slate-800" data-file-name>Choose a file <span class="font-medium text-indigo-600">or drag it here</span></span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500" data-file-help>{{ $helpText ?: 'Up to '.$maxUploadMb.' MB per file.' }}</span>
                    </span>
                </label>
                @break

            @default
                <input id="{{ $fieldId }}" type="text" name="{{ $key }}" value="{{ old($key, $field['default'] ?? '') }}" class="{{ $baseControl }}" placeholder="{{ $placeholder ?: 'Type your answer' }}" @if ($required) required @endif @if ($helpText) aria-describedby="{{ $fieldId }}-help" @endif>
        @endswitch

        @if ($error)
            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-rose-600" role="alert">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M10 6.5V10.5M10 13.5V13.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                {{ $error }}
            </p>
        @endif
    </div>
@endif
