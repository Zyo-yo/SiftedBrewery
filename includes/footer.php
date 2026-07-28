<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <span class="brand-mark footer-logo"><img src="images/sifted.jpg"></span>

            <div>
                <strong>The Sifted Brewery</strong>
                <p>Freshly made in Calamba Laguna.</p>
            </div>
        </div>

        <div class="footer-links">
            <a href="index.php">Home</a>
            <a
            class="<?= $current_page === "menu"
                ? "active"
                : "" ?>"
            href="menu.php"
            >
                Menu
            </a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </div>

        <div class="footer-contact">
            <strong>Stay connected</strong>
            <p>Instagram · Facebook · TikTok</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>
            &copy; 2026 The Sifted Brewery. All rights reserved. Red River College Polytechnic.
            © Cyrus Gatus.
        </p>

        <a href="admin/index.php">Staff Login</a>
    </div>
</footer>

</body>
</html>