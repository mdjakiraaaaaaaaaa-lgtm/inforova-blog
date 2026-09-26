// Smooth-scroll for in-page anchors (table of contents, etc.)
document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
        var el = document.querySelector(a.getAttribute('href'));
        if (el) {
            e.preventDefault();
            el.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Mobile nav toggle
(function () {
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('siteNav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        var open = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
        if (!nav.contains(e.target) && !toggle.contains(e.target)) {
            nav.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();

// Nav dropdowns (Categories / Resource Hubs) - tap to open on touch devices
document.querySelectorAll('.nav-dropdown-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var parent = btn.closest('.nav-dropdown');
        var wasOpen = parent.classList.contains('open');
        document.querySelectorAll('.nav-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
        if (!wasOpen) parent.classList.add('open');
    });
});
document.addEventListener('click', function () {
    document.querySelectorAll('.nav-dropdown.open').forEach(function (d) { d.classList.remove('open'); });
});

// Fade-in reveal for article/category cards as they scroll into view
(function () {
    var cards = document.querySelectorAll('.card.reveal');
    if (!cards.length) return;

    if (!('IntersectionObserver' in window)) {
        cards.forEach(function (c) { c.classList.add('in'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    cards.forEach(function (c) { observer.observe(c); });
})();
