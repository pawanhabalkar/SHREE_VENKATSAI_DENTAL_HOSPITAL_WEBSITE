document.addEventListener("DOMContentLoaded", function () {

    /* ==========================================
       PATIENT INFORMATION — now loaded from the
       real logged-in session instead of being
       hardcoded as demo data.
    ========================================== */

    let patient = {
        mrn: "",
        name: ""
    };


    /* ==========================================
       FORM ELEMENTS
    ========================================== */

    const appointmentForm =
        document.getElementById("appointmentForm");

    const department =
        document.getElementById("department");

    const doctor =
        document.getElementById("doctor");

    const appointmentDate =
        document.getElementById("appointmentDate");

    const appointmentTime =
        document.getElementById("appointmentTime");

    const reason =
        document.getElementById("reason");

    const notes =
        document.getElementById("notes");

    const availabilityMessage =
        document.getElementById("availabilityMessage");


    /* ==========================================
       LOAD REAL PATIENT DETAILS FROM SESSION
    ========================================== */

    function loadPatientDetails() {

        fetch("../backend/patient/get_patient_profile.php")
            .then(function (response) { return response.json(); })
            .then(function (data) {

                patient.mrn = data.mrn;
                patient.name = data.full_name;

                document.getElementById("patientMRN").textContent =
                    patient.mrn;

                document.getElementById("reviewMRN").textContent =
                    patient.mrn;

                document.getElementById("reviewName").textContent =
                    patient.name;

                const fullNameInput = document.getElementById("fullName");
                const mobileInput = document.getElementById("mobile");
                const ageInput = document.getElementById("age");
                const genderInput = document.getElementById("gender");
                const addressInput = document.getElementById("address");

                if (fullNameInput) fullNameInput.value = data.full_name || "";
                if (mobileInput) mobileInput.value = data.mobile || "";
                if (ageInput) ageInput.value = data.age || "";
                if (genderInput) genderInput.value = data.gender || "";
                if (addressInput) addressInput.value = data.address || "";

            })
            .catch(function (error) {
                console.error("Failed to load patient details:", error);
            });

    }

    loadPatientDetails();


    /* ==========================================
       TODAY'S DATE
    ========================================== */

    const today = new Date();

    const todayString =
        today.toISOString().split("T")[0];

    appointmentDate.min = todayString;


    /* ==========================================
       DEPARTMENTS & DOCTORS — now loaded from the
       real database instead of a hardcoded list,
       so the values sent to the backend are real
       department_id / doctor_id numbers.
    ========================================== */

    let allDepartments = [];
    let allDoctors = [];

    function loadDepartmentsAndDoctors() {

        fetch("../backend/patient/get_departments_doctors.php")
            .then(function (response) { return response.json(); })
            .then(function (data) {

                allDepartments = data.departments;
                allDoctors = data.doctors;

                department.innerHTML =
                    '<option value="">Select Department</option>';

                allDepartments.forEach(function (dept) {

                    const option = document.createElement("option");
                    option.value = dept.id;
                    option.textContent = dept.department_name;
                    department.appendChild(option);

                });

            })
            .catch(function (error) {
                console.error("Failed to load departments/doctors:", error);
            });

    }

    loadDepartmentsAndDoctors();


    /* ==========================================
       TIME SLOTS
       Note: this is still a fixed list of clinic
       hours, not a real slot-availability check.
       Real double-booking prevention would need
       additional backend logic (checking existing
       appointments for that doctor/date) that
       hasn't been built yet.
    ========================================== */

    const timeSlots = [

        "09:00 AM",
        "09:30 AM",
        "10:00 AM",
        "10:30 AM",
        "11:00 AM",
        "11:30 AM",
        "12:00 PM",
        "12:30 PM",
        "02:00 PM",
        "02:30 PM",
        "03:00 PM",
        "03:30 PM",
        "04:00 PM",
        "04:30 PM",
        "05:00 PM",
        "05:30 PM"

    ];


    /* ==========================================
       DEPARTMENT CHANGE
    ========================================== */

    department.addEventListener("change", function () {

        const selectedDepartmentId =
            this.value;

        doctor.innerHTML =
            '<option value="">Select Doctor</option>';

        appointmentTime.innerHTML =
            '<option value="">Select Doctor & Date First</option>';

        appointmentTime.disabled = true;


        if (!selectedDepartmentId) {

            doctor.disabled = true;

            showAvailability(
                "Select a department, doctor and date to check available appointment slots.",
                "info"
            );

            updateReview();

            return;
        }


        const departmentDoctors =
            allDoctors.filter(function (doc) {
                return String(doc.department_id) === String(selectedDepartmentId);
            });


        departmentDoctors.forEach(function (doctorData) {

            const option =
                document.createElement("option");

            option.value =
                doctorData.id;

            option.textContent =
                doctorData.doctor_name;

            doctor.appendChild(option);

        });


        doctor.disabled = false;


        showAvailability(
            "Now select your preferred doctor and appointment date.",
            "info"
        );


        updateReview();

    });


    /* ==========================================
       DOCTOR CHANGE
    ========================================== */

    doctor.addEventListener("change", function () {

        loadTimeSlots();

        updateReview();

    });


    /* ==========================================
       DATE CHANGE
    ========================================== */

    appointmentDate.addEventListener("change", function () {

        loadTimeSlots();

        updateReview();

    });


    /* ==========================================
       LOAD AVAILABLE TIME SLOTS
    ========================================== */

    function loadTimeSlots() {

        appointmentTime.innerHTML =
            '<option value="">Select Available Time</option>';

        appointmentTime.disabled = true;


        if (!department.value ||
            !doctor.value ||
            !appointmentDate.value) {

            showAvailability(
                "Select department, doctor and date to check available appointment slots.",
                "info"
            );

            return;
        }


        const selectedDate =
            new Date(
                appointmentDate.value + "T00:00:00"
            );

        const day =
            selectedDate.getDay();


        if (day === 0) {

            showAvailability(
                "No appointments are available on Sunday. Please select another date.",
                "error"
            );

            return;
        }


        let availableCount = 0;


        timeSlots.forEach(function (slot) {

            const option =
                document.createElement("option");

            option.value = slot;

            option.textContent = slot;

            appointmentTime.appendChild(option);

            availableCount++;

        });


        if (availableCount === 0) {

            showAvailability(
                "No available slots for this doctor on the selected date.",
                "error"
            );

            return;
        }


        appointmentTime.disabled = false;


        showAvailability(
            availableCount +
            " appointment slots are currently available.",
            "success"
        );

    }


    /* ==========================================
       TIME CHANGE
    ========================================== */

    appointmentTime.addEventListener("change", function () {

        updateReview();

    });


    /* ==========================================
       REASON / NOTES
    ========================================== */

    reason.addEventListener("input", updateReview);

    notes.addEventListener("input", updateReview);


    /* ==========================================
       PRIORITY CHANGE
    ========================================== */

    document
        .querySelectorAll('input[name="priority"]')
        .forEach(function (radio) {

            radio.addEventListener(
                "change",
                updateReview
            );

        });


    /* ==========================================
       AVAILABILITY MESSAGE
    ========================================== */

    function showAvailability(message, type) {

        availabilityMessage.className =
            "availability-message";

        if (type === "error") {

            availabilityMessage.classList.add("error");

        }

        if (type === "success") {

            availabilityMessage.classList.add("success");

        }


        availabilityMessage.innerHTML = `

            <i class="fa-solid ${
                type === "error"
                    ? "fa-circle-exclamation"
                    : type === "success"
                    ? "fa-circle-check"
                    : "fa-circle-info"
            }"></i>

            <span>${message}</span>

        `;

    }


    /* ==========================================
       UPDATE REVIEW
    ========================================== */

    function updateReview() {

        const departmentText =
            department.options[
                department.selectedIndex
            ]?.text || "—";


        const doctorText =
            doctor.options[
                doctor.selectedIndex
            ]?.text || "—";


        const priority =
            document.querySelector(
                'input[name="priority"]:checked'
            )?.value || "normal";


        document.getElementById(
            "reviewDepartment"
        ).textContent =
            department.value
                ? departmentText
                : "—";


        document.getElementById(
            "reviewDoctor"
        ).textContent =
            doctor.value
                ? doctorText
                : "—";


        document.getElementById(
            "reviewDate"
        ).textContent =
            appointmentDate.value
                ? formatDate(appointmentDate.value)
                : "—";


        document.getElementById(
            "reviewTime"
        ).textContent =
            appointmentTime.value
                ? appointmentTime.value
                : "—";


        document.getElementById(
            "reviewPriority"
        ).textContent =
            capitalize(priority);

    }


    /* ==========================================
       FORMAT DATE
    ========================================== */

    function formatDate(dateString) {

        const date =
            new Date(dateString + "T00:00:00");


        return date.toLocaleDateString(
            "en-IN",
            {
                day: "2-digit",
                month: "short",
                year: "numeric"
            }
        );

    }


    /* ==========================================
       CAPITALIZE
    ========================================== */

    function capitalize(value) {

        return value.charAt(0).toUpperCase()
            + value.slice(1);

    }


    /* ==========================================
       CONVERT "09:30 AM" -> "09:30:00" (24hr)
       for the database TIME column
    ========================================== */

    function convertTo24Hour(timeString) {

        const [time, modifier] = timeString.split(" ");

        let [hours, minutes] = time.split(":");

        if (hours === "12") {
            hours = "00";
        }

        if (modifier === "PM") {
            hours = String(parseInt(hours, 10) + 12);
        }

        return `${hours.padStart(2, "0")}:${minutes}:00`;

    }


    /* ==========================================
       FORM SUBMISSION — now sends a real request
       to book_appointment.php instead of saving
       fake data to localStorage.
    ========================================== */

    appointmentForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            /* BASIC VALIDATION */

            if (!department.value) {

                alert(
                    "Please select a department."
                );

                department.focus();

                return;
            }


            if (!doctor.value) {

                alert(
                    "Please select a doctor."
                );

                doctor.focus();

                return;
            }


            if (!appointmentDate.value) {

                alert(
                    "Please select an appointment date."
                );

                appointmentDate.focus();

                return;
            }


            if (!appointmentTime.value) {

                alert(
                    "Please select an available time."
                );

                appointmentTime.focus();

                return;
            }


            if (!reason.value.trim()) {

                alert(
                    "Please enter the reason or symptoms."
                );

                reason.focus();

                return;
            }


            const selectedPriority =
                document.querySelector(
                    'input[name="priority"]:checked'
                ).value;


            /* Combine priority into notes, since the
               appointments table has no dedicated
               priority column. */
            const combinedNotes =
                "[Priority: " + capitalize(selectedPriority) + "] " +
                notes.value.trim();


            const formData = new FormData();
            formData.append("department_id", department.value);
            formData.append("doctor_id", doctor.value);
            formData.append("appointment_date", appointmentDate.value);
            formData.append("appointment_time", convertTo24Hour(appointmentTime.value));
            formData.append("reason", reason.value.trim());
            formData.append("notes", combinedNotes);


            const submitButton =
                appointmentForm.querySelector('button[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = "Booking...";
            }


            fetch("../backend/patient/book_appointment.php", {
                method: "POST",
                body: formData
            })
                .then(function (response) { return response.text(); })
                .then(function (result) {

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = "Confirm Appointment";
                    }

                    if (result.trim().startsWith("success")) {

                        const newAppointmentId =
                            result.trim().split(":")[1];

                        document.getElementById(
                            "successAppointmentId"
                        ).textContent =
                            "#" + newAppointmentId;


                        document.getElementById(
                            "successMRN"
                        ).textContent =
                            patient.mrn;


                        document.getElementById(
                            "successModal"
                        ).classList.add("show");

                    } else {

                        alert("Booking failed: " + result);

                    }

                })
                .catch(function (error) {

                    console.error("Booking request failed:", error);
                    alert("Something went wrong while booking. Please try again.");

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = "Confirm Appointment";
                    }

                });

        }
    );


    /* ==========================================
       MODAL CLOSE
    ========================================== */

    document.getElementById(
        "modalClose"
    ).addEventListener(
        "click",
        closeModal
    );


    /* ==========================================
       BOOK ANOTHER
    ========================================== */

    document.getElementById(
        "bookAnother"
    ).addEventListener(
        "click",
        function () {

            document.getElementById(
                "successModal"
            ).classList.remove("show");


            appointmentForm.reset();


            doctor.innerHTML =
                '<option value="">Select Department First</option>';

            doctor.disabled = true;


            appointmentTime.innerHTML =
                '<option value="">Select Doctor & Date First</option>';

            appointmentTime.disabled = true;


            document.querySelector(
                'input[name="priority"][value="normal"]'
            ).checked = true;


            updateReview();


            showAvailability(
                "Select a department, doctor and date to check available appointment slots.",
                "info"
            );

        }
    );


    function closeModal() {

        document.getElementById(
            "successModal"
        ).classList.remove("show");

    }


    /* ==========================================
       INITIAL REVIEW
    ========================================== */

    updateReview();

});