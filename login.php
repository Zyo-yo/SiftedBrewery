<?php

session_start();

require __DIR__ . "/includes/connect.php";

$error_message = "";

if (isset($_GET["error"])) {
    if ($_GET["error"] === "authentication_required") {
        $error_message =
            "You must log in before accessing the admin area.";
    } elseif ($_GET["error"] === "account_unavailable") {
        $error_message =
            "Your account is no longer available. Please contact the administrator.";
    } elseif ($_GET["error"] === "credentials_changed") {
        $error_message =
            "Your password was changed. Please log in again.";
    }
}

if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true) {
    header("Location: admin/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error_message = "Username and password are required.";
    } else {
        $query = "
            SELECT
                users.user_id,
                users.username,
                users.password,
                roles.role_name
            FROM users
            INNER JOIN roles
                ON roles.role_id = users.role_id
            WHERE users.username = :username
            LIMIT 1
        ";

        $statement = $db->prepare($query);

        $statement->execute([
            ":username" => $username
        ]);

        $user = $statement->fetch();

        if (
            $user &&
            password_verify($password, $user["password"])
        ) {
            session_regenerate_id(true);

                $_SESSION["authenticated"] = true;
                $_SESSION["user_id"] = (int) $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role_name"];
                $_SESSION["password_signature"] = hash(
                    "sha256",
                    $user["password"]
                );

            header("Location: admin/index.php");
            exit;
        }

        $error_message = "Invalid username or password.";
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

    <title>Admin Login</title>

    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <main class="form-container">
        <h1>Admin Login</h1>
        <?php if ($error_message): ?>
            <p class="error-message">
                <?= htmlspecialchars($error_message) ?>
            </p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>
            <button class="button button-primary" type="submit">Login</button>
            <a href="index.php"><button class="button button-primary" type="button">Home</button></a>
        </form>
    </main>
</body>
</html>