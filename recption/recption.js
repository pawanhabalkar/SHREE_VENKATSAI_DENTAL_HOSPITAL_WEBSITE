/* =========================================================
   RECEPTION APPOINTMENT MODULE
   SHREE VENKATSAI MULTI SPECIALITY DENTAL HOSPITAL
========================================================= */


/* =========================================================
   STORAGE
========================================================= */

const PATIENTS_KEY = "svmsdh_patients";
const APPOINTMENTS_KEY = "svmsdh_appointments";

const MRN_COUNTER_KEY = "svmsdh_mrn_counter";
const APPOINTMENT_COUNTER_KEY = "svmsdh_appointment_counter";


/* =========================================================
   STATE
========================================================= */

let selectedExistingPatient = null;
let lastAppointment = null;


/* =========================================================
   ELEMENTS
========================================================= */

const patientSearch =
    document.getElementById("patientSearch");

const searchPatientBtn =
    document.getElementById("searchPatientBtn");

const newPatientBtn =
    document.getElementById("newPatientBtn");

const searchMessage =
    document.getElementById("searchMessage");

const existingPatient =
    document.getElementById("existingPatient");

const foundPatientName =
    document.getElementById("foundPatientName");

const foundPatientMRN =
    document.getElementById("foundPatientMRN");

const foundPatientPhone =
    document.getElementById("foundPatientPhone");

const foundPatientAge =
    document.getElementById("foundPatientAge");

const foundPatientAddress =
    document.getElementById("foundPatientAddress");

const appointmentForm =
    document.getElementById("appointmentForm");

const mrn =
    document.getElementById("mrn");

const patientName =
    document.getElementById("patientName");

const patientAge =
    document.getElementById("patientAge");

const appointmentDate =
    document.getElementById("appointmentDate");


const otherAddressGroup =
    document.getElementById("otherAddressGroup");

const otherAddress =
    document.getElementById("otherAddress");

const consultant =
    document.getElementById("consultant");

const phone =
    document.getElementById("phone");

const alternatePhone =
    document.getElementById("alternatePhone");

const appointmentTime =
    document.getElementById("appointmentTime");

const appointmentType =
    document.getElementById("appointmentType");

const visitReason =
    document.getElementById("visitReason");

const clearFormBtn =
    document.getElementById("clearFormBtn");

const successModal =
    document.getElementById("successModal");

const closeSuccessBtn =
    document.getElementById("closeSuccessBtn");

const printAppointmentBtn =
    document.getElementById("printAppointmentBtn");


/* =========================================================
   INITIALIZE
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        initializeStorage();

        setMinimumDate();

        attachEvents();

    }
);


/* =========================================================
   STORAGE INITIALIZATION
========================================================= */

function initializeStorage() {

    if (!localStorage.getItem(PATIENTS_KEY)) {

        localStorage.setItem(
            PATIENTS_KEY,
            JSON.stringify([])
        );

    }


    if (!localStorage.getItem(APPOINTMENTS_KEY)) {

        localStorage.setItem(
            APPOINTMENTS_KEY,
            JSON.stringify([])
        );

    }


    if (!localStorage.getItem(MRN_COUNTER_KEY)) {

        localStorage.setItem(
            MRN_COUNTER_KEY,
            "0"
        );

    }


    if (!localStorage.getItem(APPOINTMENT_COUNTER_KEY)) {

        localStorage.setItem(
            APPOINTMENT_COUNTER_KEY,
            "0"
        );

    }

}


/* =========================================================
   EVENTS
========================================================= */

function attachEvents() {

    searchPatientBtn.addEventListener(
        "click",
        searchPatient
    );

    patientSearch.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Enter") {

                event.preventDefault();

                searchPatient();

            }

        }
    );

    newPatientBtn.addEventListener(
        "click",
        prepareNewPatient
    );

    // Area dropdown
    area.addEventListener(
        "change",
        handleAddressChange
    );

    appointmentForm.addEventListener(
        "submit",
        bookAppointment
    );

    clearFormBtn.addEventListener(
        "click",
        clearForm
    );

    closeSuccessBtn.addEventListener(
        "click",
        closeModal
    );

    printAppointmentBtn.addEventListener(
        "click",
        printAppointment
    );

}


/* =========================================================
   DATE
========================================================= */

function setMinimumDate() {

    const today =
        new Date()
            .toISOString()
            .split("T")[0];


    appointmentDate.min = today;

    appointmentDate.value = today;

}


/* =========================================================
   SEARCH PATIENT
========================================================= */

function searchPatient() {

    const value =
        patientSearch.value.trim();


    if (!value) {

        showMessage(
            "Please enter MRN or mobile number.",
            "error"
        );

        return;

    }


    const patients =
        getPatients();


    const patient =
        patients.find(
            item =>

                item.mrn.toLowerCase() ===
                value.toLowerCase()

                ||

                item.phone === value
        );


    if (!patient) {

        selectedExistingPatient = null;

        hideExistingPatient();

        showMessage(
            "Patient not found. Please register as a new patient.",
            "error"
        );

        clearPatientFields();

        generateMRN();

        patientName.focus();

        return;

    }


    /* Existing patient */

    selectedExistingPatient = patient;


    showExistingPatient(patient);

    loadPatientIntoForm(patient);


    showMessage(
        "Existing patient found. The existing MRN will be used.",
        "success"
    );

}


/* =========================================================
   SHOW EXISTING PATIENT
========================================================= */

function showExistingPatient(patient) {

    existingPatient.classList.remove("hidden");


    foundPatientName.textContent =
        patient.name;

    foundPatientMRN.textContent =
        patient.mrn;

    foundPatientPhone.textContent =
        patient.phone;

    foundPatientAge.textContent =
        patient.age;

    foundPatientAddress.textContent =
        patient.address;

}


/* =========================================================
   HIDE EXISTING PATIENT
========================================================= */

function hideExistingPatient() {

    existingPatient.classList.add("hidden");

}


/* =========================================================
   LOAD EXISTING PATIENT
========================================================= */

function loadPatientIntoForm(patient) {

    mrn.value =
        patient.mrn;

    patientName.value =
        patient.name;

    patientAge.value =
        patient.age;

    phone.value =
        patient.phone;

    alternatePhone.value =
        patient.alternatePhone || "";

    dateOfBirth.value =
        patient.dateOfBirth || "";

    gender.value =
        patient.gender || "";

    patientEmail.value =
        patient.email || "";

    area.value =
        patient.area || "";


    /*
        Address is loaded only if it exists
        in the predefined dropdown.
    */

    const addressExists =
    [...patientAddress.options]
        .some(
            option =>
                option.value === patient.address
        );

if (addressExists) {

    patientAddress.value =
        patient.address;

    otherAddressGroup.classList.add(
        "hidden"
    );

} else {

    patientAddress.value = "Other";

    otherAddressGroup.classList.remove(
        "hidden"
    );

    otherAddress.value =
        patient.address;

} 

area.value = patient.area || "";

if (patient.area === "Other") {

    otherAddressGroup.classList.remove("hidden");

    otherAddress.required = true;

    otherAddress.value = patient.address || "";

} else {

    otherAddressGroup.classList.add("hidden");

    otherAddress.required = false;

    otherAddress.value = "";

}

}


/* =========================================================
   NEW PATIENT
========================================================= */

function prepareNewPatient() {

    selectedExistingPatient = null;

    patientSearch.value = "";

    hideExistingPatient();

    clearPatientFields();

    generateMRN();

    showMessage(
        "New patient selected. A permanent MRN has been generated.",
        "success"
    );


    patientName.focus();

}


/* =========================================================
   GENERATE MRN
========================================================= */

function generateMRN() {

    let counter =
        parseInt(
            localStorage.getItem(
                MRN_COUNTER_KEY
            ) || "0",
            10
        );


    counter++;


    localStorage.setItem(
        MRN_COUNTER_KEY,
        counter.toString()
    );


    const generatedMRN =
        "SVMSDH-" +
        String(counter).padStart(6, "0");


    mrn.value =
        generatedMRN;


    return generatedMRN;

}


/* =========================================================
   GENERATE APPOINTMENT NUMBER
========================================================= */

function generateAppointmentNumber() {

    let counter =
        parseInt(
            localStorage.getItem(
                APPOINTMENT_COUNTER_KEY
            ) || "0",
            10
        );


    counter++;


    localStorage.setItem(
        APPOINTMENT_COUNTER_KEY,
        counter.toString()
    );


    const year =
        new Date().getFullYear();


    return (
        "APT-" +
        year +
        "-" +
        String(counter).padStart(5, "0")
    );

}


/* =========================================================
   GET PATIENTS
========================================================= */

function getPatients() {

    return JSON.parse(
        localStorage.getItem(PATIENTS_KEY)
        || "[]"
    );

}


/* =========================================================
   SAVE PATIENTS
========================================================= */

function savePatients(patients) {

    localStorage.setItem(
        PATIENTS_KEY,
        JSON.stringify(patients)
    );

}


/* =========================================================
   GET APPOINTMENTS
========================================================= */

function getAppointments() {

    return JSON.parse(
        localStorage.getItem(APPOINTMENTS_KEY)
        || "[]"
    );

}


/* =========================================================
   SAVE APPOINTMENTS
========================================================= */

function saveAppointments(appointments) {

    localStorage.setItem(
        APPOINTMENTS_KEY,
        JSON.stringify(appointments)
    );

}


/* =========================================================
   ADDRESS
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
/* ========== PRINT ========= */

const patientEmail =
    document.getElementById("patientEmail");

const dateOfBirth =
    document.getElementById("dateOfBirth");

const gender =
    document.getElementById("gender");

const area =
    document.getElementById("area");


/* =========================================================
   BOOK APPOINTMENT
========================================================= */


function bookAppointment(event) {

    event.preventDefault();

    const primaryPhone =
        phone.value.trim();

    /* PHONE VALIDATION */

    if (!/^[6-9]\d{9}$/.test(primaryPhone)) {

        alert(
            "Please enter a valid 10-digit mobile number."
        );

        phone.focus();

        return;
    }


    /* ALTERNATE PHONE */

    const alternate =
        alternatePhone.value.trim();

    if (
        alternate &&
        !/^[6-9]\d{9}$/.test(alternate)
    ) {

        alert(
            "Please enter a valid alternate mobile number."
        );

        alternatePhone.focus();

        return;
    }


    let patient;


    /* =====================================================
       EXISTING PATIENT
    ====================================================== */

    if (selectedExistingPatient) {

        patient = selectedExistingPatient;

    }


    /* =====================================================
       NEW PATIENT
    ====================================================== */

    else {

        const patients = getPatients();


        /* CHECK DUPLICATE MOBILE */

        const duplicate =
            patients.find(
                item =>
                    item.phone === primaryPhone
            );


        if (duplicate) {

            alert(
                "This mobile number is already registered. Please search the existing patient."
            );

            patientSearch.value =
                primaryPhone;

            searchPatient();

            return;
        }


        if (!mrn.value) {

            generateMRN();

        }


        patient = {

            mrn:
                mrn.value,

            name:
                patientName.value.trim(),

            age:
                patientAge.value,

            dateOfBirth:
                dateOfBirth.value,

            gender:
                gender.value,

            email:
                patientEmail.value.trim(),

            phone:
                primaryPhone,

            alternatePhone:
                alternate,

            area:
                area.value,

            address:
                getFinalAddress(),

            registeredDate:
                new Date().toISOString()

        };


        /*
         * IMPORTANT:
         * Patient will be saved only after
         * print decision.
         */
    }


    /* =====================================================
       CREATE APPOINTMENT OBJECT
    ====================================================== */

    const appointmentNumber =
        generateAppointmentNumber();


    const appointment = {

        appointmentNumber:
            appointmentNumber,

        mrn:
            patient.mrn,

        patientName:
            patient.name,

        age:
            patient.age,

        dateOfBirth:
            patient.dateOfBirth || dateOfBirth.value,

        gender:
            patient.gender || gender.value,

        email:
            patient.email || patientEmail.value.trim(),

        phone:
            patient.phone,

        alternatePhone:
            patient.alternatePhone || "",

        area:
            patient.area || area.value,

        address:
            patient.address,

        date:
            appointmentDate.value,

        time:
            appointmentTime.value,

        consultant:
            consultant.value,

        appointmentType:
            appointmentType.value,

        reason:
            visitReason.value.trim(),

        status:
            "Booked",

        createdAt:
            new Date().toISOString()

    };


    /*
     * Store temporarily.
     * DO NOT save yet.
     */

    lastAppointment = {
        appointment: appointment,
        patient: patient,
        isNewPatient: !selectedExistingPatient
    };


    /* =====================================================
       PREPARE PRINT DATA
    ====================================================== */

    preparePrintData(
        appointment,
        patient
    );


    /* =====================================================
       ASK PRINT OR SUBMIT
    ====================================================== */

    showPrintConfirmation();

}

/* =========================================================
   PRINT CONFIRMATION
========================================================= */

function showPrintConfirmation() {

    const shouldPrint =
        confirm(
            "Appointment details are ready.\n\n" +
            "Do you want to PRINT the appointment form?\n\n" +
            "YES = Print and save appointment\n" +
            "NO = Submit directly without printing"
        );


    if (shouldPrint) {

        /*
         * Print first.
         * Data will be saved after the print dialog closes.
         */

        printAppointment();

    } else {

        /*
         * Submit directly.
         */

        finalizeAppointment(false);

    }

}

/* =========================================================
   FINALIZE APPOINTMENT
========================================================= */

function finalizeAppointment(printed = false) {

    if (!lastAppointment) {

        return;

    }


    const data =
        lastAppointment;


    const appointment =
        data.appointment;

    const patient =
        data.patient;


    /* =====================================================
       SAVE NEW PATIENT
    ====================================================== */

    if (data.isNewPatient) {

        const patients =
            getPatients();


        /*
         * Double-check that patient does not
         * already exist.
         */

        const alreadyExists =
            patients.some(
                item =>
                    item.mrn === patient.mrn
            );


        if (!alreadyExists) {

            patients.push(patient);

            savePatients(patients);

        }

    }


    /* =====================================================
       SAVE APPOINTMENT
    ====================================================== */

    const appointments =
        getAppointments();


    appointments.push(appointment);

    saveAppointments(appointments);


    /* =====================================================
       SHOW SUCCESS
    ====================================================== */

    document.getElementById(
        "successMRN"
    ).textContent =
        patient.mrn;


    document.getElementById(
        "successAppointmentNo"
    ).textContent =
        appointment.appointmentNumber;


    successModal.classList.remove(
        "hidden"
    );


    /*
     * Clear temporary appointment data
     * after successful save.
     */

    lastAppointment = null;

}


/* =========================================================
   FINAL ADDRESS
========================================================= */

function getFinalAddress() {

    if (area.value === "Other") {

        return otherAddress.value.trim();

    }

    return area.value;

}


/* =========================================================
   PREPARE PRINT DATA
========================================================= */

function preparePrintData(
    appointment,
    patient
) {

    document.getElementById(
        "printMRN"
    ).textContent =
        patient.mrn;


    document.getElementById(
        "printAppointmentNo"
    ).textContent =
        appointment.appointmentNumber;


    document.getElementById(
        "printAppointmentDate"
    ).textContent =
        formatDate(
            appointment.date
        );


    document.getElementById(
        "printAppointmentTime"
    ).textContent =
        appointment.time;


    document.getElementById(
        "printAppointmentType"
    ).textContent =
        appointment.appointmentType;


    document.getElementById(
        "printPatientName"
    ).textContent =
        patient.name;


    document.getElementById(
        "printPatientAge"
    ).textContent =
        patient.age;


    document.getElementById(
        "printPhone"
    ).textContent =
        patient.phone;


    document.getElementById(
        "printAlternatePhone"
    ).textContent =
        patient.alternatePhone ||
        "Not provided";


    document.getElementById(
        "printConsultant"
    ).textContent =
        appointment.consultant;


    document.getElementById(
        "printAddress"
    ).textContent =
        patient.address;


    document.getElementById(
        "printReason"
    ).textContent =
        appointment.reason ||
        "Not provided";

}


/* =========================================================
   PRINT
========================================================= */

/* =========================================================
   PRINT APPOINTMENT
========================================================= */

function printAppointment() {

    if (!lastAppointment) {

        return;

    }


    /*
     * Open browser print dialog.
     *
     * window.print() pauses JavaScript execution
     * until the print dialog is closed.
     */

    window.print();


    /*
     * After the user finishes/cancels the print dialog,
     * save the appointment.
     */

    finalizeAppointment(true);

}

/* =========================================================
   CLOSE MODAL
========================================================= */

function closeModal() {

    successModal.classList.add(
        "hidden"
    );

}


/* =========================================================
   CLEAR FORM
========================================================= */

function clearForm() {

    appointmentForm.reset();

    selectedExistingPatient = null;

    patientSearch.value = "";

    hideExistingPatient();

    searchMessage.textContent = "";

    searchMessage.className =
        "search-message";

    otherAddressGroup.classList.add(
        "hidden"
    );

    otherAddress.required = false;

    mrn.value = "";

    setMinimumDate();

}


/* =========================================================
   CLEAR PATIENT FIELDS
========================================================= */

function clearPatientFields() {

    patientName.value = "";

    patientAge.value = "";

    phone.value = "";

    alternatePhone.value = "";

    patientEmail.value = "";

    dateOfBirth.value = "";

    gender.value = "";

    area.value = "";

    otherAddress.value = "";

    otherAddressGroup.classList.add("hidden");

    otherAddress.required = false;

}


/* =========================================================
   MESSAGE
========================================================= */

function showMessage(
    message,
    type
) {

    searchMessage.textContent =
        message;

    searchMessage.className =
        "search-message " +
        type;

}


/* =========================================================
   DATE FORMAT
========================================================= */

function formatDate(dateString) {

    const date =
        new Date(
            dateString + "T00:00:00"
        );


    return date.toLocaleDateString(
        "en-IN",
        {
            day: "2-digit",
            month: "long",
            year: "numeric"
        }
    );

}

