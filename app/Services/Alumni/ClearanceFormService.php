<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\User;
use Illuminate\Support\Str;

class ClearanceFormService
{
    public function accessStatus(?Alumni $alumni): array
    {
        $needsBioData = ! $alumni
            || ! $alumni->contact_address
            || ! $alumni->phone_number
            || ! $alumni->qualification_type;

        $needsPayments = false;

        if ($alumni) {
            try {
                $activeFees = $alumni->getActiveFees();
                $unpaidFees = $activeFees->filter(fn ($fee) => ! $fee->isPaid());
                $needsPayments = $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return [
            'needsBioData' => $needsBioData,
            'needsPayments' => $needsPayments,
            'allOk' => ! $needsBioData && ! $needsPayments,
        ];
    }

    public function canAccess(?Alumni $alumni): bool
    {
        return $this->accessStatus($alumni)['allOk'];
    }

    public function context(User $user, Alumni $alumni): array
    {
        return [
            'user' => $user,
            'alumni' => $alumni,
            'generatedAt' => now(),
            'avatarWebUrl' => $this->avatarWebUrl($user),
            'avatarPdfPath' => $this->avatarPdfPath($user),
            'sections' => $this->sections($user, $alumni),
        ];
    }

    public function pdfFileName(User $user): string
    {
        $slug = Str::slug($user->name ?: 'alumni');

        return 'clearance_form_'.$slug.'_'.now()->format('Ymd_His').'.pdf';
    }

    public function sections(User $user, Alumni $alumni): array
    {
        return [
            [
                'title' => 'Personal Information',
                'layout' => 'personal',
                'fields' => [
                    ['label' => 'Full Name', 'value' => $user->name],
                    ['label' => 'Gender', 'value' => $user->gender ? ucfirst($user->gender) : null],
                    ['label' => 'Title', 'value' => $alumni->title],
                    ['label' => 'Matriculation Number', 'value' => $alumni->matric_number],
                    ['label' => 'Date of Birth', 'value' => $alumni->date_of_birth],
                    ['label' => 'LGA', 'value' => $alumni->lga],
                    ['label' => 'State of Origin', 'value' => $alumni->state],
                    ['label' => 'Nationality', 'value' => $alumni->nationality],
                ],
            ],
            [
                'title' => 'Contact Information',
                'fields' => [
                    ['label' => 'Contact Address', 'value' => $alumni->contact_address],
                    ['label' => 'Email', 'value' => $user->email],
                    ['label' => 'Phone/WhatsApp', 'value' => $alumni->phone_number],
                ],
            ],
            [
                'title' => 'Academic Information',
                'fields' => [
                    ['label' => 'Year of Entry', 'value' => $alumni->year_of_entry],
                    ['label' => 'Year of Graduation', 'value' => $alumni->year_of_graduation],
                    ['label' => 'Department', 'value' => $alumni->department],
                    ['label' => 'Faculty', 'value' => $alumni->faculty],
                    ['label' => 'Qualification Type', 'value' => $alumni->qualification_type],
                    ['label' => 'Qualification Detail', 'value' => $alumni->qualification_details],
                ],
            ],
            [
                'title' => 'Professional Information',
                'fields' => [
                    ['label' => 'Present Employer', 'value' => $alumni->present_employer],
                    ['label' => 'Present Post/Designation', 'value' => $alumni->present_designation],
                    ['label' => 'Membership of Professional Bodies', 'value' => $alumni->professional_bodies],
                ],
            ],
            [
                'title' => 'Additional Information',
                'fields' => [
                    ['label' => 'Responsibilities as a Student', 'value' => $alumni->student_responsibilities],
                    ['label' => 'Hobbies', 'value' => $alumni->hobbies],
                    ['label' => 'Other Relevant Information', 'value' => $alumni->other_information],
                ],
            ],
        ];
    }

    public function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return (string) $value;
    }

    private function avatarWebUrl(User $user): string
    {
        return $user->avatar
            ? asset('storage/'.$user->avatar)
            : asset('images/default-avatar.png');
    }

    private function avatarPdfPath(User $user): ?string
    {
        $path = $user->avatar
            ? public_path('storage/'.$user->avatar)
            : public_path('images/default-avatar.png');

        return file_exists($path) ? $path : null;
    }
}
