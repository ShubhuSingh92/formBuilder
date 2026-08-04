<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $form->title }} · {{ config('app.name', 'Form Builder') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-900 antialiased">
        <div class="relative isolate min-h-screen overflow-hidden bg-slate-50">
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[34rem] overflow-hidden bg-slate-950">
                <div class="absolute -left-32 top-16 h-80 w-80 rounded-full bg-indigo-500/25 blur-3xl"></div>
                <div class="absolute right-0 top-0 h-96 w-96 rounded-full bg-violet-500/20 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.12]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 28px 28px;"></div>
            </div>

            <header class="mx-auto flex max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
                <a href="/" class="inline-flex items-center gap-3 text-white">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 4H17C18.1 4 19 4.9 19 6V18C19 19.1 18.1 20 17 20H7C5.9 20 5 19.1 5 18V6C5 4.9 5.9 4 7 4Z" stroke="currentColor" stroke-width="1.7"/><path d="M8.5 9H15.5M8.5 13H15.5M8.5 17H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-extrabold tracking-tight">{{ config('app.name', 'Form Builder') }}</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Secure form</span>
                    </span>
                </a>
                <span class="hidden items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-300 backdrop-blur sm:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Ready for your response
                </span>
            </header>

            <main class="mx-auto grid max-w-7xl gap-8 px-4 pb-16 pt-7 sm:px-6 sm:pt-12 lg:grid-cols-[minmax(0,0.72fr)_minmax(0,1.28fr)] lg:gap-12 lg:px-8 lg:pb-24">
                <aside class="self-start text-white lg:sticky lg:top-10">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-bold text-indigo-200 backdrop-blur">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 10L8.5 13.5L15 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Public form
                    </span>
                    <h1 class="mt-5 max-w-xl text-3xl font-extrabold leading-tight tracking-[-0.035em] sm:text-5xl lg:text-[3.35rem]">{{ $form->title }}</h1>
                    @if ($form->description)
                        <p class="mt-5 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">{{ $form->description }}</p>
                    @endif

                    <div class="mt-8 grid max-w-xl gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 text-indigo-200">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 5V10L13 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold text-slate-400">Estimated time</p>
                                    <p class="mt-0.5 text-sm font-bold text-white">{{ max(1, (int) ceil(count($form->schema ?? []) / 4)) }} min</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur">
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 text-emerald-200">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 3L16 5.5V9.5C16 13.3 13.5 16.2 10 17.5C6.5 16.2 4 13.3 4 9.5V5.5L10 3Z" stroke="currentColor" stroke-width="1.5"/><path d="M7.5 10L9.2 11.7L12.8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold text-slate-400">Your response</p>
                                    <p class="mt-0.5 text-sm font-bold text-white">Submitted securely</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="relative">
                    @if (session('status'))
                        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-lg shadow-emerald-950/5" role="status">
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-600 text-white">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 10L8.5 13.5L15 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-emerald-950">Response submitted</p>
                                    <p class="mt-1 text-xs leading-5 text-emerald-800">{{ session('status') }} Thank you for taking the time.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-hidden rounded-[1.75rem] border border-white/80 bg-white shadow-2xl shadow-slate-950/15 ring-1 ring-slate-900/5">
                        <div class="border-b border-slate-100 bg-gradient-to-r from-white to-indigo-50/50 px-5 py-5 sm:px-8">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-indigo-600">Your response</p>
                                    <h2 class="mt-1 text-lg font-extrabold tracking-tight text-slate-950">Tell us what you think</h2>
                                </div>
                                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-950 text-white shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 4H16V16H4V4Z" stroke="currentColor" stroke-width="1.5"/><path d="M7 8H13M7 11H13M7 14H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="p-5 sm:p-8 lg:p-10">
                            @include('forms.partials.respondent-form')
                        </div>
                    </div>

                    <p class="mt-5 text-center text-xs leading-5 text-slate-500">Never share passwords, payment details, or other highly sensitive information through a public form.</p>
                </section>
            </main>
        </div>
    </body>
</html>
