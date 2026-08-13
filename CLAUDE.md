# Fantastico VPS Addon — MyAdmin Plugin

Composer package · type `myadmin-plugin` · LGPL-2.1

## Commands

```bash
composer install                        # install deps incl. phpunit ^9.6
vendor/bin/phpunit tests/ -v
vendor/bin/phpunit tests/ -v --coverage-clover coverage.xml --whitelist src/
```

## Structure

- **Plugin class**: `src/Plugin.php` · namespace `Detain\MyAdminVpsFantastico\` · registers hooks via `getHooks()`
- **Addon function**: `src/vps_add_fantastico.php` · procedural, no namespace · loaded via `function_requirements()`
- **Tests**: `tests/PluginTest.php` · `tests/VpsAddFantasticoTest.php` · `tests/FileExistenceTest.php` · namespace `Detain\MyAdminVpsFantastico\Tests\`
- **CI config**: `.scrutinizer.yml` · `.codeclimate.yml` · `.bettercodehub.yml` · `.travis.yml` · `.github/` (CI/CD workflows) · `.idea/` (IDE settings: inspectionProfiles, deployment.xml, encodings.xml)

## Plugin Architecture

Hooks registered in `Plugin::getHooks()`:
```php
'function.requirements' => [__CLASS__, 'getRequirements'],
'vps.load_addons'       => [__CLASS__, 'getAddon'],
'vps.settings'          => [__CLASS__, 'getSettings']
```

Addon lifecycle in `src/Plugin.php`:
- `getRequirements(GenericEvent $event)` — registers `vps_add_fantastico` page requirement pointing to `src/vps_add_fantastico.php`
- `getAddon(GenericEvent $event)` — creates `\AddonHandler`, sets cost via `VPS_FANTASTICO_COST`, registers `doEnable`/`doDisable` callbacks
- `doEnable(\ServiceHandler $serviceOrder, ...)` — calls `activate_fantastico($ip, 2)`, logs via `myadmin_log(self::$module, 'info', ...)`
- `doDisable(\ServiceHandler $serviceOrder, ...)` — sends admin mail via `(new \MyAdmin\Mail())->adminMail(...)` using template `admin/vps_cpanel_canceled.tpl`
- `getSettings(GenericEvent $event)` — registers `vps_fantastico_cost` setting via `$settings->add_text_setting()`

Addon function pattern in `src/vps_add_fantastico.php`:
```php
function vps_add_fantastico() {
    function_requirements('class.AddServiceAddon');
    $addon = new AddServiceAddon();
    $addon->load(__FUNCTION__, 'Fantastico', 'vps', VPS_FANTASTICO_COST);
    $addon->process();
}
```

## Conventions

- PSR-4: `Detain\MyAdminVpsFantastico\` → `src/` · `Detain\MyAdminVpsFantastico\Tests\` → `tests/`
- Indentation: **tabs** (enforced by `.scrutinizer.yml`)
- Properties/params: **camelCase** (`.scrutinizer.yml` `parameters_in_camelcaps`, `properties_in_camelcaps`)
- Constants: **UPPERCASE** (`uppercase_constants: true`)
- Docblocks required on all functions: `@author`, `@package`, `@return`, `@category` — `tests/VpsAddFantasticoTest.php` asserts their presence
- Logging: `myadmin_log(self::$module, $level, $message, __LINE__, __FILE__, self::$module, $serviceId)`
- Settings access: `get_module_settings(self::$module)` returns `PREFIX`, `TABLE`, `TBLNAME`
- Service info access: `$serviceInfo[$settings['PREFIX'].'_ip']`, `$settings['PREFIX'].'_custid'`, etc.
- No PDO — rely on MyAdmin DB helpers from parent platform
- `src/vps_add_fantastico.php` must have **no namespace** (procedural, loaded via `function_requirements()`)

## Dependencies

- `symfony/event-dispatcher ^5.0` — `GenericEvent` for all hook callbacks
- `detain/fantastico-licensing` — provides `activate_fantastico()`
- `detain/myadmin-plugin-installer` — plugin type installer
- `ext-soap` required at runtime

## Plugin contract harness

This package is on the shared contract harness from `detain/myadmin-plugin-installer`.
`tests/ContractTest.php` is **generated** — run `composer myadmin:scaffold-tests` (add
`--force --write` to re-emit it), never hand-edit it.

The harness **executes** the plugin: it defines the bare constants the class body references
and then calls `getHooks()`, `getSettings()`, `getMenu()`, `apiRegister()` and — for
`type=service` packages — the activate/deactivate/change-ip/queue handlers, for real.

**So do not write reflection-only tests for the plugin class.** Asserting a handler exists,
is public, is static and takes one parameter passes whether or not the handler works; three
production bugs in this fleet were sitting behind assertions of exactly that shape. Older
guidance in this repo that says those methods must not be called predates the harness.

The harness is **additive**: it runs alongside this package's existing tests, and nothing is
deleted to make room for it. Run the whole suite, never `--filter ContractTest` alone — the
contract class primes constants and calls `register_module()`, neither of which can be undone.

See the `plugin-contract-tests` skill for the full workflow, and `docs/testing-harness.md` in
the installer.

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
