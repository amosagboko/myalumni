<x-admin.surface-styles />

<div class="main-content right-chat-active admin-surface">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row">
                <div class="col-12">

                    <div class="ads-page-header">
                        <div>
                            <h1 class="ads-page-title">Admin Dashboard</h1>
                            <p class="ads-page-subtitle">
                                User overview
                                @if($paymentYearRecord)
                                    for payment year <strong>{{ $paymentYearRecord->year }}</strong>
                                    ({{ $paymentYearRecord->start_date->format('M j, Y') }} – {{ $paymentYearRecord->end_date->format('M j, Y') }})
                                @elseif($paymentYear !== '')
                                    for {{ $paymentYear }}
                                @endif
                            </p>
                        </div>
                        <div class="ads-filters">
                            <select wire:model.live="paymentYear" class="form-select form-select-sm ads-select" aria-label="Payment year">
                                @forelse($paymentYears as $year)
                                    <option value="{{ $year->year }}">
                                        Payment year {{ $year->year }}{{ $year->is_active ? ' (active)' : '' }}
                                    </option>
                                @empty
                                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Users</h2>
                            <div class="ads-stats ads-stats-5">
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Total users</span>
                                            <span class="ads-stat-value">{{ number_format($stats['users']['total']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="users"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Active</span>
                                            <span class="ads-stat-value">{{ number_format($stats['users']['active']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="user-check"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Suspended</span>
                                            <span class="ads-stat-value">{{ number_format($stats['users']['suspended']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="user-x"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">New in period</span>
                                            <span class="ads-stat-value">{{ number_format($stats['users']['new_in_period']) }}</span>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="user-plus"></i></span>
                                    </div>
                                </div>
                                <div class="ads-stat ads-stat-highlight">
                                    <div class="ads-stat-inner">
                                        <div>
                                            <span class="ads-stat-label">Onboarding</span>
                                            <span class="ads-stat-value ads-stat-value-sm">
                                                {{ $stats['onboarding_open'] ? 'Open' : 'Closed' }}
                                            </span>
                                            <a href="{{ route('admin.onboarding-settings.index') }}" class="ads-stat-link">Manage</a>
                                        </div>
                                        <span class="ads-stat-icon"><i data-feather="log-in"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card">
                            <h2 class="ads-section-title">Quick actions</h2>
                            <div class="ads-quick-actions">
                                <a href="{{ route('admin.users') }}" class="ads-quick-action">
                                    <span class="ads-quick-action-icon"><i data-feather="users"></i></span>
                                    <span>Manage users</span>
                                </a>
                                <a href="{{ route('admin.users.create') }}" class="ads-quick-action">
                                    <span class="ads-quick-action-icon"><i data-feather="user-plus"></i></span>
                                    <span>Create user</span>
                                </a>
                                <a href="{{ route('upload.alumni') }}" class="ads-quick-action">
                                    <span class="ads-quick-action-icon"><i data-feather="upload"></i></span>
                                    <span>Upload alumni</span>
                                </a>
                                <a href="{{ route('retrieve.credentials') }}" class="ads-quick-action">
                                    <span class="ads-quick-action-icon"><i data-feather="key"></i></span>
                                    <span>Retrieve credentials</span>
                                </a>
                                <a href="{{ route('admin.fee-templates.index') }}" class="ads-quick-action">
                                    <span class="ads-quick-action-icon"><i data-feather="settings"></i></span>
                                    <span>Fee templates</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function initAdminDashboardFeather() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    document.addEventListener('DOMContentLoaded', initAdminDashboardFeather);
    document.addEventListener('livewire:navigated', initAdminDashboardFeather);

    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', () => {
            initAdminDashboardFeather();
        });
    }
</script>
@endpush
