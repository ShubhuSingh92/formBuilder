<?php

use App\Http\Controllers\AiFormController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchemaEditorController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
    Route::get('/forms/create', [FormController::class, 'create'])->name('forms.create');
    Route::get('/forms/builder', function () { return view('forms.builder'); })->name('forms.builder');
    Route::post('/forms', [FormController::class, 'store'])->name('forms.store');
    Route::get('/forms/{form}/edit', [FormController::class, 'edit'])->name('forms.edit');
    Route::put('/forms/{form}', [FormController::class, 'update'])->name('forms.update');
    Route::get('/forms/{form}/preview', [FormController::class, 'preview'])->name('forms.preview');
    Route::get('/forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions');
    Route::get('/forms/{form}/submissions/{submission}/files/{field}', [FormController::class, 'downloadSubmissionFile'])->name('forms.submissions.files.download');
    Route::get('/forms/{form}/share', [FormController::class, 'share'])->name('forms.share');
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/imports', [ImportController::class, 'create'])->name('imports.create');
    Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');
    Route::get('/imports/{job}', [ImportController::class, 'show'])->name('imports.show');
    Route::get('/ai-form', [AiFormController::class, 'createFromPrompt'])->name('ai.form');
    Route::post('/ai-generate', [AiFormController::class, 'generate'])->name('ai.generate');
    Route::post('/schema/validate', [SchemaEditorController::class, 'validate'])->name('schema.validate');
});

Route::get('/f/{slug}', [FormController::class, 'publicView'])->name('forms.public');
Route::post('/f/{form:slug}/submit', [FormController::class, 'submit'])->middleware('throttle:30,1')->name('forms.submit');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
