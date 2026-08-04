<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/csrf.php";

requireAdmin();

$request_input =
    $_SERVER["REQUEST_METHOD"] === "POST"
        ? INPUT_POST
        : INPUT_GET;

$user_id = filter_input(
    $request_input,
    "id",
    FILTER_VALIDATE_INT
);

if (!$user_id) {
    header("Location: users.php?error=invalid_user");
    exit;
}

$query = "
    SELECT
        users.user_id,
        users.username,
        roles.role_name
    FROM users
    INNER JOIN roles
        ON roles.role_id = users.role_id
    WHERE users.user_id = :user_id
    LIMIT 1
";

$statement = $db->prepare($query);

$statement->execute([
    ":user_id" => $user_id
]);

$user = $statement->fetch();

if (!$user) {
    header("Location: users.php?error=user_not_found");
    exit;
}

if ($user["role_name"] === "Admin") {
    header("Location: users.php?error=cannot_reset_admin");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST["new_password"] ?? "";

    $password_confirmation =
        $_POST["password_confirmation"] ?? "";

    if (!isValidCsrfToken($_POST["csrf_token"] ?? null)) {
        $errors[] =
            "Your session expired. Refresh the page and try again.";
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
            ":user_id" => $user_id
        ]);

        header("Location: users.php?success=password_reset");
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

    <title>Reset User Password | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>

<body>

<main class="form-container">

    <p class="eyebrow">
        Account administration
    </p>

    <h1>Reset User Password</h1>

    <p>
        Set a new password for
        <strong>
            <?= htmlspecialchars(
                $user["username"],
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </strong>.
    </p>

    <?php if (!empty($errors)): ?>

        <div class="message error-message">

            <strong>The password could not be reset.</strong>

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

        <input
            type="hidden"
            name="id"
            value="<?= (int) $user["user_id"] ?>"
        >

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

            <small>
                Use at least 12 characters.
            </small>

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
                Reset Password
            </button>

            <a
                class="button button-secondary"
                href="users.php"
            >
                Cancel
            </a>

        </div>

    </form>

</main>

</body>
</html>