<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface admin-data-table" style="padding-right: 1.25rem;">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Student Affairs Clearance</h1>
                                <p class="ads-page-subtitle">Review alumni eligibility and update student affairs clearance status.</p>
                            </div>
                            <div class="ads-page-actions d-flex flex-wrap gap-2">
                                <a href="{{ route('student-affairs.home') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('student-affairs.audit') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="clipboard" style="width: 14px; height: 14px;"></i>
                                    Audit
                                </a>
                                <button type="button" class="btn btn-sm ads-btn-primary text-white" wire:click="export">
                                    <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                    Export CSV
                                </button>
                            </div>
                        </div>

                        @if ($message)
                            <div class="ads-alert {{ $messageType === 'success' ? 'ads-alert-success' : 'ads-alert-error' }}">
                                {{ $message }}
                                <button type="button" class="btn-close float-end" wire:click="clearMessage" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="ads-stats mb-3">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total alumni</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Pending clearance</span>
                                <span class="ads-stat-value">{{ number_format($stats['pending']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Cleared</span>
                                <span class="ads-stat-value">{{ number_format($stats['cleared']) }}</span>
                            </div>
                            <div class="ads-stat ads-stat-highlight">
                                <span class="ads-stat-label">Matching filters</span>
                                <span class="ads-stat-value">{{ number_format($stats['filtered']) }}</span>
                            </div>
                        </div>

                        <div class="adt-panel">
                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.400ms="search"
                                            class="form-control form-control-sm"
                                            placeholder="Search name or matric…"
                                        >
                                    </div>
                                    <select wire:model.live="faculty" class="form-select form-select-sm adt-select">
                                        <option value="">All faculties</option>
                                        @foreach ($faculties as $f)
                                            <option value="{{ $f }}">{{ $f }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model.live="department" class="form-select form-select-sm adt-select">
                                        <option value="">All departments</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model.live="year" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="">All years</option>
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model.live="clearanceStatus" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="">All statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="cleared">Cleared</option>
                                    </select>
                                    <select wire:model.live="perPage" class="form-select form-select-sm adt-select adt-select-narrow">
                                        @foreach ([10, 20, 50, 100] as $n)
                                            <option value="{{ $n }}">{{ $n }}/page</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearFilters">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <div class="adt-toolbar border-top-0 pt-0">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggleSelectAll">
                                        Select page
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        wire:click="bulkClear"
                                        wire:confirm="Clear selected alumni for Student Affairs?"
                                    >
                                        Bulk clear
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        wire:click="bulkUnclear"
                                        wire:confirm="Unclear selected alumni for Student Affairs?"
                                    >
                                        Bulk unclear
                                    </button>
                                </div>
                                <div class="adt-muted small">
                                    {{ count($selectedAlumni) }} selected
                                </div>
                            </div>

                            @if ($alumni->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;"></th>
                                                <th>Name</th>
                                                <th>Matric</th>
                                                <th>Faculty</th>
                                                <th>Department</th>
                                                <th>Year</th>
                                                <th>Onboarding</th>
                                                <th>Payments</th>
                                                <th>SA status</th>
                                                <th class="adt-th-actions">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($alumni as $a)
                                                @php
                                                    $onboard = $a->biodata_completed ?? true;
                                                    $paid = method_exists($a, 'hasCompletedRequiredPayments') ? $a->hasCompletedRequiredPayments() : true;
                                                    $eligible = $onboard && $paid;
                                                    $cleared = (bool) $a->student_affairs_cleared;
                                                @endphp
                                                <tr wire:key="sa-clearance-{{ $a->id }}-{{ (int) $cleared }}">
                                                    <td>
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            value="{{ $a->id }}"
                                                            wire:model.live="selectedAlumni"
                                                        >
                                                    </td>
                                                    <td class="fw-medium">{{ $a->user->name ?? '—' }}</td>
                                                    <td>{{ $a->matric_number ?? '—' }}</td>
                                                    <td>{{ $a->faculty ?? '—' }}</td>
                                                    <td>{{ $a->department ?? '—' }}</td>
                                                    <td>{{ $a->year_of_graduation ?? '—' }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $onboard ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $onboard ? 'Done' : 'Pending' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="adt-status {{ $paid ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $paid ? 'Paid' : 'Unpaid' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="adt-status {{ $cleared ? 'adt-status-active' : 'adt-status-suspended' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $cleared ? 'Cleared' : 'Pending' }}
                                                        </span>
                                                    </td>
                                                    <td class="adt-actions">
                                                        @unless ($eligible)
                                                            <span class="adt-muted small">Complete onboarding & payments first</span>
                                                        @else
                                                            @if ($cleared)
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-danger py-0"
                                                                    wire:click="markUncleared({{ $a->id }})"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="markUncleared({{ $a->id }})"
                                                                >
                                                                    Unclear
                                                                </button>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-success py-0"
                                                                    wire:click="markCleared({{ $a->id }})"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="markCleared({{ $a->id }})"
                                                                >
                                                                    Mark cleared
                                                                </button>
                                                            @endif
                                                        @endunless
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($alumni->hasPages())
                                    <div class="adt-footer">
                                        <span class="adt-footer-count">
                                            {{ $alumni->firstItem() }}–{{ $alumni->lastItem() }} of {{ $alumni->total() }}
                                        </span>
                                        <div class="adt-pagination">
                                            {{ $alumni->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="users" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No alumni found</h3>
                                    <p class="adt-empty-text">Try adjusting your filters.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
        document.addEventListener('livewire:navigated', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
        document.addEventListener('livewire:init', function () {
            Livewire.hook('morph.updated', () => {
                if (typeof feather !== 'undefined') feather.replace();
            });
        });
    </script>
    @endpush
</div>
