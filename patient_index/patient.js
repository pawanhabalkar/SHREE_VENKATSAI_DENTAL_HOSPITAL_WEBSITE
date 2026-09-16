/* =========================================================
   ODSMART PATIENT PORTAL JAVASCRIPT
   ========================================================= */

// CSRF token, rendered into a <meta> tag by patient_index.php.
// Every fetch() below that changes data (book/cancel/update/mark-read)
// attaches this so the backend can verify the request actually came
// from a page we rendered, not a cross-site form.
function getCsrfToken() {
    const tag = document.querySelector('meta[name="csrf-token"]');
    return tag ? tag.content : "";
}

// Escapes text before it's dropped into innerHTML, so values that
// came from the database (appointment reason, notes, etc. — anything
// a patient or receptionist typed into a form at some point) can't
// break out of the HTML and run as script.
function escapeHtml(value) {
    if (value === null || value === undefined) return "";
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

document.addEventListener("DOMContentLoaded", function () {


    /* =====================================================
       ELEMENTS
    ===================================================== */

    const navLinks =
        document.querySelectorAll(".nav-link");

    const sections =
        document.querySelectorAll(".page-section");

    const pageTitle =
        document.getElementById("pageTitle");

    const mobileMenu =
        document.getElementById("mobileMenu");

    const sidebar =
        document.getElementById("sidebar");

    const sidebarOverlay =
        document.getElementById("sidebarOverlay");

    const logoutBtn =
        document.getElementById("logoutBtn");

    const logoutModal =
        document.getElementById("logoutModal");

    const cancelLogout =
        document.getElementById("cancelLogout");

    const confirmLogout =
        document.getElementById("confirmLogout");




    /* =====================================================
       SECTION TITLES
    ===================================================== */

    const titles = {

        dashboard:
            "Dashboard",

        appointments:
            "My Appointments",

        "medical-records":
            "Medical Records",

        treatments:
            "My Treatments",

        prescriptions:
            "Prescriptions",

        billing:
            "Billing & Payments",

        notifications:
            "Notifications",

        referrals:
            "My Referrals",

        profile:
            "My Profile",

        settings:
            "Settings"

    };


    /* =====================================================
       SHOW SECTION
    ===================================================== */

    function showSection(sectionId) {

        sections.forEach(function (section) {

            section.classList.remove("active");

        });


        const target =
            document.getElementById(sectionId);

        if (target) {

            target.classList.add("active");

        }


        navLinks.forEach(function (link) {

            link.classList.remove("active");

            if (
                link.dataset.section ===
                sectionId
            ) {

                link.classList.add("active");

            }

        });


        pageTitle.textContent =
            titles[sectionId] ||
            "Dashboard";


        window.scrollTo({

            top: 0,

            behavior: "smooth"

        });


        sidebar.classList.remove(
            "mobile-open"
        );

        sidebarOverlay.classList.remove(
            "active"
        );

    }


    /* =====================================================
       NAVIGATION
    ===================================================== */

    navLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                const sectionId =
                    this.dataset.section;

                showSection(sectionId);

                history.replaceState(
                    null,
                    "",
                    "#" + sectionId
                );

            }
        );

    });


    /* =====================================================
       HASH ON PAGE LOAD
    ===================================================== */

    const initialHash =
        window.location.hash.replace("#", "");

    if (
        initialHash &&
        titles[initialHash]
    ) {

        showSection(initialHash);

    } else {

        showSection("dashboard");

    }


    /* =====================================================
       QUICK ACTIONS
    ===================================================== */

    document
        .querySelectorAll("[data-open]")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const section =
                        this.dataset.open;

                    showSection(section);

                    history.replaceState(
                        null,
                        "",
                        "#" + section
                    );

                }
            );

        });


    /* =====================================================
       MOBILE MENU
    ===================================================== */

    mobileMenu.addEventListener(
        "click",
        function () {

            sidebar.classList.toggle(
                "mobile-open"
            );

            sidebarOverlay.classList.toggle(
                "active"
            );

        }
    );


    sidebarOverlay.addEventListener(
        "click",
        function () {

            sidebar.classList.remove(
                "mobile-open"
            );

            sidebarOverlay.classList.remove(
                "active"
            );

        }
    );


    /* =====================================================
       LOGOUT MODAL
    ===================================================== */

    logoutBtn.addEventListener(
        "click",
        function () {

            logoutModal.classList.add(
                "active"
            );

        }
    );


    cancelLogout.addEventListener(
        "click",
        function () {

            logoutModal.classList.remove(
                "active"
            );

        }
    );


    confirmLogout.addEventListener(
        "click",
        function () {

            window.location.href =
                "../backend/auth/logout.php";

        }
    );


    /* =====================================================
       CLOSE MODAL OUTSIDE
    ===================================================== */

    logoutModal.addEventListener(
        "click",
        function (event) {

            if (
                event.target ===
                logoutModal
            ) {

                logoutModal.classList.remove(
                    "active"
                );

            }

        }
    );


    /* =====================================================
       ESCAPE
    ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape"
            ) {

                logoutModal.classList.remove(
                    "active"
                );

            }

        }
    );


    /* =====================================================
       TAB BUTTONS (Appointments: Upcoming/Completed/Cancelled)
    ===================================================== */

    const tabButtons =
        document.querySelectorAll(".tab-btn");

    tabButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                tabButtons.forEach(
                    function (btn) {
                        btn.classList.remove("active");
                    }
                );

                this.classList.add("active");

                renderAppointments(this.dataset.filter);

            }
        );

    });


    /* =====================================================
       MARK NOTIFICATIONS READ (bulk button in header)
    ===================================================== */

    const markRead =
        document.querySelector(
            ".section-top .outline-btn"
        );

    if (markRead) {

        markRead.addEventListener(
            "click",
            function () {

                document
                    .querySelectorAll(
                        ".notification-card.unread"
                    )
                    .forEach(function (item) {

                        item.classList.remove(
                            "unread"
                        );

                    });

            }
        );

    }


    /* =====================================================
       INITIAL DATA LOAD
       All calls live here, inside DOMContentLoaded, so the
       DOM is guaranteed to exist before they run.
    ===================================================== */

    loadDashboardSummary();
    loadAppointments();
    loadMedicalRecords();
    loadTreatments();
    loadPrescriptions();
    loadInvoices();
    loadNotifications();
    loadReferrals();


});


// ==========================================
// PROFILE EDIT FUNCTIONALITY
// ==========================================

let profileEditing = false;


// EDIT PROFILE
function toggleProfileEdit() {

    const editButton = document.getElementById("editProfileBtn");
    const profileActions = document.getElementById("profileActions");

    const inputs = document.querySelectorAll(
        "#profile input, #profile textarea"
    );

    const gender = document.getElementById("gender");


    profileEditing = true;


    inputs.forEach(function(input) {

        input.removeAttribute("readonly");

    });


    if (gender) {
        gender.removeAttribute("disabled");
    }


    profileActions.style.display = "flex";


    editButton.innerHTML =
        '<i class="fa-solid fa-pen"></i> Editing...';

    editButton.disabled = true;

}


// ==========================================
// CANCEL EDIT
// ==========================================

function cancelProfileEdit() {

    location.reload();

}


/* =====================================================
   DASHBOARD SUMMARY
===================================================== */

function loadDashboardSummary() {

    fetch("../backend/patient/get_dashboard_summary.php")
        .then(function (response) { return response.json(); })
        .then(function (data) {

            const upcomingEl = document.getElementById("statUpcomingCount");
            const dueEl = document.getElementById("statDueAmount");
            const recordsEl = document.getElementById("statRecordsCount");
            const nextDateEl = document.getElementById("statNextDate");

            if (upcomingEl) {
                upcomingEl.textContent = data.total_appointments ?? "0";
            }

            if (dueEl) {
                dueEl.textContent = "₹" + Number(data.total_due || 0).toLocaleString("en-IN");
            }

            if (recordsEl) {
                recordsEl.textContent = data.total_medical_records ?? "0";
            }

            if (nextDateEl) {
                if (data.next_appointment) {
                    nextDateEl.textContent =
                        "Next: " + data.next_appointment.appointment_date +
                        " " + data.next_appointment.appointment_time;
                } else {
                    nextDateEl.textContent = "No upcoming appointment";
                }
            }

        })
        .catch(function (error) {
            console.error("Failed to load dashboard summary:", error);
        });

}


/* =====================================================
   APPOINTMENTS
===================================================== */

let allAppointments = [];
let currentAppointmentFilter = "upcoming";

function loadAppointments() {

    const container = document.getElementById("appointmentListContainer");
    if (!container) return;

    fetch("../backend/patient/get_appointments.php")
        .then(function (response) { return response.json(); })
        .then(function (appointments) {

            allAppointments = appointments;

            /* Wire the real "upcoming" count into the sidebar badge */
            const upcomingCount = appointments.filter(function (a) {
                const s = (a.status || "").toLowerCase();
                return s === "booked" || s === "checked-in" || s === "waiting" || s === "with doctor";
            }).length;

            const badge = document.getElementById("navAppointmentsBadge");
            if (badge) badge.textContent = upcomingCount;

            renderAppointments(currentAppointmentFilter);

        })
        .catch(function (error) {
            console.error("Failed to load appointments:", error);
        });

}


function renderAppointments(filter) {

    const container = document.getElementById("appointmentListContainer");
    if (!container) return;

    currentAppointmentFilter = filter;

    const filtered = allAppointments.filter(function (appt) {

        const status = (appt.status || "").toLowerCase();

        if (filter === "upcoming") {
            return status === "booked" ||
                   status === "checked-in" ||
                   status === "waiting" ||
                   status === "with doctor";
        }

        if (filter === "completed") {
            return status === "completed";
        }

        if (filter === "cancelled") {
            return status === "cancelled";
        }

        return true;

    });

    container.innerHTML = "";

    if (filtered.length === 0) {
        container.innerHTML = "<p>No " + filter + " appointments.</p>";
        return;
    }

    filtered.forEach(function (appt) {

        const card = document.createElement("div");
        card.className = "full-appointment-card";

        const canCancel = filter === "upcoming";

        card.innerHTML = `
            <div class="appointment-date large">
                <strong>${escapeHtml(appt.appointment_date)}</strong>
                <span>${escapeHtml(appt.appointment_time)}</span>
            </div>
            <div>
                <p><strong>${escapeHtml(appt.doctor_name) || "Not assigned"}</strong></p>
                <p>${escapeHtml(appt.department_name)}</p>
                <p>${escapeHtml(appt.reason)}</p>
                <span class="status-badge">${escapeHtml(appt.status)}</span>
            </div>
            <div class="appointment-actions">
                ${canCancel ? `<button class="cancel-btn" data-appointment-id="${escapeHtml(appt.id)}">Cancel</button>` : ""}
            </div>
        `;

        container.appendChild(card);

    });

    container.querySelectorAll(".cancel-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            cancelAppointment(this.dataset.appointmentId);
        });
    });

}


function cancelAppointment(appointmentId) {

    if (!confirm("Cancel this appointment?")) return;

    const formData = new FormData();
    formData.append("appointment_id", appointmentId);
    formData.append("csrf_token", getCsrfToken());

    fetch("../backend/patient/cancel_appointment.php", {
        method: "POST",
        body: formData
    })
        .then(function (response) { return response.json(); })
        .then(function (result) {

            if (result.result === "success") {
                if (result.refund_eligible && result.refunded_amount > 0) {
                    alert("Appointment cancelled. Refund of ₹" + result.refunded_amount + " has been processed.");
                } else {
                    alert("Appointment cancelled.");
                }
                loadAppointments();
                loadDashboardSummary();
            } else {
                alert("Could not cancel appointment: " + (result.error || "unknown error"));
            }

        })
        .catch(function (error) {
            console.error("Cancel appointment failed:", error);
        });

}


function bookAppointment(formData) {

    return fetch("../backend/patient/book_appointment.php", {
        method: "POST",
        body: formData
    })
        .then(function (response) { return response.text(); })
        .then(function (result) {

            if (result.trim().startsWith("success")) {
                alert("Appointment booked successfully.");
                loadAppointments();
                loadDashboardSummary();
            } else {
                alert("Booking failed: " + result);
            }

            return result;

        });

}


/* =====================================================
   MEDICAL RECORDS
===================================================== */

function loadMedicalRecords() {

    const container = document.getElementById("medicalRecordsContainer");
    if (!container) return;

    fetch("../backend/patient/get_medical_records.php")
        .then(function (response) { return response.json(); })
        .then(function (records) {

            container.innerHTML = "";

            if (records.length === 0) {
                container.innerHTML = "<p>No medical records found.</p>";
                return;
            }

            records.forEach(function (rec) {

                const card = document.createElement("div");
                card.className = "record-card";

                /* Doctor name already includes "Dr." from the
                   database, so it's not prefixed again here. */
                card.innerHTML = `
                    <h4>${escapeHtml(rec.title) || escapeHtml(rec.record_type) || "Record"}</h4>
                    <p>${escapeHtml(rec.description)}</p>
                    <small>${escapeHtml(rec.record_date)} — ${escapeHtml(rec.doctor_name) || "N/A"}</small>
                    ${rec.file_path && !String(rec.file_path).trim().toLowerCase().startsWith("javascript:")
                        ? `<a href="${escapeHtml(rec.file_path)}" target="_blank" rel="noopener noreferrer">View File</a>`
                        : ""}
                `;

                container.appendChild(card);

            });

        })
        .catch(function (error) {
            console.error("Failed to load medical records:", error);
        });

}


/* =====================================================
   PRESCRIPTIONS
===================================================== */

function loadPrescriptions() {

    const container = document.getElementById("prescriptionListContainer");
    if (!container) return;

    fetch("../backend/patient/get_prescriptions.php")
        .then(function (response) { return response.json(); })
        .then(function (prescriptions) {

            container.innerHTML = "";

            if (prescriptions.length === 0) {
                container.innerHTML = "<p>No prescriptions found.</p>";
                return;
            }

            prescriptions.forEach(function (rx) {

                const itemsHtml = rx.items.map(function (item) {
                    return `<li>${escapeHtml(item.medicine_name)} — ${escapeHtml(item.dosage)} (${escapeHtml(item.frequency)}, ${escapeHtml(item.duration)})</li>`;
                }).join("");

                const card = document.createElement("div");
                card.className = "prescription-card";

                /* Doctor name already includes "Dr." from the
                   database, so it's not prefixed again here. */
                card.innerHTML = `
                    <div class="prescription-header">
                        <strong>${escapeHtml(rx.prescription_date)}</strong>
                        <span>${escapeHtml(rx.doctor_name) || "N/A"}</span>
                    </div>
                    <ul>${itemsHtml}</ul>
                    ${rx.notes ? `<p>${escapeHtml(rx.notes)}</p>` : ""}
                `;

                container.appendChild(card);

            });

        })
        .catch(function (error) {
            console.error("Failed to load prescriptions:", error);
        });

}


/* =====================================================
   BILLING
===================================================== */

function loadInvoices() {

    const tbody = document.getElementById("invoicesTableBody");

    fetch("../backend/patient/get_invoices.php")
        .then(function (response) { return response.json(); })
        .then(function (invoices) {

            let totalBilled = 0;
            let totalPaid = 0;
            let totalDue = 0;

            invoices.forEach(function (inv) {
                totalBilled += Number(inv.net_amount);
                totalPaid += Number(inv.paid_amount);
                totalDue += Number(inv.due_amount);
            });

            const billingTotalEl = document.getElementById("billingTotalAmount");
            const billingPaidEl = document.getElementById("billingPaidAmount");
            const billingDueEl = document.getElementById("billingDueAmount");

            if (billingTotalEl) billingTotalEl.textContent = "₹" + totalBilled.toLocaleString("en-IN");
            if (billingPaidEl) billingPaidEl.textContent = "₹" + totalPaid.toLocaleString("en-IN");
            if (billingDueEl) billingDueEl.textContent = "₹" + totalDue.toLocaleString("en-IN");

            if (!tbody) return;

            tbody.innerHTML = "";

            if (invoices.length === 0) {
                tbody.innerHTML = "<tr><td colspan='6'>No invoices found.</td></tr>";
                return;
            }

            invoices.forEach(function (inv) {

                const row = document.createElement("tr");

                row.innerHTML = `
                    <td>${escapeHtml(inv.invoice_number)}</td>
                    <td>${escapeHtml(inv.invoice_date)}</td>
                    <td>Invoice</td>
                    <td>₹${Number(inv.net_amount).toLocaleString("en-IN")}</td>
                    <td>${escapeHtml(inv.status)}</td>
                    <td><button class="outline-btn" onclick="viewInvoiceDetail(${Number(inv.id)})">View</button></td>
                `;

                tbody.appendChild(row);

            });

        })
        .catch(function (error) {
            console.error("Failed to load invoices:", error);
        });

}


function viewInvoiceDetail(invoiceId) {

    fetch("../backend/patient/get_invoice_detail.php?invoice_id=" + invoiceId)
        .then(function (response) { return response.json(); })
        .then(function (data) {

            if (data.error) {
                alert("Could not load invoice.");
                return;
            }

            let paymentsText = data.payments.map(function (p) {
                return `${p.payment_date}: ₹${p.amount} via ${p.payment_method} (${p.status})`;
            }).join("\n");

            alert(
                `Invoice ${data.invoice_number}\n` +
                `Total: ₹${data.net_amount}\n` +
                `Paid: ₹${data.paid_amount}\n` +
                `Due: ₹${data.due_amount}\n\n` +
                `Payments:\n${paymentsText || "None yet"}`
            );

        })
        .catch(function (error) {
            console.error("Failed to load invoice detail:", error);
        });

}


/* =====================================================
   NOTIFICATIONS
===================================================== */

function loadNotifications() {

    const container = document.getElementById("notificationListContainer");
    if (!container) return;

    fetch("../backend/patient/get_notifications.php")
        .then(function (response) { return response.json(); })
        .then(function (notifications) {

            container.innerHTML = "";

            /* Wire the real unread count into the sidebar badge */
            const unreadCount = notifications.filter(function (n) {
                return n.is_read == 0;
            }).length;

            const badge = document.getElementById("navNotificationsBadge");
            if (badge) badge.textContent = unreadCount;

            if (notifications.length === 0) {
                container.innerHTML = "<p>No notifications.</p>";
                return;
            }

            notifications.forEach(function (n) {

                const card = document.createElement("div");
                card.className = "notification-card" + (n.is_read == 0 ? " unread" : "");

                card.innerHTML = `
                    <div>
                        <strong>${escapeHtml(n.title)}</strong>
                        <p>${escapeHtml(n.message)}</p>
                        <small>${escapeHtml(n.created_at)}</small>
                    </div>
                    ${n.is_read == 0 ? `<button class="text-btn" data-notif-id="${escapeHtml(n.id)}">Mark as read</button>` : ""}
                `;

                container.appendChild(card);

            });

            container.querySelectorAll("[data-notif-id]").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    markNotificationRead(this.dataset.notifId);
                });
            });

        })
        .catch(function (error) {
            console.error("Failed to load notifications:", error);
        });

}


function markNotificationRead(notificationId) {

    const formData = new FormData();
    formData.append("notification_id", notificationId);
    formData.append("csrf_token", getCsrfToken());

    fetch("../backend/patient/mark_notification_read.php", {
        method: "POST",
        body: formData
    })
        .then(function (response) { return response.text(); })
        .then(function (result) {
            if (result.trim() === "success") {
                loadNotifications();
                loadDashboardSummary();
            }
        })
        .catch(function (error) {
            console.error("Failed to mark notification read:", error);
        });

}


/* =====================================================
   REFERRALS
===================================================== */

function loadReferrals() {

    const container = document.getElementById("referralListContainer");
    if (!container) return;

    fetch("../backend/patient/get_referrals.php")
        .then(function (response) { return response.json(); })
        .then(function (referrals) {

            /* Wire the real "pending" referral count into the sidebar badge */
            const pendingCount = referrals.filter(function (r) {
                return (r.status || "").toLowerCase() === "pending";
            }).length;

            const badge = document.getElementById("navReferralsBadge");
            if (badge) badge.textContent = pendingCount;

            container.innerHTML = "";

            if (referrals.length === 0) {
                container.innerHTML = "<p>No referrals found.</p>";
                return;
            }

            referrals.forEach(function (ref) {

                const item = document.createElement("div");
                item.className = "referral-history-item";

                const statusClass =
                    (ref.status || "").toLowerCase() === "completed" ? "confirmed" : "";

                item.innerHTML = `
                    <div class="history-icon">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <strong>${escapeHtml(ref.referring_doctor_name) || "N/A"}</strong>
                        <p>Referred to ${escapeHtml(ref.referred_doctor_name) || "N/A"}${ref.reason ? " — " + escapeHtml(ref.reason) : ""}</p>
                        <small>${escapeHtml(ref.referral_date)}</small>
                    </div>
                    <span class="status ${statusClass}">
                        ${escapeHtml((ref.status || "").toUpperCase())}
                    </span>
                `;

                container.appendChild(item);

            });

        })
        .catch(function (error) {
            console.error("Failed to load referrals:", error);
        });

}


/* =====================================================
   TREATMENTS
===================================================== */

function loadTreatments() {

    const container = document.getElementById("treatmentListContainer");
    if (!container) return;

    fetch("../backend/patient/get_treatments.php")
        .then(function (response) { return response.json(); })
        .then(function (treatments) {

            /* Wire the dashboard "Active Treatment" stat card */
            const active = treatments.find(function (t) {
                return (t.status || "").toLowerCase() === "in progress";
            });

            const activeCountEl = document.getElementById("statActiveTreatmentCount");
            const activeNameEl = document.getElementById("statActiveTreatmentName");

            const activeCount = treatments.filter(function (t) {
                return (t.status || "").toLowerCase() === "in progress";
            }).length;

            if (activeCountEl) activeCountEl.textContent = activeCount;
            if (activeNameEl) {
                activeNameEl.textContent = active ? active.treatment_name : "No active treatment";
            }

            container.innerHTML = "";

            if (treatments.length === 0) {
                container.innerHTML = "<p>No treatments found.</p>";
                return;
            }

            treatments.forEach(function (t) {

                const card = document.createElement("div");
                card.className = "treatment-card";

                const statusLabel = (t.status || "").toUpperCase();

                card.innerHTML = `
                    <div class="treatment-header">
                        <div class="treatment-tooth">
                            <i class="fa-solid fa-tooth"></i>
                        </div>
                        <div>
                            <span>${t.tooth_number ? "TOOTH " + escapeHtml(t.tooth_number) : "TREATMENT"}</span>
                            <h3>${escapeHtml(t.treatment_name)}</h3>
                            <p>
                                ${t.start_date ? "Started on " + escapeHtml(t.start_date) : ""}
                                ${t.doctor_name ? " · " + escapeHtml(t.doctor_name) : ""}
                            </p>
                        </div>
                        <span class="treatment-status">${escapeHtml(statusLabel)}</span>
                    </div>
                    ${t.description ? `<p>${escapeHtml(t.description)}</p>` : ""}
                    ${t.end_date ? `<p><small>Completed: ${escapeHtml(t.end_date)}</small></p>` : ""}
                `;

                container.appendChild(card);

            });

        })
        .catch(function (error) {
            console.error("Failed to load treatments:", error);
        });

}


/* =====================================================
   SAVE PROFILE
===================================================== */

function saveProfile() {

    const fullName = document.getElementById("fullName").value;
    const phone = document.getElementById("phone").value;
    const gender = document.getElementById("gender").value;
    const area = document.getElementById("area").value;
    const address = document.getElementById("address").value;

    if (fullName.trim() === "" || phone.trim() === "") {
        alert("Please fill all required fields.");
        return;
    }

    const formData = new FormData();
    formData.append("full_name", fullName);
    formData.append("mobile", phone);
    formData.append("gender", gender);
    formData.append("area", area);
    formData.append("address", address);
    formData.append("csrf_token", getCsrfToken());

    fetch("../backend/patient/update_profile.php", {
        method: "POST",
        body: formData
    })
        .then(function (response) { return response.text(); })
        .then(function (result) {

            if (result.trim() === "success") {

                document.getElementById("profileName").textContent = fullName;

                document
                    .querySelectorAll("#profile input, #profile textarea")
                    .forEach(function (input) {
                        input.setAttribute("readonly", true);
                    });

                document.getElementById("profileActions").style.display = "none";

                const editButton = document.getElementById("editProfileBtn");
                editButton.innerHTML = '<i class="fa-solid fa-pen"></i> Edit Profile';
                editButton.disabled = false;

                profileEditing = false;

                alert("Profile updated successfully.");

            } else {
                alert("Update failed: " + result);
            }

        })
        .catch(function (error) {
            console.error("Profile update failed:", error);
            alert("Something went wrong updating your profile.");
        });

}