<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/profile_data.php';

$profile = getAdminProfile();

$error = '';


/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $name === '' ||
        $email === '' ||
        $phone === '' ||
        $location === ''
    ) {

        $error = 'Please complete all required fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Check Email Is Not Used By Another User
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = ?
                  AND id <> ?
                LIMIT 1
            ");

            $stmt->execute([
                $email,
                $_SESSION['user_id']
            ]);

            if ($stmt->fetch()) {

                $error =
                    'This email address is already being used by another account.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Update User
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET
                        full_name = ?,
                        email = ?,
                        phone = ?,
                        location = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $name,
                    $email,
                    $phone,
                    $location,
                    $_SESSION['user_id']
                ]);


                /*
                |--------------------------------------------------------------------------
                | Keep Login/Header Session Synchronized
                |--------------------------------------------------------------------------
                */

                $_SESSION['full_name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;


                header(
                    'Location: profile.php?success=profile'
                );

                exit;
            }


        } catch (Throwable $e) {

            $error =
                'Unable to update your profile. Please try again.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values Visible If Validation Fails
    |--------------------------------------------------------------------------
    */

    $profile['name'] = $name;
    $profile['email'] = $email;
    $profile['phone'] = $phone;
    $profile['location'] = $location;
}


$pageTitle = 'Edit Profile';
$pageSubtitle = 'Update administrator information';

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


<div class="app-card edit-form-card">

    <div class="card-header">

        <div>

            <h3>Edit Personal Information</h3>

            <span class="card-subtitle">
                Update your administrator profile
            </span>

        </div>

    </div>


    <div class="card-body">

        <form method="POST">

            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="name"
                        class="app-input"
                        value="<?= htmlspecialchars($profile['name']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Role</label>

                    <input
                        type="text"
                        class="app-input"
                        value="<?= htmlspecialchars($profile['role']) ?>"
                        disabled
                    >

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Email Address</label>

                    <input
                        type="email"
                        name="email"
                        class="app-input"
                        value="<?= htmlspecialchars($profile['email']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Phone Number</label>

                    <input
                        type="text"
                        name="phone"
                        class="app-input"
                        value="<?= htmlspecialchars($profile['phone']) ?>"
                        required
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Location</label>

                <input
                    type="text"
                    name="location"
                    class="app-input"
                    value="<?= htmlspecialchars($profile['location']) ?>"
                    required
                >

            </div>


            <div class="app-form-group">

                <label>Member Since</label>

                <input
                    type="date"
                    class="app-input"
                    value="<?= htmlspecialchars($profile['joined']) ?>"
                    disabled
                >

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
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>