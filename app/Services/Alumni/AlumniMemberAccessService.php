<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\User;
use App\Services\PortalModeService;
use Illuminate\Http\Request;

class AlumniMemberAccessService
{
    /** Roles that use the alumni member portal and its access gate. */
    public const MEMBER_PORTAL_ROLES = [
        'alumni',
        'alumni-president',
        'alumni-agent',
    ];

    /**
     * Full member access requires biodata, cohort statutory fees, and any active annual due.
     */
    public function status(?Alumni $alumni): array
    {
        $needsBioData = ! $alumni
            || ! $alumni->contact_address
            || ! $alumni->phone_number
            || ! $alumni->qualification_type;

        $needsPayments = false;

        if ($alumni) {
            try {
                $activeFees = $alumni->getActiveFees();
                $needsPayments = $activeFees->contains(fn ($fee) => ! $fee->isPaid());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $isFullMember = ! $needsBioData && ! $needsPayments;

        return [
            'needsBioData' => $needsBioData,
            'needsPayments' => $needsPayments,
            'isFullMember' => $isFullMember,
            // Backward-compatible keys used across the app
            'allOk' => $isFullMember,
        ];
    }

    public function isFullMember(?Alumni $alumni): bool
    {
        return $this->status($alumni)['isFullMember'];
    }

    public function restrictionMessage(array $status): string
    {
        if ($status['needsBioData'] ?? false) {
            return 'Please complete your bio-data form to access this feature.';
        }

        if ($status['needsPayments'] ?? false) {
            return 'Please complete all outstanding payments (statutory and annual dues) to access this feature.';
        }

        return 'Please complete your profile and payments to access this feature.';
    }

    public function redirectRoute(array $status): string
    {
        if ($status['needsBioData'] ?? false) {
            return 'alumni.bio-data';
        }

        return 'alumni.payments.index';
    }

    /**
     * Apply the member gate only for portal users, not while using operational dashboards.
     */
    public function shouldEnforcePortalGate(?User $user, ?Request $request = null): bool
    {
        if (! $user || ! $user->alumni) {
            return false;
        }

        if (! $user->hasAnyRole(self::MEMBER_PORTAL_ROLES)) {
            return false;
        }

        $request ??= request();

        if (app(PortalModeService::class)->getMode($user, $request) === PortalModeService::MODE_OPERATIONAL) {
            return false;
        }

        if ($request && $this->isOperationalRoute($request)) {
            return false;
        }

        return true;
    }

    public function isOperationalRoute(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*',
            'alumni-relations-officer',
            'alumni-relations-officer/*',
            'alumni-president',
            'alumni-president/*',
            'elcom-chairman',
            'elcom-chairman/*',
            'elcom',
            'elcom/*',
            'student-affairs',
            'student-affairs/*',
            'academic-affairs',
            'academic-affairs/*',
            'agent',
            'agent/*',
        );
    }

    /**
     * Routes a limited member may still visit (pay, onboard, view status, settings).
     */
    public function isAllowedLimitedRoute(Request $request): bool
    {
        return $request->is(
            'payments',
            'payments/*',
            'bio-data',
            'bio-data/*',
            'profile',
            'profile/*',
            'alumni/clearance-status',
            'alumni/onboarding',
            'alumni/onboarding/*',
            'logout',
            'dashboard',
            'livewire/*',
            'portal/switch',
        ) || $request->routeIs(
            'alumni.payments.*',
            'alumni.bio-data',
            'alumni.bio-data.update',
            'profile.*',
            'alumni.clearance-status',
            'alumni.onboarding',
            'alumni.onboarding.*',
            'logout',
            'dashboard',
            'portal.switch',
        );
    }
}
