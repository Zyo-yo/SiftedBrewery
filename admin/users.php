<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";

requireAdmin();

$query = "
    SELECT
        users.user_id,
        users.username,
        roles.role_name,
        users.created_at,
        users.updated_at
    FROM users
    INNER JOIN roles
        ON roles.role_id = users.role_id
    ORDER BY users.username ASC
";

$statement = $db->prepare($query);
$statement->execute();

$users = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Accounts | The Sifted Brewery</title>

    <link rel="stylesheet" href="../styles.css">
</head>

<body>

<header class="site-header">

    <nav
        class="navigation"
        aria-label="Admin navigation"
    >

        <a class="brand" href="../index.php">

            <span class="brand-mark">
                <img
                    src="../images/sifted.jpg"
                    alt="The Sifted Brewery logo"
                >
            </span>

            <span class="brand-text">
                <strong>The Sifted</strong>
                <small>Brewery Admin</small>
            </span>

        </a>

        <div class="navigation-links admin-navigation">

            <a href="../index.php">View Website</a>

            <a href="index.php">Products</a>

            <a class="active" href="users.php">
                Accounts
            </a>

            <a class="admin-link" href="../logout.php">
                Logout
            </a>

        </div>

    </nav>

</header>

<main class="admin-container">

    <section class="admin-heading">

        <div>

            <p class="eyebrow">
                Administration
            </p>

            <h1>Manage Accounts</h1>

            <p class="admin-description">
                View the accounts that can access the content
                management system.
            </p>

        </div>
        <a
            class="button button-primary"
            href="create-user.php"
        >
            + Create Account
        </a>

    </section>

    <section class="admin-toolbar">
        <?php if (($_GET["success"] ?? "") === "created"): ?>

            <div class="message success-message">
                Account created successfully.
            </div>

        <?php endif; ?>
        <div>
            <strong>
                <?= count($users) ?>
                <?= count($users) === 1 ? "account" : "accounts" ?>
            </strong>
        </div>

    </section>

    <?php if (empty($users)): ?>

        <section class="empty-state">

            <h2>No accounts found</h2>

            <p>
                There are currently no CMS accounts.
            </p>

        </section>

    <?php else: ?>

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Role</th>
                        <th scope="col">Created</th>
                        <th scope="col">Updated</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $user["username"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </strong>

                                <?php if (
                                    (int) $user["user_id"] ===
                                    (int) $_SESSION["user_id"]
                                ): ?>
                                    <span class="status status-available">
                                        You
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $user["role_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    "M j, Y",
                                    strtotime($user["created_at"])
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    "M j, Y",
                                    strtotime($user["updated_at"])
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</main>

</body>
</html>