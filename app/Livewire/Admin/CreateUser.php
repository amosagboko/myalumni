<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CreateUser extends Component
{
    public $name, $email, $password, $role, $created_by;
    public $availableRoles = [];

    public function mount()
    {
        // Only show roles that can be assigned by the current user
        if (Auth::user()->hasRole('administrator')) {
            $this->availableRoles = Role::all();
        } elseif (Auth::user()->hasRole('alumni-relations-officer')) {
            $this->availableRoles = Role::whereIn('name', ['alumni'])->get();
        }
    }

    public function createUser()
    {
        $validated = $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name'
        ]);

        // Check if user has permission to assign the selected role
        if (!Auth::user()->hasRole('administrator') && $this->role !== 'alumni') {
            toastr()->error('You do not have permission to assign this role.');
            return;
        }

        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'created_by' => Auth::id(),
        ]);

        $user->sendEmailVerificationNotification();
        $user->assignRole($this->role);
        
        $this->dispatch('userCreated');
        toastr()->success('User Created successfully.');
        $this->reset(['name', 'email', 'password', 'role']);
    }

    public function render()
    {
        return view('livewire.admin.create-user', [
            'roles' => $this->availableRoles
        ])->layout('layouts.admin');
    }
}
