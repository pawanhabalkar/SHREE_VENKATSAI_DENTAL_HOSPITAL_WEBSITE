/* =====================================================
   SHREE VENKATSAI DENTAL HOSPITAL
   LOGIN (PATIENT + RECEPTIONIST)
===================================================== */


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


/* =====================================================
   CSRF TOKEN
   Static HTML page — no server-rendered <meta> tag, so
   fetch one on load and attach it to the login POST.
===================================================== */

let csrfToken = "";

fetch("../backend/auth/csrf_token.php")
    .then(function (response) { return response.json(); })
    .then(function (data) { csrfToken = data.csrf_token || ""; })
    .catch(function (error) { console.error("Failed to fetch CSRF token:", error); });


/* =====================================================
   FORGOT PASSWORD
   There's no self-service reset flow (would need email
   sending infrastructure this project doesn't have), so
   rather than leave a "#" link that silently does
   nothing, tell the person how to actually get help.
===================================================== */

if (forgotPassword) {

    forgotPassword.addEventListener("click", function (event) {

        event.preventDefault();

        alert(
            "Password resets aren't available online yet. " +
            "Please call the front desk or visit us in person and " +
            "our receptionist can help you regain access to your account."
        );

    });

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
                "&password=" + encodeURIComponent(password) +
                "&csrf_token=" + encodeURIComponent(csrfToken)
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