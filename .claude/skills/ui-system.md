# UI System
The visual identity follows the Content Creators Summit (CCS — قمة صناع المحتوى) branding, defined in docs/هوية قمة صناع المحتوى-1.pdf.

Style:

Premium

Minimal

Corporate

Technology

Dark theme

Deep reds

Large typography

Generous whitespace

Animations should be subtle.

Avoid trendy effects.

Tailwind CSS.

No Bootstrap.

## Brand identity

Logo: "CCS" wordmark in a red parallelogram/flag shape with a coral top edge, paired with the Arabic wordmark "قمة صناع المحتوى" and English tagline "Content Creators Summit."

Campaign tagline: "أثر يتوالى" ("Impact that continues").

Palette:

- Coral `#ff7e71`
- Red (primary) `#d33333`
- Dark maroon `#430d14`
- Near-black `#171f22`
- Teal `#2a7675`
- Light teal `#7ccbcf`
- Gold/tan `#fad48b`, `#a48755` (accent gradient)

## Bilingual / RTL

The platform is bilingual: Arabic (RTL) and English (LTR). Layouts, grid direction (Tailwind's `rtl:`/`ltr:` variants), and typography must work in both directions — do not hardcode LTR-only markup. Per-event and per-Ticket-Type content is reusable across events, so brand colors above are the CCS default theme, not hardcoded platform-wide values (see CLAUDE.md: never hardcode event-specific values).
