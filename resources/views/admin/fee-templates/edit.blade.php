<x-alumniadmin-dashboard>
	<div class="container mt-4">
		<div class="row justify-content-center">
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header bg-white d-flex align-items-center justify-content-between">
						<h5 class="mb-0">Edit Fee Template</h5>
						<a href="{{ route('admin.fee-templates.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
					</div>
					<div class="card-body">
						@if(session('error'))
							<div class="alert alert-danger" role="alert">
								{{ session('error') }}
							</div>
						@endif

						<form action="{{ route('admin.fee-templates.update', $feeTemplate) }}" method="POST">
							@csrf
							@method('PUT')

							<div class="row g-3">
								<!-- Fee Type -->
								<div class="col-md-6">
									<label for="fee_type_id" class="form-label">Fee Type *</label>
									<select name="fee_type_id" id="fee_type_id" class="form-select" required>
										<option value="">Select Fee Type</option>
										@foreach($feeTypes as $feeType)
											<option value="{{ $feeType->id }}" {{ old('fee_type_id', $feeTemplate->fee_type_id) == $feeType->id ? 'selected' : '' }}>
												{{ $feeType->name }} ({{ $feeType->code }})
											</option>
										@endforeach
									</select>
									@error('fee_type_id')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Year -->
								<div class="col-md-6">
									<label for="graduation_year" class="form-label" id="year_field_label">Graduation Year *</label>
									<select name="graduation_year" id="graduation_year" class="form-select" required>
										<option value="">Select Year</option>
									</select>
									<div class="form-text" id="year_field_help"></div>
									@error('graduation_year')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Category (onboarding 2025+ only) -->
								<div id="category_section" class="col-md-6 d-none">
									<label for="category_id" class="form-label">Alumni Category *</label>
									<select name="category_id" id="category_id" class="form-select">
										<option value="">Select Category</option>
										@foreach($categories as $category)
											<option value="{{ $category->id }}" {{ old('category_id', $feeTemplate->category_id) == $category->id ? 'selected' : '' }}>
												{{ $category->name }}
											</option>
										@endforeach
									</select>
									<div class="form-text">Required for 2025+ graduates</div>
									@error('category_id')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Amount -->
								<div class="col-md-6">
									<label for="amount" class="form-label">Amount (₦) *</label>
									<input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $feeTemplate->amount) }}" class="form-control" required>
									@error('amount')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror>
								</div>

								<!-- Valid From -->
								<div class="col-md-6">
									<label for="valid_from" class="form-label">Valid From *</label>
									<input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from', $feeTemplate->valid_from->format('Y-m-d')) }}" class="form-control" required>
									@error('valid_from')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Valid Until -->
								<div class="col-md-6">
									<label for="valid_until" class="form-label">Valid Until</label>
									<input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until', $feeTemplate->valid_until ? $feeTemplate->valid_until->format('Y-m-d') : '') }}" class="form-control">
									<div class="form-text">Leave empty for no expiration</div>
									@error('valid_until')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>
							</div>

							<!-- Description -->
							<div class="mt-3">
								<label for="description" class="form-label">Description</label>
								<textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $feeTemplate->description) }}</textarea>
								@error('description')
									<div class="form-text text-danger">{{ $message }}</div>
								@enderror
							</div>

							<!-- Active Status -->
							<div class="form-check mt-2">
								<input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $feeTemplate->is_active) ? 'checked' : '' }}>
								<label class="form-check-label" for="is_active">Active</label>
							</div>

							<div class="d-flex justify-content-end gap-2 mt-3">
								<a href="{{ route('admin.fee-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
								<button type="submit" class="btn btn-primary">Update Fee Template</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const feeTypeSelect = document.getElementById('fee_type_id');
			const graduationYearSelect = document.getElementById('graduation_year');
			const categorySection = document.getElementById('category_section');
			const categorySelect = document.getElementById('category_id');
			const yearFieldLabel = document.getElementById('year_field_label');
			const yearFieldHelp = document.getElementById('year_field_help');

			const annualDueTypeIds = @json($annualDueTypeIds);
			const paymentYears = @json($paymentYears->values());
			const onboardingYears = @json(range(date('Y') + 1, 2020));
			const selectedYear = @json(old('graduation_year', $feeTemplate->graduation_year));

			function isAnnualRenewalType() {
				return annualDueTypeIds.includes(parseInt(feeTypeSelect.value, 10));
			}

			function rebuildYearOptions() {
				const preserve = graduationYearSelect.value || String(selectedYear);
				graduationYearSelect.innerHTML = '<option value="">Select Year</option>';

				if (isAnnualRenewalType()) {
					yearFieldLabel.textContent = 'Payment Year *';
					yearFieldHelp.textContent = 'Use “All payment years” for one amount across every year, or pick a specific year from Dues Config.';

					const allOpt = document.createElement('option');
					allOpt.value = '0';
					allOpt.textContent = 'All payment years';
					if (preserve === '0') {
						allOpt.selected = true;
					}
					graduationYearSelect.appendChild(allOpt);

					paymentYears.forEach(function(year) {
						const opt = document.createElement('option');
						opt.value = year;
						opt.textContent = year + ' (payment year)';
						if (preserve === String(year)) {
							opt.selected = true;
						}
						graduationYearSelect.appendChild(opt);
					});
				} else {
					yearFieldLabel.textContent = 'Graduation Year *';
					yearFieldHelp.textContent = 'Cohort year for onboarding fees (2025+ requires a category).';

					onboardingYears.forEach(function(year) {
						const opt = document.createElement('option');
						opt.value = year;
						opt.textContent = year;
						if (preserve === String(year)) {
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

			feeTypeSelect.addEventListener('change', function() {
				rebuildYearOptions();
				toggleCategorySection();
			});
			graduationYearSelect.addEventListener('change', toggleCategorySection);
			rebuildYearOptions();
			toggleCategorySection();
		});
	</script>
</x-alumniadmin-dashboard>