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
                                            <span class="ads-stat-label">Uploaded alumni onboarding</span>
                                            <span class="ads-stat-value ads-stat-value-sm">
                                                {{ $setting->is_onboarding_enabled ? 'Open' : 'Closed' }}
                                            </span>
                                            <span class="small text-muted d-block mt-1">
                                                @if ($setting->is_onboarding_enabled)
                                                    Uploaded alumni can retrieve credentials and complete onboarding
                                                @else
                                                    Uploaded alumni credential retrieval is blocked
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
                                            <span class="ads-stat-label">2026+ self-enrollment</span>
                                            <span class="ads-stat-value ads-stat-value-sm">
                                                {{ $setting->is_self_enrollment_enabled ? 'Open' : 'Closed' }}
                                            </span>
                                            <span class="small text-muted d-block mt-1">
                                                @if ($setting->is_self_enrollment_enabled)
                                                    Unuploaded graduates can self-enroll via matric + directory API
                                                @else
                                                    Self-enrollment is disabled
                                                @endif
                                            </span>
                                        </div>
                                        <span class="ads-stat-icon">
                                            <i data-feather="{{ $setting->is_self_enrollment_enabled ? 'user-plus' : 'user-x' }}"></i>
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
                                                    No onboarding changes recorded yet
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
                                        Closing onboarding prevents uploaded alumni from retrieving credentials and completing onboarding.
                                        This is typically used during elections or maintenance. It does <strong>not</strong> affect the 2026+ self-enrollment toggle.
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
                                            onclick="return confirm('Are you sure you want to close onboarding? This will prevent uploaded alumni from registering and completing their profiles.')"
                                        >
                                            <i data-feather="lock" style="width: 14px; height: 14px;"></i>
                                            Close onboarding
                                        </button>
                                    </form>
                                @else
                                    <h2 class="ads-section-title">Reopen onboarding</h2>
                                    <p class="text-muted small mb-3">
                                        Reopening allows uploaded alumni to retrieve credentials and complete onboarding again.
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
                                <h2 class="ads-section-title">2026+ self-enrollment</h2>
                                <p class="text-muted small mb-3">
                                    When enabled, alumni who are <strong>not already uploaded</strong> can enroll by matriculation number
                                    via the university student directory. Year of graduation is fixed as <strong>2026</strong>,
                                    category defaults to <strong>Undergraduate (Full-time)</strong>.
                                    This toggle works independently of uploaded-alumni onboarding.
                                </p>
                                @if ($setting->self_enrollment_updated_at)
                                    <p class="small text-muted mb-3">
                                        Last changed {{ $setting->self_enrollment_updated_at->format('M d, Y \a\t g:i A') }}
                                        @if ($setting->selfEnrollmentUpdatedBy)
                                            by {{ $setting->selfEnrollmentUpdatedBy->name }}
                                        @endif
                                    </p>
                                @endif
                                @if ($setting->is_self_enrollment_enabled)
                                    <form action="{{ route('admin.onboarding-settings.self-enrollment.disable') }}" method="POST">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-secondary"
                                            onclick="return confirm('Disable 2026+ self-enrollment? New unuploaded alumni will no longer be able to enroll via the directory API.')"
                                        >
                                            <i data-feather="user-x" style="width: 14px; height: 14px;"></i>
                                            Disable self-enrollment
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.onboarding-settings.self-enrollment.enable') }}" method="POST">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm ads-btn-primary"
                                            onclick="return confirm('Enable 2026+ self-enrollment? Unuploaded alumni found in the student directory will be able to create accounts.')"
                                        >
                                            <i data-feather="user-plus" style="width: 14px; height: 14px;"></i>
                                            Enable self-enrollment
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Division clearance offices</h2>
                                <p class="text-muted small mb-3">
                                    Choose which offices must clear alumni graduating in <strong>2025 or later</strong>.
                                    Enabled offices are the only ones required for “fully cleared” status.
                                    If both are off, division clearance is not required. Print access stays based on bio-data and payments only.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <div class="fw-semibold">Student Affairs</div>
                                                    <div class="small text-muted">
                                                        {{ $setting->is_student_affairs_clearance_enabled ? 'Enabled' : 'Disabled' }}
                                                    </div>
                                                </div>
                                                <span class="badge {{ $setting->is_student_affairs_clearance_enabled ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $setting->is_student_affairs_clearance_enabled ? 'ON' : 'OFF' }}
                                                </span>
                                            </div>
                                            @if ($setting->student_affairs_clearance_updated_at)
                                                <p class="small text-muted mb-3">
                                                    Last changed {{ $setting->student_affairs_clearance_updated_at->format('M d, Y \a\t g:i A') }}
                                                    @if ($setting->studentAffairsClearanceUpdatedBy)
                                                        by {{ $setting->studentAffairsClearanceUpdatedBy->name }}
                                                    @endif
                                                </p>
                                            @endif
                                            @if ($setting->is_student_affairs_clearance_enabled)
                                                <form action="{{ route('admin.onboarding-settings.student-affairs-clearance.disable') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                        onclick="return confirm('Disable Student Affairs clearance? This office will no longer be required to clear alumni.')">
                                                        Disable Student Affairs
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.onboarding-settings.student-affairs-clearance.enable') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm ads-btn-primary"
                                                        onclick="return confirm('Enable Student Affairs clearance? This office will be required to clear alumni (2025+).')">
                                                        Enable Student Affairs
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <div class="fw-semibold">Academic Affairs</div>
                                                    <div class="small text-muted">
                                                        {{ $setting->is_academic_affairs_clearance_enabled ? 'Enabled' : 'Disabled' }}
                                                    </div>
                                                </div>
                                                <span class="badge {{ $setting->is_academic_affairs_clearance_enabled ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $setting->is_academic_affairs_clearance_enabled ? 'ON' : 'OFF' }}
                                                </span>
                                            </div>
                                            @if ($setting->academic_affairs_clearance_updated_at)
                                                <p class="small text-muted mb-3">
                                                    Last changed {{ $setting->academic_affairs_clearance_updated_at->format('M d, Y \a\t g:i A') }}
                                                    @if ($setting->academicAffairsClearanceUpdatedBy)
                                                        by {{ $setting->academicAffairsClearanceUpdatedBy->name }}
                                                    @endif
                                                </p>
                                            @endif
                                            @if ($setting->is_academic_affairs_clearance_enabled)
                                                <form action="{{ route('admin.onboarding-settings.academic-affairs-clearance.disable') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                        onclick="return confirm('Disable Academic Affairs clearance? This office will no longer be required to clear alumni.')">
                                                        Disable Academic Affairs
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.onboarding-settings.academic-affairs-clearance.enable') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm ads-btn-primary"
                                                        onclick="return confirm('Enable Academic Affairs clearance? This office will be required to clear alumni (2025+).')">
                                                        Enable Academic Affairs
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Important information</h2>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li class="mb-1">Uploaded-alumni onboarding and 2026+ self-enrollment are <strong>independent</strong> toggles.</li>
                                    <li class="mb-1">Student Affairs / Academic Affairs clearance toggles apply only to alumni graduating in <strong>2025 or later</strong>.</li>
                                    <li class="mb-1">Only enabled offices are required for “fully cleared” status. Both may be turned off.</li>
                                    <li class="mb-1">A disabled office cannot toggle clearance flags; their Clearance page shows an admin-disabled notice.</li>
                                    <li class="mb-1">Clearance Form printing remains based on bio-data and payments only.</li>
                                    <li class="mb-1">Self-enrollment only applies when a matric is <strong>not already uploaded</strong> and is found in the student directory.</li>
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
