document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contactForm");
    const message = document.getElementById("formMessage");
    if (!form || !message) return;
    form.addEventListener("submit", event => {
        event.preventDefault();
        message.textContent = "Thank you. Your enquiry has been recorded for this frontend demo.";
        form.reset();
    });
});
