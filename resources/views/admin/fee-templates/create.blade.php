<x-alumniadmin-dashboard>
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12 col-lg-10">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Create fee template</h1>
                                <p class="ads-page-subtitle">Define amount, validity, and cohort for a fee type.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.fee-templates.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to templates
                                </a>
                            </div>
                        </div>

                        @if (session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Template details</h2>

                                <div class="ads-alert mb-3" id="annual_due_hint" style="display: none;">
                                    <strong>Annual due:</strong> choose <em>All payment years</em> if the same amount applies every year, or a specific payment year.
                                    You can also configure annual dues per year under
                                    <a href="{{ route('admin.payment-years.index') }}">Dues config</a>.
                                </div>

                                <form action="{{ route('admin.fee-templates.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="fee_type_id" class="form-label">Fee type <span class="text-danger">*</span></label>
                                            <select name="fee_type_id" id="fee_type_id" class="form-select form-select-sm" required>
                                                <option value="">Select fee type</option>
                                                @foreach ($feeTypes as $feeType)
                                                    <option value="{{ $feeType->id }}" @selected(old('fee_type_id') == $feeType->id)>
                                                        {{ $feeType->name }} ({{ $feeType->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('fee_type_id')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="graduation_year" class="form-label" id="year_field_label">Graduation year <span class="text-danger">*</span></label>
                                            <select name="graduation_year" id="graduation_year" class="form-select form-select-sm" required>
                                                <option value="">Select year</option>
                                            </select>
                                            <div class="form-text" id="year_field_help"></div>
                                            @error('graduation_year')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div id="category_section" class="col-md-6 d-none">
                                            <label for="category_id" class="form-label">Alumni category <span class="text-danger">*</span></label>
                                            <select name="category_id" id="category_id" class="form-select form-select-sm">
                                                <option value="">Select category</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Required for 2025+ graduates</div>
                                            @error('category_id')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="amount" class="form-label">Amount (₦) <span class="text-danger">*</span></label>
                                            <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount') }}" class="form-control form-control-sm" required>
                                            @error('amount')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="valid_from" class="form-label">Valid from <span class="text-danger">*</span></label>
                                            <input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" class="form-control form-control-sm" required>
                                            @error('valid_from')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="valid_until" class="form-label">Valid until</label>
                                            <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until') }}" class="form-control form-control-sm">
                                            <div class="form-text">Leave empty for no expiration</div>
                                            @error('valid_until')
                                                <div class="form-text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea name="description" id="description" rows="3" class="form-control form-control-sm">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="form-text text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', true))>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-4">
                                        <button type="submit" class="btn btn-sm ads-btn-primary">
                                            <i data-feather="save" style="width: 14px; height: 14px;"></i>
                                            Create template
                                        </button>
                                        <a href="{{ route('admin.fee-templates.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
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

            const feeTypeSelect = document.getElementById('fee_type_id');
            const graduationYearSelect = document.getElementById('graduation_year');
            const categorySection = document.getElementById('category_section');
            const categorySelect = document.getElementById('category_id');
            const yearFieldLabel = document.getElementById('year_field_label');
            const yearFieldHelp = document.getElementById('year_field_help');
            const annualDueHint = document.getElementById('annual_due_hint');

            const annualDueTypeIds = @json($annualDueTypeIds);
            const paymentYears = @json($paymentYears->values());
            const onboardingYears = @json(range(date('Y') + 1, 2020));
            const selectedYear = @json(old('graduation_year'));

            function isAnnualRenewalType() {
                return annualDueTypeIds.includes(parseInt(feeTypeSelect.value, 10));
            }

            function rebuildYearOptions() {
                const current = graduationYearSelect.value;
                graduationYearSelect.innerHTML = '<option value="">Select year</option>';

                if (isAnnualRenewalType()) {
                    yearFieldLabel.innerHTML = 'Payment year <span class="text-danger">*</span>';
                    yearFieldHelp.textContent = 'Use “All payment years” for one amount across every year, or pick a specific year from Dues config.';

                    const allOpt = document.createElement('option');
                    allOpt.value = '0';
                    allOpt.textContent = 'All payment years';
                    if (String(selectedYear) === '0' || current === '0') {
                        allOpt.selected = true;
                    }
                    graduationYearSelect.appendChild(allOpt);

                    paymentYears.forEach(function (year) {
                        const opt = document.createElement('option');
                        opt.value = year;
                        opt.textContent = year + ' (payment year)';
                        if (String(selectedYear) === String(year) || current === String(year)) {
                            opt.selected = true;
                        }
                        graduationYearSelect.appendChild(opt);
                    });
                } else {
                    yearFieldLabel.innerHTML = 'Graduation year <span class="text-danger">*</span>';
                    yearFieldHelp.textContent = 'Cohort year for onboarding fees (2025+ requires a category).';

                    onboardingYears.forEach(function (year) {
                        const opt = document.createElement('option');
                        opt.value = year;
                        opt.textContent = year;
                        if (String(selectedYear) === String(year) || current === String(year)) {
                            opt.selected = true;
                        }
                        graduationYearSelect.appendChild(opt);
                    });
                }
            }

            function toggleCategorySection() {
                const year = parseInt(graduationYearSelect.value, 10);
                if (!isAnnualRenewalType() && year >= 2025) {
                    categorySection.classList.remove('d-none');
                    categorySelect.required = true;
                } else {
                    categorySection.classList.add('d-none');
                    categorySelect.required = false;
                    if (isAnnualRenewalType()) {
                        categorySelect.value = '';
                    }
                }
            }

            function refreshForm() {
                rebuildYearOptions();
                toggleCategorySection();
                if (annualDueHint) {
                    annualDueHint.style.display = isAnnualRenewalType() ? 'block' : 'none';
                }
            }

            feeTypeSelect.addEventListener('change', refreshForm);
            graduationYearSelect.addEventListener('change', toggleCategorySection);
            refreshForm();
        });
    </script>
    @endpush
</x-alumniadmin-dashboard>
