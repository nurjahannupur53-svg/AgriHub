<?php
session_start();

require_once "../config/db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = "";
$success = "";

$fullName = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $fullName === "" ||
        $email === "" ||
        $phone === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {

        $error = "Please complete all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    } else {

        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        $check->execute([$email]);

        if ($check->fetch()) {

            $error = "An account with this email already exists.";

        } else {

            $hashedPassword =
                password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users
                (
                    full_name,
                    email,
                    phone,
                    password,
                    role
                )
                VALUES (?, ?, ?, ?, 'Admin')
            ");

            $stmt->execute([
                $fullName,
                $email,
                $phone,
                $hashedPassword
            ]);

            header(
                "Location: login.php?registered=1"
            );

            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Account | AgriHub</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .register-page {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 40px 20px;

            background:
                linear-gradient(
                    135deg,
                    #009448,
                    #08b84f
                );
        }

        .register-card {
            width: 100%;
            max-width: 520px;

            padding: 38px;

            border-radius: 16px;

            background: white;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .register-header {
            margin-bottom: 30px;

            text-align: center;
        }

        .register-logo {
            width: 48px;
            height: 48px;

            margin: 0 auto 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #08b84f;

            color: white;

            font-size: 20px;
            font-weight: bold;
        }

        .register-header h1 {
            margin-bottom: 7px;

            font-size: 26px;
        }

        .register-header p {
            color: #7c847f;

            font-size: 13px;
        }

        .register-btn {
            width: 100%;
            height: 49px;

            margin-top: 5px;

            border: none;
            border-radius: 7px;

            background: #08b84f;

            color: white;

            font-size: 14px;
            font-weight: bold;

            cursor: pointer;
        }

        .register-btn:hover {
            background: #079c44;
        }

        .back-login {
            display: block;

            margin-top: 22px;

            text-align: center;

            color: #08a948;

            text-decoration: none;

            font-size: 13px;
            font-weight: 600;
        }

        .back-login:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="register-page">

    <div class="register-card">

        <div class="register-header">

            <div class="register-logo">
                A
            </div>

            <h1>Create your account</h1>

            <p>
                Register to access the AgriHub management system.
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST"
              action="register.php">

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?= htmlspecialchars($fullName) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Phone Number</label>

                <input
                    type="text"
                    name="phone"
                    placeholder="+880 1XXXXXXXXX"
                    value="<?= htmlspecialchars($phone) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Enter password again"
                    required
                >

            </div>


            <button
                type="submit"
                class="register-btn"
            >
                Register
            </button>

        </form>


        <a
            href="login.php"
            class="back-login"
        >
            ← Back to Login
        </a>

    </div>

</div>

</body>
</html>