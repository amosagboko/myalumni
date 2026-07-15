<div class="row feed-body alumni-my-events-page">
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h2 class="fw-700 text-grey-900 font-md mb-1">My events</h2>
                        <p class="font-xssss text-grey-500 mb-0">
                            Community events you have submitted for alumni to discover.
                        </p>
                    </div>
                    <a href="{{ route('alumni.events.create') }}"
                       class="btn btn-sm rounded-xl font-xssss fw-600 bg-primary-gradiant text-white text-decoration-none px-4">
                        <i class="feather-plus me-1"></i> Create event
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success font-xssss mb-3">{{ session('success') }}</div>
        @endif

        @if($events->isEmpty())
            <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
                <div class="card-body p-4 text-center">
                    <i class="feather-calendar font-xl text-grey-400 mb-3 d-block"></i>
                    <p class="font-xssss text-grey-500 mb-3">You have not created any events yet.</p>
                    <a href="{{ route('alumni.events.create') }}"
                       class="btn btn-sm rounded-xl font-xssss fw-600 bg-primary-gradiant text-white text-decoration-none px-4">
                        Create your first event
                    </a>
                </div>
            </div>
        @else
            <div class="row ps-1 pe-1">
                @foreach($events as $event)
                    @php
                        $isPast = $event->date && $event->date->isPast();
                    @endphp
                    <div class="col-lg-6 mb-3 d-flex">
                        <div class="card bg-white w-100 shadow-xss rounded-xxl border-0 overflow-hidden">
                            <div class="card-body p-4 d-flex flex-column h-100">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    @if($event->is_published)
                                        <span class="badge bg-success font-xsssss">Published</span>
                                    @else
                                        <span class="badge bg-warning text-dark font-xsssss">Pending review</span>
                                    @endif
                                    @if($isPast)
                                        <span class="badge bg-greylight text-grey-700 font-xsssss">Past</span>
                                    @endif
                                </div>

                                <h3 class="fw-700 font-xssss text-grey-900 mb-2">{{ $event->eventname }}</h3>

                                <div class="mb-3">
                                    @if($event->date)
                                        <p class="font-xsssss text-grey-500 mb-1">
                                            <i class="feather-calendar me-1"></i>{{ $event->date->format('M j, Y') }}
                                        </p>
                                    @endif
                                    @if($event->venue)
                                        <p class="font-xsssss text-grey-500 mb-0">
                                            <i class="feather-map-pin me-1"></i>{{ $event->venue }}
                                        </p>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-auto pt-3 border-top">
                                    <a href="{{ route('alumni.events.show', $event) }}"
                                       class="btn btn-sm rounded-xl font-xssss fw-600 bg-greylight text-grey-700 text-decoration-none px-3">
                                        View
                                    </a>
                                    <a href="{{ route('alumni.events.edit', $event) }}"
                                       class="btn btn-sm rounded-xl font-xssss fw-600 bg-primary-gradiant text-white text-decoration-none px-3">
                                        Edit
                                    </a>
                                    <button type="button"
                                            wire:click="deleteEvent({{ $event->id }})"
                                            wire:confirm="Delete this event?"
                                            class="btn btn-sm rounded-xl font-xssss fw-600 btn-outline-danger px-3">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
