/**
 * Tailwind is scoped and prefixed so it won’t clash with Bootstrap.
 * - prefix: use `tw-` utilities (e.g., `tw-flex`) to avoid class name conflicts
 * - important: only apply utilities within `#fi-app` container (Filament area)
 * - preflight: disabled to avoid global resets fighting Bootstrap
 */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './vendor/filament/**/*.blade.php',
  ],
  prefix: 'tw-',
  important: '#fi-app',
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {},
  },
  plugins: [],
};

