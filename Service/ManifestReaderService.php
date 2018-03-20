<?php

namespace Trikoder\ManifestAssetBundle\Service;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig_Loader_Filesystem;

class ManifestReaderService
{
    /** @var $manifest array */
    protected $manifestRegistry = [];

    /** @var  KernelInterface $kernel */
    protected $kernel;

    /** @var  Twig_Loader_Filesystem $twigLoaderFilesystem */
    protected $twigLoaderFilesystem;

    /** @var string $manifestFileName */
    protected $manifestFileName;

    /**
     * ManifestReaderService constructor.
     * @param KernelInterface $kernel
     * @param string $manifestFileName
     */
    public function __construct(KernelInterface $kernel, Twig_Loader_Filesystem $twigLoaderFilesystem, $manifestFileName = 'asset-manifest.json')
    {
        $this->kernel = $kernel;
        $this->twigLoaderFilesystem = $twigLoaderFilesystem;
        $this->manifestFileName = $manifestFileName;
    }

    /**
     * @param $reference
     * @return mixed
     */
    public function getManifest($reference)
    {
        if (false === array_key_exists($reference, $this->manifestRegistry)) {
            $this->loadManifest($reference);
        }

        return $this->manifestRegistry[$reference];
    }

    /**
     * @param $reference
     * @return mixed
     */
    protected function loadManifest($reference)
    {
        $assetRoot = null;
        $manifestPath = null;

        // Bundle reference
        if (preg_match('/^@((\w+)Bundle)/', $reference, $matches)) {
            $manifestPath = $this->getManifestPathFromBundle($reference, $this->manifestFileName);
            $assetRoot = 'bundles/' . strtolower($matches[2]) . '/';
        }

        // Twig namespace reference
        else if (preg_match('/^@(\w+)/', $reference, $matches)) {
            $manifestPath = $this->getManifestPathFromTwigNamespace($reference, $this->manifestFileName);
            $assetRoot = strtolower($matches[1]) . '/';
        }

        // Invalid reference
        else {
            throw new InvalidArgumentException(
                sprintf('Invalid reference format for %s.', $reference)
            );
        }

        // load it
        $manifest = json_decode(file_get_contents($manifestPath), true);

        // check if we got contents
        if (true === is_null($manifest)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Manifest file %1$s/%2$s does not have valid contents',
                    $reference,
                    $this->manifestFileName
                )
            );
        }

        // TODO validate manifest has right structure

        // to speed up process we cache resolve the root of the bundle/twig namespace
        $manifest['root'] = $assetRoot;

        // save it in registry and return the value
        return $this->manifestRegistry[$reference] = $manifest;
    }

    /**
     * @param $reference
     * @param $manifestFileName
     * @return string
     */
    protected function getManifestPathFromBundle($reference, $manifestFileName)
    {
        try {
            return $this->kernel->locateResource("{$reference}/{$manifestFileName}", null, true);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf('Manifest file for bundle %s does not exist.', $reference),
                0,
                $exception
            );
        }
    }

    /**
     * @param $reference
     * @param $manifestFileName
     * @return string
     */
    protected function getManifestPathFromTwigNamespace($reference, $manifestFileName)
    {
        $projectRoot = realpath($this->kernel->getRootDir() . '/..') . '/';

        if (true === $this->twigLoaderFilesystem->exists("{$reference}/{$manifestFileName}")) {
            return $projectRoot . $this->twigLoaderFilesystem->getCacheKey("{$reference}/{$manifestFileName}");
        }

        throw new InvalidArgumentException(
            sprintf('Manifest file for twig namespace %s does not exist.', $reference)
        );
    }
}
