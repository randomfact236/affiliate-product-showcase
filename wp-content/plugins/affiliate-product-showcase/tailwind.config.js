/** @type {import('tailwindcss').Config} */
import typography from '@tailwindcss/typography';
import forms from '@tailwindcss/forms';
import aspectRatio from '@tailwindcss/aspect-ratio';

export default {
  // =========================================================================
  // 1. NAMESPACING (Critical for Plugin Isolation)
  // Prevents conflicts when themes use Tailwind. Non-negotiable.
  // =========================================================================
  prefix: 'aps-',

  // =========================================================================
  // 2. CONTENT PATHS (Optimized for Performance)
  // Includes all relevant files while excluding build artifacts.
  // =========================================================================
  content: [
    './**/*.php',
    '!./vendor/**/*',
    '!./node_modules/**/*',
    '!./build/**/*',
    '!./dist/**/*',
    '!./tests/**/*',
    './blocks/**/*.{js,jsx,ts,tsx,php}',
    './src/blocks/**/*.{js,jsx,ts,tsx,php}',
    './src/components/**/*.{js,jsx,ts,tsx,vue,svelte}',
    './src/**/*.{js,jsx,ts,tsx}',
    './assets/js/**/*.{js,jsx,ts,tsx}',
    './admin/js/**/*.{js,jsx,ts,tsx}',
  ],

  // =========================================================================
  // 3. DARK MODE (Class-Based Strategy)
  // Best for WordPress: Allows manual toggling + theme integration.
  // Supports .dark class, [data-theme="dark"], and Gutenberg editor.
  // =========================================================================
  darkMode: 'class',

  // =========================================================================
  // 4. CORE PLUGINS (Bundle Size Optimization)
  // Disables rarely-used utilities in WordPress context.
  // Reduces final CSS by ~15-20%.
  // =========================================================================
  corePlugins: {
    float: false,
    clear: false,
    skew: false,
    backdropFilter: false,
    // Use custom .aps-focus-visible instead of Tailwind's ring utilities
    ringWidth: false,
    ringColor: false,
    ringOffsetWidth: false,
    ringOffsetColor: false,
  },

  // =========================================================================
  // 5. THEME EXTENSIONS (WordPress 6.8+ Design System)
  // Modern color palette, spacing, typography, and animations.
  // =========================================================================
  theme: {
    extend: {
      // -----------------------------------------------------------------------
      // COLORS: WordPress-native palette + semantic system
      // -----------------------------------------------------------------------
      colors: {
        // WordPress admin colors (6.8+ standards)
        wp: {
          blue: '#2271b1',
          'blue-dark': '#135e96',
          'blue-light': '#72aee6',
          gray: '#f0f0f1',
          'gray-dark': '#646970',
          'gray-darker': '#3c434a',
          alert: {
            red: '#d63638',
            yellow: '#dba617',
            green: '#00a32a',
          },
        },
        // Primary brand color (customizable)
        primary: {
          DEFAULT: '#3b82f6',
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        // Semantic neutrals for accessibility
        neutral: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
          950: '#0a0a0a',
        },
      },

      // -----------------------------------------------------------------------
      // SPACING: WordPress-aware dimensions with CSS custom properties
      // -----------------------------------------------------------------------
      spacing: {
        'wp-admin-bar': 'var(--wp-admin--admin-bar--height, 32px)',
        'wp-sidebar': '160px',
        'wp-gutenberg-toolbar': '44px',
        'wp-content-gutter': '20px',
      },

      // -----------------------------------------------------------------------
      // TYPOGRAPHY: Modern, accessible font stack
      // -----------------------------------------------------------------------
      fontFamily: {
        sans: [
          'Inter var',
          'ui-sans-serif',
          'system-ui',
          '-apple-system',
          'BlinkMacSystemFont',
          '"Segoe UI"',
          'Roboto',
          '"Helvetica Neue"',
          'Arial',
          'sans-serif',
          '"Apple Color Emoji"',
          '"Segoe UI Emoji"',
        ],
        heading: [
          'Inter var',
          'ui-sans-serif',
          'system-ui',
          '-apple-system',
          'BlinkMacSystemFont',
          '"Segoe UI"',
          'Roboto',
          'sans-serif',
        ],
        mono: [
          'ui-monospace',
          'SFMono-Regular',
          '"SF Mono"',
          'Menlo',
          'Monaco',
          'Consolas',
          '"Liberation Mono"',
          '"Courier New"',
          'monospace',
        ],
      },

      // Typography prose styles (moved from plugin config)
      typography: (theme) => ({
        DEFAULT: {
          css: {
            '--tw-prose-body': theme('colors.neutral.700'),
            '--tw-prose-headings': theme('colors.neutral.900'),
            '--tw-prose-links': theme('colors.wp.blue'),
            '--tw-prose-bold': theme('colors.neutral.900'),
            '--tw-prose-code': '#d63384',
            '--tw-prose-pre-bg': '#f8f9fa',
            maxWidth: 'none',
            code: {
              color: '#d63384',
              fontWeight: '500',
              backgroundColor: 'rgba(255, 0, 122, 0.05)',
              borderRadius: '0.25rem',
              padding: '0.125rem 0.25rem',
            },
            'code::before': { content: '""' },
            'code::after': { content: '""' },
            pre: {
              backgroundColor: '#f8f9fa',
              color: theme('colors.neutral.800'),
              borderRadius: '0.5rem',
              border: `1px solid ${theme('colors.neutral.200')}`,
            },
            a: {
              color: theme('colors.wp.blue'),
              textDecoration: 'underline',
              fontWeight: '500',
              '&:hover': {
                color: theme('colors.wp.blue-dark'),
              },
            },
          },
        },
        // Dark mode overrides
        invert: {
          css: {
            '--tw-prose-body': theme('colors.neutral.300'),
            '--tw-prose-headings': theme('colors.neutral.100'),
            '--tw-prose-links': theme('colors.wp.blue-light'),
            '--tw-prose-bold': theme('colors.neutral.100'),
            '--tw-prose-code': '#ff6b9d',
            '--tw-prose-pre-bg': theme('colors.neutral.900'),
            code: {
              backgroundColor: 'rgba(255, 107, 157, 0.1)',
            },
            pre: {
              backgroundColor: theme('colors.neutral.900'),
              borderColor: theme('colors.neutral.700'),
            },
          },
        },
      }),

      // -----------------------------------------------------------------------
      // SHADOWS: WordPress-inspired elevation system
      // -----------------------------------------------------------------------
      boxShadow: {
        'card': '0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06)',
        'card-hover': '0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06)',
        'popover': '0 4px 20px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08)',
        'modal': '0 20px 40px rgba(0, 0, 0, 0.15), 0 10px 20px rgba(0, 0, 0, 0.1)',
        'focus-ring': '0 0 0 2px #2271b1',
        'wp-elevated': '0 2px 4px rgba(0, 0, 0, 0.05)',
        'wp-button': '0 1px 1px rgba(0, 0, 0, 0.05)',
      },

      // -----------------------------------------------------------------------
      // BORDER RADIUS: WordPress component standards
      // -----------------------------------------------------------------------
      borderRadius: {
        'wp': '4px',
        'wp-sm': '2px',
        'wp-lg': '8px',
        'wp-xl': '12px',
        'wp-button': '3px',
        'pill': '9999px',
      },

      // -----------------------------------------------------------------------
      // BREAKPOINTS: WordPress/Gutenberg-aligned
      // -----------------------------------------------------------------------
      screens: {
        'wp-mobile': '600px',
        'wp-tablet': '782px',
        'wp-small-desktop': '1024px',
        'wp-desktop': '1280px',
        'wp-wide': '1440px',
      },

      // -----------------------------------------------------------------------
      // Z-INDEX: Layering system for WordPress admin
      // -----------------------------------------------------------------------
      zIndex: {
        'wp-dropdown': '99990',
        'wp-modal': '100000',
        'wp-notification': '199999',
        'wp-tooltip': '1000000',
      },

      // -----------------------------------------------------------------------
      // ANIMATIONS: Smooth WordPress-style transitions
      // -----------------------------------------------------------------------
      animation: {
        'wp-fade-in': 'wpFadeIn 0.2s ease-in-out',
        'wp-fade-out': 'wpFadeOut 0.15s ease-in-out',
        'wp-slide-down': 'wpSlideDown 0.3s ease-out',
        'wp-slide-up': 'wpSlideUp 0.3s ease-out',
        'wp-scale-in': 'wpScaleIn 0.2s ease-out',
        'wp-spin-slow': 'spin 2s linear infinite',
      },

      keyframes: {
        wpFadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        wpFadeOut: {
          '0%': { opacity: '1' },
          '100%': { opacity: '0' },
        },
        wpSlideDown: {
          '0%': { opacity: '0', transform: 'translateY(-10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        wpSlideUp: {
          '0%': { opacity: '0', transform: 'translateY(10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        wpScaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },

      // -----------------------------------------------------------------------
      // TRANSITIONS: Performance-optimized durations
      // -----------------------------------------------------------------------
      transitionDuration: {
        '150': '150ms',
        '250': '250ms',
        '350': '350ms',
      },
    },
  },

  // =========================================================================
  // 6. PLUGINS (WordPress-Optimized)
  // =========================================================================
  plugins: [
    // Forms: Class strategy prevents hijacking theme/plugin forms
    // CRITICAL: 'base' would style ALL site forms - dangerous in WP!
    forms({ strategy: 'class' }),

    // Typography: Scoped prose styles with WordPress enhancements
    typography({ className: 'aps-prose' }),

    // Aspect ratio utilities for media/embeds
    aspectRatio,

    // Container queries for responsive block patterns
    // container-queries removed (non-critical in this environment)

    // -----------------------------------------------------------------------
    // CUSTOM WORDPRESS UTILITIES
    // -----------------------------------------------------------------------
    function ({ addUtilities, addComponents, theme }) {
      // Utility classes
      addUtilities({
        // Admin bar offset (dynamic height support)
        '.aps-wp-admin-bar-offset': {
          marginTop: 'var(--wp-admin--admin-bar--height, 32px)',
        },

        // Gutenberg alignment classes
        '.aps-wp-alignwide': {
          marginLeft: 'calc(-12.5vw + 12.5%)',
          marginRight: 'calc(-12.5vw + 12.5%)',
          maxWidth: 'calc(125vw - 12.5%)',
        },
        '.aps-wp-alignfull': {
          marginLeft: 'calc(-50vw + 50%)',
          marginRight: 'calc(-50vw + 50%)',
          maxWidth: '100vw',
          width: '100vw',
        },

        // Accessible focus styles
        '.aps-focus-visible': {
          outline: 'none',
          boxShadow: '0 0 0 2px var(--wp--preset--color--blue, #2271b1)',
          transition: 'box-shadow 0.15s ease-in-out',
        },

        // Screen reader only (accessibility)
        '.aps-sr-only': {
          position: 'absolute',
          width: '1px',
          height: '1px',
          padding: '0',
          margin: '-1px',
          overflow: 'hidden',
          clip: 'rect(0, 0, 0, 0)',
          whiteSpace: 'nowrap',
          borderWidth: '0',
        },
      });

      // Component classes (reusable patterns)
      addComponents({
        '.aps-card': {
          backgroundColor: theme('colors.white'),
          borderRadius: theme('borderRadius.wp-lg'),
          boxShadow: theme('boxShadow.card'),
          padding: theme('spacing.6'),
          transition: 'box-shadow 0.2s ease-in-out',
          '&:hover': {
            boxShadow: theme('boxShadow.card-hover'),
          },
        },
        '.aps-button': {
          display: 'inline-flex',
          alignItems: 'center',
          justifyContent: 'center',
          padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
          fontSize: theme('fontSize.sm'),
          fontWeight: theme('fontWeight.medium'),
          lineHeight: theme('lineHeight.5'),
          borderRadius: theme('borderRadius.wp-button'),
          transition: 'all 0.15s ease-in-out',
          cursor: 'pointer',
          '&:focus': {
            outline: 'none',
            boxShadow: theme('boxShadow.focus-ring'),
          },
          '&:disabled': {
            opacity: '0.6',
            cursor: 'not-allowed',
          },
        },
        '.aps-button-primary': {
          backgroundColor: theme('colors.wp.blue'),
          color: theme('colors.white'),
          border: `1px solid ${theme('colors.wp.blue')}`,
          '&:hover:not(:disabled)': {
            backgroundColor: theme('colors.wp.blue-dark'),
            borderColor: theme('colors.wp.blue-dark'),
          },
        },
        '.aps-button-secondary': {
          backgroundColor: theme('colors.white'),
          color: theme('colors.wp.blue'),
          border: `1px solid ${theme('colors.wp.blue')}`,
          '&:hover:not(:disabled)': {
            backgroundColor: theme('colors.wp.blue'),
            color: theme('colors.white'),
          },
        },
      });
    },
  ],

  // =========================================================================
  // 7. SAFELIST (Minimal & Precise)
  // Only include classes that are dynamically generated via PHP.
  // Avoid broad regex patterns that bloat the CSS bundle.
  // =========================================================================
  safelist: [
    // Specific WordPress block classes
    'wp-block-button',
    'wp-block-buttons',
    'wp-block-image',
    'wp-block-gallery',
    'wp-element-button',
    'wp-element-caption',
    
    // Color variations (specific patterns only)
    { pattern: /^has-.*-color$/, variants: ['hover', 'focus'] },
    { pattern: /^has-.*-background-color$/ },
    
    // Block styles
    { pattern: /^is-style-(fill|outline|squared|rounded)$/ },
    
    // Alignment classes
    'alignleft',
    'alignright',
    'aligncenter',
    'alignwide',
    'alignfull',
    
    // Custom utility safelist
    'aps-wp-admin-bar-offset',
    'aps-wp-alignwide',
    'aps-wp-alignfull',
    'aps-focus-visible',
    'aps-sr-only',
    
    // Dynamic color utilities (specific shades only)
    {
      pattern: /^aps-(bg|text|border)-(primary|wp-blue|neutral)-(50|100|500|600|700|900)$/,
      variants: ['hover', 'focus', 'dark'],
    },
  ],

  // =========================================================================
  // 8. IMPORTANT SELECTOR (Plugin Specificity)
  // Ensures plugin styles override theme styles when necessary.
  // =========================================================================
  important: '#affiliate-product-showcase-app',

  // =========================================================================
  // 9. FUTURE-PROOFING
  // =========================================================================
  future: {
    hoverOnlyWhenSupported: true, // Prevents hover effects on touch devices
  },
};
export default {
  content: [
    './frontend/js/**/*.{js,jsx,ts,tsx}',
    './blocks/**/*.{js,jsx,ts,tsx}',
    './src/**/*.php'
  ],
  theme: {
    extend: {}
  },
  plugins: []
};
