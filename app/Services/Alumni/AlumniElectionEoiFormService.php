<?php

namespace App\Services\Alumni;

use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\FeeTemplate;

class AlumniElectionEoiFormService
{
    public function screeningFeeForOffice(ElectionOffice $office): ?FeeTemplate
    {
        return FeeTemplate::where('fee_type_id', $office->fee_type_id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>', now());
            })
            ->first();
    }

    public function formPageData(Election $election, ElectionOffice $office): array
    {
        return [
            'election' => $election,
            'office' => $office,
            'screeningFee' => $this->screeningFeeForOffice($office),
            'remainingSlots' => $office->getRemainingApplicantSlots(),
            'maxCandidates' => $office->max_candidates,
        ];
    }

    public function previewPageData(
        Election $election,
        ElectionOffice $office,
        ?string $manifesto,
        array $documents,
        string $passport,
        string $previewToken,
    ): array {
        return [
            'election' => $election,
            'office' => $office,
            'screeningFee' => $this->screeningFeeForOffice($office),
            'manifesto' => $manifesto,
            'documents' => $documents,
            'passport' => $passport,
            'previewToken' => $previewToken,
            'documentCount' => count($documents),
        ];
    }

    public function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/'.$path);
    }
}
