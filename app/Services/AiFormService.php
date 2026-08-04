<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiFormService
{
    public function generateSchema(string $prompt, ?array $existingSchema = null, string $mode = 'create'): array
    {
        $provider = env('AI_PROVIDER', 'openrouter');
        $key = env('OPENROUTER_API_KEY') ?: env('GROQ_API_KEY');

        if ($key) {
            $result = $this->callProvider($prompt, $existingSchema, $mode, $provider, $key);
            if ($result['ok']) {
                return $result['schema'];
            }
        }

        return $this->buildFallbackSchema($prompt, $existingSchema, $mode);
    }

    public function callProvider(string $prompt, ?array $existingSchema, string $mode, string $provider, string $key): array
    {
        $payload = [
            'model' => $provider === 'groq' ? 'llama-3.1-8b-instant' : 'openai/gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a senior form design assistant. Return ONLY a JSON object with a "schema" array of form field objects. Each field must have type, key, label, required, placeholder, help_text, default, options, and validations. Supported types: text, textarea, number, email, phone, date, dropdown, radio, checkbox, file, section_heading, rating. Keep the schema valid and concise.',
                ],
                [
                    'role' => 'user',
                    'content' => ($mode === 'edit' && !empty($existingSchema) ? 'Edit the following existing schema to satisfy this request: '.json_encode($existingSchema).'\n' : '').'User request: '.$prompt,
                ],
            ],
            'temperature' => 0.2,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($provider === 'groq' ? 'https://api.groq.com/openai/v1/chat/completions' : 'https://openrouter.ai/api/v1/chat/completions', $payload);

        if ($response->failed()) {
            return ['ok' => false, 'schema' => []];
        }

        $content = $response->json('choices.0.message.content') ?? '';
        $decoded = json_decode($content, true);
        $schema = $decoded['schema'] ?? $decoded['fields'] ?? null;

        if (!is_array($schema)) {
            return ['ok' => false, 'schema' => []];
        }

        return ['ok' => true, 'schema' => $schema];
    }

    public function buildFallbackSchema(string $prompt, ?array $existingSchema = null, string $mode = 'create'): array
    {
        $words = preg_split('/\s+/', strtolower($prompt));
        $schema = [];

        if ($mode === 'edit' && is_array($existingSchema) && !empty($existingSchema)) {
            $schema = $existingSchema;
        }

        $hasEmail = str_contains($prompt, 'email');
        $hasPhone = str_contains($prompt, 'phone');
        $hasResume = str_contains($prompt, 'resume') || str_contains($prompt, 'cv');
        $hasSkills = str_contains($prompt, 'skill');
        $hasEducation = str_contains($prompt, 'education') || str_contains($prompt, 'degree');

        if (!empty($schema)) {
            $schema[] = [
                'type' => 'text',
                'key' => 'notes',
                'label' => 'Additional details',
                'required' => false,
                'placeholder' => 'Add any extra context',
                'help_text' => 'Share anything important not captured above.',
                'default' => '',
                'options' => [],
                'validations' => [],
            ];

            return $schema;
        }

        $schema[] = [
            'type' => 'section_heading',
            'key' => 'overview',
            'label' => 'Overview',
            'required' => false,
            'placeholder' => '',
            'help_text' => '',
            'default' => '',
            'options' => [],
            'validations' => [],
        ];

        $schema[] = [
            'type' => 'text',
            'key' => 'full_name',
            'label' => 'Full name',
            'required' => true,
            'placeholder' => 'Enter your full name',
            'help_text' => 'We will use this for your record.',
            'default' => '',
            'options' => [],
            'validations' => ['required'],
        ];

        if ($hasEmail || in_array('email', $words, true)) {
            $schema[] = [
                'type' => 'email',
                'key' => 'email',
                'label' => 'Email address',
                'required' => true,
                'placeholder' => 'name@example.com',
                'help_text' => 'We will contact you here.',
                'default' => '',
                'options' => [],
                'validations' => ['required', 'email'],
            ];
        }

        if ($hasPhone || in_array('phone', $words, true)) {
            $schema[] = [
                'type' => 'phone',
                'key' => 'phone',
                'label' => 'Phone number',
                'required' => false,
                'placeholder' => 'Enter your phone number',
                'help_text' => 'Optional but helpful.',
                'default' => '',
                'options' => [],
                'validations' => ['required'],
            ];
        }

        if ($hasEducation || in_array('education', $words, true)) {
            $schema[] = [
                'type' => 'textarea',
                'key' => 'education_history',
                'label' => 'Education history',
                'required' => false,
                'placeholder' => 'Share your recent education background',
                'help_text' => 'A brief summary is enough.',
                'default' => '',
                'options' => [],
                'validations' => [],
            ];
        }

        if ($hasSkills || in_array('skills', $words, true)) {
            $schema[] = [
                'type' => 'checkbox',
                'key' => 'skills',
                'label' => 'Skills',
                'required' => false,
                'placeholder' => '',
                'help_text' => 'Choose what you bring to the table.',
                'default' => '',
                'options' => ['Leadership', 'Design', 'Backend', 'Frontend', 'Communication'],
                'validations' => [],
            ];
        }

        if ($hasResume || str_contains($prompt, 'upload')) {
            $schema[] = [
                'type' => 'file',
                'key' => 'resume',
                'label' => 'Resume upload',
                'required' => false,
                'placeholder' => '',
                'help_text' => 'Upload your resume or portfolio.',
                'default' => '',
                'options' => [],
                'validations' => ['file_types:pdf,docx'],
            ];
        }

        $schema[] = [
            'type' => 'textarea',
            'key' => 'additional_notes',
            'label' => 'Additional notes',
            'required' => false,
            'placeholder' => 'Anything else you want to share?',
            'help_text' => 'Optional.',
            'default' => '',
            'options' => [],
            'validations' => [],
        ];

        return $schema;
    }
}
