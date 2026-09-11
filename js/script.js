document.addEventListener("DOMContentLoaded", () => {
    const header = document.getElementById("header");
    const menuBtn = document.getElementById("menuBtn");
    const navbar = document.getElementById("navbar");

    const updateHeader = () => {
        if (header) header.classList.toggle("scrolled", window.scrollY > 40);
    };
    updateHeader();
    window.addEventListener("scroll", updateHeader, { passive: true });

    const closeMenu = () => {
        if (!navbar || !menuBtn) return;
        navbar.classList.remove("mobile-open");
        menuBtn.setAttribute("aria-expanded", "false");
        menuBtn.setAttribute("aria-label", "Open navigation menu");
        const icon = menuBtn.querySelector("i");
        if (icon) { icon.classList.add("fa-bars"); icon.classList.remove("fa-xmark"); }
    };

    // if (menuBtn && navbar) {
    //     menuBtn.addEventListener("click", () => {
    //         const open = navbar.classList.toggle("mobile-open");
    //         menuBtn.setAttribute("aria-expanded", String(open));
    //         menuBtn.setAttribute("aria-label", open ? "Close navigation menu" : "Open navigation menu");
    //         const icon = menuBtn.querySelector("i");
    //         if (icon) { icon.classList.toggle("fa-bars", !open); icon.classList.toggle("fa-xmark", open); }
    //     });
    //     navbar.querySelectorAll("a").forEach(link => link.addEventListener("click", closeMenu));
    // }
    if (menuBtn && navbar) {
    menuBtn.addEventListener("click", () => {
        const open = navbar.classList.toggle("mobile-open");

        menuBtn.setAttribute("aria-expanded", String(open));

        menuBtn.setAttribute(
            "aria-label",
            open ? "Close navigation menu" : "Open navigation menu"
        );

        const icon = menuBtn.querySelector("i");

        if (icon) {
            icon.classList.toggle("fa-bars", !open);
            icon.classList.toggle("fa-xmark", open);
        }
    });

    navbar.querySelectorAll("a").forEach(link =>
        link.addEventListener("click", closeMenu)
    );
}

    // Mark the correct page active while keeping Home section links functional.
    const current = window.location.pathname.split("/").pop() || "index.html";
    navbar?.querySelectorAll(".nav-link").forEach(link => {
        const href = link.getAttribute("href") || "";
        const target = href.split("#")[0] || "index.html";
        link.classList.toggle("active", target === current);
    });

    // Counter animation is enabled only when counters exist.
    const counters = document.querySelectorAll(".counter");
    if (counters.length && "IntersectionObserver" in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const counter = entry.target;
                const target = Number(counter.dataset.target || 0);
                const start = performance.now();
                const duration = 1300;
                const animate = now => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = Math.floor(target * eased).toLocaleString();
                    if (progress < 1) requestAnimationFrame(animate);
                    else counter.textContent = target.toLocaleString();
                };
                requestAnimationFrame(animate);
                observer.unobserve(counter);
            });
        }, { threshold: 0.35 });
        counters.forEach(counter => observer.observe(counter));
    }

    const revealElements = document.querySelectorAll(".speciality-card, .why-card, .doctor-card, .contact-item, .testimonial-preview-card, .contact-detail-card");
    if ("IntersectionObserver" in window && revealElements.length) {
        revealElements.forEach(el => { el.style.opacity = "0"; el.style.transform = "translateY(18px)"; el.style.transition = "opacity .6s ease, transform .6s ease"; });
        const revealObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.12 });
        revealElements.forEach(el => revealObserver.observe(el));
    }
});
