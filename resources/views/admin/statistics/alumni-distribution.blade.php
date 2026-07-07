<x-alumniadmin-dashboard title="Alumni Distribution | FuLafia Alumni">
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Alumni distribution</h1>
                                <p class="ads-page-subtitle">Breakdown of alumni records by graduation year and faculty.</p>
                            </div>
                        </div>

                        @php
                            $totalByYear = $alumniByYear->sum('total');
                            $totalByFaculty = $alumniByFaculty->sum('total');
                        @endphp

                        <div class="ads-stats ads-stats-3">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total alumni</span>
                                <span class="ads-stat-value">{{ number_format($totalAlumni) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Graduation years</span>
                                <span class="ads-stat-value">{{ number_format($alumniByYear->count()) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Faculties</span>
                                <span class="ads-stat-value">{{ number_format($alumniByFaculty->count()) }}</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">By graduation year</h2>
                                    @if ($alumniByYear->count() > 0)
                                        <div class="ads-compact-table-wrap">
                                            <table class="ads-compact-table">
                                                <thead>
                                                    <tr>
                                                        <th>Year</th>
                                                        <th>Count</th>
                                                        <th>Share</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($alumniByYear as $year)
                                                        <tr>
                                                            <td>{{ $year->year_of_graduation ?? 'Unknown' }}</td>
                                                            <td>{{ number_format($year->total) }}</td>
                                                            <td>{{ $totalByYear > 0 ? number_format(($year->total / $totalByYear) * 100, 1) : 0 }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>Total</th>
                                                        <th>{{ number_format($totalByYear) }}</th>
                                                        <th>100%</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <p class="ads-empty-inline mb-0">No graduation year data available.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="ads-section-card h-100">
                                    <h2 class="ads-section-title">By faculty</h2>
                                    @if ($alumniByFaculty->count() > 0)
                                        <div class="ads-compact-table-wrap">
                                            <table class="ads-compact-table">
                                                <thead>
                                                    <tr>
                                                        <th>Faculty</th>
                                                        <th>Count</th>
                                                        <th>Share</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($alumniByFaculty as $faculty)
                                                        <tr>
                                                            <td>{{ $faculty->faculty ?: 'Unknown' }}</td>
                                                            <td>{{ number_format($faculty->total) }}</td>
                                                            <td>{{ $totalByFaculty > 0 ? number_format(($faculty->total / $totalByFaculty) * 100, 1) : 0 }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>Total</th>
                                                        <th>{{ number_format($totalByFaculty) }}</th>
                                                        <th>100%</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <p class="ads-empty-inline mb-0">No faculty data available.</p>
                                    @endif
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
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
    @endpush
</x-alumniadmin-dashboard>
