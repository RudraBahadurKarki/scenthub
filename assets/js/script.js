(function () {
    const nav = document.getElementById('luxNavbar');
    const updateNav = () => {
        if (!nav) return;
        nav.classList.toggle('nav-scrolled', window.scrollY > 18);
    };
    updateNav();
    window.addEventListener('scroll', updateNav, { passive: true });

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!reduceMotion) {
        document.querySelectorAll('.tilt-card').forEach((card) => {
            card.addEventListener('mousemove', (event) => {
                if (window.innerWidth < 768) return;
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                const rotateY = ((x / rect.width) - 0.5) * 9;
                const rotateX = (0.5 - (y / rect.height)) * 9;
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }

    const revealItems = document.querySelectorAll('.reveal-on-scroll');
    if ('IntersectionObserver' in window && !reduceMotion) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    document.querySelectorAll('a[href*="delete"], .btn-outline-danger').forEach((btn) => {
        if (!btn.hasAttribute('onclick') && btn.textContent.trim().toLowerCase().includes('delete')) {
            btn.addEventListener('click', (event) => {
                if (!confirm('Are you sure?')) event.preventDefault();
            });
        }
    });

    if (reduceMotion) return;

    document.querySelectorAll('.btn').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 650);
        });
    });

    document.querySelectorAll('.btn, a').forEach((btn) => {
        if (!btn.textContent.trim().toLowerCase().includes('add to cart')) return;

        btn.addEventListener('click', function () {
            this.classList.add('btn-added');
            setTimeout(() => this.classList.remove('btn-added'), 500);

            const cartBadge = document.querySelector('.cart-badge');
            const cartTarget = cartBadge ? (cartBadge.closest('a, button') || cartBadge) : null;
            if (!cartTarget) return;

            const start = this.getBoundingClientRect();
            const end = cartTarget.getBoundingClientRect();

            const dot = document.createElement('div');
            dot.className = 'fly-to-cart';
            dot.style.width = dot.style.height = '14px';
            dot.style.left = (start.left + start.width / 2) + 'px';
            dot.style.top = (start.top + start.height / 2) + 'px';
            document.body.appendChild(dot);

            requestAnimationFrame(() => {
                dot.style.left = (end.left + end.width / 2) + 'px';
                dot.style.top = (end.top + end.height / 2) + 'px';
                dot.style.transform = 'scale(.3)';
                dot.style.opacity = '0.3';
            });

            setTimeout(() => {
                dot.remove();
                if (cartBadge) {
                    cartBadge.classList.add('bump');
                    setTimeout(() => cartBadge.classList.remove('bump'), 450);
                }
            }, 700);
        });
    });

    document.querySelectorAll('a, button').forEach((el) => {
        if (!el.textContent.trim().toLowerCase().includes('view details')) return;

        el.addEventListener('click', function (e) {
            const card = this.closest('.tilt-card, .product-card, .card');
            const href = this.tagName === 'A' ? this.getAttribute('href') : null;

            if (card && href) {
                e.preventDefault();
                card.classList.add('morphing');
                setTimeout(() => { window.location.href = href; }, 380);
            }
        });
    });
})();