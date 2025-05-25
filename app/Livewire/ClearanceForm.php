<?php

namespace App\Livewire;

use App\Models\Report;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ClearanceForm extends Component
{
    public $alumni;

    public function mount()
    {
        // Load the authenticated user's alumni data
        $this->alumni = Auth::user()->alumni;
        
        if (!$this->alumni) {
            session()->flash('error', 'Alumni information not found. Please complete your profile first.');
            return redirect()->route('alumni.bio-data');
        }
    }

    public function generateReport()
    {
        $data = [
            'title' => 'Clearance Form Report',
            'content' => $this->formatReportContent(),
            'type' => 'clearance',
            'status' => 'draft',
            'metadata' => [
                'surname' => $this->alumni->surname,
                'firstname' => $this->alumni->firstname,
                'title' => $this->alumni->title,
                'matriculation_number' => $this->alumni->matriculation_number,
                'date_of_birth' => $this->alumni->date_of_birth,
                'lga' => $this->alumni->lga,
                'state_of_origin' => $this->alumni->state_of_origin,
                'nationality' => $this->alumni->nationality,
                'contact_address' => $this->alumni->contact_address,
                'email' => $this->alumni->email,
                'phone' => $this->alumni->phone,
                'year_of_entry' => $this->alumni->year_of_entry,
                'year_of_graduation' => $this->alumni->year_of_graduation,
                'department' => $this->alumni->department,
                'faculty' => $this->alumni->faculty,
                'qualification_type' => $this->alumni->qualification_type,
                'qualification_detail' => $this->alumni->qualification_detail,
                'present_employer' => $this->alumni->present_employer,
                'present_post' => $this->alumni->present_post,
                'professional_bodies' => $this->alumni->professional_bodies,
                'student_responsibilities' => $this->alumni->student_responsibilities,
                'hobbies' => $this->alumni->hobbies,
                'additional_info' => $this->alumni->additional_info
            ]
        ];

        Report::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'],
            'status' => $data['status'],
            'metadata' => $data['metadata']
        ]);

        session()->flash('message', 'Clearance form report generated successfully.');
        return redirect()->route('reports');
    }

    private function formatReportContent()
    {
        return view('reports.clearance-form', [
            'data' => [
                'surname' => $this->alumni->surname,
                'firstname' => $this->alumni->firstname,
                'title' => $this->alumni->title,
                'matriculation_number' => $this->alumni->matriculation_number,
                'date_of_birth' => $this->alumni->date_of_birth,
                'lga' => $this->alumni->lga,
                'state_of_origin' => $this->alumni->state_of_origin,
                'nationality' => $this->alumni->nationality,
                'contact_address' => $this->alumni->contact_address,
                'email' => $this->alumni->email,
                'phone' => $this->alumni->phone,
                'year_of_entry' => $this->alumni->year_of_entry,
                'year_of_graduation' => $this->alumni->year_of_graduation,
                'department' => $this->alumni->department,
                'faculty' => $this->alumni->faculty,
                'qualification_type' => $this->alumni->qualification_type,
                'qualification_detail' => $this->alumni->qualification_detail,
                'present_employer' => $this->alumni->present_employer,
                'present_post' => $this->alumni->present_post,
                'professional_bodies' => $this->alumni->professional_bodies,
                'student_responsibilities' => $this->alumni->student_responsibilities,
                'hobbies' => $this->alumni->hobbies,
                'additional_info' => $this->alumni->additional_info
            ]
        ])->render();
    }

    public function render()
    {
        return view('livewire.clearance-form', [
            'alumni' => $this->alumni
        ])->layout('layouts.alumni', [
            'title' => 'Clearance Form'
        ]);
    }
} 