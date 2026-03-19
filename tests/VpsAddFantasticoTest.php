<?php

namespace Detain\MyAdminVpsFantastico\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the vps_add_fantastico procedural include file.
 *
 * Since this file defines a standalone function that depends heavily on
 * the MyAdmin framework (AddServiceAddon, function_requirements, etc.),
 * we use static analysis via file_get_contents to verify its structure
 * and correctness without requiring the full application bootstrap.
 */
class VpsAddFantasticoTest extends TestCase
{
    /**
     * @var string Absolute path to the source file under test.
     */
    private string $filePath;

    /**
     * @var string Contents of the source file.
     */
    private string $source;

    protected function setUp(): void
    {
        $this->filePath = dirname(__DIR__) . '/src/vps_add_fantastico.php';
        $this->source = file_get_contents($this->filePath);
        $this->assertIsString($this->source);
    }

    /**
     * Verify the source file exists on disk.
     *
     * @return void
     */
    public function testFileExists(): void
    {
        $this->assertFileExists($this->filePath);
    }

    /**
     * Verify the file starts with a PHP opening tag.
     *
     * @return void
     */
    public function testFileStartsWithPhpTag(): void
    {
        $this->assertStringStartsWith('<?php', $this->source);
    }

    /**
     * Verify the file defines the vps_add_fantastico function.
     *
     * @return void
     */
    public function testDefinesVpsAddFantasticoFunction(): void
    {
        $this->assertMatchesRegularExpression(
            '/function\s+vps_add_fantastico\s*\(/',
            $this->source
        );
    }

    /**
     * Verify the function calls function_requirements to load AddServiceAddon.
     *
     * @return void
     */
    public function testCallsFunctionRequirements(): void
    {
        $this->assertStringContainsString("function_requirements('class.AddServiceAddon')", $this->source);
    }

    /**
     * Verify the function instantiates AddServiceAddon.
     *
     * @return void
     */
    public function testInstantiatesAddServiceAddon(): void
    {
        $this->assertStringContainsString('new AddServiceAddon()', $this->source);
    }

    /**
     * Verify the function calls load() with the correct parameters.
     *
     * @return void
     */
    public function testCallsLoadWithCorrectParams(): void
    {
        $this->assertStringContainsString("__FUNCTION__", $this->source);
        $this->assertStringContainsString("'Fantastico'", $this->source);
        $this->assertStringContainsString("'vps'", $this->source);
        $this->assertStringContainsString('VPS_FANTASTICO_COST', $this->source);
    }

    /**
     * Verify the function calls process() on the addon.
     *
     * @return void
     */
    public function testCallsProcess(): void
    {
        $this->assertStringContainsString('$addon->process()', $this->source);
    }

    /**
     * Verify the file has a proper docblock with author info.
     *
     * @return void
     */
    public function testFileHasDocblock(): void
    {
        $this->assertStringContainsString('@author', $this->source);
        $this->assertStringContainsString('@package', $this->source);
    }

    /**
     * Verify the function docblock declares a void return type.
     *
     * @return void
     */
    public function testFunctionDocblockHasReturnVoid(): void
    {
        $this->assertStringContainsString('@return void', $this->source);
    }

    /**
     * Verify the file does not reside in a namespace (global scope function).
     *
     * @return void
     */
    public function testFileHasNoNamespace(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/^\s*namespace\s+/m',
            $this->source
        );
    }

    /**
     * Verify the file references the VPS category.
     *
     * @return void
     */
    public function testFileReferencesVpsCategory(): void
    {
        $this->assertStringContainsString('@category VPS', $this->source);
    }
}
