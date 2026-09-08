
/* =========================================================
   ODSMART PATIENT PORTAL JAVASCRIPT
   ========================================================= */

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

           confirmLogout.addEventListener(
    "click",
    function () {

        window.location.href =
            "../backend/auth/logout.php";

    }
);
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
       TAB BUTTONS
    ===================================================== */

    const tabButtons =
        document.querySelectorAll(".tab-btn");

    tabButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                tabButtons.forEach(
                    function (btn) {

                        btn.classList.remove(
                            "active"
                        );

                    }
                );

                this.classList.add(
                    "active"
                );

            }
        );

    });


    /* =====================================================
       CANCEL APPOINTMENT
    ===================================================== */

    document
        .querySelectorAll(".cancel-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const confirmCancel =
                        confirm(
                            "Are you sure you want to cancel this appointment?"
                        );

                    if (confirmCancel) {

                        this.closest(
                            ".full-appointment-card"
                        ).style.opacity = "0.5";

                        this.textContent =
                            "Cancelled";

                        this.disabled =
                            true;

                    }

                }
            );

        });


    /* =====================================================
       MARK NOTIFICATIONS READ
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


    // Enable text fields
    inputs.forEach(function(input) {

        input.removeAttribute("readonly");

    });


    // Enable gender dropdown
    gender.removeAttribute("disabled");


    // Show Save + Cancel buttons
    profileActions.style.display = "flex";


    // Change Edit button
    editButton.innerHTML =
        '<i class="fa-solid fa-pen"></i> Editing...';

    editButton.disabled = true;

}


// ==========================================
// SAVE PROFILE
// ==========================================

function saveProfile() {

    const fullName =
        document.getElementById("fullName").value;

    const email =
        document.getElementById("email").value;

    const phone =
        document.getElementById("phone").value;

    const dateOfBirth =
        document.getElementById("dateOfBirth").value;

    const gender =
        document.getElementById("gender").value;

    const area =
        document.getElementById("area").value;

    const address =
        document.getElementById("address").value;


    // Basic validation
    if (
        fullName.trim() === "" ||
        email.trim() === "" ||
        phone.trim() === "" ||
        gender === "" ||
        area.trim() === "" ||
        address.trim() === ""
    ) {

        alert("Please fill all required fields.");

        return;

    }


    // Update profile name displayed at the top
    document.getElementById("profileName").textContent =
        fullName;


    // Make fields read-only again
    document
        .querySelectorAll("#profile input, #profile textarea")
        .forEach(function(input) {

            input.setAttribute("readonly", true);

        });


    // Disable gender dropdown
    document
        .getElementById("gender")
        .setAttribute("disabled", true);


    // Hide Save + Cancel
    document.getElementById("profileActions").style.display =
        "none";


    // Enable Edit button again
    const editButton =
        document.getElementById("editProfileBtn");

    editButton.innerHTML =
        '<i class="fa-solid fa-pen"></i> Edit Profile';

    editButton.disabled = false;


    profileEditing = false;


    alert("Profile updated successfully.");

}


// ==========================================
// CANCEL EDIT
// ==========================================

function cancelProfileEdit() {

    // Reload the page to restore original values
    location.reload();

}
