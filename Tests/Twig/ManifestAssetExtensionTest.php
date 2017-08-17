<?php

namespace Tests\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Trikoder\ManifestAssetBundle\Twig\ManifestAssetExtension;
use Trikoder\ManifestAssetBundle\Service\ManifestReaderService;

class ManifestAssetExtensionTest extends TestCase
{
    /**
     * @var KernelInterface $kernelMock
     */
    private $kernelMock;

    protected function setUp()
    {
        $this->kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCorrectAbsolutePathToAssetIsReturned()
    {
        $this->kernelMock->expects($this->any())
            ->method('locateResource')
            ->will($this->returnCallback(function(...$args) {
                if ($args[0] === '@TrikoderManifestAssetBundle/test-manifest.json') {
                    return realpath(__DIR__ .'/../Service/test-manifest.json');
                }
            }));

        $service = new ManifestReaderService($this->kernelMock, 'test-manifest.json');
        $request_stack = new RequestStack();
        $extension = new ManifestAssetExtension($service, $request_stack, __DIR__.'/../public');

        $this->assertSame('This is Sparta.', $extension->manifestAssetInlineFilter("@TrikoderManifestAssetBundle:test.txt"));
    }

    public function testExceptionIsThrownIfFileDoesNotExist()
    {
        $this->kernelMock->expects($this->any())
            ->method('locateResource')
            ->will($this->returnCallback(function(...$args) {
                if ($args[0] === '@TrikoderManifestAssetBundle/test-manifest.json') {
                    return realpath(__DIR__ .'/../Service/test-manifest.json');
                }
            }));

        $service = new ManifestReaderService($this->kernelMock, 'test-manifest.json');
        $request_stack = new RequestStack();
        $extension = new ManifestAssetExtension($service, $request_stack, __DIR__.'/../public');

        $this->expectException(\InvalidArgumentException::class);
        $extension->manifestAssetInlineFilter("@TrikoderManifestAssetBundle:doesNotExist.css");
    }

}