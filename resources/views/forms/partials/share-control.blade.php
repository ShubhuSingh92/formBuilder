@php
    $compact = $compact ?? false;
    $shareUrl = route('forms.public', $form->slug);
@endphp

@if ($form->is_public)
    <div class="{{ $compact ? 'mt-3' : 'mt-5' }}" data-share-control>
        <div class="flex min-w-0 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1.5">
            <span class="hidden h-8 w-8 shrink-0 place-items-center rounded-lg bg-white text-indigo-600 shadow-sm sm:grid">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7.5 11.5L12.5 6.5M6.5 7.5L8 6C9.4 4.6 11.6 4.6 13 6C14.4 7.4 14.4 9.6 13 11L11.5 12.5M13.5 12.5L12 14C10.6 15.4 8.4 15.4 7 14C5.6 12.6 5.6 10.4 7 9L8.5 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </span>
            <input
                type="text"
                value="{{ $shareUrl }}"
                readonly
                aria-label="Public form share URL"
                class="min-w-0 flex-1 border-0 bg-transparent px-2 py-1.5 font-mono text-xs text-slate-600 shadow-none focus:ring-0"
                onclick="this.select()"
            >
            <button
                type="button"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-500/20"
                data-copy-url="{{ $shareUrl }}"
                aria-label="Copy public form URL"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 6V4.8C7 3.8 7.8 3 8.8 3H15.2C16.2 3 17 3.8 17 4.8V11.2C17 12.2 16.2 13 15.2 13H14" stroke="currentColor" stroke-width="1.5"/><path d="M4.8 7H11.2C12.2 7 13 7.8 13 8.8V15.2C13 16.2 12.2 17 11.2 17H4.8C3.8 17 3 16.2 3 15.2V8.8C3 7.8 3.8 7 4.8 7Z" stroke="currentColor" stroke-width="1.5"/></svg>
                <span data-copy-label>{{ $compact ? 'Copy' : 'Copy link' }}</span>
            </button>
        </div>
        <p class="mt-1.5 hidden text-xs font-semibold text-emerald-600" data-copy-feedback role="status">URL copied to clipboard.</p>
    </div>
@else
    <div class="{{ $compact ? 'mt-3' : 'mt-5' }} flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs font-semibold text-amber-800">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="4.5" y="8" width="11" height="8" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M7 8V6.5C7 4.8 8.3 3.5 10 3.5C11.7 3.5 13 4.8 13 6.5V8" stroke="currentColor" stroke-width="1.5"/></svg>
        This form is private. Enable “Public form” in the builder to share it.
    </div>
@endif
