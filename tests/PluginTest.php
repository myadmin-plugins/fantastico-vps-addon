<?php

namespace Detain\MyAdminVpsFantastico\Tests;

use Detain\MyAdminVpsFantastico\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Tests for the Fantastico VPS Addon Plugin class.
 *
 * Covers class structure, static properties, hook registration,
 * event handler signatures, and static analysis of method bodies.
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass<Plugin>
     */
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    // ---------------------------------------------------------------
    // Class structure tests
    // ---------------------------------------------------------------

    /**
     * Verify the Plugin class exists and is instantiable.
     *
     * @return void
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }

    /**
     * Verify the Plugin class is not abstract.
     *
     * @return void
     */
    public function testClassIsNotAbstract(): void
    {
        $this->assertFalse($this->reflection->isAbstract());
    }

    /**
     * Verify the Plugin class is not an interface.
     *
     * @return void
     */
    public function testClassIsNotInterface(): void
    {
        $this->assertFalse($this->reflection->isInterface());
    }

    /**
     * Verify the Plugin class can be instantiated.
     *
     * @return void
     */
    public function testClassIsInstantiable(): void
    {
        $this->assertTrue($this->reflection->isInstantiable());
    }

    /**
     * Verify the Plugin class resides in the correct namespace.
     *
     * @return void
     */
    public function testClassNamespace(): void
    {
        $this->assertSame('Detain\\MyAdminVpsFantastico', $this->reflection->getNamespaceName());
    }

    /**
     * Verify the Plugin class has the expected short name.
     *
     * @return void
     */
    public function testClassShortName(): void
    {
        $this->assertSame('Plugin', $this->reflection->getShortName());
    }

    // ---------------------------------------------------------------
    // Static property tests
    // ---------------------------------------------------------------

    /**
     * Verify the $name static property exists and has the expected value.
     *
     * @return void
     */
    public function testNameProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('name'));
        $this->assertSame('Fantastico VPS Addon', Plugin::$name);
    }

    /**
     * Verify the $description static property exists and contains expected content.
     *
     * @return void
     */
    public function testDescriptionProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('description'));
        $this->assertIsString(Plugin::$description);
        $this->assertStringContainsString('Fantastico', Plugin::$description);
        $this->assertStringContainsString('netenberg.com', Plugin::$description);
    }

    /**
     * Verify the $help static property exists and is a non-empty string.
     *
     * @return void
     */
    public function testHelpProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('help'));
        $this->assertIsString(Plugin::$help);
        $this->assertNotEmpty(Plugin::$help);
    }

    /**
     * Verify the $module static property equals 'vps'.
     *
     * @return void
     */
    public function testModuleProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('module'));
        $this->assertSame('vps', Plugin::$module);
    }

    /**
     * Verify the $type static property equals 'addon'.
     *
     * @return void
     */
    public function testTypeProperty(): void
    {
        $this->assertTrue($this->reflection->hasProperty('type'));
        $this->assertSame('addon', Plugin::$type);
    }

    /**
     * Verify all static properties are public.
     *
     * @return void
     */
    public function testStaticPropertiesArePublic(): void
    {
        $properties = ['name', 'description', 'help', 'module', 'type'];
        foreach ($properties as $prop) {
            $refProp = $this->reflection->getProperty($prop);
            $this->assertTrue($refProp->isPublic(), "Property \${$prop} should be public");
            $this->assertTrue($refProp->isStatic(), "Property \${$prop} should be static");
        }
    }

    // ---------------------------------------------------------------
    // Constructor tests
    // ---------------------------------------------------------------

    /**
     * Verify the constructor exists and takes no required parameters.
     *
     * @return void
     */
    public function testConstructorExists(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertSame(0, $constructor->getNumberOfRequiredParameters());
    }

    /**
     * Verify Plugin can be instantiated without arguments.
     *
     * @return void
     */
    public function testConstructorCreatesInstance(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    // ---------------------------------------------------------------
    // getHooks() tests
    // ---------------------------------------------------------------

    /**
     * Verify getHooks() is a public static method.
     *
     * @return void
     */
    public function testGetHooksIsPublicStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Verify getHooks() returns an array.
     *
     * @return void
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Verify getHooks() contains exactly three hook entries.
     *
     * @return void
     */
    public function testGetHooksHasThreeEntries(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertCount(3, $hooks);
    }

    /**
     * Verify getHooks() registers a function.requirements hook.
     *
     * @return void
     */
    public function testGetHooksContainsFunctionRequirements(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('function.requirements', $hooks);
        $this->assertSame([Plugin::class, 'getRequirements'], $hooks['function.requirements']);
    }

    /**
     * Verify getHooks() registers a vps.load_addons hook.
     *
     * @return void
     */
    public function testGetHooksContainsLoadAddons(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('vps.load_addons', $hooks);
        $this->assertSame([Plugin::class, 'getAddon'], $hooks['vps.load_addons']);
    }

    /**
     * Verify getHooks() registers a vps.settings hook.
     *
     * @return void
     */
    public function testGetHooksContainsSettings(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('vps.settings', $hooks);
        $this->assertSame([Plugin::class, 'getSettings'], $hooks['vps.settings']);
    }

    /**
     * Verify hook keys use the module property value as prefix.
     *
     * @return void
     */
    public function testHookKeysUseModulePrefix(): void
    {
        $hooks = Plugin::getHooks();
        $moduleHooks = array_filter(
            array_keys($hooks),
            static fn(string $key): bool => str_starts_with($key, Plugin::$module . '.')
        );
        $this->assertCount(2, $moduleHooks);
    }

    /**
     * Verify all hook callbacks reference callable method names on Plugin.
     *
     * @return void
     */
    public function testHookCallbacksReferenceExistingMethods(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $hookName => $callback) {
            $this->assertIsArray($callback, "Callback for '{$hookName}' should be an array");
            $this->assertCount(2, $callback, "Callback for '{$hookName}' should have exactly 2 elements");
            $this->assertSame(Plugin::class, $callback[0], "Callback class for '{$hookName}' should be Plugin");
            $this->assertTrue(
                $this->reflection->hasMethod($callback[1]),
                "Method '{$callback[1]}' referenced in '{$hookName}' hook should exist"
            );
        }
    }

    // ---------------------------------------------------------------
    // Event handler signature tests
    // ---------------------------------------------------------------

    /**
     * Verify getRequirements() accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetRequirementsSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());

        $param = $method->getParameters()[0];
        $this->assertNotNull($param->getType());
        $this->assertSame(GenericEvent::class, $param->getType()->getName());
    }

    /**
     * Verify getAddon() accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetAddonSignature(): void
    {
        $method = $this->reflection->getMethod('getAddon');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());

        $param = $method->getParameters()[0];
        $this->assertNotNull($param->getType());
        $this->assertSame(GenericEvent::class, $param->getType()->getName());
    }

    /**
     * Verify getSettings() accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetSettingsSignature(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());

        $param = $method->getParameters()[0];
        $this->assertNotNull($param->getType());
        $this->assertSame(GenericEvent::class, $param->getType()->getName());
    }

    /**
     * Verify doEnable() has the correct parameter signature.
     *
     * @return void
     */
    public function testDoEnableSignature(): void
    {
        $method = $this->reflection->getMethod('doEnable');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(2, $method->getNumberOfRequiredParameters());
        $this->assertSame(3, $method->getNumberOfParameters());

        $params = $method->getParameters();
        $this->assertSame('serviceOrder', $params[0]->getName());
        $this->assertSame('repeatInvoiceId', $params[1]->getName());
        $this->assertSame('regexMatch', $params[2]->getName());
        $this->assertTrue($params[2]->isDefaultValueAvailable());
        $this->assertFalse($params[2]->getDefaultValue());
    }

    /**
     * Verify doDisable() has the correct parameter signature.
     *
     * @return void
     */
    public function testDoDisableSignature(): void
    {
        $method = $this->reflection->getMethod('doDisable');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(2, $method->getNumberOfRequiredParameters());
        $this->assertSame(3, $method->getNumberOfParameters());

        $params = $method->getParameters();
        $this->assertSame('serviceOrder', $params[0]->getName());
        $this->assertSame('repeatInvoiceId', $params[1]->getName());
        $this->assertSame('regexMatch', $params[2]->getName());
        $this->assertTrue($params[2]->isDefaultValueAvailable());
        $this->assertFalse($params[2]->getDefaultValue());
    }

    // ---------------------------------------------------------------
    // Method inventory tests
    // ---------------------------------------------------------------

    /**
     * Verify the Plugin class has exactly the expected public methods.
     *
     * @return void
     */
    public function testExpectedPublicMethods(): void
    {
        $expectedMethods = [
            '__construct',
            'getHooks',
            'getRequirements',
            'getAddon',
            'getSettings',
            'doEnable',
            'doDisable',
        ];

        $publicMethods = array_map(
            static fn(ReflectionMethod $m): string => $m->getName(),
            $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC)
        );

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $publicMethods, "Public method '{$method}' should exist");
        }
    }

    /**
     * Verify all declared methods are public (no private/protected methods).
     *
     * @return void
     */
    public function testNoNonPublicMethods(): void
    {
        $nonPublic = $this->reflection->getMethods(
            ReflectionMethod::IS_PRIVATE | ReflectionMethod::IS_PROTECTED
        );
        $this->assertCount(0, $nonPublic, 'Plugin class should have no private or protected methods');
    }

    // ---------------------------------------------------------------
    // Static analysis via source code inspection
    // ---------------------------------------------------------------

    /**
     * Verify getRequirements() calls add_page_requirement with the correct path.
     *
     * @return void
     */
    public function testGetRequirementsSourceCallsAddPageRequirement(): void
    {
        $source = file_get_contents((string) $this->reflection->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString('add_page_requirement', $source);
        $this->assertStringContainsString('vps_add_fantastico', $source);
        $this->assertStringContainsString('myadmin-fantastico-vps-addon/src/vps_add_fantastico.php', $source);
    }

    /**
     * Verify getAddon() source sets up an AddonHandler with expected configuration.
     *
     * @return void
     */
    public function testGetAddonSourceSetsUpAddonHandler(): void
    {
        $source = file_get_contents((string) $this->reflection->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString('AddonHandler', $source);
        $this->assertStringContainsString('setModule', $source);
        $this->assertStringContainsString("set_text('Fantastico')", $source);
        $this->assertStringContainsString('set_cost', $source);
        $this->assertStringContainsString('VPS_FANTASTICO_COST', $source);
        $this->assertStringContainsString('set_require_ip(true)', $source);
        $this->assertStringContainsString('setEnable', $source);
        $this->assertStringContainsString('setDisable', $source);
        $this->assertStringContainsString('register()', $source);
        $this->assertStringContainsString('addAddon', $source);
    }

    /**
     * Verify doEnable() source calls required external functions.
     *
     * @return void
     */
    public function testDoEnableSourceCallsExpectedFunctions(): void
    {
        $source = file_get_contents((string) $this->reflection->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString('get_module_settings', $source);
        $this->assertStringContainsString('license.functions.inc.php', $source);
        $this->assertStringContainsString('myadmin_log', $source);
        $this->assertStringContainsString('activate_fantastico', $source);
    }

    /**
     * Verify doDisable() source sends admin email with cancellation info.
     *
     * @return void
     */
    public function testDoDisableSourceSendsAdminMail(): void
    {
        $source = file_get_contents((string) $this->reflection->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString('adminMail', $source);
        $this->assertStringContainsString('Canceled', $source);
        $this->assertStringContainsString('vps_cpanel_canceled.tpl', $source);
    }

    /**
     * Verify getSettings() source configures the fantastico cost setting.
     *
     * @return void
     */
    public function testGetSettingsSourceAddsTextSetting(): void
    {
        $source = file_get_contents((string) $this->reflection->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString('add_text_setting', $source);
        $this->assertStringContainsString('vps_fantastico_cost', $source);
        $this->assertStringContainsString('setTarget', $source);
    }

    /**
     * Verify the source file uses the Symfony EventDispatcher GenericEvent import.
     *
     * @return void
     */
    public function testSourceUsesGenericEventImport(): void
    {
        $source = file_get_contents((string) $this->reflection->getFileName());
        $this->assertIsString($source);
        $this->assertStringContainsString('use Symfony\\Component\\EventDispatcher\\GenericEvent;', $source);
    }
}
