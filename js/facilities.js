document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".facility-card");
    if (!cards.length || !("IntersectionObserver" in window)) return;
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add("show"); observer.unobserve(entry.target); }
        });
    }, { threshold: 0.12 });
    cards.forEach(card => observer.observe(card));
});
