---
name: plugin-hook
description: Adds a new EventDispatcher hook to src/Plugin.php. Use when user says 'add hook', 'register event', 'new plugin event', or needs to respond to a new MyAdmin event. Covers getHooks() registration, GenericEvent callback signature, and myadmin_log instrumentation. Do NOT use for modifying the addon handler setup (AddonHandler, doEnable, doDisable).
---
# plugin-hook

## Critical

- All handler methods MUST be `public static` — the EventDispatcher invokes them statically via `[__CLASS__, 'methodName']`.
- The `GenericEvent` type hint is required: `use Symfony\Component\EventDispatcher\GenericEvent;` is already at the top of `src/Plugin.php` — do NOT add a duplicate import.
- Every new method needs a docblock with `@param \Symfony\Component\EventDispatcher\GenericEvent $event` — `tests/PluginTest.php` inspects method signatures via reflection.
- Hook keys that target the plugin's own module MUST use `self::$module.'.event_name'` (not a hard-coded `'vps.'` prefix) so the key stays correct if `$module` changes.
- Indentation is **tabs**, not spaces (enforced by `.scrutinizer.yml`).

## Instructions

1. **Identify the event name** — confirm the MyAdmin platform fires the event you need (e.g. `vps.after_provision`). Event names follow the pattern `<module>.<action>` for module-scoped events or `function.requirements` for global loader events.

2. **Register the hook in `getHooks()`** (`src/Plugin.php`). Add one entry to the returned array:
   ```php
   self::$module.'.your_event' => [__CLASS__, 'yourHandler'],
   ```
   Verify `Plugin::getHooks()` still returns an array — `tests/PluginTest.php::testGetHooksReturnsArray` will catch a syntax error immediately.

3. **Write the handler method** after the last existing method in `src/Plugin.php`. Follow this exact shape:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function yourHandler(GenericEvent $event)
   {
   	$subject = $event->getSubject();   // cast to the expected type
   	$settings = get_module_settings(self::$module);
   	myadmin_log(self::$module, 'info', self::$name.' YourAction', __LINE__, __FILE__, self::$module, 0);
   	// handler logic here
   }
   ```
   - Use `$event->getSubject()` to get the injected object (e.g. `\ServiceHandler`, `\MyAdmin\Plugins\Loader`, `\MyAdmin\Settings`).
   - Replace the trailing `0` in `myadmin_log` with the service ID when available: `$serviceInfo[$settings['PREFIX'].'_id']`.

4. **Access service data** (when the subject is a `ServiceHandler`) using the settings prefix:
   ```php
   $serviceInfo = $subject->getServiceInfo();
   $ip       = $serviceInfo[$settings['PREFIX'].'_ip'];
   $custId   = $serviceInfo[$settings['PREFIX'].'_custid'];
   $serviceId = $serviceInfo[$settings['PREFIX'].'_id'];
   ```

5. **Run tests** to confirm the new method is wired correctly:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   `PluginTest::testHookCallbacksReferenceExistingMethods` will fail if the method name in `getHooks()` does not match an actual method on the class.

## Examples

**User says:** "Add a hook that logs when a VPS backup completes."

**Actions taken:**

1. Add to `getHooks()` return array in `src/Plugin.php`:
   ```php
   self::$module.'.backup_complete' => [__CLASS__, 'onBackupComplete'],
   ```

2. Add handler method:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function onBackupComplete(GenericEvent $event)
   {
   	/** @var \ServiceHandler $service */
   	$service = $event->getSubject();
   	$settings = get_module_settings(self::$module);
   	$serviceInfo = $service->getServiceInfo();
   	myadmin_log(self::$module, 'info', self::$name.' Backup Complete', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
   }
   ```

3. Run `vendor/bin/phpunit tests/ -v` — all tests pass.

## Common Issues

- **`testGetHooksHasThreeEntries` fails after adding a hook** — this test asserts exactly 3 hooks. Update that assertion in `tests/PluginTest.php` to match the new count, or the test was intentionally strict; confirm with the team whether the count assertion should change.
- **`testHookCallbacksReferenceExistingMethods` fails** — the method name string in `getHooks()` does not match the actual method name. Compare the string in the array (`'yourHandler'`) against the `public static function` declaration character-for-character.
- **`testNoNonPublicMethods` fails** — you accidentally declared the handler as `protected` or `private`. All Plugin methods must be `public`.
- **`Call to undefined method` at runtime** — `$event->getSubject()` returned an unexpected type. Add a `/** @var ExpectedClass $subject */` docblock and verify which object the platform injects for that event name by checking existing hooks in other plugins under `vendor/detain/myadmin-*-addon/src/Plugin.php`.
