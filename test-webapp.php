<?php
/*
 * Kara David's Wish Note
 * Local development/testing application.
 *
 * Intentional exercise flaw:
 * Location is accepted and displayed in the confirmation modal, but is
 * intentionally omitted from the INSERT column list and bind_param().
 * Therefore, Location is not saved by this INSERT statement.
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "birthday_wishnote";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($servername, $username, $password, $dbname);

$dbConnectionError = "";
if ($conn->connect_error) {
    $dbConnectionError = "Database connection failed. Check that MariaDB is running and the database exists.";
} else {
    $conn->set_charset("utf8mb4");
}

$successMessage = "";
$errorMessage = "";

$firstName = "";
$lastName = "";
$govPosition = "";
$location = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$dbConnectionError) {
    $firstName = trim($_POST["FirstName"] ?? "");
    $lastName = trim($_POST["LastName"] ?? "");
    $govPosition = trim($_POST["GovPosition"] ?? "");
    $location = trim($_POST["Location"] ?? "");

    // Server-side validation is required even when HTML required/maxlength are present.
    if ($firstName === "" || $lastName === "" || $govPosition === "") {
        $errorMessage = "Please complete all required fields.";
    } elseif (mb_strlen($firstName) > 30 || mb_strlen($lastName) > 30 ||
              mb_strlen($govPosition) > 20 || mb_strlen($location) > 40) {
        $errorMessage = "One or more fields exceed their maximum character limit.";
    } else {
        /*
         * INTENTIONAL FLAW FOR THIS TEST CASE:
         * Location is deliberately absent from the INSERT columns and from
         * bind_param(). Only FirstName, LastName, and GovPosition are stored.
         */
        $sql = "INSERT INTO main_corrupt_politicians (FirstName, LastName, GovPosition)
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $errorMessage = "Unable to prepare the database query. Please check the table and column names.";
        } else {
            $stmt->bind_param("sss", $firstName, $lastName, $govPosition);

            if ($stmt->execute()) {
                $successMessage = "Success! The wish note has been sent.";
                $firstName = $lastName = $govPosition = $location = "";
            } else {
                $errorMessage = "The entry could not be saved. Please check the database table structure.";
            }
            $stmt->close();
        }
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kara David's Wish Note</title>
    <style>
        :root { --blue: #1769aa; --blue-dark: #0e4f86; --bg: #f1f4f8; --text: #243447; }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 24px 14px; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: var(--bg); color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }
        .card {
            width: 100%; max-width: 520px; padding: 28px;
            background: #fff; border-radius: 14px;
            box-shadow: 0 8px 28px rgba(25, 55, 90, .12);
        }
        h2 { margin: 0 0 8px; color: var(--blue-dark); text-align: center; }
        .intro { margin: 0 0 22px; color: #637184; text-align: center; line-height: 1.5; }
        .field { margin-bottom: 16px; }
        label { display: block; margin-bottom: 7px; font-weight: 700; font-size: .94rem; }
        input {
            width: 100%; padding: 12px 13px; border: 1px solid #cbd5e1;
            border-radius: 7px; font-size: 1rem; background: #fff;
        }
        input:focus { outline: 3px solid rgba(23,105,170,.16); border-color: var(--blue); }
        .hint { display: block; margin-top: 5px; color: #718096; font-size: .8rem; }
        button {
            border: 0; border-radius: 7px; padding: 12px 16px; font-size: 1rem;
            font-weight: 700; cursor: pointer; transition: background .18s ease;
        }
        .primary { width: 100%; color: #fff; background: var(--blue); }
        .primary:hover { background: var(--blue-dark); }
        .secondary { background: #e7edf4; color: #263648; }
        .secondary:hover { background: #d5dee9; }
        .success, .error, .notice {
            margin: 0 0 18px; padding: 12px 14px; border-radius: 7px; line-height: 1.45;
        }
        .success { background: #e7f7ed; color: #176534; border: 1px solid #b8e4c7; }
        .error { background: #fff0f0; color: #982c2c; border: 1px solid #f0c2c2; }
        .notice { background: #fff8e6; color: #765411; border: 1px solid #ead59c; }
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 10; padding: 18px;
            align-items: center; justify-content: center; background: rgba(13, 28, 45, .58);
        }
        .modal-overlay.open { display: flex; }
        .modal {
            width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto;
            padding: 24px; background: #fff; border-radius: 12px;
            box-shadow: 0 16px 48px rgba(0,0,0,.24);
        }
        .modal h3 { margin: 0 0 8px; color: var(--blue-dark); }
        .modal p { line-height: 1.5; }
        .review-list { margin: 18px 0; }
        .review-row { padding: 10px 0; border-bottom: 1px solid #e8edf2; overflow-wrap: anywhere; }
        .review-row strong { display: block; margin-bottom: 4px; font-size: .88rem; color: #526174; }
        .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
        .modal-actions button { flex: 1; }
        @media (max-width: 420px) {
            .card { padding: 21px 17px; }
            .modal-actions { flex-direction: column-reverse; }
        }
    </style>
</head>
<body>
<main class="card">
    <h2>Kara David's Wish Note</h2>
    <p class="intro">Enter the target's details and verify them before sending.</p>

    <?php if ($dbConnectionError !== ""): ?>
        <div class="error" role="alert"><?php echo h($dbConnectionError); ?></div>
    <?php endif; ?>
    <?php if ($successMessage !== ""): ?>
        <div class="success" role="status"><?php echo h($successMessage); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ""): ?>
        <div class="error" role="alert"><?php echo h($errorMessage); ?></div>
    <?php endif; ?>

    <form id="wishForm" method="post" action="">
        <div class="field">
            <label for="FirstName">Target's First Name</label>
            <input id="FirstName" name="FirstName" type="text" maxlength="30" required
                   value="<?php echo h($firstName); ?>">
        </div>
        <div class="field">
            <label for="LastName">Target's Last Name</label>
            <input id="LastName" name="LastName" type="text" maxlength="30" required
                   value="<?php echo h($lastName); ?>">
        </div>
        <div class="field">
            <label for="GovPosition">Target's Government Position</label>
            <input id="GovPosition" name="GovPosition" type="text" maxlength="20" required
                   value="<?php echo h($govPosition); ?>">
        </div>
        <div class="field">
            <label for="Location">Target's Location</label>
            <input id="Location" name="Location" type="text" maxlength="40"
                   value="<?php echo h($location); ?>">
            <span class="hint">Optional · maximum 40 characters</span>
        </div>
        <button class="primary" id="sendButton" type="submit" <?php echo $dbConnectionError ? "disabled" : ""; ?>>
            Send Wish Note
        </button>
    </form>
</main>

<div class="modal-overlay" id="verifyOverlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <section class="modal">
        <h3 id="modalTitle">Verify Target Data</h3>
        <p>Please check the information below before sending.</p>
        <div class="review-list">
            <div class="review-row"><strong>Target's First Name</strong><span id="reviewFirstName"></span></div>
            <div class="review-row"><strong>Target's Last Name</strong><span id="reviewLastName"></span></div>
            <div class="review-row"><strong>Target's Government Position</strong><span id="reviewGovPosition"></span></div>
            <div class="review-row"><strong>Target's Location</strong><span id="reviewLocation"></span></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="secondary" id="cancelButton">Cancel</button>
            <button type="button" class="primary" id="confirmButton">Confirm &amp; Send</button>
        </div>
    </section>
</div>

<script>
(() => {
    const form = document.getElementById("wishForm");
    const overlay = document.getElementById("verifyOverlay");
    const cancelButton = document.getElementById("cancelButton");
    const confirmButton = document.getElementById("confirmButton");

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        if (!form.reportValidity()) return;

        // textContent safely displays user-entered values without interpreting HTML.
        document.getElementById("reviewFirstName").textContent =
            document.getElementById("FirstName").value || "(blank)";
        document.getElementById("reviewLastName").textContent =
            document.getElementById("LastName").value || "(blank)";
        document.getElementById("reviewGovPosition").textContent =
            document.getElementById("GovPosition").value || "(blank)";
        document.getElementById("reviewLocation").textContent =
            document.getElementById("Location").value || "(blank)";

        overlay.classList.add("open");
        cancelButton.focus();
    });

    cancelButton.addEventListener("click", () => {
        overlay.classList.remove("open");
        document.getElementById("sendButton").focus();
    });

    confirmButton.addEventListener("click", () => {
        // Native submit sends the POST request without triggering the submit listener again.
        HTMLFormElement.prototype.submit.call(form);
    });

    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) overlay.classList.remove("open");
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && overlay.classList.contains("open")) {
            overlay.classList.remove("open");
        }
    });
})();
</script>
</body>
</html>
<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
