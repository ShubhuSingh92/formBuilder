<?php

namespace App\Http\Controllers;

use App\Services\FormSchemaService;
use Illuminate\Http\Request;

class SchemaEditorController extends Controller
{
    public function validate(Request $request, FormSchemaService $schemaService)
    {
        $data = $request->validate(['schema' => ['required', 'array']]);

        $normalized = $schemaService->normalizeSchema($data['schema']);
        $validation = $schemaService->validateSchema($normalized);

        return response()->json($validation);
    }
}
