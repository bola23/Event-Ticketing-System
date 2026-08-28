/**
 * Motion for the CCS landing page: hero entrance, parallax phone cards, the Reel
 * coverflow and the scroll progress bar.
 *
 * Every routine bails out when its markup is absent, so this module is safe to load on
 * every page. Generic section reveals are still handled by the IntersectionObserver in
 * app.js — GSAP is reserved for the orchestrated moments.
 */
import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initScrollProgress() {
    const bar = document.querySelector('[data-scroll-progress]');
    if (!bar) return;

    gsap.to(bar, {
        scaleX: 1,
        ease: 'none',
        scrollTrigger: { start: 0, end: 'max', scrub: prefersReducedMotion ? true : 0.25 },
    });
}

function initHero() {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;

    const lines = hero.querySelectorAll('[data-hero-line] > span');
    const bits = hero.querySelectorAll('[data-hero-bit]');
    const phones = hero.querySelectorAll('[data-hero-phone]');

    if (prefersReducedMotion) {
        gsap.set([...lines, ...bits, ...phones], { clearProps: 'all', opacity: 1, y: 0 });
        return;
    }

    const intro = gsap.timeline({ defaults: { ease: 'power3.out' } });

    intro
        .from(lines, { yPercent: 115, duration: 1.05, stagger: 0.09 })
        .from(bits, { y: 26, opacity: 0, duration: 0.65, stagger: 0.09 }, '-=0.55')
        .from(
            phones,
            {
                // Cards fly in from beyond their own side of the headline, so the two
                // columns resolve inward rather than all sliding the same direction.
                x: (i, el) => (el.dataset.heroPhoneSide === 'start' ? -140 : 140),
                y: 60,
                rotateZ: (i, el) => (el.dataset.heroPhoneSide === 'start' ? -14 : 14),
                opacity: 0,
                duration: 1.1,
                stagger: 0.08,
            },
            '-=0.9',
        );

    // Cards drift at their own pace as the hero scrolls away.
    phones.forEach((phone, index) => {
        gsap.to(phone, {
            yPercent: index % 2 === 0 ? -22 : -38,
            ease: 'none',
            scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.4 },
        });
    });

    // Pointer parallax, weighted so nearer cards move further.
    const quickTo = [...phones].map((phone) => ({
        x: gsap.quickTo(phone, 'x', { duration: 0.7, ease: 'power3.out' }),
        y: gsap.quickTo(phone, 'y', { duration: 0.7, ease: 'power3.out' }),
    }));

    hero.addEventListener('pointermove', (event) => {
        const relX = event.clientX / window.innerWidth - 0.5;
        const relY = event.clientY / window.innerHeight - 0.5;

        quickTo.forEach((setter, index) => {
            const depth = 14 + index * 6;
            setter.x(relX * depth);
            setter.y(relY * depth);
        });
    });

    hero.addEventListener('pointerleave', () => {
        quickTo.forEach((setter) => {
            setter.x(0);
            setter.y(0);
        });
    });
}

function initReel() {
    const stage = document.querySelector('[data-reel-stage]');
    if (!stage) return;

    const cards = [...stage.querySelectorAll('[data-reel-card]')];
    if (cards.length === 0) return;

    const caption = document.querySelector('[data-reel-caption]');
    const prev = document.querySelector('[data-reel-prev]');
    const next = document.querySelector('[data-reel-next]');
    let active = Math.floor(cards.length / 2);

    stage.dataset.enhanced = 'true';

    const layout = (animate = true) => {
        cards.forEach((card, index) => {
            const offset = index - active;
            const distance = Math.abs(offset);
            const isActive = offset === 0;

            card.dataset.active = String(isActive);
            card.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            card.tabIndex = isActive ? 0 : -1;

            const target = {
                // Cards fan outwards, tucking back in depth and rotating to face centre.
                x: offset * (distance > 2 ? 132 : 158),
                z: -distance * 190,
                rotateY: offset * -26,
                scale: Math.max(0.62, 1 - distance * 0.14),
                opacity: distance > 2 ? 0 : 1 - distance * 0.22,
                zIndex: 100 - distance,
                duration: animate ? 0.75 : 0,
                ease: 'power3.out',
            };

            gsap.to(card, target);

            // Only the focused clip plays; the rest hold on their poster frame.
            const video = card.querySelector('video');
            if (video) {
                if (isActive) {
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            }
        });

        if (caption) {
            const text = cards[active].dataset.reelCaption ?? '';
            if (animate && !prefersReducedMotion) {
                gsap.fromTo(caption, { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
            }
            caption.textContent = text;
        }
    };

    const move = (delta) => {
        active = (active + delta + cards.length) % cards.length;
        layout();
    };

    prev?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));
    cards.forEach((card, index) => {
        card.addEventListener('click', () => {
            if (index !== active) {
                active = index;
                layout();
            }
        });
    });

    stage.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') { move(1); }
        if (event.key === 'ArrowLeft') { move(-1); }
    });

    layout(false);

    if (!prefersReducedMotion) {
        gsap.from(cards, {
            y: 90,
            opacity: 0,
            duration: 0.9,
            stagger: 0.06,
            ease: 'power3.out',
            scrollTrigger: { trigger: stage, start: 'top 78%' },
            onComplete: () => layout(false),
        });
    }
}

function initSponsorStagger() {
    const grid = document.querySelector('[data-sponsor-grid]');
    if (!grid || prefersReducedMotion) return;

    gsap.from(grid.querySelectorAll('[data-sponsor-logo]'), {
        y: 40,
        opacity: 0,
        duration: 0.7,
        stagger: 0.05,
        ease: 'power3.out',
        scrollTrigger: { trigger: grid, start: 'top 85%' },
    });
}

export default function initCcsMotion() {
    initScrollProgress();
    initHero();
    initReel();
    initSponsorStagger();
}
