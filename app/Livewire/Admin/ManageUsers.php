<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.alumniadmin-dashboard', ['title' => 'Manage Users | FuLafia Alumni'])]
class ManageUsers extends Component
{
    use WithPagination;

    public $selectedUser = null;

    public $roles = [];

    public $selectedRole = '';

    public $search = '';

    public $statusFilter = '';

    public $roleFilter = '';

    public $perPage = 10;

    public $sortField = 'name';

    public $sortDirection = 'asc';

    public $isAdmin = false;

    public $isAro = false;

    public $canAssignRoles = false;

    public $canCreateUsers = false;

    protected $listeners = ['userCreated' => 'refreshUsers'];

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        $this->isAdmin = $user->hasRole('administrator');
        $this->isAro = $user->hasRole('alumni-relations-officer');
        $this->canAssignRoles = $this->isAdmin || $this->isAro;
        $this->canCreateUsers = $this->isAdmin || $this->isAro;

        if ($this->isAdmin) {
            $this->roles = Role::orderBy('name')->get();
        } elseif ($this->isAro) {
            $this->roles = Role::whereIn('name', ['alumni', 'elcom-chairman'])->orderBy('name')->get();
        } else {
            $this->roles = collect();
        }
    }

    public function getUsersProperty()
    {
        $query = User::with(['roles', 'creator']);

        if ($this->isAro && ! $this->isAdmin) {
            $query->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'alumni');
            });
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->roleFilter !== '') {
            $query->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', $this->roleFilter);
            });
        }

        $allowedSortFields = ['name', 'email', 'created_at', 'status'];
        $sortField = in_array($this->sortField, $allowedSortFields, true) ? $this->sortField : 'name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return $query
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);
    }

    public function getUserStatsProperty(): array
    {
        $baseQuery = User::query();

        if ($this->isAro && ! $this->isAdmin) {
            $baseQuery->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'alumni');
            });
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'suspended' => (clone $baseQuery)->where('status', 'suspended')->count(),
            'new_today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
        ];
    }

    public function getAssignableRolesProperty()
    {
        if ($this->isAdmin) {
            return Role::orderBy('name')->get();
        }

        if ($this->isAro) {
            return Role::whereIn('name', ['alumni', 'elcom-chairman'])->orderBy('name')->get();
        }

        return collect();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function refreshUsers(): void
    {
        $this->resetPage();
    }

    public function selectUser(User $user): void
    {
        if (! $this->canAssignRoles || ! $this->canManageUser($user)) {
            return;
        }

        $this->selectedUser = $user;
        $this->selectedRole = $user->roles->pluck('name')->first() ?? '';
        $this->dispatch('showAssignRoleModal');
    }

    public function assignRole(): void
    {
        if (! $this->canAssignRoles) {
            session()->flash('error', 'You do not have permission to assign roles.');

            return;
        }

        if (! $this->selectedUser || ! $this->selectedRole) {
            session()->flash('error', 'Please select both a user and a role.');

            return;
        }

        if (! $this->canManageUser($this->selectedUser)) {
            session()->flash('error', 'You do not have permission to manage this user.');

            return;
        }

        if ($this->isAro && ! $this->isAdmin && ! in_array($this->selectedRole, ['alumni', 'elcom-chairman'], true)) {
            session()->flash('error', 'You can only assign alumni or ELCOM chairman roles.');

            return;
        }

        switch ($this->selectedRole) {
            case 'administrator':
            case 'alumni-relations-officer':
                if ($this->selectedUser->hasRole('alumni')) {
                    $this->selectedUser->removeRole('alumni');
                }
                break;

            case 'elcom-chairman':
                if (! $this->selectedUser->hasRole('alumni')) {
                    session()->flash('error', 'ELCOM chairman must be an alumni.');

                    return;
                }

                $existingChairman = User::role('elcom-chairman')->first();
                if ($existingChairman && $existingChairman->id !== $this->selectedUser->id) {
                    session()->flash('error', 'There can only be one ELCOM chairman at a time.');

                    return;
                }
                break;

            case 'alumni':
                if ($this->selectedUser->hasRole(['administrator', 'alumni-relations-officer'])) {
                    session()->flash('error', 'Alumni cannot have administrative roles.');

                    return;
                }
                break;
        }

        try {
            $this->selectedUser->syncRoles([$this->selectedRole]);

            if ($this->selectedRole === 'elcom-chairman' && ! $this->selectedUser->hasRole('alumni')) {
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

    public function suspendUser(User $user): void
    {
        if (! $this->canAssignRoles || ! $this->canManageUser($user)) {
            session()->flash('error', 'You do not have permission to suspend this user.');

            return;
        }

        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot suspend your own account.');

            return;
        }

        try {
            $user->update(['status' => 'suspended']);
            session()->flash('message', 'User suspended successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error suspending user: ' . $e->getMessage());
        }
    }

    public function restoreUser(User $user): void
    {
        if (! $this->canAssignRoles || ! $this->canManageUser($user)) {
            session()->flash('error', 'You do not have permission to restore this user.');

            return;
        }

        try {
            $user->update(['status' => 'active']);
            session()->flash('message', 'User restored successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error restoring user: ' . $e->getMessage());
        }
    }

    public function removeUser(User $user): void
    {
        if (! $this->isAdmin) {
            session()->flash('error', 'You do not have permission to remove users.');

            return;
        }

        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot remove your own account.');

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

    protected function canManageUser(User $user): bool
    {
        if ($this->isAdmin) {
            return true;
        }

        if ($this->isAro) {
            return $user->hasRole('alumni')
                && ! $user->hasRole(['administrator', 'alumni-relations-officer']);
        }

        return false;
    }

    public function render()
    {
        return view('livewire.admin.manage-users', [
            'users' => $this->users,
            'userStats' => $this->userStats,
            'assignableRoles' => $this->assignableRoles,
            'filterRoles' => $this->isAdmin
                ? Role::orderBy('name')->get()
                : $this->assignableRoles,
        ]);
    }
}
