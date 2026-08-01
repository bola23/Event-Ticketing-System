# Database Design

Scope: Core schema for Events, Ticket Types (with workshop slot counts), Workshops, WorkshopBookings, Tickets, Sponsors, Speakers, Awards, Award Votes, Discount Coupons, Landing Page content blocks, and translations for multi-language content.

Status: 🟡 Partial — content/CMS schema and core ticket workflow schema done; workshop booking, coupons, awards schema missing.

- [x] Events, Speakers, Sponsors, TicketTypes (+ TicketTypeFeatures), Workshops, AgendaItems, Faqs, LandingPageContent, GalleryPhotos, Testimonials, ContactMessages, NewsletterSubscribers
- [x] Ticket (attendee ticket + workflow state: Pending → Approved/Rejected → Payment Pending → Paid → Ticket Issued → Checked In → Cancelled — only the first four states are set by anything yet; the rest are reserved columns for Payment/QR phases). Plus `TicketRequestField` (admin-configurable extra request fields) and `TicketRequestAnswer`.
- [ ] WorkshopBooking (slot-based, keyed by Ticket ID + Workshop Booking Key)
- [ ] DiscountCoupon
- [ ] Award / AwardVote
