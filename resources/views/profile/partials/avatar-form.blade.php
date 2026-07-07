@php
    $adminSurface = $adminSurface ?? false;
    $hideHeader = $hideHeader ?? false;
    $labelClass = $adminSurface ? 'form-label small text-muted mb-1' : 'mont-font fw-600 font-xsss';
    $inputClass = $adminSurface ? 'form-control form-control-sm' : 'form-control';
    $buttonClass = $adminSurface ? 'btn btn-sm ads-btn-primary' : 'btn btn-primary btn-sm';
@endphp

<section>
    @unless ($hideHeader)
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Upload your Avatar
            </h2>
        </header>
        <br>
    @endunless

    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="{{ $labelClass }}">Profile photo</label>
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div>
                    <input type="file" name="avatar" class="{{ $inputClass }}" accept="image/*" required>
                    <button type="submit" class="{{ $buttonClass }} mt-3">
                        {{ __('Save') }}
                    </button>
                </div>

                @if (auth()->user()->avatar)
                    <div>
                        <img
                            src="{{ asset('storage/' . auth()->user()->avatar) }}"
                            alt=""
                            class="rounded-circle border"
                            style="width: 100px; height: 100px; object-fit: cover;"
                        >
                    </div>
                @endif
            </div>
        </div>
    </form>
</section>
