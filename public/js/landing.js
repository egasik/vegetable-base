(() => {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const menu = document.getElementById('store-menu');
    const closeMenu = () => {
        if (!toggle || !menu) return;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Открыть меню');
        menu.classList.remove('is-open');
        document.body.classList.remove('menu-open');
    };
    if (toggle && menu) {
        [...menu.children].forEach((link, index) => link.style.setProperty('--item', Math.min(index, 6)));
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
            menu.classList.toggle('is-open', open);
            document.body.classList.toggle('menu-open', open);
        });
        menu.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                closeMenu();
                toggle.focus();
            }
            if (event.key === 'Tab' && toggle.getAttribute('aria-expanded') === 'true') {
                const focusable = [toggle, ...menu.querySelectorAll('a, summary, button')].filter(el => el.getClientRects().length);
                const first = focusable[0], last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
        window.matchMedia('(min-width: 761px)').addEventListener('change', event => { if (event.matches) closeMenu(); });
    }
    const garden = document.querySelector('.home-garden');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (garden) {
        const motifs = [...garden.querySelectorAll('[data-garden-speed]')];
        let frame = 0;
        let positions = new Map();
        const paint = () => {
            frame = 0;
            const center = window.scrollY + window.innerHeight / 2;
            motifs.forEach(motif => {
                const distance = center - positions.get(motif);
                if (!reducedMotion.matches && Math.abs(distance) > window.innerHeight + 640) return;
                const factor = window.innerWidth <= 760 ? .5 : 1;
                const shift = reducedMotion.matches ? 0 : Math.max(-320, Math.min(320, distance * Number(motif.dataset.gardenSpeed) * factor));
                motif.style.setProperty('--garden-shift', `${shift.toFixed(1)}px`);
            });
        };
        const schedule = () => { if (!frame) frame = requestAnimationFrame(paint); };
        const measure = () => {
            const root = garden.parentElement;
            const top = root.getBoundingClientRect().top + window.scrollY;
            positions = new Map(motifs.map(motif => [motif, top + root.offsetHeight * parseFloat(motif.style.getPropertyValue('--garden-top')) / 100]));
            schedule();
        };
        measure();
        window.addEventListener('scroll', schedule, {passive: true});
        window.addEventListener('resize', measure, {passive: true});
        if ('ResizeObserver' in window) new ResizeObserver(measure).observe(garden.parentElement);
        reducedMotion.addEventListener('change', () => {
            if (reducedMotion.matches) motifs.forEach(motif => motif.style.setProperty('--garden-shift', '0px'));
            measure();
        });
    }
    if (!('IntersectionObserver' in window) || reducedMotion.matches) return;
    document.documentElement.classList.add('motion-ready');
    const reveal = new IntersectionObserver(entries => entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('revealed');
        reveal.unobserve(entry.target);
    }), {threshold: .08});
    document.querySelectorAll('[data-reveal]').forEach(section => reveal.observe(section));
    const words = new IntersectionObserver(entries => entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('word-visible');
        words.unobserve(entry.target);
    }), {rootMargin: '0px 0px -15% 0px'});
    document.querySelectorAll('[data-tagline] h2>span').forEach((word, index) => {
        word.style.transitionDelay = `${index * 60}ms`;
        words.observe(word);
    });
})();
