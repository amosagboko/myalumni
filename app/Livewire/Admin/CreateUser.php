<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

#[Layout('components.alumniadmin-dashboard', ['title' => 'Create User | FuLafia Alumni'])]
class CreateUser extends Component
{
    public $name;

    public $email;

    public $password;

    public $role;

    public $availableRoles = [];

    public function mount(): void
    {
        if (! Auth::user()->hasAnyRole(['administrator', 'alumni-relations-officer'])) {
            abort(403);
        }

        if (Auth::user()->hasRole('administrator')) {
            $this->availableRoles = Role::orderBy('name')->get();
        } elseif (Auth::user()->hasRole('alumni-relations-officer')) {
            $this->availableRoles = Role::whereIn('name', ['alumni'])->orderBy('name')->get();
        }
    }

    public function createUser(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        if (! Auth::user()->hasRole('administrator') && $this->role !== 'alumni') {
            session()->flash('error', 'You do not have permission to assign this role.');

            return;
        }

        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'created_by' => Auth::id(),
            'status' => 'active',
        ]);

        $user->sendEmailVerificationNotification();
        $user->assignRole($this->role);

        session()->flash('message', 'User created successfully.');
        $this->reset(['name', 'email', 'password', 'role']);

        $this->redirect(route('admin.users'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.create-user', [
            'roles' => $this->availableRoles,
        ]);
    }
}
