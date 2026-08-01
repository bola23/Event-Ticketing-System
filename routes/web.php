<?php

use App\Http\Controllers\Admin\AgendaItemController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryPhotoController;
use App\Http\Controllers\Admin\LandingPageContentController;
use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Admin\SpeakerController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TicketRequestFieldController;
use App\Http\Controllers\Admin\TicketRequestQueueController;
use App\Http\Controllers\Admin\TicketTypeController;
use App\Http\Controllers\Admin\WorkshopController as AdminWorkshopController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AwardsController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NewsletterSubscriberController;
use App\Http\Controllers\TicketRequestController;
use App\Http\Controllers\WorkshopController;
use App\Http\Middleware\EnsureEventIsPublished;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('landing.show', 'ccs-2026');
});

Route::prefix('events/{event}')->middleware(EnsureEventIsPublished::class)->group(function () {
    Route::get('/', [LandingPageController::class, 'show'])->name('landing.show');
    Route::get('/agenda', [AgendaController::class, 'show'])->name('agenda.show');
    Route::get('/awards', [AwardsController::class, 'show'])->name('awards.show');
    Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
    Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');
    Route::get('/request', [TicketRequestController::class, 'create'])->name('ticket-requests.create');
    Route::post('/request', [TicketRequestController::class, 'store'])->name('ticket-requests.store');
    Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');
    Route::post('/newsletter', [NewsletterSubscriberController::class, 'store'])->name('newsletter.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('events', EventController::class)->except('show');
        Route::resource('events.speakers', SpeakerController::class)->except('show');
        Route::resource('events.workshops', AdminWorkshopController::class)->except('show');
        Route::resource('events.sponsors', SponsorController::class)->except('show');
        Route::resource('events.ticket-types', TicketTypeController::class)
            ->except('show')
            ->parameters(['ticket-types' => 'ticketType']);
        Route::resource('events.agenda-items', AgendaItemController::class)
            ->except('show')
            ->parameters(['agenda-items' => 'agendaItem']);
        Route::resource('events.gallery-photos', GalleryPhotoController::class)
            ->except('show')
            ->parameters(['gallery-photos' => 'galleryPhoto']);
        Route::resource('events.testimonials', TestimonialController::class)->except('show');
        Route::get('events/{event}/contact-messages', [AdminContactMessageController::class, 'index'])->name('events.contact-messages.index');
        Route::get('events/{event}/newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('events.newsletter-subscribers.index');
        Route::resource('events.faqs', FaqController::class)->except('show');
        Route::resource('events.request-form-fields', TicketRequestFieldController::class)
            ->except('show')
            ->parameters(['request-form-fields' => 'requestField']);
        Route::get('events/{event}/ticket-requests', [TicketRequestQueueController::class, 'index'])->name('events.ticket-requests.index');
        Route::patch('events/{event}/ticket-requests/{ticket}/approve', [TicketRequestQueueController::class, 'approve'])->name('events.ticket-requests.approve');
        Route::patch('events/{event}/ticket-requests/{ticket}/reject', [TicketRequestQueueController::class, 'reject'])->name('events.ticket-requests.reject');
        Route::get('events/{event}/ticket-requests/{ticket}/answers/{answer}/download', [TicketRequestQueueController::class, 'downloadAnswer'])->name('events.ticket-requests.answers.download');
        Route::get('events/{event}/content', [LandingPageContentController::class, 'edit'])->name('events.content.edit');
        Route::put('events/{event}/content', [LandingPageContentController::class, 'update'])->name('events.content.update');
    });
});
