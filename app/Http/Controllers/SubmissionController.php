<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $ownedForms = Form::query()
            ->where('user_id', auth()->id())
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'is_public']);

        $baseQuery = FormSubmission::query()
            ->whereHas('form', fn ($query) => $query->where('user_id', auth()->id()));

        $filteredQuery = (clone $baseQuery)
            ->with(['form:id,user_id,title,slug,is_public'])
            ->when(
                $request->filled('form_id'),
                fn ($query) => $query->where('form_id', $request->integer('form_id'))
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            );

        $submissions = $filteredQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'this_week' => (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'responding_forms' => (clone $baseQuery)->distinct()->count('form_id'),
        ];

        return view('submissions.index', compact('submissions', 'ownedForms', 'stats'));
    }
}
