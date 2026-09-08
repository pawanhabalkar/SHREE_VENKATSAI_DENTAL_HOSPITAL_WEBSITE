document.addEventListener("DOMContentLoaded", function () {

    /* ==========================================
       PATIENT INFORMATION
    ========================================== */

    const patient = {

        mrn: "SHVMS-MRN-000001",

        name: "Rahul Patil",

        mobile: "9876543210",

        age: 28,

        gender: "Male",

        address: "Kalaburagi, Karnataka",

        area: "Vidya Nagar"

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
       LOAD PATIENT DETAILS
    ========================================== */

    document.getElementById("patientMRN").textContent =
        patient.mrn;

    document.getElementById("reviewMRN").textContent =
        patient.mrn;

    document.getElementById("reviewName").textContent =
        patient.name;


    /* ==========================================
       TODAY'S DATE
    ========================================== */

    const today = new Date();

    const todayString =
        today.toISOString().split("T")[0];

    appointmentDate.min = todayString;


    /* ==========================================
       DOCTORS BY DEPARTMENT
    ========================================== */

    const doctors = {

        "general-dentistry": [

            {
                id: "DOC001",
                name: "Dr. Anil Kumar"
            },

            {
                id: "DOC002",
                name: "Dr. Priya Sharma"
            }

        ],

        "endodontics": [

            {
                id: "DOC003",
                name: "Dr. Ravi Patel"
            }

        ],

        "orthodontics": [

            {
                id: "DOC004",
                name: "Dr. Sneha Rao"
            }

        ],

        "oral-surgery": [

            {
                id: "DOC005",
                name: "Dr. Kiran Reddy"
            }

        ],

        "prosthodontics": [

            {
                id: "DOC006",
                name: "Dr. Meena Joshi"
            }

        ],

        "periodontics": [

            {
                id: "DOC007",
                name: "Dr. Arjun Desai"
            }

        ],

        "pedodontics": [

            {
                id: "DOC008",
                name: "Dr. Neha Kulkarni"
            }

        ],

        "laser-treatment": [

            {
                id: "DOC009",
                name: "Dr. Vikram Shah"
            }

        ]

    };


    /* ==========================================
       TIME SLOTS - DEMO
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

        const selectedDepartment =
            this.value;

        doctor.innerHTML =
            '<option value="">Select Doctor</option>';

        appointmentTime.innerHTML =
            '<option value="">Select Doctor & Date First</option>';

        appointmentTime.disabled = true;


        if (!selectedDepartment) {

            doctor.disabled = true;

            showAvailability(
                "Select a department, doctor and date to check available appointment slots.",
                "info"
            );

            updateReview();

            return;
        }


        const departmentDoctors =
            doctors[selectedDepartment] || [];


        departmentDoctors.forEach(function (doctorData) {

            const option =
                document.createElement("option");

            option.value =
                doctorData.id;

            option.textContent =
                doctorData.name;

            option.dataset.doctorName =
                doctorData.name;

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


        /*
            Demo:
            Sunday = Hospital closed
        */

        if (day === 0) {

            showAvailability(
                "No appointments are available on Sunday. Please select another date.",
                "error"
            );

            return;
        }


        /*
            Demo occupied slots.
            Later this will come from backend/database.
        */

        const occupiedSlots = [

            "10:00 AM",
            "12:00 PM",
            "03:30 PM"

        ];


        let availableCount = 0;


        timeSlots.forEach(function (slot) {

            if (!occupiedSlots.includes(slot)) {

                const option =
                    document.createElement("option");

                option.value = slot;

                option.textContent = slot;

                appointmentTime.appendChild(option);

                availableCount++;

            }

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
       GENERATE DEMO APPOINTMENT ID
    ========================================== */

    function generateAppointmentId() {

        const year =
            new Date().getFullYear();

        const random =
            Math.floor(
                100000 +
                Math.random() * 900000
            );

        return `APT-${year}-${random}`;

    }


    /* ==========================================
       FORM SUBMISSION
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


            /*
                IMPORTANT:

                In the actual system this validation
                must be performed again by the backend
                before inserting the appointment.
            */


            const appointmentId =
                generateAppointmentId();


            const selectedPriority =
                document.querySelector(
                    'input[name="priority"]:checked'
                ).value;


            /* APPOINTMENT OBJECT */

            const appointment = {

                appointmentId: appointmentId,

                patientMRN: patient.mrn,

                patientName: patient.name,

                department:
                    department.value,

                doctor:
                    doctor.value,

                date:
                    appointmentDate.value,

                time:
                    appointmentTime.value,

                reason:
                    reason.value.trim(),

                notes:
                    notes.value.trim(),

                priority:
                    selectedPriority,

                status:
                    "PENDING"

            };


            /*
                DEMO STORAGE

                Later replace this with:
                POST /api/appointments
            */

            saveAppointment(appointment);


            /* SUCCESS MODAL */

            document.getElementById(
                "successAppointmentId"
            ).textContent =
                appointmentId;


            document.getElementById(
                "successMRN"
            ).textContent =
                patient.mrn;


            document.getElementById(
                "successModal"
            ).classList.add("show");


        }
    );


    /* ==========================================
       SAVE DEMO APPOINTMENT
    ========================================== */

    function saveAppointment(appointment) {

        const existing =
            JSON.parse(
                localStorage.getItem(
                    "patientAppointments"
                )
            ) || [];


        existing.push(appointment);


        localStorage.setItem(
            "patientAppointments",
            JSON.stringify(existing)
        );

    }


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