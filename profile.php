<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/profile_data.php';

$profile = getAdminProfile();

$success = trim($_GET['success'] ?? '');

$pageTitle = 'Profile & Settings';
$pageSubtitle = 'Manage your administrator account';

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'profile'): ?>

    <div class="flash-message flash-success">
        Profile updated successfully.
    </div>

<?php elseif ($success === 'password'): ?>

    <div class="flash-message flash-success">
        Password changed successfully.
    </div>

<?php endif; ?>


<div class="profile-layout">

    <!-- LEFT PROFILE CARD -->

    <div class="app-card profile-card">

        <div class="profile-avatar-large">

            <?php

            $nameParts = preg_split(
                '/\s+/',
                trim($profile['name'])
            );

            $profileInitials = '';

            foreach (
                array_slice($nameParts, 0, 2)
                as $part
            ) {
                if ($part !== '') {
                    $profileInitials .= strtoupper(
                        substr($part, 0, 1)
                    );
                }
            }

            echo htmlspecialchars(
                $profileInitials ?: 'AD'
            );

            ?>

        </div>


        <h2>
            <?= htmlspecialchars($profile['name']) ?>
        </h2>

        <p class="profile-role">
            <?= htmlspecialchars($profile['role']) ?>
        </p>


        <div class="profile-status">
            <span></span>
            Active Administrator
        </div>


        <a
            href="profile_edit.php"
            class="btn btn-primary profile-main-button"
        >
            Edit Profile
        </a>

    </div>


    <!-- RIGHT SIDE -->

    <div class="profile-content">

        <div class="app-card details-section">

            <div class="card-header">

                <div>
                    <h3>Personal Information</h3>

                    <span class="card-subtitle">
                        Administrator account details
                    </span>
                </div>

            </div>


            <div class="card-body">

                <div class="info-grid">

                    <div class="info-item">
                        <span>Full Name</span>

                        <strong>
                            <?= htmlspecialchars($profile['name']) ?>
                        </strong>
                    </div>


                    <div class="info-item">
                        <span>Role</span>

                        <strong>
                            <?= htmlspecialchars($profile['role']) ?>
                        </strong>
                    </div>


                    <div class="info-item">
                        <span>Email Address</span>

                        <strong>
                            <?= htmlspecialchars($profile['email']) ?>
                        </strong>
                    </div>


                    <div class="info-item">
                        <span>Phone Number</span>

                        <strong>
                            <?= htmlspecialchars($profile['phone']) ?>
                        </strong>
                    </div>


                    <div class="info-item">
                        <span>Location</span>

                        <strong>
                            <?= htmlspecialchars($profile['location']) ?>
                        </strong>
                    </div>


                    <div class="info-item">
                        <span>Joined</span>

                        <strong>
                            <?= htmlspecialchars($profile['joined']) ?>
                        </strong>
                    </div>

                </div>

            </div>

        </div>


        <div class="app-card profile-security-card">

            <div class="profile-security-icon">
                🔒
            </div>

            <div class="profile-security-text">

                <h3>Password & Security</h3>

                <p>
                    Update your administrator account password
                    regularly to keep your account secure.
                </p>

            </div>

            <a
                href="change_password.php"
                class="btn btn-light"
            >
                Change Password
            </a>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>