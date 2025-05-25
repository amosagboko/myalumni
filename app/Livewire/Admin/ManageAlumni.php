<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.alumni-relations-officer')]
class ManageAlumni extends Component
{
    use WithPagination;

    public $search = '';
    protected $listeners = ['userCreated' => '$refresh'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function suspendUser(User $user)
    {
        try {
            $user->update(['status' => 'suspended']);
            session()->flash('message', 'User suspended successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error suspending user: ' . $e->getMessage());
        }
    }

    public function activateUser(User $user)
    {
        try {
            $user->update(['status' => 'active']);
            session()->flash('message', 'User activated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error activating user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumni');
        })->with('roles');
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        $users = $query->paginate(10);

        return view('livewire.admin.manage-alumni', [
            'users' => $users
        ]);
    }
} 