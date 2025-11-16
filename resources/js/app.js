import * as bootstrap from 'bootstrap';
import Lenis from 'lenis';


// Initialize Lenis smooth scrolling
const lenis = new Lenis();

// Use requestAnimationFrame to continuously update the scroll
function raf(time) {
  lenis.raf(time);
  requestAnimationFrame(raf);
}

requestAnimationFrame(raf);
// Expose Bootstrap to global scope for inline scripts using window.bootstrap
window.bootstrap = bootstrap;
import.meta.glob([
    '../fonts/**'
])
import { GridStack } from 'gridstack';
// IMPORTANT: You might also need the styles
import 'gridstack/dist/gridstack.min.css';
window.GridStack = GridStack;
