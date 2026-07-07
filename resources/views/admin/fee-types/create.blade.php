<x-alumniadmin-dashboard>
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Add fee type</h1>
                                <p class="ads-page-subtitle">Create a new fee category for templates and payment rules.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.fee-types.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to fee types
                                </a>
                            </div>
                        </div>

                        @if (session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Fee type details</h2>

                                <form action="{{ route('admin.fee-types.store') }}" method="POST">
                                    @csrf

                                    <div class="mb-3" style="max-width: 520px;">
                                        <label for="name" class="form-label">Name</label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm @error('name') is-invalid @enderror"
                                            id="name"
                                            name="name"
                                            value="{{ old('name') }}"
                                            required
                                        >
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">A descriptive name (e.g. Registration Fee).</div>
                                    </div>

                                    <div class="mb-3" style="max-width: 520px;">
                                        <label for="code" class="form-label">Code</label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm @error('code') is-invalid @enderror"
                                            id="code"
                                            name="code"
                                            value="{{ old('code') }}"
                                        >
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Unique identifier (e.g. registration). Leave blank to auto-generate from name.</div>
                                    </div>

                                    <div class="mb-3" style="max-width: 520px;">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea
                                            class="form-control form-control-sm @error('description') is-invalid @enderror"
                                            id="description"
                                            name="description"
                                            rows="3"
                                        >{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Brief description of what this fee type is for.</div>
                                    </div>

                                    <div class="mb-4" style="max-width: 520px;">
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                class="form-check-input @error('is_active') is-invalid @enderror"
                                                id="is_active"
                                                name="is_active"
                                                value="1"
                                                @checked(old('is_active', true))
                                            >
                                            <label class="form-check-label" for="is_active">Active</label>
                                            @error('is_active')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-text">Active fee types can be used when creating new fees.</div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-sm ads-btn-primary">
                                            <i data-feather="save" style="width: 14px; height: 14px;"></i>
                                            Create fee type
                                        </button>
                                        <a href="{{ route('admin.fee-types.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
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
</x-alumniadmin-dashboard>
