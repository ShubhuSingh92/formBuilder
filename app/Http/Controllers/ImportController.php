<?php

namespace App\Http\Controllers;

use App\Models\ImportJob;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function create()
    {
        return view('imports.create');
    }

    public function store(Request $request, ImportService $importService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:docx,xlsx,xls'],
        ]);

        $path = $request->file('file')->store('imports', 'local');

        $job = ImportJob::create([
            'user_id' => auth()->id(),
            'name' => $request->file('file')->getClientOriginalName(),
            'type' => 'document',
            'status' => 'processed',
            'file_path' => $path,
            'metadata' => ['source' => 'upload'],
        ]);

        $result = $importService->parseFile(storage_path('app/'.$path));

        $job->update([
            'result_schema' => $result['schema'],
            'message' => $result['message'],
            'status' => $result['valid'] ? 'completed' : 'failed',
        ]);

        return redirect()->route('imports.show', $job);
    }

    public function show(ImportJob $job)
    {
        abort_unless($job->user_id === auth()->id(), 403);

        return view('imports.show', compact('job'));
    }
}
