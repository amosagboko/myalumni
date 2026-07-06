<div class="main-content right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-current d-flex justify-content-between align-items-center py-3">
                            <h6 class="mb-0 text-white">Create New User</h6>
                            <a href="{{ route('admin.users') }}" class="btn btn-light btn-sm">
                                <i data-feather="arrow-left" class="me-1" style="width: 14px; height: 14px;"></i>
                                Back to Users
                            </a>
                        </div>
                        <div class="card-body p-4">
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
                                            <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
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
                                <div class="alert alert-success mt-3 py-2 mb-0">{{ session('message') }}</div>
                            @endif

                            @if(session()->has('error'))
                                <div class="alert alert-danger mt-3 py-2 mb-0">{{ session('error') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>
@endpush
