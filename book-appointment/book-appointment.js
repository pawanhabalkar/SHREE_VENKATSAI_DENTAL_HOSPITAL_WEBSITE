
/* =========================================================
   ODSMART DENTAL HOSPITAL
   BOOK APPOINTMENT JAVASCRIPT
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const form =
        document.getElementById("appointmentForm");

    const phone =
        document.getElementById("phone");

    const email =
        document.getElementById("email");

    const verifyPhone =
        document.getElementById("verifyPhone");

    const verifyEmail =
        document.getElementById("verifyEmail");

    const phoneMessage =
        document.getElementById("phoneMessage");

    const emailMessage =
        document.getElementById("emailMessage");

    const appointmentDate =
        document.getElementById("appointmentDate");

    const successOverlay =
        document.getElementById("successOverlay");

    const closeModal =
        document.getElementById("closeModal");

    const bookAnother =
        document.getElementById("bookAnother");


    /* =====================================================
       SET MINIMUM DATE
       Prevent selecting previous dates
       ===================================================== */

    const today = new Date();

    const year =
        today.getFullYear();

    const month =
        String(today.getMonth() + 1)
            .padStart(2, "0");

    const day =
        String(today.getDate())
            .padStart(2, "0");

    const todayString =
        `${year}-${month}-${day}`;

    appointmentDate.min =
        todayString;


    /* =====================================================
       PHONE NUMBER
       ===================================================== */

    phone.addEventListener("input", function () {

        this.value =
            this.value.replace(/\D/g, "");

        if (this.value.length > 10) {

            this.value =
                this.value.slice(0, 10);
        }
    });


    /* =====================================================
       VERIFY PHONE
       ===================================================== */

    verifyPhone.addEventListener("click", function () {

        const number =
            phone.value.trim();

        if (number.length !== 10) {

            phoneMessage.textContent =
                "Please enter a valid 10-digit mobile number.";

            phoneMessage.style.color =
                "#8b2e35";

            return;
        }

        phoneMessage.textContent =
            "✓ Mobile number verified.";

        phoneMessage.style.color =
            "#286b58";

        verifyPhone.textContent =
            "Verified";

        verifyPhone.disabled =
            true;

    });


    /* =====================================================
       VERIFY EMAIL
       ===================================================== */

    verifyEmail.addEventListener("click", function () {

        const emailValue =
            email.value.trim();

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(emailValue)) {

            emailMessage.textContent =
                "Please enter a valid email address.";

            emailMessage.style.color =
                "#8b2e35";

            return;
        }

        emailMessage.textContent =
            "✓ Email address verified.";

        emailMessage.style.color =
            "#286b58";

        verifyEmail.textContent =
            "Verified";

        verifyEmail.disabled =
            true;

    });


    /* =====================================================
       FORM SUBMISSION
       ===================================================== */

    form.addEventListener("submit", function (event) {

        event.preventDefault();


        /* Validate phone */

        if (phone.value.trim().length !== 10) {

            alert(
                "Please enter a valid 10-digit mobile number."
            );

            phone.focus();

            return;
        }


        /* Get values */

        const fullName =
            document
                .getElementById("fullName")
                .value
                .trim();

        const doctor =
            document
                .getElementById("doctor")
                .value;

        const date =
            appointmentDate.value;

        const time =
            document
                .getElementById("appointmentTime")
                .value;


        /* Format date */

        let formattedDate =
            date;

        if (date) {

            const dateObject =
                new Date(date + "T00:00:00");

            formattedDate =
                dateObject.toLocaleDateString(
                    "en-IN",
                    {
                        day: "2-digit",
                        month: "short",
                        year: "numeric"
                    }
                );
        }


        /* Fill summary */

        document.getElementById(
            "summaryName"
        ).textContent =
            fullName || "-";

        document.getElementById(
            "summaryDoctor"
        ).textContent =
            doctor || "-";

        document.getElementById(
            "summaryDate"
        ).textContent =
            formattedDate || "-";

        document.getElementById(
            "summaryTime"
        ).textContent =
            time || "-";


        /* Show success modal */

        successOverlay.classList.add(
            "active"
        );

        document.body.style.overflow =
            "hidden";


        /*
         * Temporary frontend storage.
         *
         * This stores the appointment in
         * localStorage until your backend/API
         * is connected.
         */

        const appointmentData = {

            name: fullName,

            phone:
                phone.value.trim(),

            email:
                email.value.trim(),

            age:
                document
                    .getElementById("age")
                    .value,

            gender:
                document
                    .getElementById("gender")
                    .value,

            department:
                document
                    .getElementById("department")
                    .value,

            doctor: doctor,

            date: date,

            time: time,

            symptoms:
                document
                    .getElementById("symptoms")
                    .value
                    .trim(),

            createdAt:
                new Date().toISOString()

        };


        localStorage.setItem(
            "odsmartLastAppointment",
            JSON.stringify(appointmentData)
        );

    });


    /* =====================================================
       CLOSE MODAL
       ===================================================== */

    closeModal.addEventListener(
        "click",
        closeSuccessModal
    );


    /* =====================================================
       BOOK ANOTHER
       ===================================================== */

    bookAnother.addEventListener(
        "click",
        function () {

            closeSuccessModal();

            form.reset();

            verifyPhone.textContent =
                "Verify";

            verifyPhone.disabled =
                false;

            verifyEmail.textContent =
                "Verify";

            verifyEmail.disabled =
                false;

            phoneMessage.textContent =
                "";

            emailMessage.textContent =
                "";

        }
    );


    /* =====================================================
       CLOSE FUNCTION
       ===================================================== */

    function closeSuccessModal() {

        successOverlay.classList.remove(
            "active"
        );

        document.body.style.overflow =
            "";
    }


    /* =====================================================
       CLICK OUTSIDE MODAL
       ===================================================== */

    successOverlay.addEventListener(
        "click",
        function (event) {

            if (
                event.target ===
                successOverlay
            ) {

                closeSuccessModal();
            }

        }
    );


    /* =====================================================
       ESC KEY
       ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                successOverlay.classList.contains(
                    "active"
                )
            ) {

                closeSuccessModal();
            }

        }
    );

});

const areaSelect = document.getElementById("area");
const otherAreaGroup = document.getElementById("otherAreaGroup");
const otherAreaInput = document.getElementById("otherArea");

areaSelect.addEventListener("change", function () {

    if (this.value === "Other") {
        // Show manual area field
        otherAreaGroup.style.display = "block";

        // Make manual area required
        otherAreaInput.required = true;

        // Focus on the text area
        otherAreaInput.focus();

    } else {
        // Hide manual area field
        otherAreaGroup.style.display = "none";

        // Remove required validation
        otherAreaInput.required = false;

        // Clear previous manual entry
        otherAreaInput.value = "";
    }
});