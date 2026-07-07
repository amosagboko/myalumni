<x-alumniadmin-dashboard title="Category Details | FuLafia Alumni">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Category details</h1>
                                <p class="ads-page-subtitle">Review category metadata and the alumni currently assigned to it.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.alumni-categories.edit', $alumniCategory) }}" class="btn btn-sm ads-btn-primary">
                                    <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                    Edit category
                                </a>
                                <a href="{{ route('admin.alumni-categories.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                    Back to categories
                                </a>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-stats ads-stats-3">
                                <div class="ads-stat">
                                    <span class="ads-stat-label">Status</span>
                                    <span class="ads-stat-value ads-stat-value-sm">{{ $alumniCategory->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <div class="ads-stat">
                                    <span class="ads-stat-label">Alumni count</span>
                                    <span class="ads-stat-value">{{ number_format($alumniCategory->alumni_count) }}</span>
                                </div>
                                <div class="ads-stat">
                                    <span class="ads-stat-label">Created</span>
                                    <span class="ads-stat-value ads-stat-value-sm">{{ $alumniCategory->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Overview</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="small text-muted mb-1">Category name</div>
                                        <div class="fw-medium">{{ $alumniCategory->name }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted mb-1">Status</div>
                                        <span class="adt-status {{ $alumniCategory->is_active ? 'adt-status-active' : 'adt-status-inactive' }}">
                                            <span class="adt-status-dot"></span>
                                            {{ $alumniCategory->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    @if($alumniCategory->description)
                                        <div class="col-12">
                                            <div class="small text-muted mb-1">Description</div>
                                            <div>{{ $alumniCategory->description }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="ads-section">
                            <div class="ads-section-card">
                                <h2 class="ads-section-title">Assigned alumni</h2>
                                @if($alumniCategory->alumni_count > 0)
                                    <div class="ads-compact-table-wrap">
                                        <table class="ads-compact-table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Matric number</th>
                                                    <th>Faculty</th>
                                                    <th>Graduation year</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($alumniCategory->alumni as $alumnus)
                                                    <tr>
                                                        <td>{{ $alumnus->user->name ?? 'N/A' }}</td>
                                                        <td>{{ $alumnus->matric_number ?? 'N/A' }}</td>
                                                        <td>{{ $alumnus->faculty ?? 'N/A' }}</td>
                                                        <td>{{ $alumnus->year_of_graduation ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="ads-empty-inline mb-0">No alumni are currently assigned to this category.</p>
                                @endif
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