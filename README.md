# AI-Powered Form Builder

A Laravel 11 + Livewire + MySQL application for building, sharing, importing, and reviewing forms with AI-assisted workflows.

## Features

- Manual form creation with editable JSON schema and public fill URL
- Live preview and submission tracking
- Word and Excel import pipeline with deterministic parsing followed by draft schema generation
- AI-ready schema service and validation guardrails

## Quick start

1. Install PHP 8.2+, Composer, Node.js, and MySQL.
2. Copy .env.example to .env and configure your database credentials.
3. Run:
   - composer install
   - npm install
   - php artisan migrate
   - php artisan serve
4. Visit /register and create an account.

## Database indexes and schema notes

The main tables use indexes on user/status lookups and public slug access:
- forms: user_id + status, slug
- form_submissions: form_id + created_at, status
- import_jobs: user_id + status

## AI prompt strategy

The current implementation includes an AI-friendly schema service that validates and normalizes field definitions before persistence. In a production deployment, the AI layer would use a prompt contract like:
- System prompt: act as a senior form designer and produce a strict JSON schema.
- Output contract: array of field objects with type, key, label, required, options, and validations.
- Hallucination handling: limit supported field types to a safe whitelist and repair invalid values deterministically.
- Retries: retry once on malformed JSON and fall back to a conservative schema.

## Import strategy

- Word documents (.docx): parsed into draft text fields from the document contents.
- Excel documents (.xlsx/.xls): mapped from rows into draft text fields.
- Hybrid approach: deterministic extraction first, then AI-assisted field/type inference can be layered on top for ambiguous documents.

## Sample assets

Sample import files can be added to storage/app/samples/ for manual testing.
