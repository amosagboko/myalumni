<x-alumniadmin-dashboard>
	<div class="container mt-4">
		<div class="row justify-content-center">
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header bg-white d-flex align-items-center justify-content-between">
						<h5 class="mb-0">Create Fee Template</h5>
						<a href="{{ route('admin.fee-templates.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
					</div>
					<div class="card-body">
						@if(session('error'))
							<div class="alert alert-danger" role="alert">
								{{ session('error') }}
							</div>
						@endif

						<form action="{{ route('admin.fee-templates.store') }}" method="POST">
							@csrf

							<div class="row g-3">
								<!-- Fee Type -->
								<div class="col-md-6">
									<label for="fee_type_id" class="form-label">Fee Type *</label>
									<select name="fee_type_id" id="fee_type_id" class="form-select" required>
										<option value="">Select Fee Type</option>
										@foreach($feeTypes as $feeType)
											<option value="{{ $feeType->id }}" {{ old('fee_type_id') == $feeType->id ? 'selected' : '' }}>
												{{ $feeType->name }} ({{ $feeType->code }})
											</option>
										@endforeach
									</select>
									@error('fee_type_id')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Graduation Year -->
								<div class="col-md-6">
									<label for="graduation_year" class="form-label">Graduation Year *</label>
									<select name="graduation_year" id="graduation_year" class="form-select" required>
										<option value="">Select Year</option>
										@for($year = date('Y') + 1; $year >= 2020; $year--)
											<option value="{{ $year }}" {{ old('graduation_year') == $year ? 'selected' : '' }}>
												{{ $year }}
											</option>
										@endfor
									</select>
									@error('graduation_year')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Category (for 2025+) -->
								<div id="category_section" class="col-md-6 d-none">
									<label for="category_id" class="form-label">Alumni Category *</label>
									<select name="category_id" id="category_id" class="form-select">
										<option value="">Select Category</option>
										@foreach($categories as $category)
											<option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
									<input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount') }}" class="form-control" required>
									@error('amount')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Valid From -->
								<div class="col-md-6">
									<label for="valid_from" class="form-label">Valid From *</label>
									<input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" class="form-control" required>
									@error('valid_from')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>

								<!-- Valid Until -->
								<div class="col-md-6">
									<label for="valid_until" class="form-label">Valid Until</label>
									<input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until') }}" class="form-control">
									<div class="form-text">Leave empty for no expiration</div>
									@error('valid_until')
										<div class="form-text text-danger">{{ $message }}</div>
									@enderror
								</div>
							</div>

							<!-- Description -->
							<div class="mt-3">
								<label for="description" class="form-label">Description</label>
								<textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
								@error('description')
									<div class="form-text text-danger">{{ $message }}</div>
								@enderror>
							</div>

							<!-- Active Status -->
							<div class="form-check mt-2">
								<input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
								<label class="form-check-label" for="is_active">Active</label>
							</div>

							<div class="d-flex justify-content-end gap-2 mt-3">
								<a href="{{ route('admin.fee-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
								<button type="submit" class="btn btn-primary">Create Fee Template</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const graduationYearSelect = document.getElementById('graduation_year');
			const categorySection = document.getElementById('category_section');
			const categorySelect = document.getElementById('category_id');
			
			function toggleCategorySection() {
				const selectedYear = parseInt(graduationYearSelect.value);
				if (selectedYear >= 2025) {
					categorySection.classList.remove('d-none');
					categorySelect.required = true;
				} else {
					categorySection.classList.add('d-none');
					categorySelect.required = false;
					categorySelect.value = '';
				}
			}
			
			graduationYearSelect.addEventListener('change', toggleCategorySection);
			toggleCategorySection();
		});
	</script>
</x-alumniadmin-dashboard>