document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contactForm");
    const message = document.getElementById("formMessage");
    if (!form || !message) return;
    form.addEventListener("submit", event => {
        event.preventDefault();
        // This form isn't wired up to send an email or save to the
        // database yet, so don't tell the visitor it was "sent" or
        // "recorded" — that would be misleading. Point them to a
        // channel that actually reaches the hospital instead.
        message.textContent =
            "This form isn't connected yet, so your message wasn't sent. " +
            "Please call us at +91 9739554406 or email Shreevenkatsai@dentalhospital.com and we'll get back to you.";
        form.reset();
    });
});
