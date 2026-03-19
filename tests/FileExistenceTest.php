<?php

namespace Detain\MyAdminVpsFantastico\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests verifying the expected file and directory structure of the package.
 *
 * Ensures all required source files, configuration files, and documentation
 * are present in the package distribution.
 */
class FileExistenceTest extends TestCase
{
    /**
     * @var string Absolute path to the package root directory.
     */
    private string $packageRoot;

    protected function setUp(): void
    {
        $this->packageRoot = dirname(__DIR__);
    }

    /**
     * Verify the composer.json file exists at the package root.
     *
     * @return void
     */
    public function testComposerJsonExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/composer.json');
    }

    /**
     * Verify the composer.json file contains valid JSON.
     *
     * @return void
     */
    public function testComposerJsonIsValidJson(): void
    {
        $content = file_get_contents($this->packageRoot . '/composer.json');
        $decoded = json_decode($content, true);
        $this->assertNotNull($decoded, 'composer.json should contain valid JSON');
    }

    /**
     * Verify the composer.json file declares the correct package name.
     *
     * @return void
     */
    public function testComposerJsonHasCorrectName(): void
    {
        $content = file_get_contents($this->packageRoot . '/composer.json');
        $decoded = json_decode($content, true);
        $this->assertSame('detain/myadmin-fantastico-vps-addon', $decoded['name']);
    }

    /**
     * Verify the composer.json declares a PSR-4 autoload entry.
     *
     * @return void
     */
    public function testComposerJsonHasPsr4Autoload(): void
    {
        $content = file_get_contents($this->packageRoot . '/composer.json');
        $decoded = json_decode($content, true);
        $this->assertArrayHasKey('autoload', $decoded);
        $this->assertArrayHasKey('psr-4', $decoded['autoload']);
        $this->assertArrayHasKey('Detain\\MyAdminVpsFantastico\\', $decoded['autoload']['psr-4']);
    }

    /**
     * Verify the src directory exists.
     *
     * @return void
     */
    public function testSrcDirectoryExists(): void
    {
        $this->assertDirectoryExists($this->packageRoot . '/src');
    }

    /**
     * Verify the Plugin.php source file exists.
     *
     * @return void
     */
    public function testPluginPhpExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/src/Plugin.php');
    }

    /**
     * Verify the vps_add_fantastico.php source file exists.
     *
     * @return void
     */
    public function testVpsAddFantasticoPhpExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/src/vps_add_fantastico.php');
    }

    /**
     * Verify the README.md file exists.
     *
     * @return void
     */
    public function testReadmeExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/README.md');
    }

    /**
     * Verify the .gitignore file exists.
     *
     * @return void
     */
    public function testGitignoreExists(): void
    {
        $this->assertFileExists($this->packageRoot . '/.gitignore');
    }

    /**
     * Verify the LICENSE information is declared in composer.json.
     *
     * @return void
     */
    public function testLicenseDeclaredInComposer(): void
    {
        $content = file_get_contents($this->packageRoot . '/composer.json');
        $decoded = json_decode($content, true);
        $this->assertArrayHasKey('license', $decoded);
        $this->assertNotEmpty($decoded['license']);
    }

    /**
     * Verify the package type is declared as myadmin-plugin.
     *
     * @return void
     */
    public function testPackageTypeIsMyadminPlugin(): void
    {
        $content = file_get_contents($this->packageRoot . '/composer.json');
        $decoded = json_decode($content, true);
        $this->assertSame('myadmin-plugin', $decoded['type']);
    }

    /**
     * Verify the Plugin.php file begins with a proper PHP opening tag and namespace.
     *
     * @return void
     */
    public function testPluginPhpHasProperHeader(): void
    {
        $content = file_get_contents($this->packageRoot . '/src/Plugin.php');
        $this->assertStringStartsWith('<?php', $content);
        $this->assertStringContainsString('namespace Detain\\MyAdminVpsFantastico;', $content);
    }
}
