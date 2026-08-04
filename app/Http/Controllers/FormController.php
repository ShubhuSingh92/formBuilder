<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\FormSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::where('user_id', auth()->id())->latest()->paginate(10);

        return view('forms.index', compact('forms'));
    }

    public function create()
    {
        return view('forms.builder');
    }

    public function store(Request $request, FormSchemaService $schemaService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'schema' => ['required'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        if (is_string($data['schema'])) {
            $data['schema'] = json_decode($data['schema'], true) ?? [];
        }

        $normalized = $schemaService->normalizeSchema($data['schema']);
        $validation = $schemaService->validateSchema($normalized);

        if (!$validation['valid']) {
            return back()->withErrors(['schema' => 'The schema is invalid: '.implode(', ', $validation['errors'])])->withInput();
        }

        $form = Form::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'schema' => $normalized,
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'status' => 'draft',
            'is_public' => $request->boolean('is_public', true),
            'settings' => [
                'allow_csv_export' => true,
                'allow_ai_import' => true,
            ],
        ]);

        $publicUrl = $form->is_public ? route('forms.public', $form->slug) : null;

        return redirect()->route('forms.edit', $form)
            ->with('status', 'Form created successfully.')
            ->with('public_url', $publicUrl);
    }

    public function edit(Form $form)
    {
        abort_unless($form->user_id === auth()->id(), 403);

        return view('forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form, FormSchemaService $schemaService)
    {
        abort_unless($form->user_id === auth()->id(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'schema' => ['required'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        if (is_string($data['schema'])) {
            $data['schema'] = json_decode($data['schema'], true) ?? [];
        }

        $normalized = $schemaService->normalizeSchema($data['schema']);
        $validation = $schemaService->validateSchema($normalized);

        if (!$validation['valid']) {
            return back()->withErrors(['schema' => 'The schema is invalid: '.implode(', ', $validation['errors'])])->withInput();
        }

        $form->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'schema' => $normalized,
            'is_public' => $request->boolean('is_public', true),
        ]);

        return back()->with('status', 'Form updated.');
    }

    public function preview(Form $form)
    {
        abort_unless($form->user_id === auth()->id() || $form->is_public, 403);

        return view('forms.preview', compact('form'));
    }

    public function submissions(Form $form)
    {
        abort_unless($form->user_id === auth()->id(), 403);

        $submissions = $form->submissions()->latest()->paginate(10);

        return view('forms.submissions', compact('form', 'submissions'));
    }

    public function submit(Request $request, Form $form)
    {
        abort_unless($form->is_public || $form->user_id === auth()->id(), 403);

        [$rules, $attributes] = $this->submissionValidationRules($form);
        $request->validate($rules, [], $attributes);

        $payload = [];

        foreach ($form->schema as $field) {
            $key = (string) ($field['key'] ?? '');
            $type = strtolower((string) ($field['type'] ?? 'text'));

            if ($key === '' || in_array($type, ['section_heading', 'section', 'heading'], true)) {
                continue;
            }

            if (in_array($type, ['file', 'upload', 'file_upload'], true)) {
                $uploadedFile = $request->file($key);

                if (!$uploadedFile) {
                    $payload[$key] = null;
                    continue;
                }

                $path = $uploadedFile->store("form-submissions/{$form->id}", 'local');

                abort_unless($path, 500, 'The uploaded file could not be stored.');

                $payload[$key] = [
                    'kind' => 'file',
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'size' => $uploadedFile->getSize(),
                ];

                continue;
            }

            $payload[$key] = $request->input($key);
        }

        FormSubmission::create([
            'form_id' => $form->id,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'submitted',
        ]);

        return redirect()->back()->with('status', 'Your response has been recorded.');
    }

    public function downloadSubmissionFile(Form $form, FormSubmission $submission, string $field)
    {
        abort_unless($form->user_id === auth()->id(), 403);
        abort_unless($submission->form_id === $form->id, 404);

        $file = $submission->payload[$field] ?? null;

        abort_unless(is_array($file) && ($file['kind'] ?? null) === 'file', 404);

        $disk = (string) ($file['disk'] ?? 'local');
        $path = (string) ($file['path'] ?? '');
        $expectedPrefix = "form-submissions/{$form->id}/";

        abort_unless($path !== '' && str_starts_with($path, $expectedPrefix), 404);
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->download(
            $path,
            (string) ($file['original_name'] ?? basename($path))
        );
    }

    public function share(Form $form)
    {
        abort_unless($form->user_id === auth()->id(), 403);

        return redirect()->route('forms.public', $form->slug);
    }

    public function publicView($slug)
    {
        $form = Form::where('slug', $slug)->where('is_public', true)->firstOrFail();

        return view('forms.public', compact('form'));
    }

    private function submissionValidationRules(Form $form): array
    {
        $rules = [];
        $attributes = [];

        foreach ($form->schema as $field) {
            $key = (string) ($field['key'] ?? '');
            $type = strtolower((string) ($field['type'] ?? 'text'));

            if ($key === '' || in_array($type, ['section_heading', 'section', 'heading'], true)) {
                continue;
            }

            $required = (bool) ($field['required'] ?? false);
            $options = array_values(array_filter(array_map('strval', $field['options'] ?? []), fn ($option) => $option !== ''));
            $fieldRules = [$required ? 'required' : 'nullable'];

            switch ($type) {
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:255';
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'dropdown':
                case 'select':
                case 'radio':
                case 'rating':
                    $fieldRules[] = 'string';
                    if ($options !== []) {
                        $fieldRules[] = Rule::in($options);
                    }
                    break;

                case 'checkbox':
                    $fieldRules[] = 'array';
                    if ($required) {
                        $fieldRules[] = 'min:1';
                    }
                    $rules[$key.'.*'] = $options !== [] ? [Rule::in($options)] : ['string'];
                    break;

                case 'file':
                case 'upload':
                case 'file_upload':
                    $maxUploadMb = max(1, min(50, (int) ($field['max_file_size_mb'] ?? 10)));
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:'.($maxUploadMb * 1024);
                    break;

                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:10000';
                    break;

                case 'phone':
                case 'tel':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:50';
                    break;

                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:2000';
                    break;
            }

            $rules[$key] = $fieldRules;
            $attributes[$key] = (string) ($field['label'] ?? ucfirst(str_replace('_', ' ', $key)));
        }

        return [$rules, $attributes];
    }
}
