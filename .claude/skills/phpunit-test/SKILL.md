---
name: phpunit-test
description: Writes PHPUnit 9 tests under `tests/` for this plugin following patterns in `tests/VpsAddFantasticoTest.php`, `tests/PluginTest.php`, and `tests/FileExistenceTest.php`. Use when user says 'add test', 'write test', 'test coverage', or adds new src files. Covers source-content string assertions, regex matching, ReflectionClass structure checks, and file existence checks. Do NOT use for integration tests requiring a live MyAdmin runtime or database. NOTE: for a plugin's contract/behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
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

# phpunit-test

## Critical

- **No MyAdmin runtime available** — never instantiate classes that depend on `function_requirements()`, `get_module_db()`, `myadmin_log()`, or other MyAdmin globals. Use `file_get_contents()` + string assertions or `ReflectionClass` instead.
- **Tabs for indentation** — `.scrutinizer.yml` enforces tabs, not spaces. Every test file must use tabs.
- **Docblocks required on every method** — tests assert `@author`, `@package`, `@return` presence. All test methods need at minimum `@return void`.
- Run tests with: `vendor/bin/phpunit tests/ -v`

## Instructions

1. **Identify what to test.** Determine whether the target is:
   - A namespaced class in `src/` (→ use `ReflectionClass` + source-content pattern)
   - A procedural include in `src/` (→ use `file_get_contents()` assertions)
   - Package structure / config files (→ use `assertFileExists` + `json_decode`)

2. **Create the test file** in the `tests/` directory. Verify no file exists there before writing.

   Boilerplate header for a class test:
   ```php
   <?php

   namespace Detain\MyAdminVpsFantastico\Tests;

   use Detain\MyAdminVpsFantastico\Plugin;
   use PHPUnit\Framework\TestCase;
   use ReflectionClass;
   use ReflectionMethod;
   use Symfony\Component\EventDispatcher\GenericEvent;

   /**
    * Tests for the <Subject> class.
    *
    * @author      Joe Huss <detain@interserver.net>
    * @package     Detain\MyAdminVpsFantastico\Tests
    * @category    Tests
    */
   class SubjectTest extends TestCase
   {
   	/**
   	 * @var ReflectionClass<Subject>
   	 */
   	private ReflectionClass $reflection;

   	protected function setUp(): void
   	{
   		$this->reflection = new ReflectionClass(Subject::class);
   	}
   }
   ```

   Boilerplate header for a procedural-file test:
   ```php
   <?php

   namespace Detain\MyAdminVpsFantastico\Tests;

   use PHPUnit\Framework\TestCase;

   class SubjectTest extends TestCase
   {
   	private string $filePath;
   	private string $source;

   	protected function setUp(): void
   	{
   		$this->filePath = dirname(__DIR__) . '/src/subject_file.php';
   		$this->source = file_get_contents($this->filePath);
   		$this->assertIsString($this->source);
   	}
   }
   ```

3. **Write test methods.** Each method must:
   - Be `public function testXxx(): void`
   - Have a docblock with `@return void`
   - Assert one logical concern

   Common assertion patterns extracted from existing tests:

   | What to check | Assertion |
   |---|---|
   | File exists on disk | `$this->assertFileExists($path)` |
   | Directory exists | `$this->assertDirectoryExists($path)` |
   | File starts with `<?php` | `$this->assertStringStartsWith('<?php', $source)` |
   | String is present in source | `$this->assertStringContainsString('needle', $source)` |
   | String is absent | `$this->assertDoesNotMatchRegularExpression('/pattern/m', $source)` |
   | Regex matches source | `$this->assertMatchesRegularExpression('/pattern/', $source)` |
   | Class exists | `$this->assertTrue(class_exists(Foo::class))` |
   | Property is public+static | `$refProp->isPublic()` + `$refProp->isStatic()` |
   | Method signature | `$method->getNumberOfRequiredParameters()`, `$param->getType()->getName()` |
   | `GenericEvent` param type | `$this->assertSame(GenericEvent::class, $param->getType()->getName())` |
   | JSON file is valid | `$this->assertNotNull(json_decode(file_get_contents($path), true))` |

4. **Source-content tests for Plugin methods** — load source via `ReflectionClass::getFileName()` (not a hardcoded path):
   ```php
   public function testMethodSourceContainsExpectedCall(): void
   {
   	$source = file_get_contents((string) $this->reflection->getFileName());
   	$this->assertIsString($source);
   	$this->assertStringContainsString('expectedCall()', $source);
   }
   ```

5. **Verify tests pass** before finishing:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   All tests must show `OK`. Fix any failures before declaring done.

## Examples

**User says:** "Add tests for the new `src/vps_add_newaddon.php` procedural file"

**Actions taken:**
1. Read `src/vps_add_newaddon.php` to learn its function name, calls, and docblock tags.
2. Create `tests/VpsAddNewaddonTest.php` with namespace `Detain\MyAdminVpsFantastico\Tests`.
3. `setUp()` loads file path into `$this->filePath` and source into `$this->source`.
4. Add tests: `testFileExists`, `testFileStartsWithPhpTag`, `testDefinesVpsAddNewaddonFunction` (regex `/function\s+vps_add_newaddon\s*\(/`), `testCallsFunctionRequirements`, `testInstantiatesAddServiceAddon`, `testCallsProcess`, `testFileHasDocblock` (`@author`, `@package`), `testFunctionDocblockHasReturnVoid`, `testFileHasNoNamespace`.
5. Run `vendor/bin/phpunit tests/ -v` → confirm OK.

**Result:** New test file mirrors the existing test file structure exactly, using tabs and full docblocks.

## Common Issues

- **`Class 'Detain\MyAdminVpsFantastico\Plugin' not found`**: The autoloader isn't loaded. Run with `--bootstrap vendor/autoload.php` in your environment, or verify `composer install` has been run. Verify the `vendor/` directory exists.
- **`Call to undefined function function_requirements()`**: You instantiated a class or called a function that needs the MyAdmin runtime. Switch to `ReflectionClass` or `file_get_contents()` static analysis — never call `Plugin::getAddon()` directly in tests.
- **Indentation errors in Scrutinizer (`wrong indentation`)**: File uses spaces. Convert all indentation to hard tabs (`\t`). The `.scrutinizer.yml` `indentation: spaces: false` setting will fail the CI check.
- **`assertMatchesRegularExpression` not found`**: You're running PHPUnit < 9. Confirm `composer.json` requires `phpunit/phpunit ^9.6` and run `composer install`.
- **Test passes locally but CI fails on docblock check**: `VpsAddFantasticoTest::testFileHasDocblock` checks `@author` and `@package` in the *source file*, not the test file. Ensure your new `src/` file has both tags in its file-level docblock.
