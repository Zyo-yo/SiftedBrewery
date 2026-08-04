<?php

$page_title = "Home | The Sifted Brewery";
$current_page = "home";

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/nav.php";
?>

<main>

    <section
        class="hero"
        id="home"
    >

        <div class="hero-content">

            <p class="eyebrow">
                Freshly made in Calamba, Laguna
            </p>

            <h1>
                Small-batch treats, carefully crafted.
            </h1>

            <p class="hero-description">
                Discover handcrafted pastries, specialty drinks,
                custom cakes, and comforting meals made with
                quality ingredients.
            </p>

            <div class="hero-actions">

                <a
                    class="button button-primary"
                    href="menu.php"
                >
                    Explore Our Menu
                </a>

                <a
                    class="button button-light"
                    href="custom-order.php"
                >
                    Start a Custom Order
                </a>

            </div>

        </div>

        <div class="hero-feature">

            <div class="feature-card">

                <span class="feature-label">
                    Featured this week
                </span>

                <div class="feature-image-placeholder">

                    <span aria-hidden="true">
                        🍰
                    </span>

                </div>

                <h2>
                    Strawberry Cream Cake
                </h2>

                <p>
                    Light vanilla cake layered with fresh
                    strawberries and house-made whipped cream.
                </p>

                <a href="menu.php">
                    View featured products
                </a>

            </div>

        </div>

    </section>

    <section class="content-section homepage-menu-section">

        <div class="section-heading">

            <p class="eyebrow">
                Made for every craving
            </p>

            <h2>
                Discover our freshly prepared menu
            </h2>

            <p>
                Explore our available pastries, cakes, specialty
                drinks, and comforting meals on our complete menu
                page.
            </p>

            <a
                class="button button-primary"
                href="menu.php"
            >
                View Full Menu
            </a>

        </div>

    </section>

    <section
        class="custom-order-section"
        id="custom-orders"
    >

        <div class="custom-order-content">

            <p class="eyebrow">
                Made especially for you
            </p>

            <h2>
                Planning something worth celebrating?
            </h2>

            <p>
                Tell us about your event, preferred flavours,
                serving size, and design ideas. You will eventually
                be able to upload an inspiration image directly
                through our custom order form.
            </p>

            <a
                class="button button-light"
                href="#"
            >
                Start a Custom Order
            </a>

        </div>

        <div class="custom-order-details">

            <div>

                <strong>
                    Custom designs
                </strong>

                <span>
                    Created for your occasion
                </span>

            </div>

            <div>

                <strong>
                    Flexible sizes
                </strong>

                <span>
                    Options for small and large events
                </span>

            </div>

            <div>

                <strong>
                    Personal service
                </strong>

                <span>
                    Work directly with our baking team
                </span>

            </div>

        </div>

    </section>

    <section
        class="content-section about-section"
        id="about"
    >

        <div class="about-visual">

            <div class="about-image-placeholder">

                <span aria-hidden="true">
                    🧁
                </span>

                <p>
                    The Sifted Brewery
                </p>

            </div>

        </div>

        <div class="about-content">

            <p class="eyebrow">
                Our story
            </p>

            <h2>
                Baked with care and served with warmth.
            </h2>

            <p>
                The Sifted Brewery is a café and pastry shop
                dedicated to creating memorable food in a welcoming
                space. Our menu combines comforting classics with
                creative seasonal flavours.
            </p>

            <p>
                Whether you are stopping by for coffee, choosing a
                dessert, or planning a custom cake, our goal is to
                make every visit feel special.
            </p>

            <a
                class="text-link"
                href="#contact"
            >
                Learn more about us
            </a>

        </div>

    </section>

    <section
        class="visit-section"
        id="contact"
    >

        <div class="section-heading">

            <p class="eyebrow">
                Come visit us
            </p>

            <h2>
                Visit The Sifted Brewery
            </h2>

        </div>

        <div class="visit-grid">

            <article class="information-card">

                <h3>
                    Location
                </h3>

                <p>
                    Unit 1, Pabs2men Property, Brgy. 3,
                    Chipeco Ave.<br>
                    Calamba, Laguna, Philippines, 4027
                </p>

            </article>

            <article class="information-card">

                <h3>
                    Business Hours
                </h3>

                <p>
                    Monday–Friday: 8:00 AM–7:00 PM<br>
                    Saturday–Sunday: 9:00 AM–6:00 PM
                </p>

            </article>

            <article class="information-card">

                <h3>
                    Contact
                </h3>

                <p>
                    hello@siftedbrewery.example<br>
                    +63 916 390 7048
                </p>

            </article>

        </div>

    </section>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>