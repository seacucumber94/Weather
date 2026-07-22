---
description: A description of your rule
---

IMPORTANT FILESYSTEM RULES

You are working inside an existing VS Code workspace.

- NEVER use absolute Windows paths (e.g. E:\..., C:\..., D:\...).
- NEVER prepend the workspace path to another absolute path.
- ALWAYS treat the currently opened VS Code workspace as the project root.
- ALL file operations must use workspace-relative paths only.
- Examples:
    GOOD: src/App.tsx
    GOOD: public/index.html
    GOOD: config/settings.json
    BAD: E:\My Project\src\App.tsx
    BAD: C:\xampp\htdocs\project\src\App.tsx

Before creating, editing or deleting a file:
1. Resolve the path relative to the current workspace.
2. Verify the parent directory exists.
3. Never call mkdir using an absolute drive path.
4. Never generate paths containing duplicated drive letters such as:
   E:\Project\E:\
   C:\Repo\C:\

If a requested file cannot be found, ask for the correct relative path instead of inventing an absolute path.

Only edit files that exist inside the currently opened workspace.