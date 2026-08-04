<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require __DIR__ . "/includes/connect.php";
require __DIR__ . "/includes/csrf.php";
require __DIR__ . "/includes/order-image-upload.php";

$page_title = "Custom Order | The Sifted Brewery";
$current_page = "custom-order";

$customer_name = "";
$email = "";
$phone = "";
$order_details = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_name = trim(
        $_POST["customer_name"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    $phone = trim(
        $_POST["phone"] ?? ""
    );

    $order_details = trim(
        $_POST["order_details"] ?? ""
    );

    if (!isValidCsrfToken($_POST["csrf_token"] ?? null)) {
        $errors[] =
            "Your session expired. Refresh the page and try again.";
    }

    if ($customer_name === "") {
        $errors[] = "Your name is required.";
    } elseif (mb_strlen($customer_name) > 150) {
        $errors[] =
            "Your name cannot exceed 150 characters.";
    }

    if ($email === "") {
        $errors[] = "Your email address is required.";
    } elseif (mb_strlen($email) > 255) {
        $errors[] =
            "Your email address cannot exceed 255 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] =
            "Please enter a valid email address.";
    }

    if (mb_strlen($phone) > 30) {
        $errors[] =
            "Your phone number cannot exceed 30 characters.";
    } elseif (
        $phone !== "" &&
        !preg_match("/^[0-9+().\s-]+$/", $phone)
    ) {
        $errors[] =
            "Please enter a valid phone number.";
    }

    if ($order_details === "") {
        $errors[] =
            "Please describe your custom order.";
    } elseif (mb_strlen($order_details) < 20) {
        $errors[] =
            "Please provide at least 20 characters of order details.";
    } elseif (mb_strlen($order_details) > 5000) {
        $errors[] =
            "Order details cannot exceed 5,000 characters.";
    }

    $inspiration_image = null;
    $saved_image_path = null;

    if (empty($errors)) {
        $upload_result = saveOrderImage(
            $_FILES["inspiration_image"] ?? []
        );

        if ($upload_result["error"] !== null) {
            $errors[] = $upload_result["error"];
        } else {
            $inspiration_image =
                $upload_result["relative_path"];

            $saved_image_path =
                $upload_result["absolute_path"];
        }
    }

    if (empty($errors)) {
        $query = "
            INSERT INTO orders
            (
                customer_name,
                email,
                phone,
                order_details,
                inspiration_image,
                status
            )
            VALUES
            (
                :customer_name,
                :email,
                :phone,
                :order_details,
                :inspiration_image,
                'Pending'
            )
        ";

        $statement = $db->prepare($query);

        try {
            $statement->execute([
                ":customer_name" => $customer_name,
                ":email" => $email,
                ":phone" => $phone !== "" ? $phone : null,
                ":order_details" => $order_details,
                ":inspiration_image" => $inspiration_image
            ]);

            header(
                "Location: custom-order.php?success=submitted"
            );
            exit;
        } catch (PDOException $exception) {
            if (
                $saved_image_path !== null &&
                is_file($saved_image_path)
            ) {
                unlink($saved_image_path);
            }

            $errors[] =
                "Your request could not be saved. Please try again.";
        }
    }
}

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/nav.php";
?>

<main>

    <section class="form-container">

        <p class="eyebrow">
            Made especially for you
        </p>

        <h1>Request a Custom Order</h1>

        <p>
            Tell us about your event, preferred flavours,
            serving size, date, and design ideas. Our team will
            contact you to discuss availability and pricing.
        </p>

        <?php if (
            ($_GET["success"] ?? "") === "submitted"
        ): ?>

            <div class="message success-message">

                <strong>
                    Your request was submitted successfully.
                </strong>

                <p>
                    Our team will contact you using the information
                    you provided.
                </p>

            </div>

        <?php endif; ?>

        <?php if (!empty($errors)): ?>

            <div class="message error-message">

                <strong>
                    Your request could not be submitted.
                </strong>

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <form
            method="post"
            enctype="multipart/form-data"
        >

            <?= csrfField() ?>

            <div class="form-row">

                <div class="form-group">

                    <label for="customer_name">
                        Name
                    </label>

                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        value="<?= htmlspecialchars(
                            $customer_name,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        maxlength="150"
                        autocomplete="name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars(
                            $email,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >

                </div>

            </div>

            <div class="form-group">

                <label for="phone">
                    Phone
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?= htmlspecialchars(
                        $phone,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    maxlength="30"
                    autocomplete="tel"
                >

                <small>
                    Optional
                </small>

            </div>

            <div class="form-group">

                <label for="order_details">
                    Order Details
                </label>

                <textarea
                    id="order_details"
                    name="order_details"
                    minlength="20"
                    maxlength="5000"
                    required
                ><?= htmlspecialchars(
                    $order_details,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?></textarea>

                <small>
                    Include the occasion, preferred date,
                    flavours, serving size, and design ideas.
                </small>

            </div>
            <div class="form-group">

                <label for="inspiration_image">
                    Inspiration Image
                </label>

                <input
                    type="file"
                    id="inspiration_image"
                    name="inspiration_image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Optional. Upload a JPG, PNG, or WebP image.
                    Maximum size: 2 MB.
                </small>

            </div>

            <div class="form-actions">

                <button
                    class="button button-primary"
                    type="submit"
                >
                    Submit Request
                </button>

                <a
                    class="button button-secondary"
                    href="index.php"
                >
                    Cancel
                </a>

            </div>

        </form>

    </section>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>