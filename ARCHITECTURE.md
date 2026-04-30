# Architecture

This document provides a high-level overview of the codebase for contributors.

## Overview

Laravel Builder modifies PHP files by parsing them into an AST (Abstract Syntax Tree) using [nikic/php-parser](https://github.com/nikic/PHP-Parser), applying a chain of visitors that transform the tree, and then printing the modified tree back to a file while preserving original formatting.

**Pipeline:** Parse file → Add visitors → Traverse AST → Print with format preservation → Save

## How AST Traversal Works

The traverser walks the AST depth-first and calls visitor methods in this order:

1. **`beforeTraverse(nodes)`** — called once before traversal starts.
2. **`enterNode(node)`** — called when a node is first entered (top-down).
3. Recursion into child nodes.
4. **`leaveNode(node)`** — called after all children have been visited (bottom-up).
5. **`afterTraverse(nodes)`** — called once after traversal completes.

When multiple visitors are registered, they jointly process each node in turn before moving on to the next one. All visitors run in order at each stage (`beforeTraverse`, `enterNode`, `leaveNode`, `afterTraverse`) and on each node.

## Builders

Builders are the public entry points. They provide a fluent API for queueing modifications and executing them.

- **`PHPFileBuilder`** — Main builder. Parses the file, collects visitors via chained method calls, and runs them on `save()`.
- **`AppBootstrapBuilder`** — Extends `PHPFileBuilder` for `bootstrap/app.php` files with specialized methods.

Each fluent method (e.g. `setProperty()`, `addImports()`) creates a visitor and adds it to the internal `NodeTraverser`.

## Visitors

All code modifications happen through visitors that traverse the AST. The library uses two independent visitor hierarchies.

### Class-based visitors (`BaseNodeVisitorAbstract`)

For modifying classes, traits, enums, etc. The base class handles:

1. **Parent node matching** — Each visitor declares `$allowedParentNodesTypes` (e.g. `[Class_::class, Trait_::class]`). The base `leaveNode()` finds the matching parent and calls `modify()`.
2. **Polymorphic dispatch** — `modify()` checks which contracts the visitor implements and delegates accordingly.

#### Contracts

- **`UpdateNodeContract`** — The visitor can update an existing node. Requires `shouldUpdateNode(Node): bool` and `updateNode(Node): void`. The base class iterates over child statements and calls `updateNode()` on the first match.
- **`InsertNodeContract`** — The visitor can insert a new node. Requires `getInsertableNode(): Node`. The base class handles positioning (via `NodeInserter`) and empty line insertion.

A visitor may implement both contracts. In that case, update is attempted first — insertion happens only if no existing node matched.

**Bulk insertion visitors** extend `InsertNodesAbstractVisitor` (which extends `BaseNodeVisitorAbstract`) and handle inserting multiple nodes with built-in duplicate filtering.

### App bootstrap visitors (`AbstractAppBootstrapVisitor`)

A separate hierarchy (extends `NodeVisitorAbstract` directly) for `bootstrap/app.php` files, which have no class nodes — only method call chains. These visitors match `MethodCall` nodes.

## Support Utilities

Located in `src/Support/`:

| Class | Purpose |
|-------|---------|
| `NodeInserter` | Determines insertion position based on node type ordering and handles empty line separators |
| `ValueNodeFactory` | Converts PHP values (int, string, array, etc.) into AST node representations |
| `ParentNodeLinker` | Sets parent attributes on AST nodes |
| `NodeValueComparator` | Compares AST nodes against PHP values for equality checks |
| `StatementDuplicateChecker` | Detects whether statements are already present |

## Format Preservation

- **`Printer`** — Extends php-parser's `PrettyPrinter`, uses `printFormatPreserving()` to keep the original file formatting intact.
- **`PreformattedCode`** — Custom AST node that preserves raw user-provided code blocks without reformatting.

## Creating a New Visitor

1. Extend `BaseNodeVisitorAbstract` (or `InsertNodesAbstractVisitor` for bulk insertions).
2. Set `$allowedParentNodesTypes` to the node types your visitor targets.
3. Implement `InsertNodeContract`, `UpdateNodeContract`, or both.
4. Add a corresponding fluent method in `PHPFileBuilder` that creates and registers the visitor.
5. Add fixture files and a test case.
