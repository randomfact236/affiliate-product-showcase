# Test Files Debug Analysis

**Files Analyzed:**
- `wp-content/plugins/affiliate-product-showcase/tests/tools/compress.test.ts`
- `wp-content/plugins/affiliate-product-showcase/tests/tools/generate-sri.test.ts`

---

## Issues Identified

### Critical Issue: ES Module Mocking Problem ❌

**Problem:** Both test files use top-level `await import()` which breaks mock setup in ES modules

**Root Cause:**
```typescript
// Test file (compress.test.ts & generate-sri.test.ts)
vi.mock('fs', async () => { /* mock setup */ });

// Top-level await - this breaks the mock!
const { walk, shouldSkip, ... } = await import('../../tools/compress.ts');
```

**Why This Fails:**
1. ES modules evaluate synchronously
2. Top-level `await import()` happens AFTER mock setup but with a new context
3. The mock is not properly applied to the imported module
4. Test assertions use `vi.mocked(fs)` which may not capture actual calls

**Impact:**
- Mock functions may not be called
- Tests might fail or behave unpredictably
- Test coverage is compromised

---

### Secondary Issue: Mock Reference Problem ⚠️

**Problem:** `vi.mocked(fs)` may not work correctly with the mock setup

**Code:**
```typescript
vi.mock('fs', async () => {
  const actual = await vi.importActual('fs');
  return {
    ...actual,
    promises: {
      readdir: vi.fn(),
      readFile: vi.fn(),
      // ...
    },
  };
});
```

**Then in tests:**
```typescript
vi.mocked(fs.readdir).mockResolvedValue([...]);
```

**Issue:**
- The mock creates new `vi.fn()` instances
- When the module imports, it gets a reference to these instances
- But `vi.mocked(fs)` may not correctly track these instances
- This leads to assertions failing

---

### Additional Issue: Unused Imports ⚠️

**compress.test.ts:**
```typescript
import { brotliCompressSync, gzipSync } from 'zlib';
```

**generate-sri.test.ts:**
```typescript
import { brotliCompressSync, gzipSync } from 'zlib';
```

**Problem:** These are imported but never used in the tests
- The implementation uses them, but tests mock fs so they don't need them
- This causes linting warnings

---

## Recommended Solutions

### Solution 1: Refactor Source Files for Testability (RECOMMENDED) ✅

**Approach:** Add dependency injection to source files

**compress.ts changes:**
```typescript
// Add this at the top
import { promises as fs } from 'fs';

// Add a context object that can be overridden in tests
export const fsContext = {
  readdir: fs.readdir,
  readFile: fs.readFile,
  writeFile: fs.writeFile,
  stat: fs.stat,
  access: fs.access,
};

// Replace all fs.* calls with fsContext.*
// e.g., await fs.readdir(dir) becomes await fsContext.readdir(dir)
```

**Test file changes:**
```typescript
import { fsContext } from '../../tools/compress';

// Mock fsContext directly
vi.mock('../../tools/compress', async () => {
  const actual = await vi.importActual('../../tools/compress.ts');
  return {
    ...actual,
    fsContext: {
      readdir: vi.fn(),
      readFile: vi.fn(),
      writeFile: vi.fn(),
      stat: vi.fn(),
      access: vi.fn(),
    },
  };
});
```

**Benefits:**
- ✅ Proper mocking in ES modules
- ✅ Clear separation of concerns
- ✅ Better testability
- ✅ No top-level await needed

---

### Solution 2: Use vi.doMock with Dynamic Imports

**Approach:** Use dynamic mocking with inline imports

**Test file changes:**
```typescript
// Remove top-level imports
// Remove vi.mock('fs') at top level

// In each test, do dynamic import with mock
describe('compress.ts', () => {
  beforeEach(async () => {
    // Set up mocks
    vi.doMock('fs', () => ({
      promises: {
        readdir: vi.fn(),
        readFile: vi.fn(),
        writeFile: vi.fn(),
        stat: vi.fn(),
        access: vi.fn(),
      },
    }));

    // Clear module cache
    vi.resetModules();

    // Import fresh module with mocks
    return import('../../tools/compress.ts');
  });

  it('should work correctly', async () => {
    const { walk, ... } = await import('../../tools/compress.ts');
    // Test code
  });
});
```

**Benefits:**
- ✅ Works with ES modules
- ✅ No source code changes needed

**Drawbacks:**
- ❌ More complex test setup
- ❌ Performance overhead
- ❌ Harder to maintain

---

### Solution 3: Remove Unused Imports

**Simple fix:** Remove unused zlib imports from test files

```typescript
// Remove these lines
import { brotliCompressSync, gzipSync } from 'zlib';
```

**Benefits:**
- ✅ Removes linting warnings
- ✅ Cleaner code

---

## Recommended Implementation

**Best Approach:** Solution 1 (Refactor source files for testability)

**Reasons:**
1. Better long-term maintainability
2. Clear separation of concerns
3. Standard practice for testing external dependencies
4. Works reliably with ES modules
5. Makes code more flexible

**Priority:**
1. **HIGH:** Refactor compress.ts and generate-sri.ts to use fsContext
2. **HIGH:** Update test files to mock fsContext instead of fs
3. **MEDIUM:** Remove unused imports from test files
4. **LOW:** Update documentation to reflect testing approach

---

## Files to Modify

### Source Files (Refactoring):
1. `wp-content/plugins/affiliate-product-showcase/tools/compress.ts`
2. `wp-content/plugins/affiliate-product-showcase/tools/generate-sri.ts`

### Test Files (Updating):
1. `wp-content/plugins/affiliate-product-showcase/tests/tools/compress.test.ts`
2. `wp-content/plugins/affiliate-product-showcase/tests/tools/generate-sri.test.ts`

---

## Testing Checklist

After fixes:
- [ ] All tests run successfully
- [ ] Mock functions are properly called
- [ ] Test assertions pass
- [ ] No linting errors
- [ ] Source files still work in production
- [ ] Build process unaffected

---

**Analysis Date:** 2026-01-16
**Status:** Issues identified, solutions proposed
**Next Step:** Implement Solution 1 (Refactor for testability)
