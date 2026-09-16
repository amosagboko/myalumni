@php
    $selectedIds = collect(old('fee_template_ids', isset($paymentStructure) ? $paymentStructure->feeTemplates->pluck('id') : []))->map(fn ($id) => (int) $id);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Admin name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $paymentStructure->name ?? '') }}" class="form-control form-control-sm" required>
        @error('name')<div class="form-text text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="display_title" class="form-label">Payer title <span class="text-danger">*</span></label>
        <input type="text" name="display_title" id="display_title" value="{{ old('display_title', $paymentStructure->display_title ?? '') }}" class="form-control form-control-sm" required>
        <div class="form-text">Shown to alumni on the payments page and receipt.</div>
        @error('display_title')<div class="form-text text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="payment_mode" class="form-label">Payment model <span class="text-danger">*</span></label>
        <select name="payment_mode" id="payment_mode" class="form-select form-select-sm" required>
            <option value="combined" @selected(old('payment_mode', $paymentStructure->payment_mode ?? 'combined') === 'combined')>Combined payment</option>
            <option value="separate" @selected(old('payment_mode', $paymentStructure->payment_mode ?? '') === 'separate')>Separate payment</option>
        </select>
        @error('payment_mode')<div class="form-text text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="graduation_year" class="form-label">Graduation year</label>
        <input type="number" name="graduation_year" id="graduation_year" value="{{ old('graduation_year', $paymentStructure->graduation_year ?? '') }}" class="form-control form-control-sm" min="0" max="{{ date('Y') + 10 }}">
        <div class="form-text">Leave empty for all cohorts.</div>
        @error('graduation_year')<div class="form-text text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="category_id" class="form-label">Category</label>
        <select name="category_id" id="category_id" class="form-select form-select-sm">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $paymentStructure->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <div class="form-text">V1 combined Credo code is configured for Undergraduate Full Time only.</div>
        @error('category_id')<div class="form-text text-danger">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-check mt-3">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $paymentStructure->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
</div>

<div class="mt-4">
    <label class="form-label">Fee items <span class="text-danger">*</span></label>
    <p class="small text-muted mb-2">Select two or more statutory fees. Combined amount is the sum of unpaid items that apply to each alumni. EOI fees cannot be included.</p>
    @error('fee_template_ids')<div class="form-text text-danger mb-2">{{ $message }}</div>@enderror

    <div class="table-responsive border rounded">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Fee</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Year</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($feeTemplates as $template)
                    <tr>
                        <td>
                            <input type="checkbox" name="fee_template_ids[]" value="{{ $template->id }}" class="form-check-input" @checked($selectedIds->contains($template->id))>
                        </td>
                        <td>{{ $template->description ?: $template->feeType?->name }}</td>
                        <td>{{ $template->feeType?->name }}</td>
                        <td>{{ $template->category?->name ?? '—' }}</td>
                        <td>{{ $template->graduation_year }}</td>
                        <td>₦{{ number_format($template->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
