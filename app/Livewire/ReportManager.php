<?php

namespace App\Livewire;

use App\Models\Report;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ReportManager extends Component
{
    use WithPagination, WithFileUploads;

    public $title;
    public $content;
    public $type = 'general';
    public $status = 'draft';
    public $file;
    public $editingReportId;
    public $showForm = false;
    public $search = '';
    public $filter = 'all';

    protected $rules = [
        'title' => 'required|min:3',
        'content' => 'required|min:10',
        'type' => 'required|in:general,academic,career',
        'status' => 'required|in:draft,published,archived',
        'file' => 'nullable|file|max:10240' // 10MB max
    ];

    public function render()
    {
        $reports = Report::where('user_id', auth()->id())
            ->when($this->search, function($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('content', 'like', '%' . $this->search . '%');
            })
            ->when($this->filter !== 'all', function($query) {
                $query->where('status', $this->filter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.report-manager', [
            'reports' => $reports
        ])->layout('layouts.alumni');
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $report = Report::findOrFail($id);
        $this->editingReportId = $report->id;
        $this->title = $report->title;
        $this->content = $report->content;
        $this->type = $report->type;
        $this->status = $report->status;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'user_id' => auth()->id()
        ];

        if ($this->file) {
            $path = $this->file->store('reports', 'public');
            $data['file_path'] = $path;
        }

        if ($this->editingReportId) {
            $report = Report::find($this->editingReportId);
            if ($report->file_path && $this->file) {
                Storage::disk('public')->delete($report->file_path);
            }
            $report->update($data);
            session()->flash('message', 'Report updated successfully.');
        } else {
            Report::create($data);
            session()->flash('message', 'Report created successfully.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete($id)
    {
        $report = Report::findOrFail($id);
        if ($report->file_path) {
            Storage::disk('public')->delete($report->file_path);
        }
        $report->delete();
        session()->flash('message', 'Report deleted successfully.');
    }

    public function download($id)
    {
        $report = Report::findOrFail($id);
        if ($report->file_path) {
            return Storage::disk('public')->download($report->file_path);
        }
        return null;
    }

    private function resetForm()
    {
        $this->editingReportId = null;
        $this->title = '';
        $this->content = '';
        $this->type = 'general';
        $this->status = 'draft';
        $this->file = null;
    }
} 