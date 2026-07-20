<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AgendaItemType;
use App\Enums\EventStatus;
use App\Enums\LandingPageSection;
use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Faq;
use App\Models\LandingPageContent;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\TicketType;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class CcsEventSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::create([
            'slug' => 'ccs-2026',
            'name_ar' => 'قمة صناع المحتوى',
            'name_en' => 'Content Creators Summit',
            'tagline_ar' => 'أثر يتوالى',
            'tagline_en' => 'Impact that continues',
            'start_date' => '2026-08-15',
            'end_date' => '2026-08-16',
            'venue_name_ar' => 'مركز المؤتمرات',
            'venue_name_en' => 'Convention Center',
            'venue_address_ar' => 'الرياض، المملكة العربية السعودية',
            'venue_address_en' => 'Riyadh, Saudi Arabia',
            'map_embed_url' => 'https://maps.google.com/?q=Riyadh',
            'status' => EventStatus::Published,
        ]);

        $speakerKareem = Speaker::create([
            'event_id' => $event->id,
            'name_ar' => 'كريم السيد',
            'name_en' => 'Kareem Al-Sayed',
            'title_ar' => 'صانع محتوى',
            'title_en' => 'Content Creator',
            'bio_ar' => 'صانع محتوى رقمي بخبرة تتجاوز عشر سنوات.',
            'bio_en' => 'Digital content creator with over a decade of experience.',
            'sort_order' => 1,
        ]);
        Speaker::factory()->for($event)->count(2)->create();

        Sponsor::create([
            'event_id' => $event->id,
            'name_ar' => 'الراعي الذهبي',
            'name_en' => 'Golden Sponsor Co.',
            'tier' => 'gold',
            'sort_order' => 1,
        ]);
        Sponsor::factory()->for($event)->create(['tier' => 'silver']);

        $ticketSlots = [
            ['name_en' => 'General', 'name_ar' => 'عام', 'slots' => 0, 'price' => 300],
            ['name_en' => 'VIP', 'name_ar' => 'كبار الشخصيات', 'slots' => 1, 'price' => 700],
            ['name_en' => 'Premium', 'name_ar' => 'مميز', 'slots' => 2, 'price' => 1200],
            ['name_en' => 'Platinum', 'name_ar' => 'بلاتيني', 'slots' => null, 'price' => 2500],
        ];
        foreach ($ticketSlots as $i => $tier) {
            TicketType::create([
                'event_id' => $event->id,
                'name_ar' => $tier['name_ar'],
                'name_en' => $tier['name_en'],
                'description_ar' => 'وصف الباقة',
                'description_en' => $tier['name_en'].' ticket tier',
                'price' => $tier['price'],
                'currency' => 'SAR',
                'workshop_slot_count' => $tier['slots'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $workshop = Workshop::create([
            'event_id' => $event->id,
            'speaker_id' => $speakerKareem->id,
            'slug' => 'ai-content-workshop',
            'name_ar' => 'ورشة صناعة المحتوى بالذكاء الاصطناعي',
            'name_en' => 'AI-Powered Content Creation Workshop',
            'description_ar' => 'تعلم كيفية استخدام أدوات الذكاء الاصطناعي في صناعة المحتوى.',
            'description_en' => 'Learn how to use AI tools in content creation.',
            'capacity' => 40,
            'sort_order' => 1,
        ]);
        Workshop::factory()->for($event)->create();

        AgendaItem::create([
            'event_id' => $event->id,
            'speaker_id' => $speakerKareem->id,
            'workshop_id' => $workshop->id,
            'day_date' => '2026-08-15',
            'start_time' => '10:00',
            'end_time' => '11:30',
            'title_ar' => $workshop->name_ar,
            'title_en' => $workshop->name_en,
            'type' => AgendaItemType::WorkshopSession,
            'sort_order' => 1,
        ]);

        $faqs = [
            [
                'question_ar' => 'كيف أحصل على تذكرة؟',
                'question_en' => 'How do I get a ticket?',
                'answer_ar' => 'قدّم طلبك من صفحة التذاكر وسيتم مراجعته من قبل الإدارة.',
                'answer_en' => 'Submit a request from the Tickets section; it will be reviewed by the admin team.',
            ],
            [
                'question_ar' => 'متى أدفع؟',
                'question_en' => 'When do I pay?',
                'answer_ar' => 'بعد الموافقة على طلبك، ستصلك رسالة تحتوي على رابط الدفع.',
                'answer_en' => 'After your request is approved, you will receive an email with a payment link.',
            ],
            [
                'question_ar' => 'كيف أختار ورش العمل؟',
                'question_en' => 'How do I choose my workshops?',
                'answer_ar' => 'بعد إصدار التذكرة، استخدم معرّف التذكرة ومفتاح الحجز لاختيار ورش العمل.',
                'answer_en' => 'Once your ticket is issued, use your Ticket ID and Booking Key to pick your workshops.',
            ],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::create([...$faq, 'event_id' => $event->id, 'sort_order' => $i]);
        }

        $content = [
            [LandingPageSection::Hero, 'headline', 'قمة صناع المحتوى ٢٠٢٦', 'Content Creators Summit 2026'],
            [LandingPageSection::About, 'body', 'قمة صناع المحتوى هي ملتقى محترفي المحتوى الرقمي.', 'Content Creators Summit is where digital content professionals meet.'],
            [LandingPageSection::Location, 'intro', 'يقام الحدث في مركز المؤتمرات بالرياض.', 'The event takes place at the Convention Center in Riyadh.'],
            [LandingPageSection::AwardsTeaser, 'blurb', 'صوّت لصانع المحتوى المفضل لديك قريبًا.', 'Vote for your favorite content creator — coming soon.'],
        ];
        foreach ($content as [$section, $key, $ar, $en]) {
            LandingPageContent::create([
                'event_id' => $event->id,
                'section' => $section,
                'field_key' => $key,
                'value_ar' => $ar,
                'value_en' => $en,
            ]);
        }
    }
}
