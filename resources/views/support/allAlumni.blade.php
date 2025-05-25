<x-supportadmin-dashboard>
    <div class="container" style="max-width: 70%; margin: 20px 40px 20px auto;"> <!-- Pushed right -->
        <div class="card shadow-sm border-0">
            <div class="card-body bg-current text-white text-center rounded-top p-2">
                <h5 class="mb-0">Uploaded Alumni</h5>
            </div>
            <div class="table-responsive p-3">
                <table class="table table-striped table-bordered text-center align-middle" style="font-size: 0.8rem;">
                    <thead class="table-dark">
                        <br>
                        <tr>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Matriculation ID</th>
                            <th>Programme</th>
                            <th>Department</th>
                            <th>Faculty</th>
                            <th>Year of Graduation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{ $student->firstname }}</td>
                            <td>{{ $student->surname }}</td>
                            <td>{{ $student->matriculation_id }}</td>
                            <td>{{ $student->programme }}</td>
                            <td>{{ $student->department }}</td>
                            <td>{{ $student->faculty }}</td>
                            <td>{{ $student->year_of_graduation }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-2">
                    {{ $students->links() }}
                </div>
            </div>
    
            <!-- Filter Section Below Pagination -->
            <div class="card-body p-3">
                <form method="get" action="{{ route('dashboard.supportindex') }}">
                    <div class="row mb-3 text-center">
                        <div class="col-md-3 mb-2">
                            <select name="year_of_graduation" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Year</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year_of_graduation') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
    
                        <div class="col-md-3 mb-2">
                            <select name="faculty" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Faculty</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty }}" {{ request('faculty') == $faculty ? 'selected' : '' }}>{{ $faculty }}</option>
                                @endforeach
                            </select>
                        </div>
    
                        <div class="col-md-3 mb-2">
                            <select name="department" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>
    
                        <div class="col-md-3 mb-2">
                            <select name="programme" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Programme</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme }}" {{ request('programme') == $programme ? 'selected' : '' }}>{{ $programme }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
    
                    <div class="row mb-3 text-center">
                        <div class="col-md-4 offset-md-4">
                            <select name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Sort By</option>
                                <option value="faculty" {{ request('sort_by') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                <option value="department" {{ request('sort_by') == 'department' ? 'selected' : '' }}>Department</option>
                                <option value="programme" {{ request('sort_by') == 'programme' ? 'selected' : '' }}>Programme</option>
                                <option value="year_of_graduation" {{ request('sort_by') == 'year_of_graduation' ? 'selected' : '' }}>Year</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-supportadmin-dashboard>