# CSS Architecture Audit Report

**Generated:** 2026-02-02T20:19:50.253597

## Summary

| Metric | Count |
|--------|-------|
| SCSS Files | 39 |
| Directories | 5 |
| Naming Issues | 0 |
| Deep Nesting (>4) | 0 |
| Max Nesting Depth | 4 |
| Total Imports | 0 |

## File Structure

```
_variables.scss
components\_button-base.scss
components\_button-sizes.scss
components\_button-states.scss
components\_button-variants.scss
components\_buttons.scss
components\_card-base.scss
components\_card-body.scss
components\_card-footer.scss
components\_card-media.scss
components\_card.scss
components\_form-input.scss
components\_form-label.scss
components\_form-select.scss
components\_form-textarea.scss
components\_form-validation.scss
components\_forms.scss
components\_modals.scss
components\_toasts.scss
components\_utilities.scss
layouts\_container.scss
layouts\_flex.scss
layouts\_grid.scss
main.scss
mixins\_breakpoints.scss
mixins\_focus.scss
mixins\_typography.scss
pages\_add-product.scss
pages\_admin-form.scss
pages\_admin-products.scss
pages\_admin.scss
pages\_products.scss
pages\_ribbons.scss
pages\_settings.scss
pages\_tags.scss
utilities\_accessibility.scss
utilities\_colors.scss
utilities\_spacing.scss
utilities\_text.scss
```

## Recommendations

### 4. Variable System

- Define color palette in `_variables.scss`

- Use semantic variable names

- Create spacing scale

- Define typography scale
