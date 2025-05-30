<?php

use App\Livewire\Friends;
use App\Livewire\Homepage;
use App\Livewire\Returnpost;
use App\Livewire\FollowSystem;
use App\Livewire\Admin\CreateUser;
use App\Livewire\Admin\ManageUsers;
use Illuminate\Support\Facades\Route;
use App\Livewire\Components\CreatePost;
use App\Http\Controllers\CreateAllEvent;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Friends as ControllersFriends;
use App\Livewire\FriendRequestManager;
use App\Http\Controllers\UploadAlumniController;
use App\Http\Controllers\CreateEventController;
use App\Http\Controllers\AlumniOnboardingController;
use App\Http\Controllers\FeeTemplateController;
use App\Http\Controllers\AlumniYearController;
use App\Http\Controllers\AlumniBioDataController;
use App\Http\Controllers\AlumniPaymentController;
use App\Http\Controllers\Elcom\ElectionController;
use App\Http\Controllers\AlumniElectionController;
use App\Http\Controllers\AlumniCategoryController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Agent\CandidateController;
use App\Http\Controllers\Candidate\AgentController;
use App\Http\Controllers\ARODashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminStatisticsController;

Route::get('/', function () {
    return view('welcome');
});

// Admin routes - moved outside auth group
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', ManageUsers::class)->name('users');
    Route::get('/users/create', function () {
        return view('admin.users.create');
    })->name('users.create');
    
    // Statistics Routes
    Route::get('/statistics/transactions', [AdminStatisticsController::class, 'transactions'])->name('statistics.transactions');
    Route::get('/statistics/alumni-distribution', [AdminStatisticsController::class, 'alumniDistribution'])->name('statistics.alumni-distribution');
    
    // Fee Type Management
    Route::resource('fee-types', FeeTypeController::class);
    Route::patch('fee-types/{feeType}/toggle-status', [FeeTypeController::class, 'toggleStatus'])->name('fee-types.toggle-status');
});

// Add specific route for users management
Route::middleware(['auth', 'role:administrator|alumni-relations-officer'])->group(function () {
    Route::get('/upload-alumni', [UploadAlumniController::class, 'index'])->name('upload.alumni');
    Route::post('/upload-alumni', [UploadAlumniController::class, 'store'])->name('upload.alumni.store');
    Route::get('/upload-alumni/progress', [UploadAlumniController::class, 'getImportProgress'])->name('upload.alumni.progress');
    Route::get('/upload-alumni/search', [UploadAlumniController::class, 'search'])->name('upload.alumni.search');
    Route::get('/retrieve-credentials', [UploadAlumniController::class, 'showRetrieveCredentials'])->name('retrieve.credentials');
    Route::get('/upload-alumni/credentials', [UploadAlumniController::class, 'getCredentials'])->name('upload.alumni.credentials');
    Route::post('/upload-alumni/resend-credentials', [UploadAlumniController::class, 'resendCredentials'])->name('upload.alumni.resend-credentials');
    Route::post('/upload-alumni/update-email', [UploadAlumniController::class, 'updateAlumniEmail'])->name('upload.alumni.update-email');
    
    // Alumni Years Routes
    Route::resource('alumni-years', AlumniYearController::class);
    Route::post('alumni-years/{alumniYear}/activate', [AlumniYearController::class, 'activate'])->name('alumni-years.activate');
    Route::post('alumni-years/{alumniYear}/deactivate', [AlumniYearController::class, 'deactivate'])->name('alumni-years.deactivate');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Friends routes
    Route::get('/friends', FriendRequestManager::class)->name('friends');

    // Alumni routes
    Route::get('/alumni', [AlumniController::class, 'showAlumniForm'])->name('alumni');
    Route::post('/alumni/view', [AlumniController::class, 'viewAlumniInformation'])->name('view.alumni');

    // Support Admin routes
    Route::get('dashboard/uploadalumni', [AlumniController::class, 'supportAlumniUpload'])->name('supportupload.alumni');
    Route::get('dashboard/students', [AlumniController::class, 'supportIndex'])->name('dashboard.supportindex');

    // Alumni Relations Officer routes
    Route::middleware(['role:alumni-relations-officer'])->group(function () {
        Route::get('/alumni-relations-officer/home', [App\Http\Controllers\ARODashboardController::class, 'index'])->name('alumni-relations-officer.home');
        Route::get('/alumni-relations-officer/users', App\Livewire\Admin\ManageAlumni::class)->name('alumni-relations-officer.users');
        Route::get('/create-event', [CreateAllEvent::class, 'index'])->name('create.event.index');
        Route::post('/create-event', [CreateAllEvent::class, 'store'])->name('create.event.store');
    });

    // Alumni routes latest
    Route::middleware(['auth', 'role:alumni'])->group(function () {
        Route::get('/alumni/home', function () {
            return view('alumni.home');
        })->name('alumni.home');
    });

    // Fee Template Routes
    Route::prefix('fee-templates')->name('fee-templates.')->middleware(['auth'])->group(function () {
        // View routes
        Route::get('/', [FeeTemplateController::class, 'index'])
            ->middleware('can:view fee templates')
            ->name('index');

        // Create routes
        Route::middleware('can:create fee templates')->group(function () {
            Route::get('/create', [FeeTemplateController::class, 'create'])->name('create');
            Route::post('/', [FeeTemplateController::class, 'store'])->name('store');
        });
        
        // Show route
        Route::get('/{fee}', [FeeTemplateController::class, 'show'])
            ->middleware('can:view fee template details')
            ->name('show');

        // Edit routes
        Route::middleware('can:edit fee templates')->group(function () {
            Route::get('/{fee}/edit', [FeeTemplateController::class, 'edit'])->name('edit');
            Route::put('/{fee}', [FeeTemplateController::class, 'update'])->name('update');
        });

        // Delete route
        Route::delete('/{fee}', [FeeTemplateController::class, 'destroy'])
            ->middleware('can:delete fee templates')
            ->name('destroy');

        // Status management routes
        Route::middleware('can:activate fee templates')->group(function () {
            Route::post('/{fee}/activate', [FeeTemplateController::class, 'activate'])->name('activate');
            Route::post('/{fee}/deactivate', [FeeTemplateController::class, 'deactivate'])->name('deactivate');
        });

        // Fee Rules routes
        Route::prefix('{fee}/rules')->name('rules.')->middleware('can:manage fee rules')->group(function () {
            Route::get('/', [FeeTemplateController::class, 'rules'])->name('index');
            Route::get('/create', [FeeTemplateController::class, 'createRule'])->name('create');
            Route::post('/', [FeeTemplateController::class, 'storeRule'])->name('store');
            Route::get('/{rule}/edit', [FeeTemplateController::class, 'editRule'])->name('edit');
            Route::put('/{rule}', [FeeTemplateController::class, 'updateRule'])->name('update');
            Route::delete('/{rule}', [FeeTemplateController::class, 'destroyRule'])->name('destroy');
            Route::post('/{rule}/activate', [FeeTemplateController::class, 'activateRule'])->name('activate');
            Route::post('/{rule}/deactivate', [FeeTemplateController::class, 'deactivateRule'])->name('deactivate');
        });

        // Transactions routes
        Route::prefix('{fee}/transactions')->name('transactions.')->middleware('can:view fee transactions')->group(function () {
            Route::get('/', [FeeTemplateController::class, 'transactions'])->name('index');
            Route::get('/{transaction}', [FeeTemplateController::class, 'showTransaction'])->name('show');
            Route::post('/{transaction}/mark-paid', [FeeTemplateController::class, 'markTransactionPaid'])
                ->middleware('can:manage fee transactions')
                ->name('mark-paid');
            Route::post('/{transaction}/mark-failed', [FeeTemplateController::class, 'markTransactionFailed'])
                ->middleware('can:manage fee transactions')
                ->name('mark-failed');
        });

        // Reports routes
        Route::prefix('reports')->name('reports.')->middleware('can:view fee reports')->group(function () {
            Route::get('/', [FeeTemplateController::class, 'reports'])->name('index');
            Route::get('/export', [FeeTemplateController::class, 'exportReports'])->name('export');
            Route::get('/summary', [FeeTemplateController::class, 'summaryReport'])->name('summary');
            Route::get('/transactions', [FeeTemplateController::class, 'transactionReport'])->name('transactions');
            Route::get('/categories', [FeeTemplateController::class, 'categoryReport'])->name('categories');
        });
    });
});

// Payment webhook route (no auth middleware)
Route::post('/payments/webhook', [AlumniPaymentController::class, 'handleWebhook'])->name('alumni.payments.webhook');

// Payment redirect route (no auth middleware)
Route::get('/payments/redirect', [AlumniPaymentController::class, 'handleRedirect'])->name('alumni.payments.redirect');

// Alumni routes
Route::middleware(['auth', 'role:alumni'])->group(function () {
    // Alumni Home Route
    Route::get('/alumni/home', function () {
        return view('alumni.home');
    })->name('alumni.home');

    // Bio Data Routes
    Route::get('/bio-data', [AlumniBioDataController::class, 'show'])->name('alumni.bio-data');
    Route::put('/bio-data', [AlumniBioDataController::class, 'update'])->name('alumni.bio-data.update');

    // Payment Routes - Grouped with consistent naming
    Route::prefix('payments')->name('alumni.payments.')->group(function () {
        Route::get('/', [AlumniPaymentController::class, 'index'])->name('index');
        Route::get('/history', [AlumniPaymentController::class, 'history'])->name('history');
        Route::post('/initiate', [AlumniPaymentController::class, 'initiatePayment'])->name('initiate');
        Route::get('/{transaction}', [AlumniPaymentController::class, 'show'])->name('show');
        Route::post('/{transaction}/confirm', [AlumniPaymentController::class, 'confirmPayment'])->name('confirm');
        Route::post('/{transaction}/verify', [AlumniPaymentController::class, 'verifyPayment'])->name('verify');
        Route::get('/{transaction}/success', [AlumniPaymentController::class, 'paymentSuccess'])->name('success');
        Route::get('/{transaction}/pending', [AlumniPaymentController::class, 'paymentPending'])->name('pending');
        Route::get('/{transaction}/failed', [AlumniPaymentController::class, 'paymentFailed'])->name('failed');
    });

    // Reports Routes
    Route::get('/reports', App\Livewire\AlumniReport::class)->name('reports');
    Route::get('/reports/clearance-form', App\Livewire\ClearanceForm::class)->name('reports.clearance-form');

    // Alumni Election Routes
    Route::get('/alumni/elections', [\App\Http\Controllers\AlumniElectionController::class, 'index'])->name('alumni.elections');
    Route::get('/alumni/elections/{election}/accreditation', [\App\Http\Controllers\AlumniElectionController::class, 'accreditation'])->name('alumni.elections.accreditation');
    Route::post('/alumni/elections/{election}/accreditation', [\App\Http\Controllers\AlumniElectionController::class, 'submitAccreditation'])->name('alumni.elections.accreditation.submit');
    Route::get('/alumni/elections/{election}/vote', [\App\Http\Controllers\AlumniElectionController::class, 'vote'])->name('alumni.elections.vote');
    Route::post('/alumni/elections/{election}/vote/preview', [\App\Http\Controllers\AlumniElectionController::class, 'previewVote'])->name('alumni.elections.vote.preview');
    Route::post('/alumni/elections/{election}/vote', [\App\Http\Controllers\AlumniElectionController::class, 'submitVote'])->name('alumni.elections.submit-vote');
    Route::get('/alumni/elections/{election}/results', [\App\Http\Controllers\AlumniElectionController::class, 'results'])->name('alumni.elections.results');

    // Alumni Election Expression of Interest and Published Candidates
    Route::get('/alumni/elections/{election}/offices/{office}/expression-of-interest', [\App\Http\Controllers\AlumniElectionController::class, 'expressionOfInterestForm'])->name('alumni.elections.expression-of-interest.form');
    Route::post('/alumni/elections/{election}/offices/{office}/expression-of-interest/preview', [\App\Http\Controllers\AlumniElectionController::class, 'previewExpressionOfInterest'])->name('alumni.elections.expression-of-interest.preview');
    Route::post('/alumni/elections/{election}/offices/{office}/expression-of-interest', [\App\Http\Controllers\AlumniElectionController::class, 'submitExpressionOfInterest'])->name('alumni.elections.expression-of-interest.submit');
    Route::get('/alumni/elections/{election}/offices/{office}/candidates', [\App\Http\Controllers\AlumniElectionController::class, 'publishedCandidates'])->name('alumni.elections.published-candidates');
    Route::get('/alumni/elections/expression-of-interest/status', [\App\Http\Controllers\AlumniElectionController::class, 'expressionOfInterestStatus'])->name('alumni.elections.expression-of-interest.status');
});

// Election Management Routes - accessible by elcom, elcom-chairman, and administrator
Route::middleware(['auth', 'role:elcom|elcom-chairman|administrator'])->prefix('elcom')->name('elcom.')->group(function () {
    // Election Management Routes
    Route::get('/elections', [ElectionController::class, 'index'])->name('elections.index');
    Route::get('/elections/create', [ElectionController::class, 'create'])->name('elections.create');
    Route::post('/elections', [ElectionController::class, 'store'])->name('elections.store');
    Route::get('/elections/{election}', [ElectionController::class, 'show'])->name('elections.show');
    Route::get('/elections/{election}/edit', [ElectionController::class, 'edit'])->name('elections.edit');
    Route::put('/elections/{election}', [ElectionController::class, 'update'])->name('elections.update');
    
    // Election Office Routes
    Route::get('/elections/{election}/offices/create', [ElectionController::class, 'createOffice'])->name('election-offices.create');
    Route::post('/elections/{election}/offices', [ElectionController::class, 'storeOffice'])->name('election-offices.store');
    Route::get('/elections/{election}/offices/{office}/edit', [ElectionController::class, 'editOffice'])->name('election-offices.edit');
    Route::put('/elections/{election}/offices/{office}', [ElectionController::class, 'updateOffice'])->name('election-offices.update');
    Route::delete('/elections/{election}/offices/{office}', [ElectionController::class, 'deleteOffice'])->name('election-offices.delete');
    Route::get('/elections/{election}/offices/{office}/candidates', [ElectionController::class, 'officeCandidates'])->name('election-offices.candidates.index');
    
    // Candidate Agent Routes
    Route::get('/elections/{election}/offices/{office}/candidates/{candidate}/assign-agent', [ElectionController::class, 'assignAgentForm'])
        ->name('election-offices.candidates.assign-agent-form');
    Route::post('/elections/{election}/offices/{office}/candidates/{candidate}/assign-agent', [ElectionController::class, 'assignAgent'])
        ->name('election-offices.candidates.assign-agent');
    Route::delete('/elections/{election}/offices/{office}/candidates/{candidate}/remove-agent', [ElectionController::class, 'removeAgent'])
        ->name('election-offices.candidates.remove-agent');

    // Election Process Routes
    Route::post('/elections/{election}/start-accreditation', [ElectionController::class, 'startAccreditation'])
        ->name('elections.start-accreditation');
    Route::post('/elections/{election}/start-voting', [ElectionController::class, 'startVoting'])
        ->name('elections.start-voting');
    Route::post('/elections/{election}/end-voting', [ElectionController::class, 'endVoting'])
        ->name('elections.end-voting');
    
    // EOI Period Management
    Route::post('/elections/{election}/start-eoi', [ElectionController::class, 'startEoi'])
        ->name('elections.start-eoi');
    Route::post('/elections/{election}/end-eoi', [ElectionController::class, 'endEoi'])
        ->name('elections.end-eoi');
    
    // Election Results Routes
    Route::get('/elections/{election}/basic-results', [ElectionController::class, 'basicResults'])->name('elections.basic-results');
    Route::get('/elections/{election}/real-time-results', [ElectionController::class, 'realTimeResults'])->name('elections.real-time-results');
    Route::get('/elections/{election}/stream-results', [ElectionController::class, 'streamRealTimeResults'])->name('elections.stream-results');
    Route::get('/elections/{election}/refresh-results', [ElectionController::class, 'realTimeResults'])
        ->name('elections.refresh-results');
    Route::get('/elections/{election}/print-full-results', [ElectionController::class, 'printFullResults'])
        ->name('elections.print-full-results');
    Route::get('/elections/{election}/print-winners', [ElectionController::class, 'printWinners'])
        ->name('elections.print-winners');
    Route::get('/elections/{election}/print-certificates', [ElectionController::class, 'printCertificates'])
        ->name('elections.print-certificates');
    
    // Certificate Verification Route
    Route::get('/verify-certificate/{election}/{office}/{winner}/{code}', [ElectionController::class, 'verifyCertificate'])
        ->name('verify.certificate');
    
    // Candidate Management Routes
    Route::post('/elections/{election}/offices/{office}/candidates/{candidate}/screen', [ElectionController::class, 'screenCandidate'])
        ->name('elections.screen-candidate');

    // ELCOM Screening Routes
    Route::get('/elections/{election}/offices/{office}/candidates/screen', [ElectionController::class, 'screenCandidates'])->name('elections.offices.candidates.screen');
    Route::post('/elections/{election}/offices/{office}/candidates/{candidate}/approve', [ElectionController::class, 'approveCandidate'])->name('elections.offices.candidates.approve');
    Route::post('/elections/{election}/offices/{office}/candidates/{candidate}/reject', [ElectionController::class, 'rejectCandidate'])->name('elections.offices.candidates.reject');

    // Accredited Voters Routes
    Route::get('/elections/{election}/accredited-voters', [ElectionController::class, 'accreditedVoters'])->name('elections.accredited-voters');

    // Agent Suggestion Review Routes
    Route::get('/elections/{election}/review-agent-suggestions', [ElectionController::class, 'reviewAgentSuggestions'])
        ->name('elections.review-agent-suggestions');
    Route::post('/elections/{election}/candidates/{candidate}/approve-agent', [ElectionController::class, 'approveAgentSuggestion'])
        ->name('elections.candidates.approve-agent');
    Route::post('/elections/{election}/candidates/{candidate}/reject-agent', [ElectionController::class, 'rejectAgentSuggestion'])
        ->name('elections.candidates.reject-agent');
});

// Agent Routes
Route::middleware(['auth', 'role:alumni-agent'])->prefix('agent')->name('agent.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Agent\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/elections/{election}/results', [\App\Http\Controllers\Agent\DashboardController::class, 'electionResults'])->name('elections.results');
    
    // Candidate routes with ownership validation
    Route::middleware(['agent.owns.candidate'])->group(function () {
        Route::get('/elections/{election}/candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
        Route::get('/elections/{election}/candidates/{candidate}/edit-documents', [CandidateController::class, 'editDocuments'])->name('candidates.edit-documents');
        Route::put('/elections/{election}/candidates/{candidate}/documents', [CandidateController::class, 'updateDocuments'])->name('candidates.update-documents');
    });
    
    // Candidate listing (no ownership validation needed)
    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
});

// Candidate routes - Modified to allow alumni users who are candidates
Route::middleware(['auth', 'role:alumni'])->prefix('candidate')->name('candidate.')->group(function () {
    // Election details route
    Route::get('/elections/{election}', [\App\Http\Controllers\Candidate\ElectionController::class, 'show'])
        ->name('elections.show')
        ->middleware('can:view,election');

    // Agent suggestion routes
    Route::get('/elections/{election}/candidates/{candidate}/suggest-agent', [AgentController::class, 'suggestForm'])
        ->name('elections.candidates.suggest-agent-form')
        ->middleware('can:view,candidate');
    Route::post('/elections/{election}/candidates/{candidate}/suggest-agent', [AgentController::class, 'suggest'])
        ->name('elections.candidates.suggest-agent')
        ->middleware('can:view,candidate');
    Route::post('/elections/{election}/candidates/{candidate}/cancel-suggestion', [AgentController::class, 'cancelSuggestion'])
        ->name('elections.candidates.cancel-suggestion')
        ->middleware('can:view,candidate');
    Route::get('/elections/{election}/search-alumni', [AgentController::class, 'searchAlumni'])
        ->name('elections.search-alumni');
});

require __DIR__.'/auth.php';

// Public routes
// Route::get('/uploadalumni', [AlumniController::class, 'alumniUpload'])->name('upload.alumni');
Route::post('students/upload', [AlumniController::class, 'upload'])->name('students.upload');
Route::get('students', [AlumniController::class, 'index'])->name('students.index');
Route::get('/create-events', [CreateAllEvent::class, 'index'])->name('create.event.index');

Route::get('/alumni/{id}/print', function ($id) {
    $alumni = \App\Models\Alumni::findOrFail($id);
    $user = $alumni->user;
    return view('livewire.alumni-report-print', compact('alumni', 'user'));
})->name('alumni.print');

// Landing Page Routes
Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/search-credentials', [LandingPageController::class, 'searchCredentials'])->name('landing.search-credentials');
Route::post('/update-email', [LandingPageController::class, 'updateEmail'])->name('landing.update-email');
Route::post('/resend-credentials', [LandingPageController::class, 'resendCredentials'])->name('landing.resend-credentials');

// ELCOM Chairman routes
Route::middleware(['auth', 'role:elcom-chairman'])->prefix('elcom-chairman')->name('elcom-chairman.')->group(function () {
    Route::get('/', [App\Http\Controllers\ElcomChairman\DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\ElcomChairman\DashboardController::class, 'index'])->name('dashboard');
    
    // Redirect election management to the shared elcom routes
    Route::get('/elections', function () {
        return redirect()->route('elcom.elections.index');
    })->name('elections.index');
    Route::get('/elections/{election}', function ($election) {
        return redirect()->route('elcom.elections.show', $election);
    })->name('elections.show');
    Route::get('/elections/{election}/edit', function ($election) {
        return redirect()->route('elcom.elections.edit', $election);
    })->name('elections.edit');
    Route::get('/elections/create', function () {
        return redirect()->route('elcom.elections.create');
    })->name('elections.create');
});




