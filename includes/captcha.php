<?php

function getCaptchaCode(): string
{
    if (
        !isset($_SESSION["captcha_code"]) ||
        !is_string($_SESSION["captcha_code"])
    ) {
        $characters =
            "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";

        $captcha_code = "";

        for ($index = 0; $index < 6; $index++) {
            $captcha_code .= $characters[
                random_int(
                    0,
                    strlen($characters) - 1
                )
            ];
        }

        $_SESSION["captcha_code"] = $captcha_code;
    }

    return $_SESSION["captcha_code"];
}

function validateCaptchaAnswer(?string $answer): bool
{
    if (
        !isset($_SESSION["captcha_code"]) ||
        !is_string($_SESSION["captcha_code"])
    ) {
        return false;
    }

    $expected_answer = strtoupper(
        $_SESSION["captcha_code"]
    );

    $submitted_answer = strtoupper(
        trim($answer ?? "")
    );

    $is_valid =
        $submitted_answer !== "" &&
        hash_equals(
            $expected_answer,
            $submitted_answer
        );

    /*
     * Consume the CAPTCHA after every attempt.
     * A new image will be generated if validation fails.
     */
    unset($_SESSION["captcha_code"]);

    return $is_valid;
}