<div>
    <div class="container" style="max-width: 800px; margin: 80px auto 0; padding-top: 5rem;">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-2">
                <h6 class="mb-0">Manage Events</h6>
                <button class="btn btn-light btn-sm text-primary fw-bold"
                    data-bs-toggle="modal" data-bs-target="#eventModal"
                    wire:click="dispatch('openModal')"> 
                    + Add Event
                </button>
            </div>

            <div class="card-body p-3">
                @if(session()->has('message'))
                    <div class="alert alert-success p-2 text-center">{{ session('message') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40%;">Title</th>
                                <th style="width: 25%;">Date</th>
                                <th style="width: 25%;">Location</th>
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>{{ $event->eventname }}</td>
                                    <td>{{ $event->date }}</td>
                                    <td>{{ $event->venue }}</td>
                                    <td>
                                        <button wire:click="deleteEvent({{ $event->id }})" class="btn btn-danger btn-sm">
                                            <i class="feather-trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div wire:ignore.self class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-2">
                    <h6 class="modal-title">Create Event</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small">Event Title</label>
                        <input type="text" wire:model="eventname" class="form-control form-control-sm" placeholder="Enter event title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Date</label>
                        <input type="date" wire:model="date" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Location</label>
                        <input type="text" wire:model="venue" class="form-control form-control-sm" placeholder="Enter event location">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary btn-sm" wire:click="createEvent">Save Event</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ensure the modal is always on top of the backdrop */
        .modal {
            z-index: 1055 !important; /* Bootstrap's modal default is 1050, so we increase it */
        }

        /* Push the backdrop behind everything */
        .modal-backdrop {
            z-index: 1040 !important; /* Ensure backdrop is below the modal */
        }

        /* Ensure the backdrop doesn't prevent clicking inside the modal */
        .modal-backdrop.show {
            opacity: 0.5; /* Optional: Adjust transparency */
            pointer-events: none; /* Allows interaction with the modal */
        }
    </style>

    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('openModal', () => {
                var modalEl = document.getElementById('eventModal');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();

                setTimeout(() => {
                    // Ensure only one backdrop exists
                    document.querySelectorAll('.modal-backdrop').forEach((el, index) => {
                        if (index > 0) el.remove();
                    });

                    // Fix interaction issues by adjusting z-index
                    modalEl.style.zIndex = '1050'; // Bootstrap default
                    document.querySelectorAll('.modal-backdrop').forEach(el => {
                        el.style.zIndex = '1040'; // Keep backdrop below the modal
                    });
                }, 200);
            });

            Livewire.on('close-modal', () => {
                var modalEl = document.getElementById('eventModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                setTimeout(() => {
                    // Remove any leftover backdrop elements
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                }, 300);
            });
        });
    </script>
</div>
