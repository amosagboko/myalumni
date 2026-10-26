@php
    $adminSurface = $adminSurface ?? false;
    $hideHeader = $hideHeader ?? false;
    $compact = $compact ?? $adminSurface;
    $metaClass = $adminSurface ? 'text-muted small mb-0' : ($compact ? 'text-grey-500 font-xssss mb-0' : 'mt-1 text-sm text-gray-600');
@endphp

<section>
    @unless ($hideHeader)
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Delete Account') }}
            </h2>
        </header>
    @endunless

    <p class="{{ $hideHeader ? $metaClass : 'mt-1 text-sm text-gray-600' }}">
        {{ __('Account deletion is disabled. Contact an administrator if this account needs to be removed.') }}
    </p>
</section>
