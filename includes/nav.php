<?php

$current_page = $current_page ?? "";

?>

<header class="site-header">

    <nav
        class="navigation"
        aria-label="Main navigation"
    >

        <a
            class="brand"
            href="index.php"
            aria-label="The Sifted Brewery home"
        >

            <span class="brand-mark">

                <img
                    src="images/sifted.jpg"
                    alt="The Sifted Brewery logo"
                >

            </span>

            <span class="brand-text">

                <strong>The Sifted</strong>

                <small>Brewery</small>

            </span>

        </a>

        <button
            class="menu-toggle"
            id="menu-toggle"
            type="button"
            aria-label="Open navigation menu"
            aria-controls="navigation-links"
            aria-expanded="false"
        >

            <span></span>
            <span></span>
            <span></span>

        </button>

        <div
            class="navigation-links"
            id="navigation-links"
        >

            <a
                class="<?= $current_page === "home"
                    ? "active"
                    : "" ?>"
                href="index.php"
            >
                Home
            </a>

            <a
                class="<?= $current_page === "menu"
                    ? "active"
                    : "" ?>"
                href="menu.php"
            >
                Menu
            </a>

            <a href="index.php#custom-orders">
                Custom Orders
            </a>

            <a href="index.php#about">
                About
            </a>

            <a href="index.php#contact">
                Contact
            </a>

            <a
                class="admin-link"
                href="admin/index.php"
            >
                Admin
            </a>

        </div>

    </nav>

</header>