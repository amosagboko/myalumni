<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Election;
use App\Models\ElectionOffice;
use App\Models\Candidate;
use App\Models\AccreditedVoter;
use App\Models\Vote;
use App\Models\ElectionResult;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create test categories and fees
        $this->createTestCategoriesAndFees();
        
        // Create test users with roles
        $this->createTestUsers();
        
        // Create test elections in different states
        $this->createTestElections();
    }

    private function createTestCategoriesAndFees()
    {
        $categories = [
            'Regular Member' => 'regular',
            'Life Member' => 'life',
            'Honorary Member' => 'honorary',
        ];

        foreach ($categories as $name => $slug) {
            AlumniCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => "Test category for $name",
                    'is_active' => true,
                ]
            );
        }

        $subscriptionType = FeeType::firstOrCreate(
            ['code' => 'subscription'],
            [
                'name' => 'Subscription Registration Fee',
                'description' => 'Initial alumni subscription',
                'is_active' => true,
                'is_system' => true,
            ]
        );

        foreach ([2018, 2024] as $graduationYear) {
            FeeTemplate::firstOrCreate(
                [
                    'fee_type_id' => $subscriptionType->id,
                    'graduation_year' => $graduationYear,
                ],
                [
                    'amount' => 2000,
                    'description' => "Alumni subscription fee {$graduationYear}",
                    'is_active' => true,
                    'valid_from' => now()->subYear(),
                    'valid_until' => null,
                ]
            );
        }

        // Normalize legacy test fee code so payment sync recognizes EOI transactions.
        FeeType::where('code', 'screening')->update(['code' => 'screening_fee']);

        $screeningType = FeeType::firstOrCreate(
            ['code' => 'screening_fee'],
            [
                'name' => 'Election Screening Fee',
                'description' => 'Election candidate screening fee',
                'is_active' => true,
                'is_system' => false,
            ]
        );

        foreach ([2018, 2024] as $graduationYear) {
            FeeTemplate::firstOrCreate(
                [
                    'fee_type_id' => $screeningType->id,
                    'graduation_year' => $graduationYear,
                ],
                [
                    'amount' => 5000,
                    'description' => "Election screening fee {$graduationYear}",
                    'is_active' => true,
                    'valid_from' => now()->subYear(),
                    'valid_until' => null,
                ]
            );
        }
    }

    private function createTestUsers()
    {
        // Create administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Test Administrator',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        $admin->assignRole('administrator');

        // Create ELCOM chairman
        $elcom = User::firstOrCreate(
            ['email' => 'elcom@test.com'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Test ELCOM Chairman',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        $elcom->assignRole('elcom-chairman');

        // Create Alumni Relations Officer
        $aro = User::firstOrCreate(
            ['email' => 'aro@test.com'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Test ARO',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );
        $aro->assignRole('alumni-relations-officer');

        // Alumni President (dual portal: president office + member portal)
        $president = User::firstOrCreate(
            ['email' => 'president@test.com'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Test Alumni President',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $president->assignRole('alumni-president');

        $presidentAlumni = Alumni::firstOrCreate(
            ['user_id' => $president->id],
            [
                'matric_number' => 'MAT/PRES/1',
                'programme' => 'B.Sc Political Science',
                'department' => 'Political Science',
                'faculty' => 'Social Sciences',
                'year_of_graduation' => 2018,
                'category_id' => AlumniCategory::query()->value('id'),
                'created_by' => $admin->id,
                'date_of_birth' => '1990-05-15',
                'state' => 'Nasarawa',
                'lga' => 'Lafia',
                'year_of_entry' => 2014,
                'gender' => 'male',
                'title' => 'Mr.',
                'nationality' => 'Nigerian',
                'contact_address' => '1 President Avenue, Lafia',
                'phone_number' => '08030000001',
                'qualification_type' => 'B.Sc',
                'qualification_details' => 'B.Sc Political Science',
            ]
        );
        $this->createFeePayments($presidentAlumni);

        // Create test alumni with different statuses
        $alumniStatuses = ['active', 'suspended', 'pending'];
        $categories = AlumniCategory::all();

        for ($i = 1; $i <= 20; $i++) {
            $email = "alumni$i@test.com";
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'uuid' => Str::uuid(),
                    'name' => "Test Alumni $i",
                    'password' => bcrypt('password123'),
                    'status' => $alumniStatuses[$i % 3]
                ]
            );
            $user->assignRole('alumni');

            // Generate a random date of birth (between 18 and 65 years ago)
            $minAge = 18;
            $maxAge = 65;
            $randomAge = rand($minAge, $maxAge);
            $dateOfBirth = now()->subYears($randomAge)->subDays(rand(0, 365));

            // Create or update alumni profile
            $alumni = Alumni::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'matric_number' => "MAT/2024/$i",
                    'programme' => "Test Programme $i",
                    'department' => "Test Department $i",
                    'faculty' => "Test Faculty $i",
                    'year_of_graduation' => 2024,
                    'category_id' => $categories->random()->id,
                    'created_by' => $admin->id,
                    'date_of_birth' => $dateOfBirth,
                    'state' => "Test State $i",
                    'lga' => "Test LGA $i",
                    'year_of_entry' => 2020,
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'title' => 'Mr.',
                    'nationality' => 'Nigerian',
                    'contact_address' => "Test Address $i",
                    'phone_number' => '080' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'qualification_type' => 'Bachelor',
                    'qualification_details' => "Bachelor of Science in Test Programme $i",
                    'present_employer' => "Test Company $i",
                    'present_designation' => "Test Position $i",
                    'professional_bodies' => "Test Professional Body $i",
                    'student_responsibilities' => "Test Responsibility $i",
                    'hobbies' => "Reading, Sports, Music",
                    'other_information' => "Additional test information for alumni $i"
                ]
            );

            // Create fee payments for active alumni
            if ($user->status === 'active') {
                $this->createFeePayments($alumni);
            }
        }
    }

    private function createFeePayments($alumni)
    {
        $subscriptionType = FeeType::where('code', 'subscription')->first();

        if (! $subscriptionType) {
            return;
        }

        $template = FeeTemplate::query()
            ->where('fee_type_id', $subscriptionType->id)
            ->where('graduation_year', $alumni->year_of_graduation)
            ->first();

        if (! $template) {
            return;
        }

        $alreadyPaid = Transaction::query()
            ->where('alumni_id', $alumni->id)
            ->where('fee_template_id', $template->id)
            ->where('status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            return;
        }

        Transaction::create([
            'alumni_id' => $alumni->id,
            'fee_template_id' => $template->id,
            'amount' => $template->amount,
            'status' => 'paid',
            'payment_reference' => 'TEST-PAY-'.uniqid(),
            'payment_provider' => 'credocentral',
            'paid_at' => now(),
            'is_test_mode' => true,
        ]);
    }

    private function createTestElections()
    {
        // Create elections in different states
        $this->createDraftElection();
        $this->createAccreditationElection();
        $this->createVotingElection();
        $this->createCompletedElection();
    }

    private function createDraftElection()
    {
        $election = Election::create([
            'title' => '2024 Draft Election',
            'description' => 'Test election in draft state',
            'accreditation_start' => now()->addDays(10),
            'accreditation_end' => now()->addDays(20),
            'voting_start' => now()->addDays(25),
            'voting_end' => now()->addDays(30),
            'status' => 'draft',
            'screening_fee' => 1000.00
        ]);

        $this->createElectionOffices($election);
        $this->createDraftCandidates($election);
    }

    private function createAccreditationElection()
    {
        $election = Election::create([
            'title' => '2024 Accreditation Election',
            'description' => 'Test election in accreditation phase',
            'accreditation_start' => now()->subDays(5),
            'accreditation_end' => now()->addDays(5),
            'voting_start' => now()->addDays(10),
            'voting_end' => now()->addDays(15),
            'status' => 'accreditation',
            'screening_fee' => 1000.00
        ]);

        $this->createElectionOffices($election);
        $this->createAccreditedVoters($election);
    }

    private function createVotingElection()
    {
        $election = Election::create([
            'title' => '2024 Voting Election',
            'description' => 'Test election in voting phase',
            'accreditation_start' => now()->subDays(15),
            'accreditation_end' => now()->subDays(5),
            'voting_start' => now()->subDays(2),
            'voting_end' => now()->addDays(3),
            'status' => 'voting',
            'screening_fee' => 1000.00
        ]);

        $this->createElectionOffices($election);
        $this->createVotingCandidates($election);
        $this->createVotingVoters($election);
    }

    private function createCompletedElection()
    {
        $election = Election::create([
            'title' => '2024 Completed Election',
            'description' => 'Test completed election',
            'accreditation_start' => now()->subDays(30),
            'accreditation_end' => now()->subDays(20),
            'voting_start' => now()->subDays(15),
            'voting_end' => now()->subDays(5),
            'status' => 'completed',
            'screening_fee' => 1000.00
        ]);

        $this->createElectionOffices($election);
        $this->createCompletedCandidates($election);
        $this->createCompletedVoters($election);
        $this->createElectionResults($election);
    }

    private function createElectionOffices($election)
    {
        $feeType = FeeType::where('code', 'screening_fee')->firstOrFail();

        $offices = [
            [
                'title' => 'President',
                'description' => 'Head of the alumni association',
                'max_candidates' => 3,
                'max_terms' => 2,
            ],
            [
                'title' => 'Vice President',
                'description' => 'Deputy head of the alumni association',
                'max_candidates' => 3,
                'max_terms' => 2,
            ],
            [
                'title' => 'Secretary',
                'description' => 'Handles association documentation',
                'max_candidates' => 2,
                'max_terms' => 2,
            ],
        ];

        foreach ($offices as $office) {
            ElectionOffice::create([
                'election_id' => $election->id,
                'title' => $office['title'],
                'description' => $office['description'],
                'max_candidates' => $office['max_candidates'],
                'max_terms' => $office['max_terms'],
                'fee_type_id' => $feeType->id,
                'is_active' => true,
            ]);
        }
    }

    private function createDraftCandidates($election)
    {
        $offices = $election->offices;
        $alumni = Alumni::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($offices as $office) {
            for ($i = 0; $i < $office->max_candidates; $i++) {
                $randomAlumni = $alumni->random();
                Candidate::create([
                    'election_id' => $election->id,
                    'election_office_id' => $office->id,
                    'alumni_id' => $randomAlumni->id,
                    'status' => 'pending',
                    'has_paid_screening_fee' => false
                ]);
            }
        }
    }

    private function createAccreditedVoters($election)
    {
        $alumni = Alumni::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($alumni as $alumni) {
            AccreditedVoter::create([
                'election_id' => $election->id,
                'alumni_id' => $alumni->id,
                'has_voted' => false,
                'accredited_at' => now(),
                'voted_at' => null
            ]);
        }
    }

    private function createVotingCandidates($election)
    {
        $offices = $election->offices;
        $alumni = Alumni::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($offices as $office) {
            for ($i = 0; $i < $office->max_candidates; $i++) {
                $randomAlumni = $alumni->random();
                Candidate::create([
                    'election_id' => $election->id,
                    'election_office_id' => $office->id,
                    'alumni_id' => $randomAlumni->id,
                    'status' => 'approved',
                    'has_paid_screening_fee' => true
                ]);
            }
        }
    }

    private function createVotingVoters($election)
    {
        $alumni = Alumni::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($alumni as $alumni) {
            AccreditedVoter::create([
                'election_id' => $election->id,
                'alumni_id' => $alumni->id,
                'has_voted' => rand(0, 1), // Randomly mark some as voted
                'accredited_at' => now()->subDays(rand(1, 5)),
                'voted_at' => (rand(0, 1) ? now() : null)
            ]);
        }
    }

    private function createCompletedCandidates($election)
    {
        $offices = $election->offices;
        $alumni = Alumni::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($offices as $office) {
            for ($i = 0; $i < $office->max_candidates; $i++) {
                $randomAlumni = $alumni->random();
                Candidate::create([
                    'election_id' => $election->id,
                    'election_office_id' => $office->id,
                    'alumni_id' => $randomAlumni->id,
                    'status' => 'approved',
                    'has_paid_screening_fee' => true
                ]);
            }
        }
    }

    private function createCompletedVoters($election)
    {
        $alumni = Alumni::whereHas('user', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($alumni as $alumni) {
            AccreditedVoter::create([
                'election_id' => $election->id,
                'alumni_id' => $alumni->id,
                'has_voted' => true,
                'accredited_at' => now()->subDays(rand(20, 25)),
                'voted_at' => (rand(0, 1) ? now() : null)
            ]);
        }
    }

    private function createElectionResults($election)
    {
        foreach ($election->offices as $office) {
            $candidates = $office->candidates;
            $totalVotes = 0;
            $votes = [];

            // Generate random votes for each candidate
            $availableVoters = $election->accreditedVoters->shuffle();
            $voterIndex = 0;
            
            foreach ($candidates as $candidate) {
                $voteCount = min(rand(1, 5), count($availableVoters) - $voterIndex); // Limit votes to available voters
                $votes[$candidate->id] = $voteCount;
                $totalVotes += $voteCount;

                // Create vote records
                for ($i = 0; $i < $voteCount; $i++) {
                    if ($voterIndex >= count($availableVoters)) break;
                    
                    Vote::create([
                        'election_id' => $election->id,
                        'election_office_id' => $office->id,
                        'candidate_id' => $candidate->id,
                        'accredited_voter_id' => $availableVoters[$voterIndex]->id
                    ]);
                    $voterIndex++;
                }
            }

            // Create election results
            foreach ($candidates as $candidate) {
                $isWinner = $votes[$candidate->id] === max($votes);
                ElectionResult::create([
                    'election_id' => $election->id,
                    'election_office_id' => $office->id,
                    'candidate_id' => $candidate->id,
                    'total_votes' => $votes[$candidate->id],
                    'is_winner' => $isWinner,
                    'declared_at' => now()->subDays(5)
                ]);
            }
        }
    }
} 