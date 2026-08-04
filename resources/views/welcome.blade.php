<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A Laravel and Livewire drag-and-drop form builder assignment.">

    <title>Form Builder Assignment</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <style>
        :root {
            color-scheme: light;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 15% 15%, rgba(99, 102, 241, 0.15), transparent 34rem),
                radial-gradient(circle at 90% 85%, rgba(14, 165, 233, 0.12), transparent 30rem),
                #f8fafc;
        }

        .page {
            width: min(100%, 760px);
        }

        .card {
            position: relative;
            overflow: hidden;
            padding: clamp(32px, 7vw, 72px);
            text-align: center;
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(18px);
        }

        .card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed, #0ea5e9);
        }

        .mark {
            width: 58px;
            height: 58px;
            margin: 0 auto 24px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            color: #ffffff;
            background: linear-gradient(145deg, #4f46e5, #7c3aed);
            box-shadow: 0 14px 30px rgba(79, 70, 229, 0.28);
        }

        .eyebrow {
            margin: 0 0 14px;
            color: #4f46e5;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        h1 {
            max-width: 620px;
            margin: 0 auto;
            font-size: clamp(36px, 7vw, 64px);
            line-height: 1.04;
            letter-spacing: -0.045em;
        }

        .description {
            max-width: 560px;
            margin: 22px auto 0;
            color: #64748b;
            font-size: clamp(16px, 2vw, 19px);
            line-height: 1.7;
        }

        .button {
            min-height: 52px;
            margin-top: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 24px;
            border-radius: 14px;
            color: #ffffff;
            background: #0f172a;
            font-size: 15px;
            font-weight: 750;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.18);
            transition: transform 160ms ease, background-color 160ms ease, box-shadow 160ms ease;
        }

        .button:hover {
            transform: translateY(-2px);
            background: #1e293b;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.23);
        }

        .button:focus-visible {
            outline: 4px solid rgba(99, 102, 241, 0.22);
            outline-offset: 4px;
        }

        .note {
            margin: 18px 0 0;
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 520px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 38px 22px;
                border-radius: 24px;
            }

            .button {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .button {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="card" aria-labelledby="page-title">
            <div class="mark" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 5.75H17M7 10.75H12M7 15.75H10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M15.25 14.25L18.75 17.75M18.75 14.25L15.25 17.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <rect x="3.75" y="2.75" width="16.5" height="18.5" rx="3.25" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </div>

            <p class="eyebrow">Laravel + Livewire Assignment</p>
            <h1 id="page-title">Laravel AI Drag-and-drop Form Builder</h1>
            <p class="description">
                Create flexible forms, arrange fields visually, publish a shareable link, and review every submission from one dashboard.
            </p>

            <a class="button" href="{{ route('forms.create') }}">
                Open Form Builder
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M5 12H19M14 7L19 12L14 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            @guest
                <p class="note">You will be asked to sign in before opening the builder.</p>
            @else
                <p class="note">Signed in as {{ auth()->user()->email }}</p>
            @endguest
        </section>
    </main>
</body>
</html>
