@props([
    'embedded' => false,
    'dashboardRoute' => null,
    'dashboardLabel' => 'Back to dashboard',
    'alumni' => null,
    'name' => null,
    'matriculationId' => null,
    'tempEmail' => null,
    'category' => null,
])

@php
    $dashboardRoute = $dashboardRoute ?? (auth()->user()->hasRole('administrator')
        ? route('admin.dashboard')
        : route('alumni-relations-officer.home'));
    $matriculationValue = $matriculationId ?? request('matriculation_id', old('matriculation_id'));
@endphp

<x-admin.surface-styles />

@if ($embedded)
    <div class="admin-surface retrieve-credentials-page">
@else
    <div class="main-content right-chat-active admin-surface retrieve-credentials-page">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">
@endif

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Retrieve alumni credentials</h1>
                                <p class="ads-page-subtitle">Look up temporary login details by matriculation number.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('upload.alumni') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="upload" style="width: 14px; height: 14px;"></i>
                                    Upload alumni
                                </a>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="ads-alert ads-alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="ads-alert ads-alert-error">{{ session('error') }}</div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Search</h2>
                                <form method="GET" action="{{ route('upload.alumni.credentials') }}" class="needs-validation" novalidate id="credentials-search-form">
                                    <div class="mb-3" style="max-width: 420px;">
                                        <label for="matriculation_id" class="form-label">Matriculation number</label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="matriculation_id"
                                            name="matriculation_id"
                                            value="{{ $matriculationValue }}"
                                            required
                                            autocomplete="off"
                                        >
                                        <div class="form-text">Enter the matriculation number to retrieve temporary login credentials.</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-sm ads-btn-primary">
                                            <i data-feather="search" style="width: 14px; height: 14px;"></i>
                                            Search
                                        </button>
                                        <a href="{{ $dashboardRoute }}" class="btn btn-sm btn-outline-secondary">{{ $dashboardLabel }}</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @if ($alumni)
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Credentials</h2>
                                    <div class="ads-compact-table-wrap">
                                        <table class="ads-compact-table mb-0">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 200px;">Name</th>
                                                    <td>{{ $name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Matriculation number</th>
                                                    <td>{{ $matriculationId }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Temporary email</th>
                                                    <td><code>{{ $tempEmail }}</code></td>
                                                </tr>
                                                <tr>
                                                    <th>Category</th>
                                                    <td>{{ $category?->name ?? 'Not set' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3 retrieve-credentials-actions">
                                        <form method="POST" action="{{ route('upload.alumni.resend-credentials') }}">
                                            @csrf
                                            <input type="hidden" name="matriculation_id" value="{{ $matriculationId }}">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i data-feather="mail" style="width: 14px; height: 14px;"></i>
                                                Resend credentials
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm ads-btn-primary" id="open-update-email-modal">
                                            <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                            Update email
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="ads-modal-overlay" id="update-email-modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="updateEmailModalTitle">
                                <div class="ads-modal-dialog">
                                    <div class="ads-modal-card">
                                        <div class="ads-modal-header">
                                            <h6 class="ads-modal-title" id="updateEmailModalTitle">Update alumni email</h6>
                                            <button type="button" class="btn-close" id="close-update-email-modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('upload.alumni.update-email') }}" id="update-email-form">
                                            @csrf
                                            <div class="ads-modal-body">
                                                <input type="hidden" name="matriculation_id" value="{{ $matriculationId }}">
                                                <label for="new_email" class="form-label small text-muted mb-1">New email address</label>
                                                <input type="email" class="form-control form-control-sm" id="new_email" name="new_email" required>
                                                <div class="invalid-feedback d-block" id="email-error" style="display: none;"></div>
                                            </div>
                                            <div class="ads-modal-footer">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cancel-update-email-modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm ads-btn-primary" id="update-email-submit">Update email</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

@if ($embedded)
    </div>
@else
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@once
@push('styles')
<style>
    @media print {
        .retrieve-credentials-page .ads-page-actions,
        .retrieve-credentials-page .retrieve-credentials-actions,
        .retrieve-credentials-page form,
        .retrieve-credentials-page .ads-page-header {
            display: none !important;
        }
        .retrieve-credentials-page .ads-section-card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
    }
</style>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    const modal = document.getElementById('update-email-modal');
    const openBtn = document.getElementById('open-update-email-modal');
    const closeBtn = document.getElementById('close-update-email-modal');
    const cancelBtn = document.getElementById('cancel-update-email-modal');
    const form = document.getElementById('update-email-form');
    const submitBtn = document.getElementById('update-email-submit');
    const emailError = document.getElementById('email-error');

    function openModal() {
        if (modal) modal.style.display = 'flex';
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        if (emailError) {
            emailError.style.display = 'none';
            emailError.textContent = '';
        }
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    form?.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!submitBtn) return;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating…';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.reload();
                    return;
                }
                if (emailError) {
                    emailError.textContent = data.message || 'Unable to update email.';
                    emailError.style.display = 'block';
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update email';
            })
            .catch(function () {
                if (emailError) {
                    emailError.textContent = 'An error occurred while updating the email.';
                    emailError.style.display = 'block';
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update email';
            });
    });
});
</script>
@endpush
