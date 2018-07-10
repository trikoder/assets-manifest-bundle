<?php

namespace Tests\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Trikoder\ManifestAssetBundle\Service\ManifestReaderService;
use Trikoder\ManifestAssetBundle\Twig\ManifestAssetExtension;
use Twig_Loader_Filesystem;

class ManifestAssetExtensionTest extends TestCase
{
    /**
     * @var KernelInterface
     */
    private $kernelMock;

    /**
     * @var Twig_Loader_Filesystem
     */
    protected $twigLoaderFilesystem;

    protected function setUp()
    {
        $this->kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->kernelMock->method('getRootDir')->willReturn(realpath(__DIR__ . '/../'));
        $this->twigLoaderFilesystem = new Twig_Loader_Filesystem();
        $this->twigLoaderFilesystem->addPath('./Tests/Service', 'TestNamespace');
    }

    public function testCorrectAbsolutePathToAssetIsReturned()
    {
        $this->kernelMock->expects($this->any())
            ->method('locateResource')
            ->will($this->returnCallback(function (...$args) {
                if ('@TrikoderManifestAssetBundle/test-manifest.json' === $args[0]) {
                    return realpath(__DIR__ . '/../Service/test-manifest.json');
                }
            }));

        $service = new ManifestReaderService($this->kernelMock, $this->twigLoaderFilesystem, 'test-manifest.json');
        $requestStack = new RequestStack();
        $extension = new ManifestAssetExtension($service, $requestStack, __DIR__ . '/../public');

        $this->assertSame('This is Sparta.', $extension->manifestAssetInlineFilter('@TrikoderManifestAssetBundle:test.txt'));
        $this->assertSame('This is Sparta.', $extension->manifestAssetInlineFilter('@TestNamespace/test.txt'));

        $this->assertSame('/bundles/trikodermanifestasset/dev/test.txt', $extension->manifestAssetFilter('@TrikoderManifestAssetBundle:test.txt'));
        $this->assertSame('/testnamespace/dev/test.txt', $extension->manifestAssetFilter('@TestNamespace/test.txt'));
    }

    public function testExceptionIsThrownIfFileDoesNotExist()
    {
        $this->kernelMock->expects($this->any())
            ->method('locateResource')
            ->will($this->returnCallback(function (...$args) {
                if ('@TrikoderManifestAssetBundle/test-manifest.json' === $args[0]) {
                    return realpath(__DIR__ . '/../Service/test-manifest.json');
                }
            }));

        $service = new ManifestReaderService($this->kernelMock, $this->twigLoaderFilesystem, 'test-manifest.json');
        $requestStack = new RequestStack();
        $extension = new ManifestAssetExtension($service, $requestStack, __DIR__ . '/../public');

        $this->expectException(\InvalidArgumentException::class);
        $extension->manifestAssetInlineFilter('@TrikoderManifestAssetBundle:doesNotExist.css');
    }

    /**
     * @test
     */
    public function publicPathIsUsedIfExists()
    {
        $this->kernelMock->expects($this->any())
            ->method('locateResource')
            ->will($this->returnCallback(function (...$args) {
                if ('@TrikoderManifestAssetBundle/test-manifest.json' === $args[0]) {
                    return realpath(__DIR__ . '/../Service/test-manifest-publicPath.json');
                }
            }));

        $service = new ManifestReaderService($this->kernelMock, $this->twigLoaderFilesystem, 'test-manifest.json');
        $requestStack = new RequestStack();
        $extension = new ManifestAssetExtension($service, $requestStack, __DIR__ . '/../public');

        $this->assertEquals('https://www.google.com/images/dev/test.txt', $extension->manifestAssetFilter('@TrikoderManifestAssetBundle:test.txt'));
        $this->assertEquals('https://www.google.com/images/dev/test.txt', $extension->manifestAssetFilter('@TrikoderManifestAssetBundle:test.txt', ['absolute' => true]));

        // Now, test it with twig namespace
        $service = new ManifestReaderService($this->kernelMock, $this->twigLoaderFilesystem, 'test-manifest-publicPath.json');
        $extension = new ManifestAssetExtension($service, $requestStack, __DIR__ . '/../public');
        $this->assertEquals('https://www.google.com/images/dev/test.txt', $extension->manifestAssetFilter('@TestNamespace/test.txt'));
        $this->assertEquals('https://www.google.com/images/dev/test.txt', $extension->manifestAssetFilter('@TestNamespace/test.txt', ['absolute' => true]));
    }

    /**
     * @test
     */
    public function publicPathIsntUsedForInlineAssets()
    {
        $this->kernelMock->expects($this->any())
            ->method('locateResource')
            ->will($this->returnCallback(function (...$args) {
                if ('@TrikoderManifestAssetBundle/test-manifest.json' === $args[0]) {
                    return realpath(__DIR__ . '/../Service/test-manifest-publicPath.json');
                }
            }));

        $service = new ManifestReaderService($this->kernelMock, $this->twigLoaderFilesystem, 'test-manifest.json');
        $requestStack = new RequestStack();
        $extension = new ManifestAssetExtension($service, $requestStack, __DIR__ . '/../public');

        $this->assertEquals('This is Sparta.', $extension->manifestAssetInlineFilter('@TrikoderManifestAssetBundle:test.txt'));
        $this->assertEquals('This is Sparta.', $extension->manifestAssetInlineFilter('@TestNamespace/test.txt'));
    }
}
