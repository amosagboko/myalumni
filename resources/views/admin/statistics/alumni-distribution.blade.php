<x-alumniadmin-dashboard>
    <div class="container-fluid mt-5 pt-5 px-4" style="margin-left: 150px;">
        <div class="row justify-content-end">
            <div class="col-10">
                <div class="row g-3">
                    <!-- Alumni by Graduation Year -->
                    <div class="col-lg-5 col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Alumni by Graduation Year</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Year</th>
                                                <th>Count</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalAlumni = $alumniByYear->sum('total');
                                            @endphp
                                            @forelse($alumniByYear as $year)
                                                <tr>
                                                    <td>{{ $year->year_of_graduation }}</td>
                                                    <td>{{ number_format($year->total) }}</td>
                                                    <td>{{ number_format(($year->total / $totalAlumni) * 100, 1) }}%</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total</th>
                                                <th>{{ number_format($totalAlumni) }}</th>
                                                <th>100%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alumni by Faculty -->
                    <div class="col-lg-5 col-xl-4 ms-lg-2">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Alumni by Faculty</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Faculty</th>
                                                <th>Count</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalFaculty = $alumniByFaculty->sum('total');
                                            @endphp
                                            @forelse($alumniByFaculty as $faculty)
                                                <tr>
                                                    <td>{{ $faculty->faculty }}</td>
                                                    <td>{{ number_format($faculty->total) }}</td>
                                                    <td>{{ number_format(($faculty->total / $totalFaculty) * 100, 1) }}%</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total</th>
                                                <th>{{ number_format($totalFaculty) }}</th>
                                                <th>100%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 