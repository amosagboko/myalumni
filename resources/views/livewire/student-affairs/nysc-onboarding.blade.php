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
                                <h1 class="ads-page-title">NYSC Onboarding</h1>
                                <p class="ads-page-subtitle">Alumni cleared by Students Affairs.</p>
                            </div>
                            <div class="ads-page-actions">
                                <button type="button" class="btn btn-sm ads-btn-primary text-white" wire:click="exportExcel">
                                    <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                    Download Excel
                                </button>
                            </div>
                        </div>

                        @if ($exportMessage)
                            <div class="ads-alert ads-alert-error">
                                {{ $exportMessage }}
                            </div>
                        @endif

                        <div class="adt-panel">
                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <select wire:model.live="year" class="form-select form-select-sm adt-select adt-select-narrow">
                                        <option value="">All years</option>
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
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
                                    <select wire:model.live="programme" class="form-select form-select-sm adt-select">
                                        <option value="">All programmes</option>
                                        @foreach ($programmes as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearFilters">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            @if ($alumni->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Matric</th>
                                                <th>Faculty</th>
                                                <th>Department</th>
                                                <th>Programme</th>
                                                <th>Year</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($alumni as $a)
                                                <tr wire:key="nysc-onboarding-{{ $a->id }}">
                                                    <td class="fw-medium">{{ $a->user->name ?? '—' }}</td>
                                                    <td>{{ $a->matric_number ?? '—' }}</td>
                                                    <td>{{ $a->faculty ?? '—' }}</td>
                                                    <td>{{ $a->department ?? '—' }}</td>
                                                    <td>{{ $a->programme ?? '—' }}</td>
                                                    <td>{{ $a->year_of_graduation ?? '—' }}</td>
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
                                    <h3 class="adt-empty-title">No onboarded alumni found</h3>
                                    <p class="adt-empty-text">Try adjusting your filters.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
