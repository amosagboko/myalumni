<div class="container" style="max-width: 600px; margin-top: 80px;">
    <div class="card">
        <div class="card-header bg-white py-2">
            <h6 class="card-title mb-0 small">Clearance Form</h6>
        </div>
        <div class="card-body p-2">
            <form wire:submit="generateReport">
                <!-- Personal Information -->
                <div class="mb-2">
                    <h6 class="text-muted mb-1 small">Personal Information</h6>
                    <div class="d-flex flex-column gap-1">
                        <div>
                            <label class="form-label mb-0 small">Title</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->title }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Surname</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->surname }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">First Name</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->firstname }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Matriculation Number</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->matriculation_number }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-2">
                    <h6 class="text-muted mb-1 small">Contact Information</h6>
                    <div class="d-flex flex-column gap-1">
                        <div>
                            <label class="form-label mb-0 small">Email</label>
                            <input type="email" class="form-control form-control-sm py-0" value="{{ $alumni->email }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Phone</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->phone }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Contact Address</label>
                            <textarea class="form-control form-control-sm py-0" rows="1" readonly>{{ $alumni->contact_address }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="mb-2">
                    <h6 class="text-muted mb-1 small">Academic Information</h6>
                    <div class="d-flex flex-column gap-1">
                        <div>
                            <label class="form-label mb-0 small">Department</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->department }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Faculty</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->faculty }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Year of Entry</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->year_of_entry }}" readonly>
                        </div>
                        <div>
                            <label class="form-label mb-0 small">Year of Graduation</label>
                            <input type="text" class="form-control form-control-sm py-0" value="{{ $alumni->year_of_graduation }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-2">
                    <button type="submit" class="btn btn-primary btn-sm py-0">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 