<div class="pt-7">
    <div class="card shadow-sm" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0">Create New User</h6>
            <a href="{{ route('admin.users') }}" class="btn btn-outline-primary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to Users
            </a>
        </div>
        <div class="card-body p-3">
            <form wire:submit.prevent="createUser">
                <div class="mb-3">
                    <label for="name" class="form-label small">Full Name</label>
                    <input type="text" wire:model="name" id="name" class="form-control form-control-sm" placeholder="Enter full name">
                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small">Email Address</label>
                    <input type="email" wire:model="email" id="email" class="form-control form-control-sm" placeholder="Enter email address">
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small">Password</label>
                    <input type="password" wire:model="password" id="password" class="form-control form-control-sm" placeholder="Enter password">
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label small">Role</label>
                    <select wire:model="role" id="role" class="form-select form-select-sm">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm">Create User</button>
                </div>
            </form>

            @if(session()->has('message'))
                <div class="alert alert-success mt-3 py-2">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>