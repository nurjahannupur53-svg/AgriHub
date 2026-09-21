<?php
session_start();

require_once __DIR__ . "/../config/db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {

        $stmt = $pdo->prepare("
            SELECT id, full_name, email, phone, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            if (isset($_POST["remember"])) {
                setcookie(
                    "agrihub_email",
                    $user["email"],
                    time() + (86400 * 30),
                    "/"
                );
            } else {
                setcookie("agrihub_email", "", time() - 3600, "/");
            }

            header("Location: ../dashboard.php");
            exit;

        } else {
            $error = "Invalid email or password.";
        }
    }
}

$savedEmail = $_COOKIE["agrihub_email"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | AgriHub</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">
</head>

<body class="login-page">

<div class="login-wrapper">

    <!-- LEFT SIDE -->
    <section class="login-brand-panel">

        <div class="brand-content">

            <div class="large-agri-logo">
                <span class="crop-symbol">🌾</span>
            </div>

            <h1>AgriHub</h1>

            <p class="brand-subtitle">
                Agriculture Supply Chain Management System
            </p>

            <div class="feature-grid">

                <div class="feature-box">
                    <span>👨‍🌾</span>
                    <p>Farmer Management</p>
                </div>

                <div class="feature-box">
                    <span>📦</span>
                    <p>Advanced Tracking</p>
                </div>

                <div class="feature-box">
                    <span>🏪</span>
                    <p>Inventory Control</p>
                </div>

                <div class="feature-box">
                    <span>🚚</span>
                    <p>Smart Delivery</p>
                </div>

            </div>

        </div>

    </section>


    <!-- RIGHT SIDE -->
    <section class="login-form-panel">

        <div class="login-card">

            <div class="small-brand">

                <div class="small-logo">
                    A
                </div>

                <div>
                    <h2>AgriHub</h2>
                    <span>
                        Supply Chain Management
                    </span>
                </div>

            </div>


            <div class="login-heading">
                <h1>Welcome back</h1>

                <p>
                    Sign in to your account to continue.
                </p>
            </div>


            <?php if ($error !== ""): ?>

                <div class="alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form method="POST"
                  action="login.php"
                  class="login-form">

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@agrihub.com"
                        value="<?= htmlspecialchars($savedEmail) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                            aria-label="Show password"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <div class="login-options">

                    <label class="remember-me">

                        <input
                            type="checkbox"
                            name="remember"
                            <?= $savedEmail !== "" ? "checked" : "" ?>
                        >

                        <span>Remember me</span>

                    </label>

                    <a
                        href="#"
                        class="forgot-password"
                        onclick="showForgotPassword(event)"
                    >
                        Forgot password?
                    </a>

                </div>


                <button
                    type="submit"
                    class="login-btn"
                >
                    Sign in to AgriHub
                </button>

            </form>


            <div class="register-link">

                Don't have an account?

                <a href="register.php">
                    Create Account
                </a>

            </div>


            <p class="login-footer-text">
                Powered by AgriHub • Agriculture SCM
            </p>

        </div>

    </section>

</div>


<!-- FORGOT PASSWORD MODAL -->

<div
    class="modal-overlay"
    id="forgotModal"
>

    <div class="simple-modal">

        <button
            type="button"
            class="modal-close"
            onclick="closeForgotPassword()"
        >
            ×
        </button>

        <div class="modal-icon">
            ✉️
        </div>

        <h2>Forgot Password?</h2>

        <p>
            Password recovery is not configured yet.
            Please contact the AgriHub administrator.
        </p>

        <button
            type="button"
            class="modal-primary-btn"
            onclick="closeForgotPassword()"
        >
            Got it
        </button>

    </div>

</div>


<script>

function togglePassword() {

    const passwordInput =
        document.getElementById("password");

    if (passwordInput.type === "password") {

        passwordInput.type = "text";

    } else {

        passwordInput.type = "password";

    }
}


function showForgotPassword(event) {

    event.preventDefault();

    document
        .getElementById("forgotModal")
        .classList.add("show");
}


function closeForgotPassword() {

    document
        .getElementById("forgotModal")
        .classList.remove("show");
}


document
    .getElementById("forgotModal")
    .addEventListener("click", function(event) {

        if (event.target === this) {
            closeForgotPassword();
        }

    });

</script>

</body>
</html>