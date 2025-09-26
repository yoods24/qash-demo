import { PageFlip } from 'page-flip';
import 'bootstrap'; 


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

document.addEventListener('livewire:init', () => {
    Livewire.on('lock-scroll', () => {
        document.body.classList.add('no-scroll');
        // Initialize CTA observer after modal renders
        setTimeout(() => {
            try { initOptionModalCtaObserver(); } catch (e) { console.warn(e); }
        }, 50);
    });
    Livewire.on('unlock-scroll', () => {
        document.body.classList.remove('no-scroll');
        try {
            if (window.__optionCtaObserver) {
                window.__optionCtaObserver.disconnect();
                window.__optionCtaObserver = null;
            }
        } catch (e) { /* noop */ }
    });
});


function initOptionModalCtaObserver() {
    const wrapper = document.querySelector('#modal-wrapper');
    if (!wrapper) return;
    const hero = wrapper.querySelector('.option-img-container');
    const cta = wrapper.querySelector('.option-cta');
    if (!hero || !cta) return;

    // Ensure visible initially
    cta.classList.remove('hide-cta');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
                cta.classList.add('hide-cta');
            } else {
                cta.classList.remove('hide-cta');
            }
        });
    }, { threshold: [0, 0.25, 0.6, 0.75, 1] });

    observer.observe(hero);
    window.__optionCtaObserver = observer;
}


