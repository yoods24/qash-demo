import 'bootstrap';
import Lenis from 'lenis';

// Initialize Lenis smooth scrolling
const lenis = new Lenis({
    autoRaf: true
});


//nav
document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.querySelector(".customer-navbar");
    const toggle = document.getElementById("hamburgToggle");
    const menu = document.getElementById("customerNavMenu");
    const aboutModal = document.getElementById("sideModal");
    const menuCategoryButtons = document.querySelectorAll("[data-menu-category]");
    const menuPanels = document.querySelectorAll("[data-menu-panel]");

    if (toggle && navbar && menu) {
        const closeMenu = () => {
            navbar.classList.remove("menu-open");
            toggle.setAttribute("aria-expanded", "false");
        };

        toggle.addEventListener("click", () => {
            const isOpen = navbar.classList.toggle("menu-open");
            toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });

        menu.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                closeMenu();
            });
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        });
    }

    const stickyTarget = document.querySelector("nav");
    if (stickyTarget) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 1) {
                stickyTarget.classList.add("scrolled");
            } else {
                stickyTarget.classList.remove("scrolled");
            }
        });
    }

    // Body lock for About (side) modal
    if (aboutModal) {
        aboutModal.addEventListener("shown.bs.modal", () => {
            isAboutModalOpen = true;
            document.body.classList.add("no-scroll");
        });

        aboutModal.addEventListener("hidden.bs.modal", () => {
            isAboutModalOpen = false;
            document.body.classList.remove("no-scroll");
        });
    }

    // Customer menu category switching
    if (menuCategoryButtons.length && menuPanels.length) {
        menuCategoryButtons.forEach((button) => {
            button.addEventListener("click", () => {
                const id = button.getAttribute("data-menu-category");

                menuCategoryButtons.forEach((btn) => {
                    btn.classList.toggle("active", btn === button);
                });

                menuPanels.forEach((panel) => {
                    const panelId = panel.getAttribute("data-menu-panel");
                    panel.classList.toggle("active", panelId === id);
                });
            });
        });
    }

    // Menu book + slider for customer menu page
    try {
        initMenuCategorySplide();
    } catch (e) {
        console.warn(e);
    }
});


document.addEventListener("DOMContentLoaded", () => {
    const sections = document.querySelectorAll(".scroll-section");

    // Only enable section snapping if there are sections defined
    if (!sections.length) {
        return;
    }

    let index = 0;
    let isScrolling = false;

    const scrollToSection = (i) => {
        if (i < 0 || i >= sections.length) return;
        sections[i].scrollIntoView({ behavior: "smooth" });
        index = i;
    };

    window.addEventListener("wheel", (e) => {
        if (isAboutModalOpen || isScrolling) return;

        isScrolling = true;
        if (e.deltaY > 0) {
            scrollToSection(index + 1);
        } else {
            scrollToSection(index - 1);
        }

        setTimeout(() => (isScrolling = false), 1000);
    }, { passive: true });
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