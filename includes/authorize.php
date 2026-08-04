<?php

function isAdmin(): bool
{
    return
        isset($_SESSION["role"]) &&
        $_SESSION["role"] === "Admin";
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        header(
            "Location: ../admin/index.php?error=admin_required"
        );
        exit;
    }
}