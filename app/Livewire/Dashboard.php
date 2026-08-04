<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\ImportJob;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $forms = Form::query()
            ->where('user_id', auth()->id())
            ->withCount('submissions')
            ->latest()
            ->take(8)
            ->get();

        $submissionQuery = FormSubmission::query()
            ->whereHas('form', fn ($query) => $query->where('user_id', auth()->id()));

        $recentSubmissions = (clone $submissionQuery)
            ->with(['form:id,user_id,title'])
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'forms' => Form::where('user_id', auth()->id())->count(),
            'submissions' => (clone $submissionQuery)->count(),
            'today' => (clone $submissionQuery)->whereDate('created_at', today())->count(),
            'imports' => ImportJob::where('user_id', auth()->id())->count(),
        ];

        return view('livewire.dashboard', compact('forms', 'recentSubmissions', 'stats'));
    }
}
