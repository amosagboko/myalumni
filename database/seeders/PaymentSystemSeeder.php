<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\FeeType;
use App\Models\FeeTemplate;
use App\Models\FeeRule;
use App\Models\Transaction;
use App\Models\AlumniCategory;
use App\Models\Alumni;
use App\Models\Office;
use Carbon\Carbon;

class PaymentSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Create fee types
        $feeTypes = [
            [
                'name' => 'Registration Fee',
                'code' => 'REG',
                'description' => 'Annual registration fee for alumni association membership',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Event Participation Fee',
                'code' => 'EVT',
                'description' => 'Fee for participating in alumni events',
                'is_active' => true,
                'is_system' => false,
            ],
            [
                'name' => 'Office Contest Fee',
                'code' => 'OFF',
                'description' => 'Fee for contesting in alumni association offices',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Certificate Fee',
                'code' => 'CERT',
                'description' => 'Fee for obtaining alumni certificates',
                'is_active' => true,
                'is_system' => false,
            ],
        ];

        foreach ($feeTypes as $feeType) {
            FeeType::firstOrCreate(
                ['code' => $feeType['code']],
                $feeType
            );
        }

        // Get or create some alumni categories
        $categories = [
            'Regular Member' => 'Regular alumni association member',
            'Life Member' => 'Life member of the alumni association',
            'Honorary Member' => 'Honorary member of the alumni association',
        ];
        
        $alumniCategories = [];
        foreach ($categories as $name => $description) {
            $alumniCategories[] = AlumniCategory::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'slug' => strtolower(str_replace(' ', '-', $name)),  // This creates the slug
                    'is_active' => true
                ]
            );
        }

        // Create offices for contest fees
        $offices = [
            [
                'name' => 'President',
                'code' => 'PRES',
                'description' => 'President of the Alumni Association',
            ],
            [
                'name' => 'Vice President',
                'code' => 'VP',
                'description' => 'Vice President of the Alumni Association',
            ],
            [
                'name' => 'Secretary General',
                'code' => 'SECGEN',
                'description' => 'Secretary General of the Alumni Association',
            ],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(
                ['code' => $office['code']],
                $office
            );
        }

        // Create fee templates with rules
        $currentYear = Carbon::now()->year;
        $templates = [
            [
                'name' => 'Annual Registration 2024',
                'fee_type_id' => FeeType::where('code', 'REG')->first()->id,
                'graduation_year' => $currentYear,
                'amount' => 5000.00,
                'description' => 'Annual registration fee for 2024',
                'valid_from' => Carbon::create($currentYear, 1, 1),
                'valid_until' => Carbon::create($currentYear, 12, 31),
                'is_active' => true,
                'rules' => [
                    [
                        'category_id' => $alumniCategories[0]->id, // Regular Member
                        'amount' => 5000.00,
                        'is_active' => true,
                    ],
                    [
                        'category_id' => $alumniCategories[1]->id, // Life Member
                        'amount' => 0.00,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Annual Conference 2024',
                'fee_type_id' => FeeType::where('code', 'EVT')->first()->id,
                'graduation_year' => $currentYear,
                'amount' => 15000.00,
                'description' => 'Annual alumni conference participation fee',
                'valid_from' => Carbon::create($currentYear, 1, 1),
                'valid_until' => Carbon::create($currentYear, 12, 31),
                'is_active' => true,
                'rules' => [
                    [
                        'category_id' => $alumniCategories[0]->id, // Regular Member
                        'amount' => 15000.00,
                        'is_active' => true,
                    ],
                    [
                        'category_id' => $alumniCategories[1]->id, // Life Member
                        'amount' => 10000.00,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Office Contest 2024',
                'fee_type_id' => FeeType::where('code', 'OFF')->first()->id,
                'graduation_year' => $currentYear,
                'amount' => 25000.00,
                'description' => 'Fee for contesting in alumni association offices',
                'valid_from' => Carbon::create($currentYear, 1, 1),
                'valid_until' => Carbon::create($currentYear, 12, 31),
                'is_active' => true,
                'rules' => [
                    [
                        'category_id' => $alumniCategories[0]->id, // Regular Member
                        'amount' => 25000.00,
                        'is_active' => true,
                    ],
                    [
                        'category_id' => $alumniCategories[1]->id, // Life Member
                        'amount' => 20000.00,
                        'is_active' => true,
                    ],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            // Remove any "rules" (or category logic) from the template data
            unset($templateData['rules']);
            // (Optionally, if you want to "upsert" (update or insert) a template, you can do so as follows.)
            $template = FeeTemplate::where([
                'name' => $templateData['name'],
                'fee_type_id' => $templateData['fee_type_id'],
                'graduation_year' => $templateData['graduation_year'],
                'valid_from' => $templateData['valid_from'],
            ])->first();
            if ($template) {
                $template->update($templateData);
            } else {
                FeeTemplate::create($templateData);
            }
        }

        // Create sample transactions for testing
        $alumni = Alumni::with('user')->take(5)->get();
        $statuses = ['pending', 'paid', 'failed'];
        $templates = FeeTemplate::all();

        foreach ($alumni as $alumni) {
            // Create 2-3 transactions per alumni
            $numTransactions = rand(2, 3);
            
            for ($i = 0; $i < $numTransactions; $i++) {
                $template = $templates->random();
                $status = $statuses[array_rand($statuses)];
                $createdAt = Carbon::now()->subDays(rand(1, 30));
                $isTestMode = rand(0, 1) === 1;
                
                $transaction = Transaction::create([
                    'alumni_id' => $alumni->id,
                    'fee_template_id' => $template->id,
                    'amount' => $template->amount,
                    'status' => $status,
                    'is_test_mode' => $isTestMode,
                    'payment_reference' => 'TEST_' . strtoupper(uniqid()),
                    'payment_method' => 'test',
                    'payment_provider' => 'test',
                    'payment_provider_reference' => 'TEST_' . strtoupper(uniqid()),
                    'payment_link' => $isTestMode ? 'https://test-payment.example.com/' . uniqid() : null,
                    'payment_details' => $isTestMode ? json_encode(['test' => true]) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $status === 'pending' ? $createdAt : $createdAt->addHours(rand(1, 24)),
                ]);

                if ($status === 'paid') {
                    $transaction->paid_at = $transaction->updated_at;
                    $transaction->save();

                    // If this is an office contest fee, create an application
                    if ($template->fee_type->code === 'OFF') {
                        $office = Office::inRandomOrder()->first();
                        \App\Models\OfficeContestApplication::create([
                            'alumni_id' => $alumni->id,
                            'office_id' => $office->id,
                            'transaction_id' => $transaction->id,
                            'status' => 'approved',
                            'application_details' => json_encode([
                                'motivation' => 'Test application for ' . $office->name,
                                'qualifications' => 'Test qualifications',
                            ]),
                            'approved_at' => $transaction->paid_at,
                        ]);
                    }
                } elseif ($status === 'failed') {
                    $transaction->failed_at = $transaction->updated_at;
                    $transaction->failure_reason = 'Test transaction failed';
                    $transaction->save();
                }
            }
        }
    }
} 