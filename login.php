<?php

session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "admin" && $password === "password123") {
        $_SESSION["authenticated"] = true;
        $_SESSION["username"] = $username;

        header("Location: admin/index.php");
        exit;
    }

    $error_message = "Invalid username or password.";
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