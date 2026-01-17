# Chat Summary Format - Simple Rule

## User Request

**Every message must include:**
1. Your exact message (as "User Request")
2. Which assistant file was used (or "No assistant file is used")

## Format

### Top of Every Summary:
```
## User Request
"[Your exact message]"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)
- ✅ assistant-quality-standards.md (APPLIED)
- ✅ assistant-performance-optimization.md (APPLIED)
```

**OR if no assistant files were used:**
```
## User Request
"[Your exact message]"

## Assistant Files Used
No assistant file is used
```

### Rest of Summary:
- Summary of what was done
- Key points
- Actions taken
- Files modified
- Git commit info
- Status
- Timestamp

## Rules

1. **Every message** gets a summary
2. **Top of summary** shows your message + assistant files used
3. **If no assistant files used:** Write "No assistant file is used"
4. **Same summary** goes to chat box and chat history file
5. **Follow-up questions** update the same chat history file
6. **New chat session** creates new file (Chat-002, Chat-003, etc.)

## Example

### In Chat Box:
```
## User Request
"How do I use the GitFlow setup script?"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)

## Summary
[Summary of how to use the script]

## Key Points
- [Points]

## Actions Taken
- [Actions]

## Files Modified
- [Files]

## Status
✅ Complete
```

### In Chat History File:
```
## User Request
"How do I use the GitFlow setup script?"

## Assistant Files Used
- ✅ assistant-instructions.md (APPLIED)

## Summary
[Summary of how to use the script]

## Key Points
- [Points]

## Actions Taken
- [Actions]

## Files Modified
- [Files]

## Status
✅ Complete
```

## Why This Format

**Simple:**
- Easy to understand
- Easy to follow
- No complex rules

**Clear:**
- Shows what you asked
- Shows what was used
- Shows what was done

**Consistent:**
- Same format every time
- Same place in chat box
- Same place in history file
