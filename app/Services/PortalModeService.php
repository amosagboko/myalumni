<?php



namespace App\Services;



use App\Models\User;

use App\Services\Alumni\AlumniMemberAccessService;

use Illuminate\Http\Request;



class PortalModeService

{

    public const MODE_OPERATIONAL = 'operational';



    public const MODE_MEMBER = 'member';



    public const SESSION_KEY = 'portal_mode';



    public const PRESIDENT_ROLE = 'alumni-president';



    /** Staff roles that use an operational dashboard shell. */

    public const STAFF_OPERATIONAL_ROLES = [

        'super-admin',

        'support-admin',

        'administrator',

        'elcom-chairman',

        'elcom',

        'alumni-relations-officer',

        'student-affairs',

        'academic-affairs',

        'alumni-agent',

    ];



    public function __construct(

        private readonly AlumniMemberAccessService $memberAccess

    ) {}



    public function isAlumniPresident(?User $user): bool

    {

        return $user?->hasRole(self::PRESIDENT_ROLE) ?? false;

    }



    public function hasOperationalAccess(?User $user): bool

    {

        if (! $user) {

            return false;

        }



        return $this->isAlumniPresident($user)

            || $user->hasAnyRole(self::STAFF_OPERATIONAL_ROLES);

    }



    public function hasMemberAccess(?User $user): bool

    {

        if (! $user) {

            return false;

        }



        return $user->hasAnyRole(AlumniMemberAccessService::MEMBER_PORTAL_ROLES) || (bool) $user->alumni;

    }



    /**

     * Dual access: alumni-president with a linked alumni record, or staff + member roles.

     */

    public function hasDualPortalAccess(?User $user): bool

    {

        if (! $user) {

            return false;

        }



        if ($this->isAlumniPresident($user)) {

            return (bool) $user->alumni;

        }



        return $user->hasAnyRole(self::STAFF_OPERATIONAL_ROLES) && $this->hasMemberAccess($user);

    }



    public function getMode(?User $user = null, ?Request $request = null): string

    {

        $user ??= auth()->user();



        if (! $user) {

            return self::MODE_MEMBER;

        }



        if (! $this->hasDualPortalAccess($user)) {

            return $this->hasOperationalAccess($user)

                ? self::MODE_OPERATIONAL

                : self::MODE_MEMBER;

        }



        $sessionMode = session(self::SESSION_KEY);



        if (in_array($sessionMode, [self::MODE_OPERATIONAL, self::MODE_MEMBER], true)) {

            return $sessionMode;

        }



        if ($request) {

            if ($this->memberAccess->isOperationalRoute($request)) {

                return self::MODE_OPERATIONAL;

            }



            if ($this->isMemberRoute($request)) {

                return self::MODE_MEMBER;

            }

        }



        return self::MODE_OPERATIONAL;

    }



    public function setMode(string $mode): void

    {

        if (! in_array($mode, [self::MODE_OPERATIONAL, self::MODE_MEMBER], true)) {

            return;

        }



        session([self::SESSION_KEY => $mode]);

    }



    public function syncModeFromRequest(?User $user, Request $request): void

    {

        if (! $user || ! $this->hasDualPortalAccess($user)) {

            return;

        }



        if ($this->memberAccess->isOperationalRoute($request)) {

            $this->setMode(self::MODE_OPERATIONAL);



            return;

        }



        if ($this->isMemberRoute($request)) {

            $this->setMode(self::MODE_MEMBER);

        }

    }



    public function resolveHomeRoute(?User $user = null, ?Request $request = null): string

    {

        $user ??= auth()->user();



        if (! $user) {

            return 'login';

        }



        if ($this->getMode($user, $request) === self::MODE_MEMBER) {

            return $this->getMemberHomeRoute($user);

        }



        return $this->getOperationalHomeRoute($user);

    }



    public function getMemberHomeRoute(User $user): string

    {

        if ($this->hasMemberAccess($user)) {

            return 'alumni.home';

        }



        return 'login';

    }



    public function getOperationalHomeRoute(User $user): string

    {

        if ($user->hasRole('administrator')) {

            return 'admin.dashboard';

        }



        if ($user->hasRole('elcom-chairman')) {

            return 'elcom-chairman.dashboard';

        }



        if ($user->hasRole(self::PRESIDENT_ROLE)) {

            return 'alumni-president.dashboard';

        }



        if ($user->hasRole('elcom')) {

            return 'elcom.elections.index';

        }



        if ($user->hasRole('alumni-relations-officer')) {

            return 'alumni-relations-officer.home';

        }



        if ($user->hasRole('student-affairs')) {

            return 'student-affairs.home';

        }



        if ($user->hasRole('academic-affairs')) {

            return 'academic-affairs.home';

        }



        if ($user->hasRole('alumni-agent')) {

            return 'agent.dashboard';

        }



        if ($this->hasMemberAccess($user)) {

            return $this->getMemberHomeRoute($user);

        }



        return 'login';

    }



    public function operationalSwitchLabel(?User $user): string

    {

        return $this->isAlumniPresident($user) ? 'President Office' : 'Operations';

    }



    public function isMemberRoute(Request $request): bool

    {

        if ($this->memberAccess->isOperationalRoute($request)) {

            return false;

        }



        return $request->is(

            'alumni',

            'alumni/*',

            'friends',

            'friends/*',

            'bio-data',

            'bio-data/*',

            'payments',

            'payments/*',

            'reports',

            'reports/*',

            'candidate',

            'candidate/*',

        ) || $request->routeIs(

            'alumni.*',

            'friends',

            'friends.*',

            'alumni.bio-data',

            'alumni.bio-data.update',

            'alumni.payments.*',

            'reports',

            'reports.*',

            'candidate.*',

        );

    }

}


