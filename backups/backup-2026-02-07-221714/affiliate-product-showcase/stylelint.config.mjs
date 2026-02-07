/**
 * Stylelint Configuration for Affiliate Product Showcase
 * Generated: January 12, 2026
 */

export default {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-recommended',
    'stylelint-config-standard-scss'
  ],
  
  plugins: [
    'stylelint-order',
    'stylelint-selector-bem-pattern'
  ],
  
  rules: {
    // Tailwind CSS specific rules - allow BEM and WordPress patterns
    'selector-class-pattern': [
      '^(?:[a-z]+(?:[-_][a-z0-9]+)*)(?:__(?:[a-z0-9]+(?:[-_][a-z0-9]+)*))?(?:--(?:[a-z0-9]+(?:[-_][a-z0-9]+)*))?$',
      {
        message:
          'Expected class name to be kebab-case or BEM (e.g., "block-name__element-name--modifier")'
      }
    ],
    
    // Allow Tailwind's @apply directive and SCSS at-rules
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['tailwind', 'apply', 'layer', 'responsive', 'use', 'forward', 'mixin', 'include', 'function', 'return', 'each', 'for', 'if', 'else', 'extend', 'content', 'warn', 'error', 'debug']
      }
    ],

    'scss/at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['tailwind', 'apply', 'layer', 'responsive', 'use', 'forward', 'mixin', 'include', 'function', 'return', 'each', 'for', 'if', 'else', 'extend', 'content', 'warn', 'error', 'debug']
      }
    ],
    
    // Allow Tailwind theme customization
    'at-rule-no-vendor-prefix': [
      true,
      {
        ignoreAtRules: ['tailwind']
      }
    ],
    
    // WordPress specific overrides
    'declaration-no-important': null,
    'selector-max-id': null,
    'selector-max-type': null,
    
    // Allow calc() for modern browsers
    'function-no-unknown': [
      true,
      {
        ignoreFunctions: ['calc', 'var', 'env', 'min', 'max', 'clamp']
      }
    ],
    
    // Allow CSS custom properties
    'property-no-unknown': [
      true,
      {
        ignoreProperties: ['--.*']
      }
    ],
    
    // Allow vendor prefixes
    'value-no-vendor-prefix': null,
    'property-no-vendor-prefix': null,
    
    // Allow arbitrary values (Tailwind)
    'declaration-property-unit-disallowed-list': null,
    
    // Disable single-line declaration limit for SCSS
    'declaration-block-single-line-max-declarations': null,
    
    // Allow single colon for pseudo-elements (compatibility)
    'selector-pseudo-element-colon-notation': null,
    
    // Allow modern color function notation flexibility
    'color-function-notation': null,
    'alpha-value-notation': null,
    'color-hex-length': null,
    'value-keyword-case': null,
    'media-feature-range-notation': null,
    
    // Comment rules
    'comment-empty-line-before': null,
    'comment-no-empty': null,
    'scss/double-slash-comment-whitespace-inside': null,
    'scss/comment-no-empty': null,
    
    // SCSS-specific rules
    'scss/at-import-partial-extension': null,
    'scss/at-mixin-argumentless-call-parentheses': null,
    'scss/dollar-variable-pattern': null,
    'scss/selector-no-redundant-nesting-selector': null,
    'scss/at-extend-no-missing-placeholder': null,
    'scss/dollar-variable-empty-line-before': null,
    
    // Allow duplicate selectors (for BEM modifiers)
    'no-duplicate-selectors': null,
    
    // Allow keyframe names with aps- prefix
    'keyframes-name-pattern': null,
    
    // Allow descending specificity for BEM patterns
    'no-descending-specificity': null,
    
    // Allow higher precision for calculations
    'number-max-precision': null,
    
    // Allow shorthand property overrides
    'declaration-block-no-shorthand-property-overrides': null,
    
    // Allow rule-empty-line-before flexibility
    'rule-empty-line-before': null,
    
    // Order rules
    'order/order': [
      'custom-properties',
      'declarations'
    ],
    
    'order/properties-alphabetical-order': null
  },
  
  ignoreFiles: [
    '**/node_modules/**',
    '**/dist/**',
    '**/build/**',
    '**/vendor/**',
    '**/*.min.css',
    '**/assets/dist/**'
  ]
};
