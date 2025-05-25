<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;

class AlumniRelationsOfficer extends Component
{
    public function __construct(
        public string $title = 'FuLafia | Alumni Relations Officer'
    ) {}

    public function render()
    {
        return view('components.layouts.alumni-relations-officer');
    }
} 