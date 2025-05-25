<x-alumniadmin-dashboard>
    <div class="container mt-3 pt-7" style="margin-left: 200px;">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0">Search Results</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            @if(count($alumni) > 0)
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Matric Number</th>
                                            <th>Programme</th>
                                            <th>Department</th>
                                            <th>Faculty</th>
                                            <th>Year of Graduation</th>
                                            <th>Degree Class</th>
                                            <th>Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alumni as $alum)
                                            <tr>
                                                <td>{{ $alum->firstname }} {{ $alum->surname }}</td>
                                                <td>{{ $alum->matriculation_id }}</td>
                                                <td>{{ $alum->programme }}</td>
                                                <td>{{ $alum->department }}</td>
                                                <td>{{ $alum->faculty }}</td>
                                                <td>{{ $alum->year_of_graduation }}</td>
                                                <td>{{ $alum->degree_class }}</td>
                                                <td>{{ $alum->category }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-info mb-0">
                                    No alumni found matching your search criteria.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 