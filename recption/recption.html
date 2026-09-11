/* =========================================================
   SHREE VENKATSAI DENTAL HOSPITAL
   RECEPTION / FRONT DESK
   Connected to real backend endpoints (backend/receptionist/)
========================================================= */


/* =========================================================
   CONFIG
========================================================= */

const API_BASE = "../backend/receptionist";
const LOGIN_PAGE = "../login page/login.html";


/* =========================================================
   STATE
========================================================= */

let selectedExistingPatient = null; // { id, mrn, full_name, mobile, ... } or null
let departmentsData = [];
let doctorsData = [];


/* =========================================================
   DOM ELEMENTS
========================================================= */

const patientSearch = document.getElementById("patientSearch");
const searchPatientBtn = document.getElementById("searchPatientBtn");
const newPatientBtn = document.getElementById("newPatientBtn");
const searchMessage = document.getElementById("searchMessage");

const existingPatient = document.getElementById("existingPatient");
const foundPatientName = document.getElementById("foundPatientName");
const foundPatientMRN = document.getElementById("foundPatientMRN");
const foundPatientPhone = document.getElementById("foundPatientPhone");
const foundPatientAge = document.getElementById("foundPatientAge");
const foundPatientAddress = document.getElementById("foundPatientAddress");

const appointmentForm = document.getElementById("appointmentForm");

const mrn = document.getElementById("mrn");
const patientName = document.getElementById("patientName");
const patientAge = document.getElementById("patientAge");
const patientEmail = document.getElementById("patientEmail");
const dateOfBirth = document.getElementById("dateOfBirth");
const gender = document.getElementById("gender");
const appointmentDate = document.getElementById("appointmentDate");

const patientPasswordGroup = document.getElementById("patientPasswordGroup");
const patientPassword = document.getElementById("patientPassword");

const area = document.getElementById("area");
const otherAddressGroup = document.getElementById("otherAddressGroup");
const otherAddress = document.getElementById("otherAddress");

const phone = document.getElementById("phone");
const alternatePhone = document.getElementById("alternatePhone");

const department = document.getElementById("department");
const consultant = document.getElementById("consultant");
const appointmentTime = document.getElementById("appointmentTime");
const appointmentType = document.getElementById("appointmentType");
const visitReason = document.getElementById("visitReason");

const clearFormBtn = document.getElementById("clearFormBtn");

const successModal = document.getElementById("successModal");
const successMRN = document.getElementById("successMRN");
const successAppointmentNo = document.getElementById("successAppointmentNo");
const closeSuccessBtn = document.getElementById("closeSuccessBtn");
const printAppointmentBtn = document.getElementById("printAppointmentBtn");

const logoutBtn = document.getElementById("logoutBtn");


/* =========================================================
   INIT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    checkSession();
    loadDepartmentsAndDoctors();
    setMinimumDate();
    attachEvents();
    consultant.addEventListener("change", checkDoctorAvailability);
    appointmentDate.addEventListener("change", checkDoctorAvailability);

});


/* =========================================================
   SESSION CHECK
========================================================= */

function checkSession() {

    fetch(`${API_BASE}/check_session.php`, {
        credentials: "same-origin"
    })

    .then(response => {

        if (!response.ok) {
            throw new Error("not_logged_in");
        }

        return response.json();

    })

    .then(data => {

        if (!data.logged_in) {
            window.location.href = LOGIN_PAGE;
        }

    })

    .catch(function () {

        window.location.href = LOGIN_PAGE;

    });

}


/* =========================================================
   LOGOUT
========================================================= */

if (logoutBtn) {

    logoutBtn.addEventListener("click", function (event) {

        event.preventDefault();

        const confirmLogout = confirm(
            "Are you sure you want to logout?"
        );

        if (!confirmLogout) {
            return;
        }

        // Actually destroy the PHP session server-side, not just
        // clear client-side storage — otherwise the session cookie
        // stays valid and protected endpoints remain accessible.
        window.location.href = "../backend/auth/logout.php";

    });

}


/* =========================================================
   LOAD DEPARTMENTS + DOCTORS
========================================================= */

function loadDepartmentsAndDoctors() {

    fetch(`${API_BASE}/get_departments_doctors.php`, {
        credentials: "same-origin"
    })

    .then(response => response.json())

    .then(data => {

        departmentsData = data.departments || [];
        doctorsData = data.doctors || [];

        populateDepartmentOptions();

    })

    .catch(function (error) {

        console.error("Failed to load departments/doctors:", error);

    });

}

function populateDepartmentOptions() {

    department.innerHTML = `<option value="">Select department</option>`;

    departmentsData.forEach(function (dept) {

        const option = document.createElement("option");
        option.value = dept.id;
        option.textContent = dept.department_name;
        department.appendChild(option);

    });

}

function populateConsultantOptions(selectedDepartmentId) {

    consultant.innerHTML = `<option value="">Select consultant</option>`;

    const filtered = selectedDepartmentId
        ? doctorsData.filter(doc => String(doc.department_id) === String(selectedDepartmentId))
        : doctorsData;

    filtered.forEach(function (doc) {

        const option = document.createElement("option");
        option.value = doc.id;
        option.textContent = doc.doctor_name +
            (doc.specialization ? ` (${doc.specialization})` : "");
        consultant.appendChild(option);

    });

}


/* =========================================================
   EVENTS
========================================================= */

function attachEvents() {

    searchPatientBtn.addEventListener("click", searchPatient);

    patientSearch.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            searchPatient();
        }
    });

    newPatientBtn.addEventListener("click", prepareNewPatient);

    department.addEventListener("change", function () {
        populateConsultantOptions(department.value);
    });

    area.addEventListener("change", handleAddressChange);

    appointmentForm.addEventListener("submit", bookAppointment);

    clearFormBtn.addEventListener("click", function () {
        prepareNewPatient();
    });

    closeSuccessBtn.addEventListener("click", closeModal);

    printAppointmentBtn.addEventListener("click", printAppointment);

}


/* =========================================================
   SET MINIMUM DATE (today, can't book in the past)
========================================================= */

function setMinimumDate() {

    const today = new Date().toISOString().split("T")[0];
    appointmentDate.min = today;
    appointmentDate.value = today;

}


/* =========================================================
   SEARCH PATIENT
========================================================= */

function searchPatient() {

    const query = patientSearch.value.trim();

    if (!query) {
        searchMessage.textContent = "Please enter an MRN or mobile number to search.";
        searchMessage.className = "search-message error";
        return;
    }

    searchMessage.textContent = "Searching...";
    searchMessage.className = "search-message";

    fetch(`${API_BASE}/search_patient.php?query=${encodeURIComponent(query)}`, {
        credentials: "same-origin"
    })

    .then(response => response.json())

    .then(data => {

        if (data.error) {
            searchMessage.textContent = "Search failed. Please try again.";
            searchMessage.className = "search-message error";
            return;
        }

        const patients = data.patients || [];

        if (patients.length === 0) {

            searchMessage.textContent =
                "No matching patient found. You can register them as a new patient below.";
            searchMessage.className = "search-message info";

            hideExistingPatient();
            prepareNewPatient(query);

        } else {

            showExistingPatient(patients[0]);

            searchMessage.textContent =
                patients.length > 1
                    ? `${patients.length} matches found — showing the closest match.`
                    : "Patient found.";
            searchMessage.className = "search-message success";

        }

    })

    .catch(function (error) {

        console.error(error);
        searchMessage.textContent = "Unable to connect to the server.";
        searchMessage.className = "search-message error";

    });

}


/* =========================================================
   SHOW / HIDE EXISTING PATIENT PANEL
========================================================= */

function showExistingPatient(patient) {

    selectedExistingPatient = patient;

    foundPatientName.textContent = patient.full_name;
    foundPatientMRN.textContent = patient.mrn;
    foundPatientPhone.textContent = patient.mobile || "—";

    if (patient.date_of_birth) {
        foundPatientAge.textContent = calculateAge(patient.date_of_birth);
    } else {
        foundPatientAge.textContent = "—";
    }

    foundPatientAddress.textContent = patient.address || patient.area || "—";

    existingPatient.classList.remove("hidden");

    loadPatientIntoForm(patient);

    // Existing patient already has a portal password — hide/disable the
    // "set a password" field entirely for this flow.
    patientPasswordGroup.style.display = "none";
    patientPassword.required = false;
    patientPassword.value = "";

}

function hideExistingPatient() {

    selectedExistingPatient = null;
    existingPatient.classList.add("hidden");

}


/* =========================================================
   LOAD EXISTING PATIENT INTO FORM (read-only-ish, for booking)
========================================================= */

function loadPatientIntoForm(patient) {

    mrn.value = patient.mrn;
    patientName.value = patient.full_name;

    if (patient.date_of_birth) {
        patientAge.value = calculateAge(patient.date_of_birth);
        dateOfBirth.value = patient.date_of_birth;
    }

    phone.value = patient.mobile || "";
    patientEmail.value = patient.email || "";
    gender.value = patient.gender || "";

    const areaExists =
        [...area.options].some(option => option.value === patient.area);

    if (areaExists) {

        area.value = patient.area;
        otherAddressGroup.classList.add("hidden");
        otherAddress.required = false;

    } else {

        area.value = "Other";
        otherAddressGroup.classList.remove("hidden");
        otherAddress.required = true;
        otherAddress.value = patient.address || "";

    }

}


/* =========================================================
   NEW PATIENT
========================================================= */

function prepareNewPatient(prefillMobileOrMrn) {

    hideExistingPatient();

    appointmentForm.reset();
    mrn.value = "";

    otherAddressGroup.classList.add("hidden");
    otherAddress.required = false;

    // New patient — needs a portal password.
    patientPasswordGroup.style.display = "";
    patientPassword.required = true;

    setMinimumDate();

    if (prefillMobileOrMrn && /^\d{10}$/.test(prefillMobileOrMrn)) {
        phone.value = prefillMobileOrMrn;
    }

    patientName.focus();

}


/* =========================================================
   ADDRESS / AREA CHANGE
========================================================= */

function handleAddressChange() {

    if (area.value === "Other") {

        otherAddressGroup.classList.remove("hidden");
        otherAddress.required = true;

    } else {

        otherAddressGroup.classList.add("hidden");
        otherAddress.required = false;
        otherAddress.value = "";

    }

}


/* =========================================================
   DOCTOR AVAILABILITY CHECK
   Disables already-booked time slots and blocks inactive doctors.
========================================================= */

function checkDoctorAvailability() {

    const doctorId = consultant.value;
    const date = appointmentDate.value;

    [...appointmentTime.options].forEach(option => {
        option.disabled = false;
        option.textContent = option.textContent.replace(" (Booked)", "");
    });

    if (!doctorId || !date) {
        return;
    }

    fetch(`${API_BASE}/get_doctor_availability.php?doctor_id=${doctorId}&date=${date}`, {
        credentials: "same-origin"
    })

    .then(response => response.json())

    .then(data => {

        if (data.error) {
            console.error("Availability check failed:", data.error);
            return;
        }

        if (data.doctor_status !== "active") {

            alert("This doctor is currently unavailable. Please choose a different consultant.");
            consultant.value = "";
            return;

        }

        const bookedSlots = data.booked_slots || [];

        [...appointmentTime.options].forEach(option => {

            if (!option.value) return;

            const time24 = convertTo24Hour(option.value).substring(0, 5);

            if (bookedSlots.includes(time24)) {
                option.disabled = true;
                option.textContent = option.textContent + " (Booked)";
            }

        });

        const selectedOption = appointmentTime.options[appointmentTime.selectedIndex];
        if (selectedOption && selectedOption.disabled) {
            appointmentTime.value = "";
        }

    })

    .catch(function (error) {
        console.error("Availability check error:", error);
    });

}


/* =========================================================
   HELPERS
========================================================= */

function calculateAge(dobString) {

    const dob = new Date(dobString);
    const today = new Date();

    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    return age;

}

function getFinalAddress() {

    if (area.value === "Other") {
        return otherAddress.value.trim();
    }

    return area.value;

}

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


/* =========================================================
   BOOK APPOINTMENT (register if new, then book)
========================================================= */

function bookAppointment(event) {

    event.preventDefault();

    const primaryPhone = phone.value.trim();

    if (!/^[6-9]\d{9}$/.test(primaryPhone)) {
        alert("Please enter a valid 10-digit mobile number.");
        phone.focus();
        return;
    }

    const alternate = alternatePhone.value.trim();
    if (alternate && !/^[6-9]\d{9}$/.test(alternate)) {
        alert("Please enter a valid alternate mobile number.");
        alternatePhone.focus();
        return;
    }

    if (!department.value) {
        alert("Please select a department.");
        department.focus();
        return;
    }

    if (!consultant.value) {
        alert("Please select a consultant.");
        consultant.focus();
        return;
    }

    // Password is only required when registering a brand-new patient —
    // existing patients already have portal access.
    if (!selectedExistingPatient) {

        const passwordValue = patientPassword.value;

        if (!passwordValue || passwordValue.length < 6) {
            alert("Please set a password (at least 6 characters) for the patient's portal access.");
            patientPassword.focus();
            return;
        }

    }

    const submitBtn = appointmentForm.querySelector("button[type='submit']");
    submitBtn.disabled = true;
    submitBtn.textContent = "Booking...";

    if (selectedExistingPatient) {

        createAppointment(selectedExistingPatient.id, selectedExistingPatient.mrn)
            .finally(() => resetSubmitButton(submitBtn));

    } else {

        const formData = new URLSearchParams();
        formData.append("full_name", patientName.value.trim());
        formData.append("mobile", primaryPhone);
        formData.append("date_of_birth", dateOfBirth.value);
        formData.append("gender", gender.value);
        formData.append("password", patientPassword.value);
        formData.append("email", patientEmail.value.trim());
        formData.append("area", area.value);
        formData.append("address", getFinalAddress());

        fetch(`${API_BASE}/register_patient.php`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            body: formData.toString()
        })

        .then(response => response.json())

        .then(data => {

            if (data.result !== "success") {
                alert("Could not register patient: " + (data.error || "unknown error"));
                return Promise.reject(data.error);
            }

            mrn.value = data.mrn;

            return createAppointment(data.patient_id, data.mrn);

        })

        .catch(function (error) {
            console.error(error);
        })

        .finally(() => resetSubmitButton(submitBtn));

    }

}

function resetSubmitButton(submitBtn) {

    submitBtn.disabled = false;
    submitBtn.innerHTML = `
        <i class="fa-solid fa-calendar-check"></i>
        Book Appointment
    `;

}

function createAppointment(patientId, patientMrn) {

    const formData = new URLSearchParams();
    formData.append("patient_id", patientId);
    formData.append("department_id", department.value);
    formData.append("doctor_id", consultant.value);
    formData.append("appointment_date", appointmentDate.value);
    formData.append("appointment_time", convertTo24Hour(appointmentTime.value));
    formData.append("reason", visitReason.value.trim());
    formData.append("status", "Booked");

    return fetch(`${API_BASE}/book_appointment.php`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        credentials: "same-origin",
        body: formData.toString()
    })

    .then(response => response.json())

    .then(data => {

        if (data.result === "success") {

            finalizeAppointment(patientMrn, data.appointment_id);

        } else {

            alert("Could not book appointment: " + (data.error || "unknown error"));

        }

    })

    .catch(function (error) {

        console.error(error);
        alert("Unable to connect to the server.");

    });

}


/* =========================================================
   SUCCESS MODAL
========================================================= */

function finalizeAppointment(patientMrn, appointmentId) {

    successMRN.textContent = patientMrn;
    successAppointmentNo.textContent = appointmentId;

    successModal.classList.remove("hidden");

}

function closeModal() {

    successModal.classList.add("hidden");
    hideExistingPatient();
    prepareNewPatient();
    searchMessage.textContent = "";
    patientSearch.value = "";

}


/* =========================================================
   PRINT
========================================================= */

function printAppointment() {

    document.getElementById("printAppointmentNo").textContent =
        successAppointmentNo.textContent;

    document.getElementById("printAppointmentDate").textContent =
        appointmentDate.value;

    document.getElementById("printAppointmentTime").textContent =
        appointmentTime.value;

    document.getElementById("printAppointmentType").textContent =
        appointmentType.value;

    document.getElementById("printMRN").textContent =
        successMRN.textContent;

    document.getElementById("printPatientName").textContent =
        patientName.value;

    document.getElementById("printPatientAge").textContent =
        patientAge.value;

    document.getElementById("printPhone").textContent =
        phone.value;

    document.getElementById("printAlternatePhone").textContent =
        alternatePhone.value || "—";

    document.getElementById("printConsultant").textContent =
        consultant.options[consultant.selectedIndex]
            ? consultant.options[consultant.selectedIndex].textContent
            : "—";

    document.getElementById("printAddress").textContent =
        getFinalAddress();

    document.getElementById("printReason").textContent =
        visitReason.value || "—";

    window.print();

}