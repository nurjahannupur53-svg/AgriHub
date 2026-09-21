<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$error = '';


/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPassword =
        $_POST['current_password'] ?? '';

    $newPassword =
        $_POST['new_password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {

        $error = 'Please complete all password fields.';

    } elseif (strlen($newPassword) < 6) {

        $error =
            'New password must be at least 6 characters.';

    } elseif ($newPassword !== $confirmPassword) {

        $error =
            'New password and confirmation do not match.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Load Current Password Hash From Database
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    password
                FROM users
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([
                $_SESSION['user_id']
            ]);

            $user = $stmt->fetch();


            if (!$user) {

                $error =
                    'Your account could not be found.';

            } elseif (
                !password_verify(
                    $currentPassword,
                    $user['password']
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Verify Current Password
                |--------------------------------------------------------------------------
                */

                $error =
                    'Current password is incorrect.';

            } elseif (
                password_verify(
                    $newPassword,
                    $user['password']
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | New Password Must Be Different
                |--------------------------------------------------------------------------
                */

                $error =
                    'New password must be different from the current password.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Hash New Password
                |--------------------------------------------------------------------------
                */

                $newPasswordHash =
                    password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );


                if ($newPasswordHash === false) {

                    $error =
                        'Unable to secure the new password. Please try again.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Save New Password To Database
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET password = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $newPasswordHash,
                        $_SESSION['user_id']
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Redirect
                    |--------------------------------------------------------------------------
                    */

                    header(
                        'Location: profile.php?success=password'
                    );

                    exit;
                }
            }


        } catch (Throwable $e) {

            $error =
                'Unable to change your password. Please try again.';
        }
    }
}


$pageTitle = 'Change Password';
$pageSubtitle = 'Update your account security';

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="profile.php"
        class="btn btn-light"
    >
        ← Back to Profile
    </a>

</div>


<?php if ($error !== ''): ?>

    <div class="flash-message flash-error">
        <?= htmlspecialchars($error) ?>
    </div>

<?php endif; ?>


<div class="app-card password-form-card">

    <div class="card-header">

        <div>

            <h3>Change Password</h3>

            <span class="card-subtitle">
                Choose a secure password for your account
            </span>

        </div>

    </div>


    <div class="card-body">

        <form method="POST">

            <div class="app-form-group">

                <label>Current Password</label>

                <div class="password-input-wrap">

                    <input
                        type="password"
                        name="current_password"
                        id="currentPassword"
                        class="app-input"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-password-target="currentPassword"
                    >
                        Show
                    </button>

                </div>

            </div>


            <div class="app-form-group">

                <label>New Password</label>

                <div class="password-input-wrap">

                    <input
                        type="password"
                        name="new_password"
                        id="newPassword"
                        class="app-input"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-password-target="newPassword"
                    >
                        Show
                    </button>

                </div>

                <small class="form-help">
                    Minimum 6 characters.
                </small>

            </div>


            <div class="app-form-group">

                <label>Confirm New Password</label>

                <div class="password-input-wrap">

                    <input
                        type="password"
                        name="confirm_password"
                        id="confirmPassword"
                        class="app-input"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-password-target="confirmPassword"
                    >
                        Show
                    </button>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="profile.php"
                    class="btn btn-light"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Change Password
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener(
    "DOMContentLoaded",
    function () {

        const buttons =
            document.querySelectorAll(
                "[data-password-target]"
            );

        buttons.forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const input =
                        document.getElementById(
                            button.dataset.passwordTarget
                        );

                    if (!input) {
                        return;
                    }

                    if (input.type === "password") {

                        input.type = "text";
                        button.textContent = "Hide";

                    } else {

                        input.type = "password";
                        button.textContent = "Show";

                    }

                }
            );

        });

    }
);
</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>