<?php

namespace Tests\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Trikoder\ManifestAssetBundle\Service\ManifestReaderService;
use Twig_Loader_Filesystem;

class ManifestReaderServiceTest extends TestCase
{
    /**
     * @var Twig_Loader_Filesystem $twigLoaderFilesystem
     */
    protected $twigLoaderFilesystem;

    protected function setUp()
    {
        $this->twigLoaderFilesystem = new Twig_Loader_Filesystem();
        $this->twigLoaderFilesystem->addPath('./Tests/Service', 'TestNamespace');
    }

    /**
     * @test
     */
    public function validateManifestLoadingAndFormat()
    {
        $kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $kernelMock->method('locateResource')->willReturn(realpath(__DIR__.'/test-manifest.json'));

        $service = new ManifestReaderService($kernelMock, $this->twigLoaderFilesystem, 'test-manifest.json');
        $result = $service->getManifest('@TrikoderManifestAssetBundle');

        $this->assertTrue(is_array($result));

        $this->assertArrayHasKey('version', $result, 'Manifest needs version field');
        $this->assertRegExp('/(\d+)\.(\d+)\.(\d+)/', $result['version'], 'Version needs to follow SemVer format');

        $this->assertArrayHasKey('buildPath', $result, 'Manifest needs buildPath field');
        $this->assertArrayHasKey('srcPath', $result, 'Manifest needs srcPath field');
        $this->assertArrayHasKey('distPath', $result, 'Manifest needs distPath field');
    }

    /**
     * @test
     */
    public function validateManifestLoadingFromTwigNamespace()
    {
        $kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $kernelMock->method('getRootDir')
            ->willReturn(realpath(__DIR__.'/../'));

        $service = new ManifestReaderService($kernelMock, $this->twigLoaderFilesystem, 'test-manifest.json');

        $result = $service->getManifest('@TestNamespace');
        $this->assertTrue(is_array($result));
    }

    /**
     * @test
     */
    public function invalidManifestFileFromBundle()
    {
        $kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kernelMock->method('locateResource')->will($this->throwException(new InvalidArgumentException()));
        $service = new ManifestReaderService($kernelMock, $this->twigLoaderFilesystem);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Manifest file .* does not exist/');

        $result = $service->getManifest('@TrikoderManifestAssetBundle');
    }

    /**
     * @test
     */
    public function invalidManifestFileFromTwigNamespace()
    {
        $kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service = new ManifestReaderService($kernelMock, $this->twigLoaderFilesystem);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Manifest file .* does not exist/');

        $result = $service->getManifest('@TestNamespace');
    }
}
