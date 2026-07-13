@php
    /** @var \App\Services\Alumni\ClearanceFormService $clearanceFormService */
    $clearanceFormService = app(\App\Services\Alumni\ClearanceFormService::class);
    $forPdf = $forPdf ?? false;
@endphp

<div class="clearance-form-header">
    <div class="clearance-form-header__logo">
        @if ($forPdf && file_exists(public_path('images/fulafia-logo.jpg')))
            <img src="{{ public_path('images/fulafia-logo.jpg') }}" alt="FULAFIA Logo">
        @else
            <img src="{{ asset('images/fulafia-logo.jpg') }}" alt="FULAFIA Logo">
        @endif
    </div>
    <div class="clearance-form-header__text">
        <h2>Federal University of Lafia</h2>
        <h3>Alumni Personal Data Registration Form</h3>
    </div>
    <div class="clearance-form-header__logo clearance-form-header__logo--right">
        @if ($forPdf && file_exists(public_path('images/alumni-logo.jpg')))
            <img src="{{ public_path('images/alumni-logo.jpg') }}" alt="ALUMNI Logo">
        @else
            <img src="{{ asset('images/alumni-logo.jpg') }}" alt="ALUMNI Logo">
        @endif
    </div>
</div>

@foreach ($sections as $section)
    <div class="clearance-form-section">
        <div class="clearance-form-section__header">{{ $section['title'] }}</div>
        <div class="clearance-form-section__body">
            @if (($section['layout'] ?? null) === 'personal')
                <div class="clearance-form-row clearance-form-row--personal">
                    <div class="clearance-form-col clearance-form-col--avatar">
                        @if ($forPdf && $avatarPdfPath)
                            <img src="{{ $avatarPdfPath }}" alt="Alumni Photo" class="clearance-form-avatar">
                        @else
                            <img src="{{ $avatarWebUrl }}" alt="Alumni Photo" class="clearance-form-avatar">
                        @endif
                    </div>
                    <div class="clearance-form-col">
                        @foreach (array_slice($section['fields'], 0, (int) ceil(count($section['fields']) / 2)) as $field)
                            <div class="clearance-form-field">
                                <div class="clearance-form-field__label">{{ $field['label'] }}:</div>
                                <div class="clearance-form-field__value">{{ $clearanceFormService->displayValue($field['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="clearance-form-col">
                        @foreach (array_slice($section['fields'], (int) ceil(count($section['fields']) / 2)) as $field)
                            <div class="clearance-form-field">
                                <div class="clearance-form-field__label">{{ $field['label'] }}:</div>
                                <div class="clearance-form-field__value">{{ $clearanceFormService->displayValue($field['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                @foreach (array_chunk($section['fields'], 3) as $fieldChunk)
                    <div class="clearance-form-row">
                        @foreach ($fieldChunk as $field)
                            <div class="clearance-form-col">
                                <div class="clearance-form-field">
                                    <div class="clearance-form-field__label">{{ $field['label'] }}:</div>
                                    <div class="clearance-form-field__value">{{ $clearanceFormService->displayValue($field['value']) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endforeach

<div class="clearance-form-signatures">
    <div class="clearance-form-signatures__box">
        <p>Signature of Head Alumni Relations Unit</p>
    </div>
    <div class="clearance-form-signatures__box">
        <p>Date: {{ $generatedAt->format('d/m/Y') }}</p>
    </div>
</div>
