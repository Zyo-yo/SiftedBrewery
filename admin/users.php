<?php

require __DIR__ . "/../includes/authenticate.php";
require __DIR__ . "/../includes/authorize.php";
require __DIR__ . "/../includes/connect.php";
require __DIR__ . "/../includes/csrf.php";

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

            <a href="../index.php">
                View Website
            </a>

            <a href="index.php">
                Products
            </a>

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
                View and manage the accounts that can access the
                content management system.
            </p>

        </div>

        <a
            class="button button-primary"
            href="create-user.php"
        >
            + Create Account
        </a>

    </section>

    <?php if (($_GET["success"] ?? "") === "created"): ?>

        <div class="message success-message">
            Account created successfully.
        </div>

    <?php elseif (($_GET["success"] ?? "") === "deleted"): ?>

        <div class="message success-message">
            Account deleted successfully.
        </div>

    <?php elseif (($_GET["success"] ?? "") === "password_reset"): ?>

        <div class="message success-message">
            User password reset successfully.
        </div>

    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>

        <div class="message error-message">

            <?php if ($_GET["error"] === "cannot_delete_admin"): ?>

                The main Admin account cannot be deleted.

            <?php elseif ($_GET["error"] === "cannot_delete_self"): ?>

                You cannot delete your own account.

            <?php elseif ($_GET["error"] === "user_not_found"): ?>

                The selected account could not be found.

            <?php elseif ($_GET["error"] === "invalid_request"): ?>

                The request expired or was invalid. Please try again.

            <?php elseif ($_GET["error"] === "invalid_user"): ?>

                The selected account is invalid.

            <?php else: ?>

                The account could not be deleted.

            <?php endif; ?>

        </div>

    <?php endif; ?>

    <section class="admin-toolbar">

        <div>
            <strong>
                <?= count($users) ?>
                <?= count($users) === 1
                    ? "account"
                    : "accounts" ?>
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
                        <th scope="col">Actions</th>
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
                        <td>

                            <?php if (
                                $user["role_name"] !== "Admin"
                            ): ?>

                                <div class="table-actions">

                                    <a
                                        class="edit-link"
                                        href="reset-user-password.php?id=<?= (int) $user["user_id"] ?>"
                                    >
                                        Reset Password
                                    </a>

                                    <form
                                        method="post"
                                        action="delete-user.php"
                                        onsubmit="return confirm('Are you sure you want to delete this account?');"
                                    >

                                        <?= csrfField() ?>

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int) $user["user_id"] ?>"
                                        >

                                        <button
                                            class="delete-link"
                                            type="submit"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            <?php else: ?>

                                <span class="status status-available">
                                    Protected
                                </span>

                            <?php endif; ?>

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