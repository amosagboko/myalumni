<x-alumniadmin-dashboard>
    <div class="main-content bg-lightblue theme-dark-bg right-chat-active">
            
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left">
                <div class="middle-wrap">
                    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                        <div class="card-body p-4 w-100 bg-current border-0 d-flex rounded-3">
                            <a href="" class="d-inline-block mt-2"><i class="ti-arrow-left font-sm text-white"></i></a>
                            <h4 class="font-xs text-white fw-600 ms-4 mb-0 mt-2">Uploaded Alumni</h4>
                        </div>
                        <div class="card-body p-lg-5 p-4 w-100 border-0">
                            <form method="GET" action="{{ route('students.index') }}">
                                <label for="sort_by" class="form-label">Sort By:</label>
                                <select name="sort_by" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Select --</option>
                                    <option value="faculty" {{ request('sort_by') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                    <option value="department" {{ request('sort_by') == 'department' ? 'selected' : '' }}>Department</option>
                                    <option value="programme" {{ request('sort_by') == 'programme' ? 'selected' : '' }}>Programme</option>
                                </select>
                            </form>
                        </div><br>

                        <div>
                            <table class="table table-striped">
                                <thead>
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
                            {{ $students->links() }}
                        </div>
                    </div>
                </div>
            </div>
             
        </div>            
    </div>
</x-alumniadmin-dashboard>