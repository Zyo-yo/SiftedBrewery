<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/csrf.php";

requireAdmin();

$username = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $password_confirmation =
        $_POST["password_confirmation"] ?? "";

    if (!isValidCsrfToken($_POST["csrf_token"] ?? null)) {
        $errors[] =
            "Your session expired. Please refresh the page and try again.";
    }

    if ($username === "") {
        $errors[] = "The username is required.";
    } elseif (mb_strlen($username) < 3) {
        $errors[] =
            "The username must be at least 3 characters.";
    } elseif (mb_strlen($username) > 100) {
        $errors[] =
            "The username cannot exceed 100 characters.";
    } elseif (
        !preg_match("/^[a-zA-Z0-9_.-]+$/", $username)
    ) {
        $errors[] =
            "The username may only contain letters, numbers, periods, hyphens, and underscores.";
    }

    if (strlen($password) < 12) {
        $errors[] =
            "The password must be at least 12 characters.";
    } elseif (strlen($password) > 72) {
        $errors[] =
            "The password cannot exceed 72 characters.";
    }

    if ($password !== $password_confirmation) {
        $errors[] = "The passwords do not match.";
    }

    if (empty($errors)) {
        $check_query = "
            SELECT user_id
            FROM users
            WHERE username = :username
            LIMIT 1
        ";

        $check_statement = $db->prepare($check_query);

        $check_statement->execute([
            ":username" => $username
        ]);

        if ($check_statement->fetch()) {
            $errors[] = "That username is already in use.";
        }
    }

    if (empty($errors)) {
        $role_query = "
            SELECT role_id
            FROM roles
            WHERE role_name = 'User'
            LIMIT 1
        ";

        $role_statement = $db->prepare($role_query);
        $role_statement->execute();

        $user_role = $role_statement->fetch();

        if (!$user_role) {
            $errors[] = "The User role could not be found.";
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $insert_query = "
            INSERT INTO users
            (
                username,
                password,
                role_id
            )
            VALUES
            (
                :username,
                :password,
                :role_id
            )
        ";

        $insert_statement = $db->prepare($insert_query);

        $insert_statement->execute([
            ":username" => $username,
            ":password" => $password_hash,
            ":role_id" => $user_role["role_id"]
        ]);

        header("Location: users.php?success=created");
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

    <title>Create Account | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>

<body>

<main class="form-container">

    <p class="eyebrow">Administration</p>

    <h1>Create Account</h1>

    <p>
        Create a new user account for the content management system.
    </p>

    <?php if (!empty($errors)): ?>

        <div class="message error-message">

            <strong>The account could not be created.</strong>

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

            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars(
                    $username,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
                minlength="3"
                maxlength="100"
                autocomplete="username"
                required
            >

        </div>

        <div class="form-group">

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
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
                Confirm Password
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
                Create Account
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