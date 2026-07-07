<x-alumniadmin-dashboard title="New Payment Year | FuLafia Alumni">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row justify-content-center">
                    <div class="col-lg-8">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Create payment year</h1>
                                <p class="ads-page-subtitle">Define the renewal window and optionally activate it immediately.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.payment-years.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back
                                </a>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Payment year details</h2>

                                <form action="{{ route('admin.payment-years.store') }}" method="POST">
                                    @csrf

                                    <div class="mb-3" style="max-width: 520px;">
                                        <label for="year" class="form-label">Payment year</label>
                                        <input type="number" name="year" id="year" class="form-control form-control-sm @error('year') is-invalid @enderror"
                                            value="{{ old('year', $suggestedYear) }}" min="2000" max="2100" required>
                                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="row g-3" style="max-width: 720px;">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label">Start date</label>
                                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm @error('start_date') is-invalid @enderror"
                                                value="{{ old('start_date', $suggestedYear . '-01-01') }}" required>
                                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">End date</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm @error('end_date') is-invalid @enderror"
                                                value="{{ old('end_date', $suggestedYear . '-12-31') }}" required>
                                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                                                {{ old('is_active') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Set as active payment year immediately</label>
                                        </div>
                                    </div>

                                    @if($previousYear?->annualDueTemplate())
                                        <div class="mt-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="copy_annual_due_from_previous" id="copy_annual_due_from_previous" value="1"
                                                    class="form-check-input" {{ old('copy_annual_due_from_previous', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="copy_annual_due_from_previous">
                                                    Copy annual due amount from {{ $previousYear->year }}
                                                    (₦{{ number_format($previousYear->annualDueTemplate()->amount, 2) }})
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2 mt-4">
                                        <button type="submit" class="btn btn-sm ads-btn-primary">
                                            <i data-feather="save" style="width: 14px; height: 14px;"></i>
                                            Create payment year
                                        </button>
                                        <a href="{{ route('admin.payment-years.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
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
