@php
    /** @var \App\Services\Alumni\ClearanceFormService $clearanceFormService */
    $clearanceFormService = app(\App\Services\Alumni\ClearanceFormService::class);
@endphp

@foreach ($sections as $section)
    <div class="card mb-3 border shadow-xs">
        <div class="card-header bg-white border-bottom py-2">
            <h6 class="mb-0 fw-600 font-xssss text-grey-900">{{ $section['title'] }}</h6>
        </div>
        <div class="card-body py-3">
            @if (($section['layout'] ?? null) === 'personal')
                <div class="row g-3">
                    <div class="col-md-3 text-center">
                        <img src="{{ $avatarWebUrl }}"
                             alt="Alumni photo"
                             class="rounded-circle clearance-form-avatar"
                             width="150"
                             height="150"
                             style="object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <div class="row g-2">
                            @foreach ($section['fields'] as $field)
                                <div class="col-md-6">
                                    <div class="clearance-form-field">
                                        <div class="text-grey-500 font-xsssss text-uppercase fw-600">{{ $field['label'] }}</div>
                                        <div class="font-xssss text-grey-900">{{ $clearanceFormService->displayValue($field['value']) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="row g-2">
                    @foreach ($section['fields'] as $field)
                        <div class="col-md-4">
                            <div class="clearance-form-field">
                                <div class="text-grey-500 font-xsssss text-uppercase fw-600">{{ $field['label'] }}</div>
                                <div class="font-xssss text-grey-900">{{ $clearanceFormService->displayValue($field['value']) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endforeach

<div class="row mt-2">
    <div class="col-md-6">
        <div class="border-top pt-2">
            <p class="mb-0 font-xssss text-grey-700">Signature of Head Alumni Relations Unit</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="border-top pt-2">
            <p class="mb-0 font-xssss text-grey-700">Date: {{ $generatedAt->format('d/m/Y') }}</p>
        </div>
    </div>
</div>
