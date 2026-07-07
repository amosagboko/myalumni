<x-alumniadmin-dashboard title="Edit Alumni Year | FuLafia Alumni">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Edit alumni year</h1>
                                <p class="ads-page-subtitle">Update payment year {{ $alumniYear->year }} and its active status.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.payment-years.show', $alumniYear) }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="settings" style="width: 14px; height: 14px;"></i>
                                    Configure dues
                                </a>
                                <a href="{{ route('alumni-years.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to years
                                </a>
                            </div>
                        </div>

                        @if (session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card" style="max-width: 560px;">
                                <h2 class="ads-section-title">Year details</h2>

                                <form action="{{ route('alumni-years.update', $alumniYear) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="year" class="form-label">Year</label>
                                        <input
                                            type="number"
                                            name="year"
                                            id="year"
                                            value="{{ old('year', $alumniYear->year) }}"
                                            class="form-control form-control-sm @error('year') is-invalid @enderror"
                                            required
                                            min="1900"
                                            max="{{ date('Y') + 1 }}"
                                        >
                                        @error('year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start date</label>
                                        <input
                                            type="date"
                                            name="start_date"
                                            id="start_date"
                                            value="{{ old('start_date', $alumniYear->start_date->format('Y-m-d')) }}"
                                            class="form-control form-control-sm @error('start_date') is-invalid @enderror"
                                            required
                                        >
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">End date</label>
                                        <input
                                            type="date"
                                            name="end_date"
                                            id="end_date"
                                            value="{{ old('end_date', $alumniYear->end_date->format('Y-m-d')) }}"
                                            class="form-control form-control-sm @error('end_date') is-invalid @enderror"
                                            required
                                        >
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                id="is_active"
                                                class="form-check-input"
                                                {{ old('is_active', $alumniYear->is_active) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="is_active">Set as active year</label>
                                            <div class="form-text">Activating this year will deactivate all other years.</div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('alumni-years.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                        <button type="submit" class="btn btn-sm ads-btn-primary">Update year</button>
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
