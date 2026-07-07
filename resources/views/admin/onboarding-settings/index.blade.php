<x-alumniadmin-dashboard>
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Onboarding settings</h1>
                                <p class="ads-page-subtitle">Control whether alumni can register and complete onboarding.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to dashboard
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
                            <div class="ads-stats">
                                <div class="ads-stat ads-stat-highlight">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Current status</span>
                                            <span class="ads-stat-value ads-stat-value-sm">
                                                {{ $setting->is_onboarding_enabled ? 'Open' : 'Closed' }}
                                            </span>
                                            <span class="small text-muted d-block mt-1">
                                                @if ($setting->is_onboarding_enabled)
                                                    Alumni can register and complete onboarding
                                                @else
                                                    Registration and onboarding are blocked
                                                @endif
                                            </span>
                                        </div>
                                        <span class="ads-stat-icon">
                                            <i data-feather="{{ $setting->is_onboarding_enabled ? 'unlock' : 'lock' }}"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Last updated</span>
                                            <span class="ads-stat-value ads-stat-value-sm">
                                                @if ($setting->is_onboarding_enabled && $setting->reopened_at)
                                                    {{ $setting->reopened_at->format('M j, Y') }}
                                                @elseif (!$setting->is_onboarding_enabled && $setting->closed_at)
                                                    {{ $setting->closed_at->format('M j, Y') }}
                                                @else
                                                    —
                                                @endif
                                            </span>
                                            <span class="small text-muted d-block mt-1">
                                                @if ($setting->is_onboarding_enabled && $setting->reopenedBy)
                                                    Reopened by {{ $setting->reopenedBy->name }}
                                                @elseif (!$setting->is_onboarding_enabled && $setting->closedBy)
                                                    Closed by {{ $setting->closedBy->name }}
                                                @else
                                                    No changes recorded yet
                                                @endif
                                            </span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="clock"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($setting->closed_at || $setting->reopened_at || $setting->closure_reason)
                            <div class="ads-section">
                                <div class="ads-section-card">
                                    <h2 class="ads-section-title">Status history</h2>
                                    <div class="row g-3">
                                        @if ($setting->closed_at)
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Closed on</div>
                                                <div class="fw-medium">{{ $setting->closed_at->format('M d, Y \a\t g:i A') }}</div>
                                                @if ($setting->closedBy)
                                                    <div class="small text-muted">by {{ $setting->closedBy->name }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($setting->reopened_at)
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Reopened on</div>
                                                <div class="fw-medium">{{ $setting->reopened_at->format('M d, Y \a\t g:i A') }}</div>
                                                @if ($setting->reopenedBy)
                                                    <div class="small text-muted">by {{ $setting->reopenedBy->name }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($setting->closure_reason)
                                            <div class="col-12">
                                                <div class="small text-muted mb-1">Reason for closure</div>
                                                <div class="fst-italic">"{{ $setting->closure_reason }}"</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="ads-section">
                            <div class="ads-section-card">
                                @if ($setting->is_onboarding_enabled)
                                    <h2 class="ads-section-title">Close onboarding</h2>
                                    <p class="text-muted small mb-3">
                                        Closing onboarding prevents new alumni from registering and blocks in-progress onboarding.
                                        This is typically used during elections or maintenance.
                                    </p>
                                    <form action="{{ route('admin.onboarding-settings.close') }}" method="POST">
                                        @csrf
                                        <div class="mb-3" style="max-width: 520px;">
                                            <label for="closure_reason" class="form-label">
                                                Reason for closure <span class="text-danger">*</span>
                                            </label>
                                            <textarea
                                                class="form-control form-control-sm @error('closure_reason') is-invalid @enderror"
                                                id="closure_reason"
                                                name="closure_reason"
                                                rows="3"
                                                placeholder="e.g. Onboarding temporarily closed during election period"
                                                required
                                            >{{ old('closure_reason') }}</textarea>
                                            @error('closure_reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-secondary"
                                            onclick="return confirm('Are you sure you want to close onboarding? This will prevent alumni from registering and completing their profiles.')"
                                        >
                                            <i data-feather="lock" style="width: 14px; height: 14px;"></i>
                                            Close onboarding
                                        </button>
                                    </form>
                                @else
                                    <h2 class="ads-section-title">Reopen onboarding</h2>
                                    <p class="text-muted small mb-3">
                                        Reopening allows alumni to register and complete onboarding again.
                                    </p>
                                    <form action="{{ route('admin.onboarding-settings.reopen') }}" method="POST">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm ads-btn-primary"
                                            onclick="return confirm('Are you sure you want to reopen onboarding? Alumni will be able to register and complete their profiles again.')"
                                        >
                                            <i data-feather="unlock" style="width: 14px; height: 14px;"></i>
                                            Reopen onboarding
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Important information</h2>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li class="mb-1">Closing onboarding affects <strong>all alumni categories</strong> regardless of graduation year.</li>
                                    <li class="mb-1">Users who already completed onboarding can still access the platform.</li>
                                    <li class="mb-1">Users mid-onboarding will be blocked from finishing their profiles.</li>
                                    <li class="mb-1">This setting is typically used during elections or system maintenance.</li>
                                    <li>All actions are logged for audit purposes.</li>
                                </ul>
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
