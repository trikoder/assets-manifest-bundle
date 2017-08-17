<?php

namespace Trikoder\ManifestAssetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trikoder_manifest_asset');

        $rootNode->children()
            ->scalarNode('web_dir')->defaultValue('web')->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
