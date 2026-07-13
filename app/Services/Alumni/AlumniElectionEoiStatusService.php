<?php

namespace App\Services\Alumni;

use App\Models\User;
use App\Services\PaymentCompletionService;

class AlumniElectionEoiStatusService
{
    public function __construct(
        private readonly PaymentCompletionService $paymentCompletion,
        private readonly AlumniElectionHubService $hubService,
    ) {}

    public function pageData(?User $user): ?array
    {
        $alumni = $user?->alumni;
        $candidate = $alumni?->getCurrentExpressionOfInterest();

        if (! $candidate) {
            return null;
        }

        $candidate->loadMissing([
            'election',
            'office',
            'suggestedAgent.user',
            'screener',
            'alumni.user',
        ]);

        $pendingPaymentUrl = null;

        if ($candidate->isUnpaidPending() && $alumni && $candidate->office) {
            $transaction = $this->paymentCompletion->findPendingEoiTransaction(
                $alumni->id,
                $candidate->election_id,
                $candidate->election_office_id,
                $candidate->office->fee_type_id
            );

            if ($transaction) {
                $pendingPaymentUrl = route('alumni.payments.process', $transaction);
            }
        }

        return [
            'expressionOfInterest' => $candidate,
            'statusLabel' => $candidate->status_label,
            'statusBadgeClass' => $this->hubService->eoiStatusBadgeClass($candidate->status),
            'agentStatusBadgeClass' => $this->agentStatusBadgeClass($candidate->agent_status),
            'agentName' => $candidate->suggestedAgent?->user?->name,
            'canManageAgent' => $candidate->isApproved() || $candidate->isPaidAwaitingScreening() || $candidate->isUnpaidPending(),
            'pendingPaymentUrl' => $pendingPaymentUrl,
        ];
    }

    public function statusBadgeClass(?string $status): string
    {
        return $this->hubService->eoiStatusBadgeClass($status);
    }

    public function agentStatusBadgeClass(?string $agentStatus): string
    {
        return match ($agentStatus) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'pending' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/'.$path);
    }
}
