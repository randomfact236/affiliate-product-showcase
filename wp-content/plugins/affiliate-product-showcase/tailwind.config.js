/**
 * Enterprise-Grade Tailwind CSS Configuration
 * WordPress Plugin: Affiliate Product Showcase
 *
 * @version 3.0.0
 * @description Production-ready Tailwind config with WordPress compatibility,
 *              namespace isolation, and performance optimization.
 *
 * @package AffiliateProductShowcase
 */

/** @type {import('tailwindcss').Config} */
module.exports = {
  // =========================================================================
  // 1. NAMESPACE ISOLATION (Critical for WordPress)
  // =========================================================================
  prefix: 'aps-',
  important: '.aps-root', // Scopes all utilities to plugin container
  
  // =========================================================================
  // 2. CONTENT PATHS (Optimized for WordPress Plugin Structure)
  // =========================================================================
  content: [
    // Frontend JavaScript/TypeScript
    './frontend/**/*.{js,jsx,ts,tsx,vue}',
    
    // All PHP templates (plugin root + subdirectories)
    './**/*.php',
    
    // Block editor files (if using Gutenberg)
    './blocks/**/*.{js,jsx,php}',
    './src/blocks/**/*.{js,jsx,php}',
    
    // Exclude paths (performance optimization)
    '!./vendor/**/*',
    '!./node_modules/**/*',
    '!./tests/**/*',
    '!./build/**/*',
  ],

  // =========================================================================
  // 3. DARK MODE (WordPress 5.9+ Support)
  // =========================================================================
  darkMode: 'class', // Uses .dark class (controlled by WP theme)

  // =========================================================================
  // 4. THEME CONFIGURATION
  // =========================================================================
  theme: {
    extend: {
      // -----------------------------------------------------------------------
      // COLORS (WordPress-Aligned Palette)
      // -----------------------------------------------------------------------
      colors: {
        // Brand colors
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
        secondary: {
          DEFAULT: '#10b981',
          50: '#ecfdf5',
          100: '#d1fae5',
          200: '#a7f3d0',
          300: '#6ee7b7',
          400: '#34d399',
          500: '#10b981',
          600: '#059669',
          700: '#047857',
          800: '#065f46',
          900: '#064e3b',
          950: '#022c22',
        },
        
        // WordPress admin colors (exact match)
        wp: {
          blue: '#2271b1',
          'blue-dark': '#135e96',
          'blue-light': '#72aee6',
          gray: {
            50: '#f9f9f9',
            100: '#f0f0f1',
            200: '#dcdcde',
            300: '#c3c4c7',
            400: '#a7aaad',
            500: '#8c8f94',
            600: '#646970',
            700: '#50575e',
            800: '#3c434a',
            900: '#2c3338',
            950: '#1d2327',
          },
          success: '#00a32a',
          warning: '#dba617',
          error: '#d63638',
        },
      },

      // -----------------------------------------------------------------------
      // SPACING (WordPress Admin Compatible)
      // -----------------------------------------------------------------------
      spacing: {
        // WordPress-specific spacing
        'wp-sm': '8px',
        'wp-md': '16px',
        'wp-lg': '24px',
        'wp-xl': '32px',
        'wp-2xl': '48px',
        
        // WordPress admin bar height
        'wp-admin-bar': 'var(--wp-admin--admin-bar--height, 32px)',
        
        // WordPress content widths
        'wp-content': '640px',
        'wp-wide': '1140px',
        'wp-full': '100%',
      },

      // -----------------------------------------------------------------------
      // TYPOGRAPHY (Matches WordPress Core)
      // -----------------------------------------------------------------------
      fontFamily: {
        sans: [
          '-apple-system',
          'BlinkMacSystemFont',
          'Segoe UI',
          'Roboto',
          'Oxygen-Sans',
          'Ubuntu',
          'Cantarell',
          'Helvetica Neue',
          'sans-serif',
        ],
        mono: [
          'Menlo',
          'Consolas',
          'Monaco',
          'Liberation Mono',
          'Lucida Console',
          'monospace',
        ],
      },
      
      fontSize: {
        'wp-xs': ['13px', { lineHeight: '1.4' }],
        'wp-sm': ['13px', { lineHeight: '1.4' }],
        'wp-base': ['14px', { lineHeight: '1.4' }],
        'wp-lg': ['18px', { lineHeight: '1.5' }],
        'wp-xl': ['23px', { lineHeight: '1.4' }],
      },

      // -----------------------------------------------------------------------
      // BORDER RADIUS (WordPress Style)
      // -----------------------------------------------------------------------
      borderRadius: {
        'wp': '2px',
        'wp-sm': '2px',
        'wp-md': '4px',
        'wp-lg': '8px',
        'wp-button': '3px',
      },

      // -----------------------------------------------------------------------
      // BOX SHADOWS (WordPress Admin Shadows)
      // -----------------------------------------------------------------------
      boxShadow: {
        'wp': '0 1px 1px rgba(0,0,0,.04)',
        'wp-elevated': '0 2px 4px rgba(0,0,0,.05)',
        'wp-focus': '0 0 0 1px #fff, 0 0 0 3px #2271b1',
        'wp-error': '0 0 0 1px #fff, 0 0 0 3px #d63638',
      },

      // -----------------------------------------------------------------------
      // Z-INDEX (WordPress Layer Stack)
      // -----------------------------------------------------------------------
      zIndex: {
        'wp-dropdown': '99990',
        'wp-sticky': '99999',
        'wp-modal': '100000',
        'wp-notification': '199999',
        'wp-admin-bar': '99999',
      },

      // -----------------------------------------------------------------------
      // BREAKPOINTS (WordPress Responsive Breakpoints)
      // -----------------------------------------------------------------------
      screens: {
        'wp-mobile': '600px',   // WordPress mobile breakpoint
        'wp-tablet': '782px',   // WordPress admin menu breakpoint
        'wp-desktop': '1280px', // WordPress wide screen
      },

      // -----------------------------------------------------------------------
      // ANIMATIONS (WordPress-Style Animations)
      // -----------------------------------------------------------------------
      animation: {
        'wp-fade-in': 'wpFadeIn 0.2s ease-out',
        'wp-slide-in': 'wpSlideIn 0.3s ease-out',
        'wp-scale-in': 'wpScaleIn 0.2s ease-out',
      },
      
      keyframes: {
        wpFadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        wpSlideIn: {
          '0%': { opacity: '0', transform: 'translateY(-8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        wpScaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },

      // -----------------------------------------------------------------------
      // TRANSITIONS (Smooth WordPress Interactions)
      // -----------------------------------------------------------------------
      transitionDuration: {
        'wp': '150ms',
        'wp-slow': '300ms',
      },
    },
  },

  // =========================================================================
  // 5. CORE PLUGINS (Disable Unused Features for Performance)
  // =========================================================================
  corePlugins: {
    preflight: false, // CRITICAL: Disable CSS reset for WordPress compatibility
    
    // Disable rarely used utilities (reduce bundle size)
    backdropBlur: false,
    backdropBrightness: false,
    backdropContrast: false,
    backdropGrayscale: false,
    backdropHueRotate: false,
    backdropInvert: false,
    backdropOpacity: false,
    backdropSaturate: false,
  },

  // =========================================================================
  // 6. PLUGINS
  // =========================================================================
  plugins: [
    // Custom WordPress-specific utilities
    function({ addComponents, theme }) {
      addComponents({
        // WordPress button styles
        '.aps-btn-wp': {
          padding: '6px 12px',
          fontSize: '13px',
          lineHeight: '2.15384615',
          borderRadius: '3px',
          border: '1px solid #2271b1',
          backgroundColor: '#2271b1',
          color: '#fff',
          cursor: 'pointer',
          transition: 'all 150ms ease-in-out',
          '&:hover': {
            backgroundColor: '#135e96',
            borderColor: '#135e96',
          },
          '&:focus': {
            boxShadow: '0 0 0 1px #fff, 0 0 0 3px #2271b1',
            outline: '2px solid transparent',
          },
        },
        
        // WordPress card component
        '.aps-card-wp': {
          backgroundColor: '#fff',
          border: '1px solid #c3c4c7',
          borderRadius: '4px',
          boxShadow: '0 1px 1px rgba(0,0,0,.04)',
          padding: '16px',
        },
        
        // WordPress notice styles
        '.aps-notice-wp': {
          backgroundColor: '#fff',
          border: '1px solid #c3c4c7',
          borderLeftWidth: '4px',
          padding: '12px',
          margin: '16px 0',
          '&.aps-notice-success': {
            borderLeftColor: '#00a32a',
          },
          '&.aps-notice-warning': {
            borderLeftColor: '#dba617',
          },
          '&.aps-notice-error': {
            borderLeftColor: '#d63638',
          },
          '&.aps-notice-info': {
            borderLeftColor: '#2271b1',
          },
        },
      });
    },

    // WordPress-style form components
    function({ addBase, theme }) {
      addBase({
        // Form field styles (WordPress-compatible)
        '.aps-input-wp': {
          padding: '6px 12px',
          fontSize: '14px',
          lineHeight: '1.4',
          border: '1px solid #8c8f94',
          borderRadius: '4px',
          backgroundColor: '#fff',
          transition: 'border-color 150ms ease-in-out',
          '&:focus': {
            borderColor: '#2271b1',
            outline: 'none',
            boxShadow: '0 0 0 1px #2271b1',
          },
        },
        
        // WordPress checkbox styles
        '.aps-checkbox-wp': {
          width: '16px',
          height: '16px',
          border: '1px solid #8c8f94',
          borderRadius: '2px',
          backgroundColor: '#fff',
          cursor: 'pointer',
          '&:checked': {
            backgroundColor: '#2271b1',
            borderColor: '#2271b1',
          },
        },
      });
    },
  ],

  // =========================================================================
  // 7. SAFELIST (Prevent Purging of Dynamic Classes)
  // =========================================================================
  safelist: [
    // Dynamic utility patterns
    {
      pattern: /^aps-(grid-cols|gap|p|m|text|bg|border)-.+$/,
      variants: ['sm', 'md', 'lg', 'hover', 'focus'],
    },
    
    // WordPress notice classes
    'aps-notice-success',
    'aps-notice-warning',
    'aps-notice-error',
    'aps-notice-info',
    
    // Common dynamic classes
    'aps-hidden',
    'aps-block',
    'aps-flex',
    'aps-grid',
  ],

  // =========================================================================
  // 8. FUTURE (Opt-in to New Features)
  // =========================================================================
  future: {
    hoverOnlyWhenSupported: true,
    respectDefaultRingColorOpacity: true,
  },
};
