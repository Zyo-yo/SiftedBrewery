"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.querySelector("#menu-toggle");
    const navigationLinks = document.querySelector("#navigation-links");

    if (!menuToggle || !navigationLinks) {
        return;
    }

    const closeMenu = () => {
        menuToggle.classList.remove("open");
        navigationLinks.classList.remove("open");
        document.body.classList.remove("menu-open");

        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute(
            "aria-label",
            "Open navigation menu"
        );
    };

    const openMenu = () => {
        menuToggle.classList.add("open");
        navigationLinks.classList.add("open");
        document.body.classList.add("menu-open");

        menuToggle.setAttribute("aria-expanded", "true");
        menuToggle.setAttribute(
            "aria-label",
            "Close navigation menu"
        );
    };

    menuToggle.addEventListener("click", () => {
        const isOpen = navigationLinks.classList.contains("open");

        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    navigationLinks.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth > 800) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeMenu();
        }
    });
});