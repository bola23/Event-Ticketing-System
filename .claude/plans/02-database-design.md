# Database Design

Scope: Core schema for Events, Ticket Types (with workshop slot counts), Workshops, WorkshopBookings, Tickets, Sponsors, Speakers, Awards, Award Votes, Discount Coupons, Landing Page content blocks, and translations for multi-language content.

Status: 🟡 Partial — content/CMS schema done, ticket-workflow schema missing.

- [x] Events, Speakers, Sponsors, TicketTypes (+ TicketTypeFeatures), Workshops, AgendaItems, Faqs, LandingPageContent, GalleryPhotos, Testimonials, ContactMessages, NewsletterSubscribers
- [ ] Ticket (attendee ticket + workflow state: Pending → Approved/Rejected → Payment Pending → Paid → Ticket Issued → Checked In → Cancelled)
- [ ] WorkshopBooking (slot-based, keyed by Ticket ID + Workshop Booking Key)
- [ ] DiscountCoupon
- [ ] Award / AwardVote
