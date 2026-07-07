<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Homepage content</h1>
                                <p class="ads-page-subtitle">Manage connect cards, news, and events shown on the public homepage.</p>
                            </div>
                            <button type="button" class="btn btn-sm ads-btn-primary" wire:click="openCreateModal">
                                <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                                Add content
                            </button>
                        </div>

                        <div class="ads-stats ads-stats-5">
                            <div class="ads-stat">
                                <span class="ads-stat-label">Total</span>
                                <span class="ads-stat-value">{{ number_format($stats['total']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Published</span>
                                <span class="ads-stat-value">{{ number_format($stats['published']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Connect</span>
                                <span class="ads-stat-value">{{ number_format($stats['connect']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">News</span>
                                <span class="ads-stat-value">{{ number_format($stats['news']) }}</span>
                            </div>
                            <div class="ads-stat">
                                <span class="ads-stat-label">Events</span>
                                <span class="ads-stat-value">{{ number_format($stats['events']) }}</span>
                            </div>
                        </div>

                        <div class="adt-panel">
                            @if (session()->has('message'))
                                <div class="adt-alert adt-alert-success mx-3 mt-3 mb-0" role="alert">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <select wire:model.live="filterType" class="form-select form-select-sm adt-select">
                                        <option value="all">All types</option>
                                        <option value="connect">Connect</option>
                                        <option value="event">News</option>
                                        <option value="opportunity">Events</option>
                                    </select>
                                </div>
                            </div>

                            @if ($events->count() > 0)
                                <div class="adt-table-wrap">
                                    <table class="adt-table">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Date</th>
                                                <th>Published</th>
                                                <th>Order</th>
                                                <th class="adt-th-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($events as $content)
                                                <tr wire:key="homepage-content-{{ $content->id }}">
                                                    <td>
                                                        <span class="adt-tag">
                                                            @if ($content->type === 'connect')
                                                                Connect
                                                            @elseif ($content->type === 'event')
                                                                News
                                                            @else
                                                                Events
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td class="fw-medium">{{ Str::limit($content->eventname, 40) }}</td>
                                                    <td class="adt-muted">{{ Str::limit($content->description ?? '—', 50) }}</td>
                                                    <td class="adt-muted">{{ $content->date ? $content->date->format('M j, Y') : '—' }}</td>
                                                    <td>
                                                        <span class="adt-status {{ $content->is_published ? 'adt-status-active' : 'adt-status-inactive' }}">
                                                            <span class="adt-status-dot"></span>
                                                            {{ $content->is_published ? 'Yes' : 'No' }}
                                                        </span>
                                                    </td>
                                                    <td class="adt-muted">{{ $content->order ?? '—' }}</td>
                                                    <td>
                                                        <div class="adt-actions">
                                                            <button
                                                                type="button"
                                                                wire:click.stop="showDetails({{ $content->id }})"
                                                                class="adt-action-btn"
                                                                title="View details"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click.stop="openEditor({{ $content->id }})"
                                                                class="adt-action-btn"
                                                                title="Edit"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click.stop="deleteEvent({{ $content->id }})"
                                                                wire:confirm="Delete this content item?"
                                                                class="adt-action-btn adt-action-danger"
                                                                title="Delete"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="adt-empty">
                                    <div class="adt-empty-icon">
                                        <i data-feather="layout" style="width: 28px; height: 28px;"></i>
                                    </div>
                                    <h3 class="adt-empty-title">No content found</h3>
                                    <p class="adt-empty-text">Add homepage content to populate the public landing page.</p>
                                    <button type="button" class="btn btn-sm ads-btn-primary mt-2" wire:click="openCreateModal">
                                        Add content
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($modalMode)
        <div
            class="ads-modal-overlay"
            wire:click.self="closeModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="contentModalTitle"
        >
            <div class="ads-modal-dialog ads-modal-dialog-lg">
                <div class="ads-modal-card">
                    <div class="ads-modal-header">
                        <h6 class="ads-modal-title" id="contentModalTitle">
                            @if ($modalMode === 'view')
                                Content details
                            @elseif ($modalMode === 'edit')
                                Edit homepage content
                            @else
                                Create homepage content
                            @endif
                        </h6>
                        <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    </div>

                    <div class="ads-modal-body">
                        @if ($modalMode === 'view' && $selectedEvent)
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Content type</label>
                                <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    readonly
                                    value="{{ $selectedEvent->type === 'connect' ? 'Connect' : ($selectedEvent->type === 'event' ? 'News' : 'Events') }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Title</label>
                                <input type="text" class="form-control form-control-sm" readonly value="{{ $selectedEvent->eventname }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Description</label>
                                <textarea class="form-control form-control-sm" rows="3" readonly>{{ $selectedEvent->description }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Date</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        readonly
                                        value="{{ $selectedEvent->date ? $selectedEvent->date->format('Y-m-d') : '' }}"
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Location / venue</label>
                                    <input type="text" class="form-control form-control-sm" readonly value="{{ $selectedEvent->venue }}">
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label class="form-label small text-muted mb-1">Image</label>
                                @if ($selectedEvent->image)
                                    <div class="mt-1">
                                        <img
                                            src="{{ asset('storage/' . $selectedEvent->image) }}"
                                            alt="{{ $selectedEvent->eventname }}"
                                            class="rounded border"
                                            style="max-width: 200px; max-height: 150px; object-fit: cover;"
                                        >
                                    </div>
                                @else
                                    <input type="text" class="form-control form-control-sm" readonly value="No image uploaded">
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Link URL</label>
                                <input type="url" class="form-control form-control-sm" readonly value="{{ $selectedEvent->link }}">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted mb-1">Display order</label>
                                    <input type="text" class="form-control form-control-sm" readonly value="{{ $selectedEvent->order }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="view_is_published"
                                            disabled
                                            @checked($selectedEvent->is_published)
                                        >
                                        <label class="form-check-label small" for="view_is_published">Published</label>
                                    </div>
                                </div>
                            </div>
                        @else
                                <div class="mb-3">
                                    <label class="form-label small text-muted mb-1">Content type <span class="text-danger">*</span></label>
                                    <select wire:model="type" class="form-select form-select-sm">
                                        <option value="connect">Connect</option>
                                        <option value="event">News</option>
                                        <option value="opportunity">Events</option>
                                    </select>
                                    @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small text-muted mb-1">Title <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="eventname" class="form-control form-control-sm" placeholder="Enter title">
                                    @error('eventname') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small text-muted mb-1">Description</label>
                                    <textarea wire:model="description" class="form-control form-control-sm" rows="3" placeholder="Enter description"></textarea>
                                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Date</label>
                                        <input type="date" wire:model="date" class="form-control form-control-sm">
                                        @error('date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Location / venue</label>
                                        <input type="text" wire:model="venue" class="form-control form-control-sm" placeholder="Enter location">
                                        @error('venue') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-3 mt-3">
                                    <label class="form-label small text-muted mb-1">Image</label>
                                    <input type="file" wire:model="image" class="form-control form-control-sm" accept="image/*">
                                    @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    @if ($image)
                                        <div class="mt-2">
                                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="rounded border" style="max-width: 200px; max-height: 150px; object-fit: cover;">
                                        </div>
                                    @elseif ($existingImagePath)
                                        <div class="mt-2">
                                            <div class="small text-muted mb-1">Current image</div>
                                            <img src="{{ asset('storage/' . $existingImagePath) }}" alt="Current" class="rounded border" style="max-width: 200px; max-height: 150px; object-fit: cover;">
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small text-muted mb-1">Link URL</label>
                                    <input type="url" wire:model="link" class="form-control form-control-sm" placeholder="https://example.com">
                                    <div class="form-text">Optional link users will be directed to.</div>
                                    @error('link') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Display order</label>
                                        <input type="number" wire:model="order" class="form-control form-control-sm" placeholder="Lower numbers appear first">
                                        @error('order') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" wire:model="is_published" class="form-check-input" id="is_published">
                                            <label class="form-check-label small" for="is_published">Published</label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="ads-modal-footer">
                            @if ($modalMode === 'view')
                                <button type="button" class="btn btn-light btn-sm" wire:click="closeModal">Close</button>
                                <button type="button" class="btn btn-sm ads-btn-primary" wire:click="openEditor">
                                    <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                    Edit content
                                </button>
                            @else
                                <button type="button" class="btn btn-light btn-sm" wire:click="closeModal">Cancel</button>
                                @if ($modalMode === 'edit')
                                    <button type="button" class="btn btn-sm ads-btn-primary" wire:click="updateEvent" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="updateEvent,image">Save changes</span>
                                        <span wire:loading wire:target="updateEvent,image">Saving…</span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm ads-btn-primary" wire:click="createEvent" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="createEvent,image">Save content</span>
                                        <span wire:loading wire:target="createEvent,image">Saving…</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
    @endif

    @push('scripts')
    <script>
        function initManageEventsFeather() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }

        function syncManageEventsModalBodyLock() {
            document.body.classList.toggle('ads-modal-open', !!document.querySelector('.ads-modal-overlay'));
        }

        document.addEventListener('DOMContentLoaded', () => {
            initManageEventsFeather();
            syncManageEventsModalBodyLock();
        });

        document.addEventListener('livewire:navigated', () => {
            initManageEventsFeather();
            syncManageEventsModalBodyLock();
        });

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                initManageEventsFeather();
                syncManageEventsModalBodyLock();
            });
        }
    </script>
    @endpush
</div>
