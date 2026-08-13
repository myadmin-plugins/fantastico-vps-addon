---
name: addon-lifecycle
description: Implements or modifies enable/disable/settings methods in src/Plugin.php for MyAdmin VPS addon plugins. Use when user says 'enable logic', 'deactivate addon', 'add setting', 'activation flow', or changes doEnable/doDisable/getSettings. Covers AddonHandler fluent setup, get_module_settings, myadmin_log, and admin mail via \MyAdmin\Mail. Do NOT use for adding new hooks to getHooks() or creating new Plugin classes from scratch. For a plugin's contract or behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
---
<!-- myadmin-contract-harness-notice -->
> ### ⚠️ Read this before the rest of the file
>
> This package is on the **shared plugin contract harness**. Parts of the guidance below
> predate it and are now wrong in one specific way:
>
> **Any instruction here that a plugin's `getHooks()` / `getSettings()` / `getActivate()` /
> `getDeactivate()` / `getQueue()` must not be *called* — that only its existence, visibility
> or parameter count may be checked through `ReflectionClass` — no longer applies.** That rule
> existed because those methods reference bare constants (`PRORATE_BILLING` and friends) that
> only a live MyAdmin request defines, so calling them from a test used to fatal. The harness
> defines them first. It then executes the handlers for real, in a process of its own.
>
> A reflection-only assertion passes whether or not the thing works: `getActivate()` can exist,
> be public, be static, take one argument, and still fatal the moment it runs. Three real
> production bugs in this fleet were sitting behind assertions of exactly that shape.
>
> **Use the `plugin-contract-tests` skill** for anything touching `tests/ContractTest.php`,
> the contract inspectors, or `composer myadmin:scaffold-tests`.
>
> **Everything else in this file is still accurate and still applies** — this package's own
> classes, its API wrappers, its fixtures, its bootstrap, and the reasons certain classes must
> not be constructed. Nothing below has been removed.

# Addon Lifecycle

## Critical

- All methods in `src/Plugin.php` must be `public static` — no private/protected methods allowed (tests assert this)
- Every method requires a docblock with `@param` and `@return` tags — `tests/PluginTest.php` verifies method signatures via reflection
- Use tabs for indentation — enforced by `.scrutinizer.yml` (`indentation: spaces: false`)
- Always use `self::$module` (never hardcode `'vps'`) so the module reference stays consistent
- Never use PDO — use `get_module_settings()` to get PREFIX/TABLE/TBLNAME for DB key construction
- Service field access pattern: `$serviceInfo[$settings['PREFIX'].'_ip']`, `$serviceInfo[$settings['PREFIX'].'_id']`, etc.

## Instructions

### 1. Set up `doEnable(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)`

Retrieve service info and settings first:
```php
$serviceInfo = $serviceOrder->getServiceInfo();
$settings = get_module_settings(self::$module);
```
Log activation before any external call:
```php
myadmin_log(self::$module, 'info', self::$name.' Activation', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
```
Call the activation function (load it first via `function_requirements`):
```php
function_requirements('activate_fantastico');
activate_fantastico($serviceInfo[$settings['PREFIX'].'_ip'], 2);
```
Record history via `$GLOBALS['tf']->history->add()`:
```php
$GLOBALS['tf']->history->add($settings['TABLE'], 'add_fantastico', $serviceInfo[$settings['PREFIX'].'_id'], $serviceInfo[$settings['PREFIX'].'_ip'], $serviceInfo[$settings['PREFIX'].'_custid']);
```
Verify: `$settings['PREFIX']`, `$settings['TABLE']` are non-empty before proceeding.

### 2. Set up `doDisable(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)`

Retrieve service info and settings:
```php
$serviceInfo = $serviceOrder->getServiceInfo();
$settings = get_module_settings(self::$module);
```
Log deactivation:
```php
myadmin_log(self::$module, 'info', self::$name.' Deactivation', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
```
Build the cancellation email body and subject, then send via `\MyAdmin\Mail`:
```php
$email = $settings['TBLNAME'].' ID: '.$serviceInfo[$settings['PREFIX'].'_id'].'<br>'
    .$settings['TBLNAME'].' Hostname: '.$serviceInfo[$settings['PREFIX'].'_hostname'].'<br>'
    .'Repeat Invoice: '.$repeatInvoiceId.'<br>'
    .'Description: '.self::$name.'<br>';
$subject = $settings['TBLNAME'].' '.$serviceInfo[$settings['PREFIX'].'_id'].' Canceled '.self::$name;
(new \MyAdmin\Mail())->adminMail($subject, $email, false, 'admin/vps_cpanel_canceled.tpl');
```
Verify: template path is relative (`admin/vps_cpanel_canceled.tpl`), `false` is passed as third arg.

### 3. Set up `getSettings(GenericEvent $event)`

Get the settings subject and set target to `'module'` before adding, then restore to `'global'` after:
```php
/** @var \MyAdmin\Settings $settings **/
$settings = $event->getSubject();
$settings->setTarget('module');
$settings->add_text_setting(
    self::$module,
    _('Addon Costs'),
    'vps_fantastico_cost',
    _('VPS Fantastico License'),
    _('This is the cost for purchasing a fantastico license on top of a VPS.'),
    $settings->get_setting('VPS_FANTASTICO_COST')
);
$settings->setTarget('global');
```
Verify: `setTarget('module')` called before `add_text_setting()`, `setTarget('global')` called after.

### 4. Run tests

```bash
vendor/bin/phpunit tests/ -v
```
Verify all tests in `tests/PluginTest.php` pass, especially `testDoEnableSignature`, `testDoDisableSignature`, `testGetSettingsSourceAddsTextSetting`.

## Examples

**User says:** "Add a setting for the addon cost and wire up activation to call `activate_mycpanel($ip)`"

**Actions taken:**
1. In `doEnable`: retrieve `$serviceInfo` and `$settings`, log via `myadmin_log`, call `function_requirements('activate_mycpanel')`, then `activate_mycpanel($serviceInfo[$settings['PREFIX'].'_ip'])`
2. In `getSettings`: call `$settings->setTarget('module')`, add via `$settings->add_text_setting(self::$module, _('Addon Costs'), 'vps_mycpanel_cost', ...)`, then `$settings->setTarget('global')`

**Result:** Matches the exact pattern in `src/Plugin.php`.

## Common Issues

- **`Undefined index: PREFIX`** — `get_module_settings()` returned an empty array. Verify `self::$module` equals a registered module name (`'vps'`, not `'VPS'`).
- **`Call to undefined function activate_fantastico()`** — missing `function_requirements('activate_fantastico')` before the call. Always call `function_requirements` before any lazily-loaded function.
- **`testDoEnableSignature` fails: expected 2 required params, got 1`** — `$regexMatch = false` must be the third param with a default; `$repeatInvoiceId` must be required (no default).
- **`testNoNonPublicMethods` fails`** — a method was declared `protected` or `private`. All Plugin methods must be `public`.
- **`adminMail` not found** — ensure you instantiate with `new \MyAdmin\Mail()` (leading backslash, since Plugin is in `Detain\MyAdminVpsFantastico\` namespace).
- **`setTarget` not called / wrong order`** — `testGetSettingsSourceAddsTextSetting` checks for `setTarget` in source. Always bracket `add_text_setting()` calls with `setTarget('module')` before and `setTarget('global')` after.
