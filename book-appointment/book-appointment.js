/* =========================================================
   ODSMART DENTAL HOSPITAL
   BOOK APPOINTMENT JAVASCRIPT
   ========================================================= */

let allDepartments = [];
let allDoctors = [];

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

    const departmentSelect =
        document.getElementById("department");

    const doctorSelect =
        document.getElementById("doctor");

    const areaSelect =
        document.getElementById("area");

    const otherAreaGroup =
        document.getElementById("otherAreaGroup");

    const otherAreaInput =
        document.getElementById("otherArea");


    /* =====================================================
       LOAD DEPARTMENTS + DOCTORS FROM BACKEND
       ===================================================== */

    fetch("../backend/public/get_departments_doctors.php")
        .then(response => response.json())
        .then(data => {

            allDepartments = data.departments || [];
            allDoctors = data.doctors || [];

            departmentSelect.innerHTML = `<option value="">Select Department</option>`;

            allDepartments.forEach(function (dept) {
                const option = document.createElement("option");
                option.value = dept.id;
                option.textContent = dept.department_name;
                departmentSelect.appendChild(option);
            });

        })
        .catch(function (error) {
            console.error("Failed to load departments/doctors:", error);
        });


    departmentSelect.addEventListener("change", function () {

        const selectedDepartmentId = departmentSelect.value;

        doctorSelect.innerHTML = `<option value="">Select Doctor</option>`;

        if (!selectedDepartmentId) {
            return;
        }

        const filteredDoctors = allDoctors.filter(function (doc) {
            return String(doc.department_id) === String(selectedDepartmentId);
        });

        filteredDoctors.forEach(function (doc) {
            const option = document.createElement("option");
            option.value = doc.id;
            option.textContent = doc.doctor_name +
                (doc.specialization ? ` (${doc.specialization})` : "");
            doctorSelect.appendChild(option);
        });

    });


    /* =====================================================
       AREA "OTHER" TOGGLE
       ===================================================== */

    areaSelect.addEventListener("change", function () {

        if (this.value === "Other") {

            otherAreaGroup.style.display = "block";
            otherAreaInput.required = true;
            otherAreaInput.focus();

        } else {

            otherAreaGroup.style.display = "none";
            otherAreaInput.required = false;
            otherAreaInput.value = "";

        }
    });


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

        const departmentId =
            departmentSelect.value;

        const doctorId =
            doctorSelect.value;

        const doctorText =
            doctorSelect.options[doctorSelect.selectedIndex]
                ? doctorSelect.options[doctorSelect.selectedIndex].textContent
                : "";

        const date =
            appointmentDate.value;

        const time =
            document
                .getElementById("appointmentTime")
                .value;

        const finalArea =
            areaSelect.value === "Other"
                ? otherAreaInput.value.trim()
                : areaSelect.value;

        if (!departmentId) {
            alert("Please select a department.");
            departmentSelect.focus();
            return;
        }

        if (!doctorId) {
            alert("Please select a doctor.");
            doctorSelect.focus();
            return;
        }

        if (!date) {
            alert("Please select an appointment date.");
            appointmentDate.focus();
            return;
        }

        if (!time) {
            alert("Please select an appointment time.");
            return;
        }

        const password = document.getElementById("password").value;

        if (!password || password.length < 6) {
            alert("Please set a password (at least 6 characters) for your patient portal.");
            document.getElementById("password").focus();
            return;
        }

        /* Format date for the summary display */

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


        const formPayload = new URLSearchParams();
        formPayload.append("full_name", fullName);
        formPayload.append("mobile", phone.value.trim());
        formPayload.append("email", email.value.trim());
        formPayload.append("password", password);
        formPayload.append("age", document.getElementById("age").value);
        formPayload.append("gender", document.getElementById("gender").value);
        formPayload.append("area", finalArea);
        formPayload.append("address", document.getElementById("address").value.trim());
        formPayload.append("department_id", departmentId);
        formPayload.append("doctor_id", doctorId);
        formPayload.append("appointment_date", date);
        formPayload.append("appointment_time", convertTo24Hour(time));
        formPayload.append("reason", document.getElementById("symptoms").value.trim());

        const submitBtn = form.querySelector("button[type='submit']");
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        fetch("../backend/public/book_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formPayload.toString()
        })
        .then(response => response.json())
        .then(data => {

            if (data.result === "success") {

                document.getElementById("summaryName").textContent = fullName || "-";
                document.getElementById("summaryDoctor").textContent = doctorText || "-";
                document.getElementById("summaryDate").textContent = formattedDate || "-";
                document.getElementById("summaryTime").textContent = time || "-";

                const loginEmail = email.value.trim() || `(no email provided — use ${phone.value.trim()}@no-email.local)`;
                alert("Your Patient Portal login email is: " + loginEmail + "\nUse the password you just created to log in.");

                successOverlay.classList.add("active");
                document.body.style.overflow = "hidden";

            } else {

                alert("Booking failed: " + (data.error || "unknown error"));

            }

        })
        .catch(function (error) {
            console.error("Booking failed:", error);
            alert("Unable to connect to the server.");
        })
        .finally(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });

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

            doctorSelect.innerHTML = `<option value="">Select Doctor</option>`;

            otherAreaGroup.style.display = "none";
            otherAreaInput.required = false;

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


/* =====================================================
   CONVERT "04:00 PM" -> "16:00:00" FOR THE BACKEND
   (appointments.appointment_time is a TIME column)
   ===================================================== */

function convertTo24Hour(timeStr) {

    const [time, modifier] = timeStr.split(" ");
    let [hours, minutes] = time.split(":");

    hours = parseInt(hours, 10);

    if (modifier === "PM" && hours !== 12) {
        hours += 12;
    }

    if (modifier === "AM" && hours === 12) {
        hours = 0;
    }

    return `${String(hours).padStart(2, "0")}:${minutes}:00`;

}