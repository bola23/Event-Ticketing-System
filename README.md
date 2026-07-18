# Event & Ticketing Platform

A reusable, event-agnostic conference/summit ticketing platform. First reference deployment: CCS (Content Creators Summit).

Approval-based ticket requests, online payment, slot-based workshop booking, an awards/voting system, and an admin dashboard (events, ticket types, coupons, landing-page CMS, multi-language content, reports, approvals) plus a separate QR check-in team portal. No user login anywhere — the ticket itself is the attendee's identity.

Stack: Laravel 12, PHP 8.3, MySQL, Laravel Boost, Bootstrap 5 + SCSS, AlpineJS where needed. MVC + Service Layer + Repository pattern.

- `.claude/CLAUDE.md` — top-level project instructions
- `.claude/skills/` — durable domain rules (event domain, workshop module, UI system, coding standards)
- `.claude/plans/` — per-subsystem build roadmap
- `docs/decisions/` — architecture decision records
- `docs/` — brand identity assets (CCS)

## Starter Docs
