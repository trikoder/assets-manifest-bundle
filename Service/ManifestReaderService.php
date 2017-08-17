<?php

namespace Trikoder\ManifestAssetBundle\Service;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;

class ManifestReaderService
{
    /** @var $manifest array */
    protected $manifestRegistry = [];

    /** @var  KernelInterface $kernel */
    protected $kernel;

    /** @var string $manifestFileName */
    protected $manifestFileName;

    /**
     * ManifestReaderService constructor.
     * @param KernelInterface $kernel
     * @param string $manifestFileName
     */
    public function __construct(KernelInterface $kernel, $manifestFileName = 'asset-manifest.json')
    {
        $this->kernel = $kernel;
        $this->manifestFileName = $manifestFileName;
    }

    /**
     * @param $bundleRef
     * @return mixed
     */
    public function getBundleManifest($bundleRef)
    {
        if (false === array_key_exists($bundleRef, $this->manifestRegistry)) {
            $this->loadBundleManifest($bundleRef);
        }

        return $this->manifestRegistry[$bundleRef];
    }

    /**
     * @param $bundleRef
     * @return mixed
     */
    protected function loadBundleManifest($bundleRef)
    {
        // try to locate file
        try {
            $manifestPath = $this->kernel->locateResource("{$bundleRef}/{$this->manifestFileName}", null, true);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf('Manifest file for bundle %s does not exist', $bundleRef),
                0,
                $exception
            );
        }

        // load it
        $manifest = json_decode(file_get_contents($manifestPath), true);

        // check if we got contents
        if (true === is_null($manifest)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Manifest file %1$s/%2$s does not have valid contents',
                    $bundleRef,
                    $this->manifestFileName
                )
            );
        }

        // TODO validate manifest has right structure

        // to speed up process we cache resolve the root of the bundle
        $manifest['root'] = $this->resolveBundleAssetRoot($bundleRef);

        // save it in registry and return the value
        return $this->manifestRegistry[$bundleRef] = $manifest;
    }

    /**
     * @param $bundleRef
     * @return string
     */
    protected function resolveBundleAssetRoot($bundleRef)
    {
        // all to lovercase and remove @ at start and bundle keyword fron the end
        $bundleDir = substr(strtolower($bundleRef), 1, -6);

        return "bundles/{$bundleDir}/";
    }
}
