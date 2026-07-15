<?php

namespace App\Http\Controllers\ElcomChairman;

use App\Http\Controllers\Controller;
use App\Models\AccreditedVoter;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\FeeType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $availableYears = Election::query()
            ->whereNotNull('election_year')
            ->distinct()
            ->orderByDesc('election_year')
            ->pluck('election_year')
            ->map(fn ($year) => (int) $year)
            ->values();

        $defaultYear = Election::query()
            ->whereNotNull('election_year')
            ->where('status', '!=', 'archived')
            ->max('election_year');

        if ($defaultYear === null) {
            $defaultYear = $availableYears->first();
        }

        $selectedYear = $request->integer('year') ?: ($defaultYear ? (int) $defaultYear : null);

        if ($selectedYear && $availableYears->isNotEmpty() && ! $availableYears->contains($selectedYear)) {
            $selectedYear = (int) $defaultYear;
        }

        $electionIds = $this->cycleElections($selectedYear)->pluck('id');

        $activeElections = $this->cycleElections($selectedYear)
            ->whereIn('status', ['accreditation', 'voting', 'eoi', 'eoi_closed'])
            ->count();

        $totalCandidates = $electionIds->isEmpty()
            ? 0
            : Candidate::query()
                ->whereIn('election_id', $electionIds)
                ->where(function ($query) {
                    $query->where('has_paid_screening_fee', true)
                        ->orWhereIn('status', [
                            Candidate::STATUS_PAID_AWAITING_SCREENING,
                            Candidate::STATUS_APPROVED,
                        ]);
                })
                ->count();

        $totalVotes = $electionIds->isEmpty()
            ? 0
            : AccreditedVoter::query()
                ->whereIn('election_id', $electionIds)
                ->where('has_voted', true)
                ->count();

        $duesFeeTypeIds = FeeType::whereIn('code', ['subscription', FeeType::ANNUAL_DUE_CODE])->pluck('id');
        $paidDuesAlumni = $duesFeeTypeIds->isEmpty()
            ? 0
            : Transaction::where('status', 'paid')
                ->whereHas('feeTemplate', fn ($query) => $query->whereIn('fee_type_id', $duesFeeTypeIds))
                ->distinct('alumni_id')
                ->count('alumni_id');

        $recentElections = $this->cycleElections($selectedYear)
            ->latest()
            ->take(5)
            ->get();

        $totalElections = $this->cycleElections($selectedYear)->count();
        $completedElections = $this->cycleElections($selectedYear)->where('status', 'completed')->count();
        $pendingElections = $this->cycleElections($selectedYear)->where('status', 'draft')->count();
        $archivedElections = $this->cycleElections($selectedYear)->where('status', 'archived')->count();
        $totalAccreditedVoters = $electionIds->isEmpty()
            ? 0
            : AccreditedVoter::query()->whereIn('election_id', $electionIds)->count();

        $cycleLabel = $this->cycleElections($selectedYear)
            ->whereNotNull('cycle_label')
            ->where('cycle_label', '!=', '')
            ->where('status', '!=', 'archived')
            ->orderByDesc('id')
            ->value('cycle_label');

        return view('elcom-chairman.dashboard', compact(
            'activeElections',
            'totalCandidates',
            'totalVotes',
            'paidDuesAlumni',
            'recentElections',
            'totalElections',
            'completedElections',
            'pendingElections',
            'archivedElections',
            'totalAccreditedVoters',
            'availableYears',
            'selectedYear',
            'cycleLabel',
        ));
    }

    protected function cycleElections(?int $selectedYear): Builder
    {
        return Election::query()
            ->when($selectedYear, fn (Builder $query) => $query->where('election_year', $selectedYear));
    }
}
