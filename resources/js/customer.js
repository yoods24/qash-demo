import { PageFlip } from 'page-flip';

//nav
document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.querySelector("nav");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 1) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const sections = document.querySelectorAll(".scroll-section");
    let index = 0;
    let isScrolling = false;

    const scrollToSection = (i) => {
        if (i < 0 || i >= sections.length) return;
        sections[i].scrollIntoView({ behavior: "smooth" });
        index = i;
    };

    window.addEventListener("wheel", (e) => {
        if (isScrolling) return;

        isScrolling = true;
        if (e.deltaY > 0) {
            scrollToSection(index + 1);
        } else {
            scrollToSection(index - 1);
        }

        setTimeout(() => (isScrolling = false), 1000);
    });
});

// book menu
document.addEventListener("DOMContentLoaded", function () {
    const pageFlip = new PageFlip(document.getElementById('book'), {
        width: 500, // required parameter - base page width
        height: 600, // required parameter - base page height
    });

    pageFlip.loadFromHTML(document.querySelectorAll('.my-page'));
});


