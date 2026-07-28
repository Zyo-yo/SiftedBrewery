<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="The Sifted Brewery is a Winnipeg café offering pastries, cakes, drinks, meals, and custom cake orders."
    >

    <title>
    <?= htmlspecialchars(
        $page_title ?? "The Sifted Brewery",
        ENT_QUOTES,
        "UTF-8"
    ) ?>
    </title>

    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
</head>

<body>