<div>
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Create user</h1>
                                <p class="ads-page-subtitle">Add a new account and assign an initial role.</p>
                            </div>
                            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">
                                <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                Back to users
                            </a>
                        </div>

                        @if (session()->has('message'))
                            <div class="ads-alert ads-alert-success">{{ session('message') }}</div>
                        @endif

                        @if (session()->has('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card" style="max-width: 560px;">
                                <h2 class="ads-section-title">Account details</h2>

                                <form wire:submit.prevent="createUser">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full name</label>
                                        <input type="text" wire:model="name" id="name" class="form-control form-control-sm" placeholder="Enter full name">
                                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email address</label>
                                        <input type="email" wire:model="email" id="email" class="form-control form-control-sm" placeholder="Enter email address">
                                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" wire:model="password" id="password" class="form-control form-control-sm" placeholder="Enter password">
                                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="role" class="form-label">Role</label>
                                        <select wire:model="role" id="role" class="form-select form-select-sm">
                                            <option value="">Select role</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}">{{ \Illuminate\Support\Str::headline($role->name) }}</option>
                                            @endforeach
                                        </select>
                                        @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                        <button type="submit" class="btn btn-sm ads-btn-primary" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="createUser">Create user</span>
                                            <span wire:loading wire:target="createUser">Creating…</span>
                                        </button>
                                    </div>
                                </form>
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
</div>
