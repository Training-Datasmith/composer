# Architecture: composer

## Purpose

A Magento-specific Composer command wrapper that extends Composer's CLI with Magento-aware commands. It provides a `require-update-dry-run` command to safely preview dependency changes and an `info` command with Magento-specific output, wrapped in a Magento Composer application shell.

## Directory Structure

```
src/
  Magento_Composer_Application.php    — Custom Application subclass: registers Magento commands into Composer's CLI
  Require_Update_Dry_Run_Command.php  — Command: runs composer require + update in dry-run mode, reporting changes without applying them
  Info_Command.php                    — Command: enhanced package info with Magento-specific metadata
  Console_Array_Input_Factory.php     — Factory: builds Symfony Console ArrayInput from raw argument arrays for programmatic invocation
```

## Key Design Decisions

- **Extends Composer's Application** — `Magento_Composer_Application` composes Composer's standard Application rather than replacing it, inheriting all standard commands while adding Magento-specific ones.
- **Dry-run preview** — `Require_Update_Dry_Run_Command` runs the resolver without writing `composer.lock` or downloading packages, allowing CI pipelines to preview dependency changes safely.
- **Programmatic invocation** — `Console_Array_Input_Factory` enables other Magento components to invoke Composer commands programmatically without spawning a subprocess.

## Extension Points

- Register additional custom commands in `Magento_Composer_Application::getDefaultCommands()`.

## Dependency Flow

```
Magento_Composer_Application (Composer CLI)
  └── Require_Update_Dry_Run_Command / Info_Command
        └── Console_Array_Input_Factory (builds Input objects)
              └── Composer internals (dependency resolver, downloader)
```
