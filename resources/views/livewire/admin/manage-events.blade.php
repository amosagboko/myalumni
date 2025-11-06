<div>
    <div class="container" style="max-width: 1000px; margin: 80px auto 0; padding-top: 5rem;">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-2">
                <h6 class="mb-0">Manage Homepage Content</h6>
                <button class="btn btn-light btn-sm text-primary fw-bold"
                    data-bs-toggle="modal" data-bs-target="#eventModal"
                    wire:click="dispatch('openModal')"> 
                    + Add Content
                </button>
            </div>

            <div class="card-body p-3">
                @if(session()->has('message'))
                    <div class="alert alert-success p-2 text-center">{{ session('message') }}</div>
                @endif

                <!-- Filter by Type -->
                <div class="mb-3">
                    <label class="form-label small">Filter by Type:</label>
                    <select wire:model.live="filterType" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="connect">Connect</option>
                        <option value="event">Events</option>
                        <option value="opportunity">Opportunities</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 10%;">Type</th>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 20%;">Description</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 10%;">Published</th>
                                <th style="width: 10%;">Order</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr>
                                    <td>
                                        <span class="badge 
                                            @if($event->type === 'connect') bg-info
                                            @elseif($event->type === 'event') bg-primary
                                            @else bg-success
                                            @endif">
                                            {{ ucfirst($event->type) }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($event->eventname, 30) }}</td>
                                    <td>{{ Str::limit($event->description ?? 'N/A', 40) }}</td>
                                    <td>{{ $event->date ? $event->date->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $event->is_published ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $event->is_published ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>{{ $event->order ?? 'N/A' }}</td>
                                    <td>
                                        <button wire:click="deleteEvent({{ $event->id }})" 
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this content?')">
                                            <i class="feather-trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No content found. Click "Add Content" to create one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Content Modal -->
    <div wire:ignore.self class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header p-2">
                    <h6 class="modal-title">Create Homepage Content</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label small">Content Type <span class="text-danger">*</span></label>
                        <select wire:model="type" class="form-select form-select-sm">
                            <option value="connect">Connect</option>
                            <option value="event">Event</option>
                            <option value="opportunity">Opportunity</option>
                        </select>
                        @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Title <span class="text-danger">*</span></label>
                        <input type="text" wire:model="eventname" class="form-control form-control-sm" placeholder="Enter title">
                        @error('eventname') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Description</label>
                        <textarea wire:model="description" class="form-control form-control-sm" rows="3" placeholder="Enter description (will appear on homepage)"></textarea>
                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small">Date</label>
                            <input type="date" wire:model="date" class="form-control form-control-sm">
                            @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small">Location/Venue</label>
                            <input type="text" wire:model="venue" class="form-control form-control-sm" placeholder="Enter location">
                            @error('venue') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Image</label>
                        <input type="file" wire:model="image" class="form-control form-control-sm" accept="image/*">
                        @error('image') <span class="text-danger small">{{ $message }}</span> @enderror
                        @if($image)
                            <div class="mt-2">
                                <small class="text-muted">Preview:</small><br>
                                <img src="{{ $image->temporaryUrl() }}" alt="Preview" style="max-width: 200px; max-height: 150px;" class="mt-1">
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Link URL</label>
                        <input type="url" wire:model="link" class="form-control form-control-sm" placeholder="https://example.com">
                        <small class="text-muted">Optional: Link users will be directed to</small>
                        @error('link') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small">Display Order</label>
                            <input type="number" wire:model="order" class="form-control form-control-sm" placeholder="Lower numbers appear first">
                            <small class="text-muted">Optional: Controls display order</small>
                            @error('order') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" wire:model="is_published" class="form-check-input" id="is_published">
                                <label class="form-check-label small" for="is_published">Published</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary btn-sm" wire:click="createEvent" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createEvent">Save Content</span>
                        <span wire:loading wire:target="createEvent">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ensure the modal is always on top of the backdrop */
        .modal {
            z-index: 1055 !important;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal-backdrop.show {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>

    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('openModal', () => {
                var modalEl = document.getElementById('eventModal');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();

                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach((el, index) => {
                        if (index > 0) el.remove();
                    });

                    modalEl.style.zIndex = '1050';
                    document.querySelectorAll('.modal-backdrop').forEach(el => {
                        el.style.zIndex = '1040';
                    });
                }, 200);
            });

            Livewire.on('close-modal', () => {
                var modalEl = document.getElementById('eventModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }

                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                }, 300);
            });

            // Close modal after successful creation
            Livewire.hook('morph.updated', ({ component }) => {
                if (component.__instance?.events?.length > 0) {
                    var modalEl = document.getElementById('eventModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        setTimeout(() => {
                            modal.hide();
                        }, 500);
                    }
                }
            });
        });
    </script>
</div>
