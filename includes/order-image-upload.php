<?php

function saveOrderImage(array $file): array
{
    if (
        !isset($file["error"]) ||
        $file["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => null
        ];
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "The inspiration image could not be uploaded."
        ];
    }

    if (($file["size"] ?? 0) > 2 * 1024 * 1024) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "The inspiration image must be 2 MB or smaller."
        ];
    }

    $temporary_path = $file["tmp_name"] ?? "";

    if (
        $temporary_path === "" ||
        !is_uploaded_file($temporary_path)
    ) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "The uploaded image is invalid."
        ];
    }

    $allowed_types = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $file_information = finfo_open(
        FILEINFO_MIME_TYPE
    );

    if ($file_information === false) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "The uploaded image could not be validated."
        ];
    }

    $mime_type = finfo_file(
        $file_information,
        $temporary_path
    );

    finfo_close($file_information);

    if (
        !is_string($mime_type) ||
        !array_key_exists($mime_type, $allowed_types) ||
        getimagesize($temporary_path) === false
    ) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "Please upload a valid JPG, PNG, or WebP image."
        ];
    }

    $upload_directory =
        dirname(__DIR__) . "/images/orders/";

    if (
        !is_dir($upload_directory) &&
        !mkdir($upload_directory, 0755, true)
    ) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "The image upload folder could not be created."
        ];
    }

    $extension = $allowed_types[$mime_type];

    $filename =
        "order_" .
        bin2hex(random_bytes(16)) .
        "." .
        $extension;

    $absolute_path =
        $upload_directory . $filename;

    if (
        !move_uploaded_file(
            $temporary_path,
            $absolute_path
        )
    ) {
        return [
            "relative_path" => null,
            "absolute_path" => null,
            "error" => "The inspiration image could not be saved."
        ];
    }

    return [
        "relative_path" => "images/orders/" . $filename,
        "absolute_path" => $absolute_path,
        "error" => null
    ];
}