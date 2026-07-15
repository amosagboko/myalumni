<nav class="navigation scroll-bar">
    <div class="container ps-0 pe-0">
        <div class="nav-content">
            @php
                $memberNavRestricted = $alumniMemberRestricted ?? false;
                $memberNavMessage = $alumniMemberRestrictionMessage ?? 'Please complete your profile and payments to access this feature.';
            @endphp

            {{-- Social feeds (template: New Feeds) --}}
            <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2 mt-2">
                <div class="nav-caption fw-600 font-xssss text-grey-500"><span>New </span>Feeds</div>
                <ul class="mb-1 top-content">
                    <li class="logo d-none d-xl-block d-lg-block"></li>
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.home') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.home') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-activity btn-round-md bg-blue-gradiant me-3"></i><span>Newsfeed</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('friends') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('friends') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-users btn-round-md bg-primary-gradiant me-3"></i><span>Connections</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.discover') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.discover', 'alumni.events.show') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-compass btn-round-md bg-gold-gradiant me-3"></i><span>Discover</span>
                        </a>
                    </li>
                    @can('create event')
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.events.create') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.events.create', 'alumni.events.edit') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-plus-circle btn-round-md bg-success me-3"></i><span>Create Event</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.events.mine') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.events.mine') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-calendar btn-round-md bg-red-gradiant me-3"></i><span>My Events</span>
                        </a>
                    </li>
                    @endcan
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.members.show', auth()->user()) }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.members.show') && (int) request()->route('user')?->id === auth()->id() ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-user btn-round-md bg-mini-gradiant me-3"></i><span>My Profile</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Portal / My Dashboard (preserved routes) --}}
            @auth
            <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2">
                <div class="nav-caption fw-600 font-xssss text-grey-500"><span>My </span>Dashboard</div>
                <ul class="mb-1 top-content">
                    @if(auth()->user()->hasRole('alumni-president') && auth()->user()->alumni)
                    <li>
                        <form method="POST" action="{{ route('portal.switch') }}">
                            @csrf
                            <input type="hidden" name="mode" value="operational">
                            <button type="submit" class="nav-content-bttn open-font border-0 bg-transparent w-100 text-start {{ request()->routeIs('alumni-president.*') ? 'active' : '' }}">
                                <i class="feather-briefcase btn-round-md bg-gold-gradiant me-3"></i><span>President Office</span>
                            </button>
                        </form>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('alumni.payments.history') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni.payments.history') ? ' active' : '' }}">
                            <i class="feather-credit-card btn-round-md bg-red-gradiant me-3"></i><span>Payment History</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('reports') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('reports*') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-edit-3 btn-round-md bg-blue-gradiant me-3"></i><span>Clearance Form</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('alumni.clearance-status') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni.clearance-status') ? 'active' : '' }}">
                            <i class="feather-check-circle btn-round-md bg-success me-3"></i><span>Clearance Status</span>
                        </a>
                    </li>
                    @if(auth()->user()->hasAnyRole(['alumni', 'alumni-president']))
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.elections') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.elections*') && !request()->routeIs('alumni.elections.expression-of-interest.status') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-flag btn-round-md bg-warning me-3"></i><span>Elections</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $memberNavRestricted ? '#' : route('alumni.elections.expression-of-interest.status') }}"
                           class="nav-content-bttn open-font{{ $memberNavRestricted ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.elections.expression-of-interest.status') ? ' active' : '' }}"
                           @if($memberNavRestricted)
                               onclick="event.preventDefault(); alert(@json($memberNavMessage));"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-clipboard btn-round-md bg-gold-gradiant me-3"></i><span>EOI Status</span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasRole('alumni-agent'))
                    <li class="mt-3">
                        <div class="nav-caption fw-600 font-xssss text-grey-500"><span>Agent </span>Dashboard</div>
                    </li>
                    <li>
                        <a href="{{ route('agent.dashboard') }}"
                           class="nav-content-bttn open-font {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                            <i class="feather-briefcase btn-round-md bg-blue-gradiant me-3"></i><span>Agent Dashboard</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>

            <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1">
                <div class="nav-caption fw-600 font-xssss text-grey-500"><span></span> Account</div>
                <ul class="mb-1">
                    <li class="logo d-none d-xl-block d-lg-block"></li>
                    <li>
                        <a href="{{ route('profile.edit') }}" class="nav-content-bttn open-font h-auto pt-2 pb-2 {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="font-sm feather-settings me-3 text-grey-500"></i><span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-content-bttn open-font h-auto pt-2 pb-2 disabled-link" title="Messaging — Phase 2" tabindex="-1" aria-disabled="true">
                            <i class="font-sm feather-message-square me-3 text-grey-500"></i><span>Chat</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
    </div>
</nav>
