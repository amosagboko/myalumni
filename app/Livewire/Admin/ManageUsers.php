<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.alumniadmin-dashboard')]
class ManageUsers extends Component
{
    use WithPagination;

    public $selectedUser = null;
    public $roles = [];
    public $selectedRole = '';
    public $search = '';
    public $statusFilter = '';
    public $isAdmin = false;
    protected $listeners = ['userCreated' => 'refreshUsers'];

    public function mount()
    {
        $this->isAdmin = Auth::user()->hasRole('administrator');
        // Get roles based on user's permissions
        if ($this->isAdmin) {
            $this->roles = Role::all();
        } else if (Auth::user()->hasRole('alumni-relations-officer')) {
            $this->roles = Role::whereIn('name', ['alumni', 'elcom-chairman'])->get();
        } else {
            $this->roles = collect();
        }
    }

    public function getUsersProperty()
    {
        $query = User::with('roles', 'creator');
        
        if (!$this->isAdmin) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', 'alumni');
            });
        }
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('roles', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }
        
        return $query->orderBy('name')->paginate(10);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectUser(User $user)
    {
        if (!$this->isAdmin) return;
        
        $this->selectedUser = $user;
        $this->selectedRole = $user->roles->pluck('name')->first() ?? '';
        $this->dispatch('showAssignRoleModal');
    }

    public function assignRole()
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser->hasRole('administrator');
        $isARO = $currentUser->hasRole('alumni-relations-officer');

        if (!$isAdmin && !$isARO) {
            session()->flash('error', 'You do not have permission to assign roles.');
            return;
        }

        if (!$this->selectedUser || !$this->selectedRole) {
            session()->flash('error', 'Please select both a user and a role.');
            return;
        }

        // Check if the user has permission to assign the selected role
        if ($isARO && !in_array($this->selectedRole, ['alumni', 'elcom-chairman'])) {
            session()->flash('error', 'You can only assign alumni or ELCOM chairman roles.');
            return;
        }

        // Role-specific validations
        switch ($this->selectedRole) {
            case 'administrator':
            case 'alumni-relations-officer':
                // These roles should not have the alumni role
                if ($this->selectedUser->hasRole('alumni')) {
                    $this->selectedUser->removeRole('alumni');
                }
                break;

            case 'elcom-chairman':
                // ELCOM chairman must be an alumni
                if (!$this->selectedUser->hasRole('alumni')) {
                    session()->flash('error', 'ELCOM chairman must be an alumni.');
                    return;
                }
                // Check if there's already an ELCOM chairman
                $existingChairman = User::role('elcom-chairman')->first();
                if ($existingChairman && $existingChairman->id !== $this->selectedUser->id) {
                    session()->flash('error', 'There can only be one ELCOM chairman at a time.');
                    return;
                }
                break;

            case 'alumni':
                // Alumni should not have administrative roles
                if ($this->selectedUser->hasRole(['administrator', 'alumni-relations-officer'])) {
                    session()->flash('error', 'Alumni cannot have administrative roles.');
                    return;
                }
                break;
        }

        try {
            // Remove all existing roles and assign the new one
            $this->selectedUser->syncRoles([$this->selectedRole]);
            
            // If the role is elcom-chairman, ensure they also have the alumni role
            if ($this->selectedRole === 'elcom-chairman' && !$this->selectedUser->hasRole('alumni')) {
                $this->selectedUser->assignRole('alumni');
            }
            
            session()->flash('message', 'Role updated successfully!');
            $this->dispatch('hideAssignRoleModal');
            $this->selectedUser = null;
            $this->selectedRole = '';
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    public function suspendUser(User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasRole(['administrator', 'alumni-relations-officer'])) {
            session()->flash('error', 'You do not have permission to suspend users.');
            return;
        }

        try {
            $user->update(['status' => 'suspended']);
            session()->flash('message', 'User suspended successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error suspending user: ' . $e->getMessage());
        }
    }

    public function restoreUser(User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasRole(['administrator', 'alumni-relations-officer'])) {
            session()->flash('error', 'You do not have permission to restore users.');
            return;
        }

        try {
            $user->update(['status' => 'active']);
            session()->flash('message', 'User restored successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error restoring user: ' . $e->getMessage());
        }
    }

    public function removeUser(User $user)
    {
        if (!$this->isAdmin) {
            session()->flash('error', 'You do not have permission to remove users.');
            return;
        }

        if ($user->hasRole(['administrator', 'alumni-relations-officer'])) {
            session()->flash('error', 'Cannot remove Administrator or Alumni Relations Officer.');
            return;
        }

        try {
            $user->delete();
            session()->flash('message', 'User removed successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing user: ' . $e->getMessage());
        }
    }

    public function getUserStats()
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'new_today' => User::whereDate('created_at', today())->count()
        ];
    }

    public function render()
    {
        return view('livewire.admin.manage-users', [
            'users' => $this->users
        ]);
    }
}
