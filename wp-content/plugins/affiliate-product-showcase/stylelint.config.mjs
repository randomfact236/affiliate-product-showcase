/**
 * Stylelint Configuration for Affiliate Product Showcase
 * Generated: January 12, 2026
 */

export default {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-recommended'
  ],
  
  plugins: [
    'stylelint-order',
    'stylelint-selector-bem-pattern'
  ],
  
  rules: {
    // Tailwind CSS specific rules
    'selector-class-pattern': [
      '^(?:[a-z]+(?:-[a-z0-9]+)*)(?:__(?:[a-z0-9]+(?:-[a-z0-9]+)*))?(?:--(?:[a-z0-9]+(?:-[a-z0-9]+)*))?$',
      {
        message:
          'Expected class name to be kebab-case or BEM (e.g., "block-name__element-name--modifier")'
      }
    ],
    
    // Allow Tailwind's @apply directive
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: ['tailwind', 'apply', 'layer', 'responsive']
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
    
    // Allow arbitrary values (Tailwind)
    'declaration-property-unit-disallowed-list': null,
    
    // Comment rules
    'comment-empty-line-before': null,
    'comment-no-empty': null,
    
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
