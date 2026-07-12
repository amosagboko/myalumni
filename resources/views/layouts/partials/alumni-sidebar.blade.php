<nav class="navigation scroll-bar">
    <div class="container ps-0 pe-0">
        <div class="nav-content">
            {{-- Social feeds (template: New Feeds) --}}
            <div class="nav-wrap bg-white bg-transparent-card rounded-xxl shadow-xss pt-3 pb-1 mb-2 mt-2">
                <div class="nav-caption fw-600 font-xssss text-grey-500"><span>New </span>Feeds</div>
                <ul class="mb-1 top-content">
                    <li class="logo d-none d-xl-block d-lg-block"></li>
                    <li>
                        <a href="{{ route('alumni.home') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni.home') ? 'active' : '' }}">
                            <i class="feather-activity btn-round-md bg-blue-gradiant me-3"></i><span>Newsfeed</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('friends') }}" class="nav-content-bttn open-font {{ request()->routeIs('friends') ? 'active' : '' }}">
                            <i class="feather-users btn-round-md bg-primary-gradiant me-3"></i><span>Connections</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('alumni.events') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni.events*') ? 'active' : '' }}">
                            <i class="feather-calendar btn-round-md bg-gold-gradiant me-3"></i><span>Official Events</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('alumni.members.show', auth()->user()) }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni.members.show') && (int) request()->route('user')?->id === auth()->id() ? 'active' : '' }}">
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
                    <li>
                        <a href="{{ route('alumni.payments.history') }}" class="nav-content-bttn open-font {{ request()->routeIs('alumni.payments.history') ? 'active' : '' }}">
                            <i class="feather-credit-card btn-round-md bg-red-gradiant me-3"></i><span>Payment History</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $alumniClearanceDisabled ? '#' : route('reports') }}"
                           class="nav-content-bttn open-font{{ $alumniClearanceDisabled ? ' disabled-link' : '' }}{{ request()->routeIs('reports*') ? ' active' : '' }}"
                           @if($alumniClearanceDisabled)
                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access the Clearance Form.');"
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
                    @if(auth()->user()->hasRole('alumni'))
                    <li>
                        <a href="{{ $alumniElectionLinksDisabled ? '#' : route('alumni.elections') }}"
                           class="nav-content-bttn open-font{{ $alumniElectionLinksDisabled ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.elections*') && !request()->routeIs('alumni.elections.expression-of-interest.status') ? ' active' : '' }}"
                           @if($alumniElectionLinksDisabled)
                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access Elections.');"
                               tabindex="-1" aria-disabled="true"
                           @endif
                        >
                            <i class="feather-flag btn-round-md bg-warning me-3"></i><span>Elections</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $alumniElectionLinksDisabled ? '#' : route('alumni.elections.expression-of-interest.status') }}"
                           class="nav-content-bttn open-font{{ $alumniElectionLinksDisabled ? ' disabled-link' : '' }}{{ request()->routeIs('alumni.elections.expression-of-interest.status') ? ' active' : '' }}"
                           @if($alumniElectionLinksDisabled)
                               onclick="event.preventDefault(); alert('Please complete your profile and payments to access EOI Status.');"
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
