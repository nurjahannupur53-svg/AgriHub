<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/consultation_data.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$consultation = findConsultationById($id);

if (!$consultation) {
    header('Location: consultation.php');
    exit;
}

if ($consultation['status'] === 'Cancelled') {
    header('Location: consultation.php');
    exit;
}

$error = '';


/*
|--------------------------------------------------------------------------
| Update Consultation
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $expert = trim($_POST['expert'] ?? '');
    $topic = trim($_POST['topic'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $allowedStatuses = [
        'Scheduled',
        'Completed'
    ];


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $expert === '' ||
        $topic === '' ||
        $date === ''
    ) {

        $error = 'Please complete all fields correctly.';

    } elseif (!in_array($status, $allowedStatuses, true)) {

        $error = 'Please select a valid consultation status.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Consultation
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    farmer_id,
                    status
                FROM consultations
                WHERE id = ?
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $lockedConsultation = $stmt->fetch();

            if (!$lockedConsultation) {
                throw new Exception('Consultation not found.');
            }


            /*
            |--------------------------------------------------------------------------
            | Cancelled Consultation Cannot Be Updated
            |--------------------------------------------------------------------------
            */

            if ($lockedConsultation['status'] === 'Cancelled') {

                $pdo->rollBack();

                header(
                    'Location: consultation.php?error=cancelled'
                );

                exit;
            }


            /*
            |--------------------------------------------------------------------------
            | Update Consultation
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE consultations
                SET
                    expert = ?,
                    topic = ?,
                    consultation_date = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $expert,
                $topic,
                $date,
                $status,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            header(
                'Location: consultation.php?success=updated'
            );

            exit;


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                'Unable to update the consultation. Please try again.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values After Error
    |--------------------------------------------------------------------------
    */

    $consultation['expert'] = $expert;
    $consultation['topic'] = $topic;
    $consultation['date'] = $date;
    $consultation['status'] = $status;
}


$pageTitle = 'Update Consultation';

$pageSubtitle =
    $consultation['id'] . ' — ' . $consultation['farmer'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="consultation_details.php?id=<?= urlencode($consultation['id']) ?>"
        class="btn btn-light"
    >
        ← Back
    </a>

</div>


<?php if ($error !== ''): ?>

    <div class="flash-message flash-error">
        <?= htmlspecialchars($error) ?>
    </div>

<?php endif; ?>


<div class="app-card edit-form-card">

    <div class="card-header">

        <h3>Update Consultation</h3>

    </div>


    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($consultation['id']) ?>"
            >


            <div class="app-form-group">

                <label>Expert</label>

                <input
                    type="text"
                    name="expert"
                    class="app-input"
                    value="<?= htmlspecialchars($consultation['expert']) ?>"
                    required
                >

            </div>


            <div class="app-form-group">

                <label>Topic</label>

                <input
                    type="text"
                    name="topic"
                    class="app-input"
                    value="<?= htmlspecialchars($consultation['topic']) ?>"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Date</label>

                    <input
                        type="date"
                        name="date"
                        class="app-input"
                        value="<?= htmlspecialchars($consultation['date']) ?>"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Status</label>

                    <select
                        name="status"
                        class="app-select"
                        required
                    >

                        <option
                            value="Scheduled"
                            <?= $consultation['status'] === 'Scheduled'
                                ? 'selected'
                                : '' ?>
                        >
                            Scheduled
                        </option>

                        <option
                            value="Completed"
                            <?= $consultation['status'] === 'Completed'
                                ? 'selected'
                                : '' ?>
                        >
                            Completed
                        </option>

                    </select>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="consultation_details.php?id=<?= urlencode($consultation['id']) ?>"
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