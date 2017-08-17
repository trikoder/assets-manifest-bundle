<?php

namespace Trikoder\ManifestAssetBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Trikoder\ManifestAssetBundle\Service\ManifestReaderService;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class ManifestAssetExtension
 * @package Trikoder\ManifestAssetBundle\Twig
 */
class ManifestAssetExtension extends Twig_Extension
{
    /**
     * @var ManifestReaderService $readerService
     */
    private $readerService;

    /**
     * @var string $documentRoot
     */
    private $documentRoot;

    /**
     * @var RequestStack $requestStack
     */
    private $requestStack;

    /**
     * @var string $webDir
     */
    private $webDir;

    public function __construct(ManifestReaderService $readerService, RequestStack $requestStack, $webDir)
    {
        $this->readerService = $readerService;
        $this->documentRoot = "/";
        $this->requestStack = $requestStack;
        $this->webDir = $webDir;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('manifestAsset', [$this, 'manifestAssetFilter']),
            new Twig_SimpleFunction('manifestAssetInline', [$this, 'manifestAssetInlineFilter'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param $shorthand string
     * @param $options array
     * @return string
     */
    public function manifestAssetFilter($shorthand, array $options = [])
    {
        // get bundle and suffix from shorthand
        list($bundleRef, $asset) = $this->getBundleAndAssetFromShorthand($shorthand);

        // TODO - check if both values are ok

        $abs = (array_key_exists('absolute', $options) && $options['absolute']) === true;

        // load manifest
        $manifest = $this->getManifest($bundleRef);

        // find resource
        return $this->getAssetUri($manifest, $asset, $abs);
    }

    /**
     * @param $shorthand string
     * @return string
     */
    public function manifestAssetInlineFilter($shorthand)
    {
        return file_get_contents($this->getFilePath($shorthand));
    }

    /**
     * @param $shorthand string
     * @return mixed
     */
    protected function getBundleAndAssetFromShorthand($shorthand)
    {
        return explode(':', $shorthand, 2);
    }

    /**
     * @param $bundleRef string
     * @return mixed
     */
    protected function getManifest($bundleRef)
    {
        return $this->readerService->getBundleManifest($bundleRef);
    }

    /**
     * Generate absolute route prefix for current request
     * @return string
     */
    protected function generateAbsolutePrefix()
    {
        /** @var Request $req */
        $req = $this->requestStack->getCurrentRequest();

        $scheme = $req->getScheme();
        $host = $req->getHost();
        $port = '';
        if ('http' === $scheme && 80 != $req->getPort()) {
            $port = ':'.$req->getPort();
        } elseif ('https' === $scheme && 443 != $req->getPort()) {
            $port = ':'.$req->getPort();
        }
        $schemeAuthority = "$scheme://";
        $schemeAuthority .= $host.$port;

        return $schemeAuthority;
    }

    /**
     * @param $manifest array
     * @param $asset string
     * @param bool $absolute
     * @return string
     */
    protected function getAssetUri($manifest, $asset, $absolute = false)
    {
        $path = $this->documentRoot. $manifest['root'] . $manifest['buildPath'] . $asset;
        if ($absolute === true) {
            return $this->generateAbsolutePrefix() . $path;
        }

        return $path;
    }

    /**
     * @param $shorthand string
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @return string
     */
    protected function getFilePath($shorthand)
    {
        // get bundle and suffix from shorthand
        list($bundleRef, $asset) = $this->getBundleAndAssetFromShorthand($shorthand);

        // load manifest
        $manifest = $this->getManifest($bundleRef);

        $filePath = realpath($this->webDir . $this->getAssetUri($manifest, $asset));

        if (false !== $filePath) {
            return $filePath;
        } else {
            throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $shorthand));
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'trikoder.manifest_asset.manifest_asset_extension';
    }
}
