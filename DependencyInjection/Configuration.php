<?php

namespace Uniplaces\RequestLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('uniplaces_request_logger');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('uniplaces_request_logger');
        }

        return $treeBuilder;
    }
}
