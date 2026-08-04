<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require __DIR__ . "/includes/captcha.php";

$captcha_code = getCaptchaCode();

$width = 220;
$height = 70;

$image = imagecreatetruecolor(
    $width,
    $height
);

$background_color = imagecolorallocate(
    $image,
    245,
    237,
    218
);

$text_color = imagecolorallocate(
    $image,
    62,
    42,
    24
);

$noise_color = imagecolorallocate(
    $image,
    151,
    121,
    82
);

imagefilledrectangle(
    $image,
    0,
    0,
    $width,
    $height,
    $background_color
);

/*
 * Add random lines behind the CAPTCHA code.
 */
for ($line = 0; $line < 8; $line++) {
    imageline(
        $image,
        random_int(0, $width),
        random_int(0, $height),
        random_int(0, $width),
        random_int(0, $height),
        $noise_color
    );
}

/*
 * Add random noise dots.
 */
for ($dot = 0; $dot < 250; $dot++) {
    imagesetpixel(
        $image,
        random_int(0, $width - 1),
        random_int(0, $height - 1),
        $noise_color
    );
}

$font = 5;

$text_width =
    imagefontwidth($font) *
    strlen($captcha_code);

$text_height =
    imagefontheight($font);

$text_x =
    (int) (($width - $text_width) / 2);

$text_y =
    (int) (($height - $text_height) / 2);

imagestring(
    $image,
    $font,
    $text_x,
    $text_y,
    $captcha_code,
    $text_color
);

header("Content-Type: image/png");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

imagepng($image);
imagedestroy($image);