import { PageFlip } from 'page-flip';


//nav
document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.querySelector("nav");
    if(navbar) {
    window.addEventListener("scroll", () => {
        if (window.scrollY > 1) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });
    }
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


document.addEventListener('livewire:init', () => {
    Livewire.on('lock-scroll', () => {
        document.body.classList.add('no-scroll');
    });
    Livewire.on('unlock-scroll', () => {
        document.body.classList.remove('no-scroll');
    });
});


// JS to trigger footer slide up
document.addEventListener('DOMContentLoaded', function() {
    const cartFooter = document.querySelector('.cart-footer');
    if(cartFooter) {
        setTimeout(() => {
            cartFooter.classList.add('show');
        }, 100); // small delay for smooth appearance
    }
});

// cart footer 
let lastScrollY = window.scrollY;
let cartFooter = document.querySelector('.cart-footer');
let scrollTimeout;

window.addEventListener('scroll', () => {
    if (!cartFooter) return;

    // detect scroll down
    if (window.scrollY > lastScrollY) {
        cartFooter.classList.add('hide');
    } else {
        cartFooter.classList.remove('hide');
    }

    // clear old timer
    clearTimeout(scrollTimeout);

    // re-show footer after 500ms of no scroll
    scrollTimeout = setTimeout(() => {
        cartFooter.classList.remove('hide');
    }, 1000);

    lastScrollY = window.scrollY;
});

