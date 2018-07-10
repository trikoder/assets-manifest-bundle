<?php

namespace Trikoder\ManifestAssetBundle\Twig;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Trikoder\ManifestAssetBundle\Service\ManifestReaderService;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class ManifestAssetExtension
 */
class ManifestAssetExtension extends Twig_Extension
{
    /**
     * @var ManifestReaderService
     */
    private $readerService;

    /**
     * @var string
     */
    private $documentRoot;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @param ManifestReaderService $readerService
     * @param RequestStack          $requestStack
     * @param string                 $webDir
     */
    public function __construct(ManifestReaderService $readerService, RequestStack $requestStack, $webDir)
    {
        $this->readerService = $readerService;
        $this->documentRoot = '/';
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
            new Twig_SimpleFunction(
                'manifestAssetInline',
                [$this, 'manifestAssetInlineFilter'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string $shorthand
     * @param array $options
     *
     * @return string
     */
    public function manifestAssetFilter($shorthand, array $options = [])
    {
        // get bundle and suffix from shorthand
        [$reference, $asset] = $this->getReferenceAndAssetFromShorthand($shorthand);

        // TODO - check if both values are ok

        $abs = true === (array_key_exists('absolute', $options) && $options['absolute']);

        // load manifest
        $manifest = $this->getManifest($reference);

        // find resource
        return $this->getAssetUri($manifest, $asset, $abs);
    }

    /**
     * @param string $shorthand
     *
     * @return string
     */
    public function manifestAssetInlineFilter($shorthand)
    {
        return file_get_contents($this->getFilePath($shorthand));
    }

    /**
     * @param string $shorthand
     *
     * @return mixed
     */
    private function getReferenceAndAssetFromShorthand($shorthand)
    {
        // Bundle shorthand
        if (preg_match('/^(@\w+Bundle):(.+)/', $shorthand, $matches)) {
            return array_slice($matches, 1);
        }

        // Twig namespace shorthand
        if (preg_match('/^(@\w+)\/(.*)/', $shorthand, $matches)) {
            return array_slice($matches, 1);
        }

        throw new InvalidArgumentException('Invalid shorthand format.');
    }

    /**
     * @param string $reference
     *
     * @return mixed
     */
    private function getManifest($reference)
    {
        return $this->readerService->getManifest($reference);
    }

    /**
     * Generate absolute route prefix for current request
     *
     * @return string
     */
    private function generateAbsolutePrefix()
    {
        /** @var Request $req */
        $req = $this->requestStack->getCurrentRequest();

        $scheme = $req->getScheme();
        $host = $req->getHost();
        $port = '';
        if ('http' === $scheme && 80 != $req->getPort()) {
            $port = ':' . $req->getPort();
        } elseif ('https' === $scheme && 443 != $req->getPort()) {
            $port = ':' . $req->getPort();
        }
        $schemeAuthority = "$scheme://";
        $schemeAuthority .= $host . $port;

        return $schemeAuthority;
    }

    /**
     * @param array $manifest
     * @param string $asset
     * @param bool $absolute
     *
     * @return string
     */
    private function getAssetUri($manifest, $asset, $absolute)
    {
        if (array_key_exists('publicPath', $manifest)) {
            return $manifest['publicPath'] . $asset;
        }

        $path = $this->documentRoot . $manifest['root'] . $manifest['buildPath'] . $asset;
        if (true === $absolute) {
            return $this->generateAbsolutePrefix() . $path;
        }

        return $path;
    }

    /**
     * @param string $shorthand
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     *
     * @return string
     */
    private function getFilePath($shorthand)
    {
        // get reference and suffix from shorthand
        [$reference, $asset] = $this->getReferenceAndAssetFromShorthand($shorthand);

        // load manifest
        $manifest = $this->getManifest($reference);

        // do not use publicPath when generating path to file used for inlining assets
        if (array_key_exists('publicPath', $manifest)) {
            unset($manifest['publicPath']);
        }

        $filePath = realpath($this->webDir . $this->getAssetUri($manifest, $asset, false));

        if (false === $filePath) {
            throw new \InvalidArgumentException(sprintf('Unable to find "%s".', $shorthand));
        }

        return $filePath;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'trikoder.manifest_asset.manifest_asset_extension';
    }
}
