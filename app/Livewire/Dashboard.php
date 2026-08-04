<?php

namespace App\Livewire;

use App\Models\Form;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $forms = Form::where('user_id', auth()->id())->latest()->take(8)->get();

        return view('livewire.dashboard', compact('forms'));
    }
}
