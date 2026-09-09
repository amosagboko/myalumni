<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete Self-Enrollment - FuLafia Alumni Portal</title>
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
        $apiPhone = trim((string) ($pending['phone_number'] ?? ''));
        $oldState = old('state');
        $oldLga = old('lga');
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
                    <h2 class="mb-2">Confirm your details</h2>
                    <p class="text-muted mb-4">
                        Your matriculation number was verified with the university directory.
                        Please complete the missing fields to create your alumni account
                        (Class of {{ $pending['year_of_graduation'] ?? date('Y') }}).
                    </p>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-2 small">
                                <div class="col-md-6"><strong>Name:</strong> {{ $pending['name'] ?: '—' }}</div>
                                <div class="col-md-6"><strong>Matriculation No:</strong> {{ $pending['matric_number'] }}</div>
                                <div class="col-md-6"><strong>Department:</strong> {{ $pending['department'] ?: '—' }}</div>
                                <div class="col-md-6"><strong>Category:</strong> {{ $pending['category_label'] ?? 'Undergraduate (Full-time)' }}</div>
                                <div class="col-md-6"><strong>Year of Graduation:</strong> {{ $pending['year_of_graduation'] ?? date('Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('landing.self-enroll.submit') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="programme" class="form-label">Programme <span class="text-danger">*</span></label>
                                <input type="text" name="programme" id="programme" value="{{ old('programme') }}"
                                       class="form-control @error('programme') is-invalid @enderror" required>
                                @error('programme')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="faculty" class="form-label">Faculty <span class="text-danger">*</span></label>
                                <input type="text" name="faculty" id="faculty" value="{{ old('faculty') }}"
                                       class="form-control @error('faculty') is-invalid @enderror" required>
                                @error('faculty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                                       class="form-control @error('date_of_birth') is-invalid @enderror" required>
                                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State of Origin <span class="text-danger">*</span></label>
                                <select name="state" id="state" class="form-select @error('state') is-invalid @enderror" required>
                                    <option value="">Select state</option>
                                    @foreach($nigeriaStates as $stateName => $lgas)
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
                                <label for="year_of_entry" class="form-label">Year of Entry <span class="text-danger">*</span></label>
                                <input type="number" id="year_of_entry"
                                       value="{{ $pending['year_of_entry'] }}"
                                       class="form-control @error('year_of_entry') is-invalid @enderror"
                                       readonly tabindex="-1">
                                @error('year_of_entry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" id="phone_number"
                                       value="{{ old('phone_number', $apiPhone) }}"
                                       class="form-control @error('phone_number') is-invalid @enderror">
                                @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Create Alumni Account</button>
                            <a href="{{ route('landing') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const stateLgas = @json($nigeriaStates);
            const stateSelect = document.getElementById('state');
            const lgaSelect = document.getElementById('lga');
            const selectedLga = @json($oldLga);

            function populateLgas(state, preferredLga) {
                lgaSelect.innerHTML = '';

                if (!state || !stateLgas[state]) {
                    lgaSelect.disabled = true;
                    lgaSelect.innerHTML = '<option value="">Select state first</option>';
                    return;
                }

                lgaSelect.disabled = false;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select LGA';
                lgaSelect.appendChild(placeholder);

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

            stateSelect.addEventListener('change', function () {
                populateLgas(this.value, null);
            });

            populateLgas(stateSelect.value, selectedLga);
        })();
    </script>
</body>
</html>
