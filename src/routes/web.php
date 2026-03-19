<?php

use App\Http\Controllers\AttendanceCheckController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AbsencePeriodController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventResultController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\RecurrenceRuleController;
use App\Http\Controllers\ClubAdminController;
use App\Http\Controllers\ClubSwitchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AttendanceStatisticsController;
use App\Http\Controllers\PaymentRequestController;
use App\Http\Controllers\CalendarFeedController;
use App\Http\Controllers\ConsentTypeController;
use App\Http\Controllers\EventCommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PenaltyRuleController;
use App\Http\Controllers\TeamPostController;
use App\Http\Controllers\VenueCostController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\PlaceholderMemberController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['cs', 'en', 'sk'])) {
        session(['locale' => $locale]);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }
    return back();
})->name('locale.switch');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/magic-link', [MagicLinkController::class, 'showForm'])->name('magic-link');
    Route::post('/magic-link', [MagicLinkController::class, 'sendLink']);
    Route::get('/magic-link/verify/{token}', [MagicLinkController::class, 'verify'])->name('magic-link.verify');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Onboarding (before club.selected middleware)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::get('/onboarding/create-club', [OnboardingController::class, 'createClub'])->name('onboarding.create-club');
    Route::post('/onboarding/create-club', [OnboardingController::class, 'storeClub']);
    Route::get('/onboarding/join-club', [OnboardingController::class, 'joinClub'])->name('onboarding.join-club');
    Route::get('/onboarding/search-clubs', [OnboardingController::class, 'searchClubs'])->name('onboarding.search-clubs');
    Route::post('/onboarding/join-club', [OnboardingController::class, 'requestJoin']);
});

// Guardian claim page (public - shows info, requires login to process)
Route::get('/claim/{token}', [PlaceholderMemberController::class, 'showClaim'])->name('placeholder.show-claim');

// Authenticated + club-scoped routes
Route::middleware(['auth', 'club.selected'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/club/switch', [ClubSwitchController::class, 'switch'])->name('club.switch');

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.add-member');
    Route::patch('/teams/{team}/members/{membership}', [TeamController::class, 'updateMember'])->name('teams.update-member');
    Route::delete('/teams/{team}/members/{membership}', [TeamController::class, 'removeMember'])->name('teams.remove-member');

    // Placeholder members
    Route::post('/teams/{team}/placeholder', [PlaceholderMemberController::class, 'store'])->name('placeholder.store');
    Route::post('/teams/{team}/placeholder/{placeholder}/guardian-invite', [PlaceholderMemberController::class, 'sendGuardianInvite'])->name('placeholder.guardian-invite');

    // Claim guardian invite (requires auth)
    Route::post('/claim/{token}', [PlaceholderMemberController::class, 'processClaim'])->name('placeholder.process-claim');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');
    Route::post('/events/{event}/complete', [EventController::class, 'complete'])->name('events.complete');
    Route::post('/events/{event}/sync-attendances', [EventController::class, 'syncAttendances'])->name('events.sync-attendances');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::patch('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::post('/attendances/batch', [AttendanceController::class, 'batchUpdate'])->name('attendances.batch');

    Route::get('/events/{event}/nominations', [NominationController::class, 'manage'])->name('nominations.manage');
    Route::post('/events/{event}/nominations', [NominationController::class, 'store'])->name('nominations.store');
    Route::patch('/nominations/{nomination}/respond', [NominationController::class, 'respond'])->name('nominations.respond');
    Route::delete('/nominations/{nomination}', [NominationController::class, 'destroy'])->name('nominations.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/locale', [SettingsController::class, 'updateLocale'])->name('settings.locale');
    // Venues
    Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/create', [VenueController::class, 'create'])->name('venues.create');
    Route::post('/venues', [VenueController::class, 'store'])->name('venues.store');
    Route::get('/venues/{venue}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue}', [VenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue}', [VenueController::class, 'destroy'])->name('venues.destroy');

    // Seasons
    Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{season}/edit', [SeasonController::class, 'edit'])->name('seasons.edit');
    Route::put('/seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
    Route::delete('/seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');

    // Club Admin
    Route::get('/club-admin', [ClubAdminController::class, 'index'])->name('club-admin.index');
    Route::get('/club-admin/settings', [ClubAdminController::class, 'editSettings'])->name('club-admin.settings');
    Route::put('/club-admin/settings', [ClubAdminController::class, 'updateSettings'])->name('club-admin.update-settings');
    Route::patch('/club-admin/members/{clubMembership}/role', [ClubAdminController::class, 'updateRole'])->name('club-admin.update-role');
    Route::delete('/club-admin/members/{clubMembership}', [ClubAdminController::class, 'removeMember'])->name('club-admin.remove-member');
    Route::patch('/club-admin/requests/{joinRequest}/approve', [ClubAdminController::class, 'approveRequest'])->name('club-admin.approve-request');
    Route::patch('/club-admin/requests/{joinRequest}/reject', [ClubAdminController::class, 'rejectRequest'])->name('club-admin.reject-request');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}/reply', [MessageController::class, 'reply'])->name('messages.reply');

    // Attendance Check (coach)
    Route::get('/events/{event}/attendance-check', [AttendanceCheckController::class, 'show'])->name('attendance-check.show');
    Route::put('/events/{event}/attendance-check', [AttendanceCheckController::class, 'update'])->name('attendance-check.update');

    // Payments
    Route::get('/payments', [PaymentRequestController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentRequestController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentRequestController::class, 'store'])->name('payments.store');
    Route::get('/payments/{paymentRequest}', [PaymentRequestController::class, 'show'])->name('payments.show');
    Route::get('/payments/{paymentRequest}/edit', [PaymentRequestController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{paymentRequest}', [PaymentRequestController::class, 'update'])->name('payments.update');
    Route::post('/payments/{paymentRequest}/cancel', [PaymentRequestController::class, 'cancelRequest'])->name('payments.cancel-request');
    Route::patch('/payments/member/{memberPayment}/confirm', [PaymentRequestController::class, 'confirmPayment'])->name('payments.confirm');
    Route::patch('/payments/member/{memberPayment}/cancel', [PaymentRequestController::class, 'cancelPayment'])->name('payments.cancel-payment');

    // Recurrence Rules
    Route::get('/recurrence-rules', [RecurrenceRuleController::class, 'index'])->name('recurrence-rules.index');
    Route::get('/recurrence-rules/create', [RecurrenceRuleController::class, 'create'])->name('recurrence-rules.create');
    Route::post('/recurrence-rules', [RecurrenceRuleController::class, 'store'])->name('recurrence-rules.store');
    Route::get('/recurrence-rules/{recurrenceRule}/edit', [RecurrenceRuleController::class, 'edit'])->name('recurrence-rules.edit');
    Route::put('/recurrence-rules/{recurrenceRule}', [RecurrenceRuleController::class, 'update'])->name('recurrence-rules.update');
    Route::patch('/recurrence-rules/{recurrenceRule}/toggle', [RecurrenceRuleController::class, 'toggleActive'])->name('recurrence-rules.toggle');
    Route::delete('/recurrence-rules/{recurrenceRule}', [RecurrenceRuleController::class, 'destroy'])->name('recurrence-rules.destroy');

    // Absence Periods
    Route::get('/absences', [AbsencePeriodController::class, 'index'])->name('absences.index');
    Route::get('/absences/create', [AbsencePeriodController::class, 'create'])->name('absences.create');
    Route::post('/absences', [AbsencePeriodController::class, 'store'])->name('absences.store');
    Route::delete('/absences/{absencePeriod}', [AbsencePeriodController::class, 'destroy'])->name('absences.destroy');

    // Templates
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates/equipment', [TemplateController::class, 'storeEquipment'])->name('templates.store-equipment');
    Route::post('/templates/instruction', [TemplateController::class, 'storeInstruction'])->name('templates.store-instruction');
    Route::delete('/templates/equipment/{equipmentTemplate}', [TemplateController::class, 'destroyEquipment'])->name('templates.destroy-equipment');
    Route::delete('/templates/instruction/{instructionTemplate}', [TemplateController::class, 'destroyInstruction'])->name('templates.destroy-instruction');

    // Event Comments
    Route::post('/events/{event}/comments', [EventCommentController::class, 'store'])->name('event-comments.store');
    Route::delete('/event-comments/{eventComment}', [EventCommentController::class, 'destroy'])->name('event-comments.destroy');

    // Team Wall & Posts
    Route::get('/teams/{team}/wall', [TeamPostController::class, 'index'])->name('teams.wall');
    Route::post('/teams/{team}/posts', [TeamPostController::class, 'store'])->name('team-posts.store');
    Route::post('/team-posts/upload', [TeamPostController::class, 'uploadAttachment'])->name('team-posts.upload');
    Route::get('/team-posts/{teamPost}', [TeamPostController::class, 'show'])->name('team-posts.show');
    Route::delete('/team-posts/{teamPost}', [TeamPostController::class, 'destroy'])->name('team-posts.destroy');
    Route::post('/team-posts/{teamPost}/comments', [TeamPostController::class, 'storeComment'])->name('team-post-comments.store');
    Route::post('/poll-options/{pollOption}/vote', [TeamPostController::class, 'vote'])->name('poll-votes.store');

    // Consents
    Route::get('/consents', [ConsentTypeController::class, 'index'])->name('consents.index');
    Route::get('/consents/overview', [ConsentTypeController::class, 'overview'])->name('consents.overview');
    Route::get('/consent-types/create', [ConsentTypeController::class, 'create'])->name('consent-types.create');
    Route::post('/consent-types', [ConsentTypeController::class, 'store'])->name('consent-types.store');
    Route::get('/consent-types/{consentType}', [ConsentTypeController::class, 'show'])->name('consent-types.show');
    Route::get('/consent-types/{consentType}/edit', [ConsentTypeController::class, 'edit'])->name('consent-types.edit');
    Route::put('/consent-types/{consentType}', [ConsentTypeController::class, 'update'])->name('consent-types.update');
    Route::delete('/consent-types/{consentType}', [ConsentTypeController::class, 'destroy'])->name('consent-types.destroy');
    Route::post('/consents/{consentType}/grant', [ConsentTypeController::class, 'grant'])->name('consents.grant');
    Route::delete('/consents/{consentType}/revoke', [ConsentTypeController::class, 'revoke'])->name('consents.revoke');

    // Venue Costs
    Route::get('/venue-costs', [VenueCostController::class, 'index'])->name('venue-costs.index');
    Route::get('/venue-costs/create', [VenueCostController::class, 'create'])->name('venue-costs.create');
    Route::post('/venue-costs', [VenueCostController::class, 'store'])->name('venue-costs.store');
    Route::get('/venue-costs/{venueCost}/edit', [VenueCostController::class, 'edit'])->name('venue-costs.edit');
    Route::put('/venue-costs/{venueCost}', [VenueCostController::class, 'update'])->name('venue-costs.update');
    Route::delete('/venue-costs/{venueCost}', [VenueCostController::class, 'destroy'])->name('venue-costs.destroy');
    Route::post('/venue-costs/{venueCost}/generate-settlement', [VenueCostController::class, 'generateSettlement'])->name('venue-costs.generate-settlement');
    Route::get('/venue-cost-settlements/{settlement}', [VenueCostController::class, 'showSettlement'])->name('venue-cost-settlements.show');
    Route::patch('/venue-cost-shares/{share}/confirm', [VenueCostController::class, 'confirmShare'])->name('venue-cost-shares.confirm');

    // Penalty Rules
    Route::get('/penalty-rules', [PenaltyRuleController::class, 'index'])->name('penalty-rules.index');
    Route::get('/penalty-rules/create', [PenaltyRuleController::class, 'create'])->name('penalty-rules.create');
    Route::post('/penalty-rules', [PenaltyRuleController::class, 'store'])->name('penalty-rules.store');
    Route::get('/penalty-rules/{penaltyRule}/edit', [PenaltyRuleController::class, 'edit'])->name('penalty-rules.edit');
    Route::put('/penalty-rules/{penaltyRule}', [PenaltyRuleController::class, 'update'])->name('penalty-rules.update');
    Route::patch('/penalty-rules/{penaltyRule}/toggle', [PenaltyRuleController::class, 'toggleActive'])->name('penalty-rules.toggle');
    Route::delete('/penalty-rules/{penaltyRule}', [PenaltyRuleController::class, 'destroy'])->name('penalty-rules.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.update-preferences');

    // Calendar Feeds
    Route::get('/calendar-feeds', [CalendarFeedController::class, 'index'])->name('calendar-feeds.index');
    Route::post('/calendar-feeds', [CalendarFeedController::class, 'store'])->name('calendar-feeds.store');
    Route::patch('/calendar-feeds/{calendarFeed}/toggle', [CalendarFeedController::class, 'toggleActive'])->name('calendar-feeds.toggle');
    Route::delete('/calendar-feeds/{calendarFeed}', [CalendarFeedController::class, 'destroy'])->name('calendar-feeds.destroy');

    // Statistics
    Route::get('/statistics', [AttendanceStatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/export', [AttendanceStatisticsController::class, 'export'])->name('statistics.export');

    // Albums / Gallery
    Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');
    Route::post('/albums/{album}/photos', [AlbumController::class, 'uploadPhoto'])->name('albums.upload-photo');
    Route::delete('/photos/{photo}', [AlbumController::class, 'destroyPhoto'])->name('albums.delete-photo');
    Route::delete('/albums/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');

    // Event Results
    Route::post('/events/{event}/result', [EventResultController::class, 'store'])->name('event-results.store');
    Route::delete('/event-results/{eventResult}', [EventResultController::class, 'destroy'])->name('event-results.destroy');

    // Custom Fields
    Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields.index');
    Route::get('/custom-fields/create', [CustomFieldController::class, 'create'])->name('custom-fields.create');
    Route::post('/custom-fields', [CustomFieldController::class, 'store'])->name('custom-fields.store');
    Route::get('/custom-fields/{customField}/edit', [CustomFieldController::class, 'edit'])->name('custom-fields.edit');
    Route::put('/custom-fields/{customField}', [CustomFieldController::class, 'update'])->name('custom-fields.update');
    Route::delete('/custom-fields/{customField}', [CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');
});

// Public iCal feed (no auth required)
Route::get('/ical/{token}', [CalendarFeedController::class, 'ical'])->name('ical.feed');
