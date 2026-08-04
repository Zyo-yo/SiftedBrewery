<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/csrf.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $password_confirmation =
        $_POST["password_confirmation"] ?? "";

    if (!isValidCsrfToken($_POST["csrf_token"] ?? null)) {
        $errors[] =
            "Your session expired. Refresh the page and try again.";
    }

    if ($current_password === "") {
        $errors[] = "Your current password is required.";
    }

    if (strlen($new_password) < 12) {
        $errors[] =
            "The new password must be at least 12 characters.";
    } elseif (strlen($new_password) > 72) {
        $errors[] =
            "The new password cannot exceed 72 characters.";
    }

    if ($new_password !== $password_confirmation) {
        $errors[] = "The new passwords do not match.";
    }

    if (
        $current_password !== "" &&
        $current_password === $new_password
    ) {
        $errors[] =
            "The new password must differ from the current password.";
    }

    if (empty($errors)) {
        $query = "
            SELECT password
            FROM users
            WHERE user_id = :user_id
            LIMIT 1
        ";

        $statement = $db->prepare($query);

        $statement->execute([
            ":user_id" => (int) $_SESSION["user_id"]
        ]);

        $user = $statement->fetch();

        if (
            !$user ||
            !password_verify(
                $current_password,
                $user["password"]
            )
        ) {
            $errors[] = "The current password is incorrect.";
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        $update_query = "
            UPDATE users
            SET password = :password
            WHERE user_id = :user_id
        ";

        $update_statement = $db->prepare($update_query);

        $update_statement->execute([
            ":password" => $password_hash,
            ":user_id" => (int) $_SESSION["user_id"]
        ]);

        session_regenerate_id(true);

        header(
            "Location: index.php?success=password_changed"
        );
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Change Password | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>

<body>

<main class="form-container">

    <p class="eyebrow">Account security</p>

    <h1>Change Password</h1>

    <p>
        Signed in as
        <strong>
            <?= htmlspecialchars(
                $_SESSION["username"],
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </strong>
    </p>

    <?php if (!empty($errors)): ?>

        <div class="message error-message">

            <strong>The password could not be changed.</strong>

            <ul>
                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </li>

                <?php endforeach; ?>
            </ul>

        </div>

    <?php endif; ?>

    <form method="post">

        <?= csrfField() ?>

        <div class="form-group">

            <label for="current_password">
                Current Password
            </label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required
            >

        </div>

        <div class="form-group">

            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="12"
                maxlength="72"
                autocomplete="new-password"
                required
            >

        </div>

        <div class="form-group">

            <label for="password_confirmation">
                Confirm New Password
            </label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                minlength="12"
                maxlength="72"
                autocomplete="new-password"
                required
            >

        </div>

        <div class="form-actions">

            <button
                class="button button-primary"
                type="submit"
            >
                Change Password
            </button>

            <a
                class="button button-secondary"
                href="index.php"
            >
                Cancel
            </a>

        </div>

    </form>

</main>

</body>
</html>