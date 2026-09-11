/* =====================================================
   SHREE VENKATSAI DENTAL HOSPITAL
   PATIENT LOGIN
   DEMO LOGIN VERSION
===================================================== */


/* =====================================================
   DEMO CREDENTIALS
===================================================== */

const DEMO_PATIENT = {
    email: "patient@shreevenkatsai.com",
    password: "Patient@123"
};


/* =====================================================
   DOM ELEMENTS
===================================================== */

const loginForm =
    document.getElementById("loginForm");

const emailInput =
    document.getElementById("email");

const passwordInput =
    document.getElementById("password");

const rememberMe =
    document.getElementById("rememberMe");

const loginBtn =
    document.getElementById("loginBtn");

const passwordToggle =
    document.getElementById("passwordToggle");

const loginMessage =
    document.getElementById("loginMessage");

const emailError =
    document.getElementById("emailError");

const passwordError =
    document.getElementById("passwordError");

const forgotPassword =
    document.getElementById("forgotPassword");

const useDemoBtn =
    document.getElementById("useDemoBtn");

const demoEmailBtn =
    document.getElementById("demoEmailBtn");

const demoPasswordBtn =
    document.getElementById("demoPasswordBtn");


/* =====================================================
   USE DEMO CREDENTIALS
===================================================== */

function fillDemoCredentials() {

    emailInput.value =
        DEMO_PATIENT.email;

    passwordInput.value =
        DEMO_PATIENT.password;

    clearErrors();

    showMessage(
        "Demo credentials filled. Click Login to continue.",
        "success"
    );

    emailInput.focus();
}


/* =====================================================
   DEMO BUTTON
===================================================== */

if (useDemoBtn) {

    useDemoBtn.addEventListener(
        "click",
        fillDemoCredentials
    );

}


/* =====================================================
   CLICK EMAIL DEMO
===================================================== */

if (demoEmailBtn) {

    demoEmailBtn.addEventListener(
        "click",
        function () {

            emailInput.value =
                DEMO_PATIENT.email;

            emailInput.focus();

        }
    );

}


/* =====================================================
   CLICK PASSWORD DEMO
===================================================== */

if (demoPasswordBtn) {

    demoPasswordBtn.addEventListener(
        "click",
        function () {

            passwordInput.value =
                DEMO_PATIENT.password;

            passwordInput.focus();

        }
    );

}


/* =====================================================
   PASSWORD SHOW / HIDE
===================================================== */

if (passwordToggle) {

    passwordToggle.addEventListener(
        "click",
        function () {

            const icon =
                passwordToggle.querySelector("i");


            if (
                passwordInput.type === "password"
            ) {

                passwordInput.type = "text";

                icon.classList.remove(
                    "fa-eye"
                );

                icon.classList.add(
                    "fa-eye-slash"
                );

                passwordToggle.setAttribute(
                    "aria-label",
                    "Hide password"
                );

            } else {

                passwordInput.type = "password";

                icon.classList.remove(
                    "fa-eye-slash"
                );

                icon.classList.add(
                    "fa-eye"
                );

                passwordToggle.setAttribute(
                    "aria-label",
                    "Show password"
                );

            }

        }
    );

}


/* =====================================================
   CLEAR ERRORS
===================================================== */

function clearErrors() {

    emailError.textContent = "";

    passwordError.textContent = "";

    loginMessage.textContent = "";

    loginMessage.className =
        "login-message";

}


/* =====================================================
   SHOW MESSAGE
===================================================== */

function showMessage(
    message,
    type
) {

    loginMessage.textContent =
        message;

    loginMessage.className =
        "login-message " + type;

}


/* =====================================================
   EMAIL VALIDATION
===================================================== */

function validateEmail(email) {

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        .test(email);

}


/* =====================================================
   LOGIN FORM
===================================================== */

if (loginForm) {

    loginForm.addEventListener("submit", function (event) {

        event.preventDefault();

        clearErrors();

        const email = emailInput.value.trim().toLowerCase();
        const password = passwordInput.value;

        let isValid = true;

        // Email validation
        if (!email) {
            emailError.textContent = "Please enter your email address.";
            isValid = false;
        }
        else if (!validateEmail(email)) {
            emailError.textContent = "Please enter a valid email address.";
            isValid = false;
        }

        // Password validation
        if (!password) {
            passwordError.textContent = "Please enter your password.";
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        // Disable button while logging in
        loginBtn.disabled = true;
        loginBtn.innerHTML = `
            <span>Signing In...</span>
            <i class="fa-solid fa-spinner fa-spin"></i>
        `;

        fetch("../backend/auth/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            credentials: "same-origin",
            body:
                "email=" + encodeURIComponent(email) +
                "&password=" + encodeURIComponent(password)
        })

        .then(response => response.text())

        .then(data => {

            data = data.trim();

            console.log("Server Response:", data);

            if (data === "success:patient") {

                showMessage(
                    "Login Successful! Redirecting...",
                    "success"
                );

                if (rememberMe.checked) {

                    localStorage.setItem(
                        "rememberPatient",
                        "true"
                    );

                } else {

                    localStorage.removeItem(
                        "rememberPatient"
                    );

                }

                setTimeout(function () {

                    window.location.href =
                        "../patient_index/patient_index.php";

                }, 1000);

            }

            else if (data === "success:receptionist") {

                showMessage(
                    "Login Successful! Redirecting to Reception Desk...",
                    "success"
                );

                setTimeout(function () {

                    window.location.href =
                        "../recption/recption.html";

                }, 1000);

            }

            else if (data === "wrong_password") {

                showMessage(
                    "Incorrect Password.",
                    "error"
                );

            }

            else if (data === "user_not_found") {

                showMessage(
                    "User not found.",
                    "error"
                );

            }

            else {

                showMessage(
                    data,
                    "error"
                );

            }

        })

        .catch(function (error) {

            console.error(error);

            showMessage(
                "Unable to connect to the server.",
                "error"
            );

        })

        .finally(function () {

            loginBtn.disabled = false;

            loginBtn.innerHTML =
                `<span>Login to Portal</span>
                 <i class="fa-solid fa-arrow-right"></i>`;

        });

    });

}