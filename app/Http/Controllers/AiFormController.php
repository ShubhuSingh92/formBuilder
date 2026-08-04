<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Services\AiFormService;
use App\Services\FormSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiFormController extends Controller
{
    public function generate(Request $request, AiFormService $aiService, FormSchemaService $schemaService)
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'min:4'],
            'mode' => ['nullable', 'string', 'in:create,edit'],
            'form_id' => ['nullable', 'exists:forms,id'],
        ]);

        $form = null;
        if (!empty($data['form_id'])) {
            $form = Form::findOrFail($data['form_id']);
        }

        $schema = $aiService->generateSchema($data['prompt'], $form?->schema, $data['mode'] ?? 'create');
        $normalized = $schemaService->normalizeSchema($schema);
        $validation = $schemaService->validateSchema($normalized);

        if (!$validation['valid']) {
            return response()->json(['ok' => false, 'message' => 'Schema validation failed', 'errors' => $validation['errors']], 422);
        }

        return response()->json(['ok' => true, 'schema' => $normalized]);
    }

    public function createFromPrompt(Request $request)
    {
        return view('forms.ai-create');
    }
}
