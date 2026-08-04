<?php

function getCsrfToken(): string
{
    if (
        !isset($_SESSION["csrf_token"]) ||
        !is_string($_SESSION["csrf_token"])
    ) {
        $_SESSION["csrf_token"] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION["csrf_token"];
}

function csrfField(): string
{
    $token = htmlspecialchars(
        getCsrfToken(),
        ENT_QUOTES,
        "UTF-8"
    );

    return '<input type="hidden" name="csrf_token" value="' .
        $token .
        '">';
}

function isValidCsrfToken(?string $submitted_token): bool
{
    if (
        $submitted_token === null ||
        !isset($_SESSION["csrf_token"]) ||
        !is_string($_SESSION["csrf_token"])
    ) {
        return false;
    }

    return hash_equals(
        $_SESSION["csrf_token"],
        $submitted_token
    );
}