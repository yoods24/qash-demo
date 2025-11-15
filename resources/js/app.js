import * as bootstrap from 'bootstrap';
// Expose Bootstrap to global scope for inline scripts using window.bootstrap
window.bootstrap = bootstrap;
import.meta.glob([
    '../fonts/**'
])
import { GridStack } from 'gridstack';
// IMPORTANT: You might also need the styles
import 'gridstack/dist/gridstack.min.css';
window.GridStack = GridStack;
