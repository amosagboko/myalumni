<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete your alumni profile - FuLafia Alumni Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .credentials-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .form-control[readonly],
        .form-select:disabled {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    @php
        $oldFaculty = old('faculty');
        $oldDepartment = old('department');
        $oldProgramme = old('programme');
        $oldCategory = old('category_slug');
        $oldState = old('state');
        $oldLga = old('lga');
        $oldGender = old('gender');
        $oldDob = old('date_of_birth');
        $oldYearOfEntry = old('year_of_entry');
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">FuLafia Alumni Portal</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('login') }}">Login</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="credentials-card">
                    <h2 class="mb-2">Complete your alumni profile</h2>
                    <p class="text-muted mb-4">
                        Your name, matriculation number, and year of graduation are already on file.
                        Fill in the remaining details to create your account (Class of {{ $record->year_of_graduation }}).
                    </p>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="firstname" class="form-label">First name</label>
                                    <input type="text" id="firstname" class="form-control" value="{{ $record->firstname }}" readonly tabindex="-1">
                                </div>
                                <div class="col-md-6">
                                    <label for="surname" class="form-label">Surname</label>
                                    <input type="text" id="surname" class="form-control" value="{{ $record->surname }}" readonly tabindex="-1">
                                </div>
                                <div class="col-md-6">
                                    <label for="matriculation_id" class="form-label">Matriculation number</label>
                                    <input type="text" id="matriculation_id" class="form-control" value="{{ $record->matric_number }}" readonly tabindex="-1">
                                </div>
                                <div class="col-md-6">
                                    <label for="year_of_graduation" class="form-label">Year of graduation</label>
                                    <input type="text" id="year_of_graduation" class="form-control" value="{{ $record->year_of_graduation }}" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('landing.complete-profile.submit') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="faculty" class="form-label">Faculty <span class="text-danger">*</span></label>
                                <select name="faculty" id="faculty" class="form-select @error('faculty') is-invalid @enderror" required>
                                    <option value="">Select faculty</option>
                                    @foreach (array_keys($academicTree) as $facultyName)
                                        <option value="{{ $facultyName }}" @selected($oldFaculty === $facultyName)>{{ $facultyName }}</option>
                                    @endforeach
                                </select>
                                @error('faculty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department" id="department" class="form-select @error('department') is-invalid @enderror" required disabled>
                                    <option value="">Select faculty first</option>
                                </select>
                                @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="programme" class="form-label">Programme <span class="text-danger">*</span></label>
                                <select name="programme" id="programme" class="form-select @error('programme') is-invalid @enderror" required disabled>
                                    <option value="">Select department first</option>
                                </select>
                                @error('programme')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="category_slug" class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_slug" id="category_slug" class="form-select @error('category_slug') is-invalid @enderror" required>
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}" @selected($oldCategory === $category->slug)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of birth (YYYY-MM-DD) <span class="text-danger">*</span></label>
                                <input type="text" name="date_of_birth" id="date_of_birth" value="{{ $oldDob }}"
                                       class="form-control @error('date_of_birth') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" maxlength="10" pattern="\d{4}-\d{2}-\d{2}"
                                       inputmode="numeric" autocomplete="bday" required>
                                <div class="form-text">Example: 1998-05-12</div>
                                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    <option value="male" @selected($oldGender === 'male')>Male</option>
                                    <option value="female" @selected($oldGender === 'female')>Female</option>
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State of origin <span class="text-danger">*</span></label>
                                <select name="state" id="state" class="form-select @error('state') is-invalid @enderror" required>
                                    <option value="">Select state</option>
                                    @foreach ($nigeriaStates as $stateName => $lgas)
                                        <option value="{{ $stateName }}" @selected($oldState === $stateName)>{{ $stateName }}</option>
                                    @endforeach
                                </select>
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="lga" class="form-label">LGA <span class="text-danger">*</span></label>
                                <select name="lga" id="lga" class="form-select @error('lga') is-invalid @enderror" required disabled>
                                    <option value="">Select state first</option>
                                </select>
                                @error('lga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="year_of_entry" class="form-label">Year of entry <span class="text-danger">*</span></label>
                                <input type="number" name="year_of_entry" id="year_of_entry" value="{{ $oldYearOfEntry }}"
                                       class="form-control @error('year_of_entry') is-invalid @enderror"
                                       min="1900" max="{{ $record->year_of_graduation }}"
                                       placeholder="2018, not 2018/2019" required>
                                @error('year_of_entry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Save and continue</button>
                            <a href="{{ route('landing') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const academicTree = @json($academicTree);
            const stateLgas = @json($nigeriaStates);
            const facultySelect = document.getElementById('faculty');
            const departmentSelect = document.getElementById('department');
            const programmeSelect = document.getElementById('programme');
            const stateSelect = document.getElementById('state');
            const lgaSelect = document.getElementById('lga');
            const selectedDepartment = @json($oldDepartment);
            const selectedProgramme = @json($oldProgramme);
            const selectedLga = @json($oldLga);

            function resetSelect(select, placeholder, disabled) {
                select.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder;
                select.appendChild(option);
                select.disabled = disabled;
            }

            function uniqueLabels(values) {
                const seen = {};
                const unique = [];
                (values || []).forEach(function (value) {
                    const key = String(value).replace(/\s+/g, ' ').trim().toLowerCase();
                    if (!key || seen[key]) {
                        return;
                    }
                    seen[key] = true;
                    unique.push(value);
                });
                return unique;
            }

            function populateDepartments(faculty, preferredDepartment, preferredProgramme) {
                resetSelect(departmentSelect, faculty ? 'Select department' : 'Select faculty first', !faculty);
                resetSelect(programmeSelect, 'Select department first', true);

                if (!faculty || !academicTree[faculty]) {
                    return;
                }

                uniqueLabels(Object.keys(academicTree[faculty])).forEach(function (department) {
                    const option = document.createElement('option');
                    option.value = department;
                    option.textContent = department;
                    if (preferredDepartment && preferredDepartment === department) {
                        option.selected = true;
                    }
                    departmentSelect.appendChild(option);
                });

                if (departmentSelect.value) {
                    populateProgrammes(faculty, departmentSelect.value, preferredProgramme);
                }
            }

            function populateProgrammes(faculty, department, preferredProgramme) {
                resetSelect(programmeSelect, department ? 'Select programme' : 'Select department first', !department);

                if (!faculty || !department || !academicTree[faculty] || !academicTree[faculty][department]) {
                    return;
                }

                uniqueLabels(academicTree[faculty][department]).forEach(function (programme) {
                    const option = document.createElement('option');
                    option.value = programme;
                    option.textContent = programme;
                    if (preferredProgramme && preferredProgramme === programme) {
                        option.selected = true;
                    }
                    programmeSelect.appendChild(option);
                });
            }

            function populateLgas(state, preferredLga) {
                resetSelect(lgaSelect, state ? 'Select LGA' : 'Select state first', !state);

                if (!state || !stateLgas[state]) {
                    return;
                }

                stateLgas[state].forEach(function (lga) {
                    const option = document.createElement('option');
                    option.value = lga;
                    option.textContent = lga;
                    if (preferredLga && preferredLga === lga) {
                        option.selected = true;
                    }
                    lgaSelect.appendChild(option);
                });
            }

            facultySelect.addEventListener('change', function () {
                populateDepartments(facultySelect.value, null, null);
            });

            departmentSelect.addEventListener('change', function () {
                populateProgrammes(facultySelect.value, departmentSelect.value, null);
            });

            stateSelect.addEventListener('change', function () {
                populateLgas(stateSelect.value, null);
            });

            populateDepartments(facultySelect.value, selectedDepartment, selectedProgramme);
            populateLgas(stateSelect.value, selectedLga);
        })();
    </script>
</body>
</html>
