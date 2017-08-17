<?php

namespace Tests\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Trikoder\ManifestAssetBundle\Service\ManifestReaderService;

class ManifestReaderServiceTest extends TestCase
{

    /**
     * @test
     */
    public function validateManifestLoadingAndFormat()
    {
        $kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kernelMock->method('locateResource')->willReturn(realpath(__DIR__.'/test-manifest.json'));
        $service = new ManifestReaderService($kernelMock);

        $result = $service->getBundleManifest('test');

        $this->assertTrue(is_array($result));

        $this->assertArrayHasKey('version', $result, 'Manifest needs version field');
        $this->assertRegExp('/(\d+)\.(\d+)\.(\d+)/', $result['version'], 'Version needs to follow SemVer format');

        $this->assertArrayHasKey('buildPath', $result, 'Manifest needs buildPath field');
        $this->assertArrayHasKey('srcPath', $result, 'Manifest needs srcPath field');
        $this->assertArrayHasKey('distPath', $result, 'Manifest needs distPath field');
    }

    /**
     * @test
     *
     */
    public function invalidManifestFile()
    {
        $kernelMock = $this->getMockBuilder(KernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kernelMock->method('locateResource')->will($this->throwException(new InvalidArgumentException()));
        $service = new ManifestReaderService($kernelMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Manifest file for bundle test does not exist');


        $result = $service->getBundleManifest('test');
    }
}
