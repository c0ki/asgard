<?php

namespace Core\SearchengineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class SearchengineConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('searchengine');

        $rootNode
//            ->children()
//                ->booleanNode('default_client')->defaultValue(true)->end()
//            ->end()
            ->children()
                ->scalarNode('default_client')->cannotBeEmpty()->defaultValue('default')->end()
                ->arrayNode('clients')
                    ->canBeUnset()
                    ->prototype('array')
                        ->beforeNormalization()
                        ->ifTrue(function ($client) {
                            return isset($client['dsn']);
                        })
                        ->then(function ($client) {
                            $parsed_dsn = parse_url($client['dsn']);
                            unset($client['dsn']);
                            if ($parsed_dsn) {
                                $client['type'] = isset($parsed_dsn['scheme']) ? $parsed_dsn['scheme'] : 'solr';
                                if (substr($client['type'], -1) == 's') {
                                    $client['secure'] = true;
                                    $client['type'] = substr($client['type'], 0, -1);
                                }
                                $client['hostname'] = isset($parsed_dsn['host']) ? $parsed_dsn['host'] : 'localhost';
                                $client['port'] = isset($parsed_dsn['port']) ? $parsed_dsn['port'] : 8983;
                                $client['login'] = isset($parsed_dsn['user']) ? $parsed_dsn['user'] : null;
                                $client['password'] = isset($parsed_dsn['pass']) ? $parsed_dsn['pass'] : null;
                                if (isset($parsed_dsn['path'])) {
                                    $info = pathinfo($parsed_dsn['path']);
                                    if (array_key_exists('extension', $info) && !empty($info['extension'])) {
                                        $client['wt'] = $info['extension'];
                                    }
                                    $client['path'] = $info['dirname'];
                                    $client['core'] = $info['filename'];
                                }
                            }

                            return $client;
                        })
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')->defaultValue('solr')->end()
                        ->scalarNode('secure')->defaultValue(false)->end()
                        ->scalarNode('hostname')->defaultValue('localhost')->end()
                        ->scalarNode('port')->defaultValue(8983)->end()
                        ->scalarNode('path')->defaultValue('/solr')->end()
                        ->scalarNode('core')->defaultValue('default')->end()
                        ->scalarNode('wt')->defaultValue('json')->end()
                        ->scalarNode('login')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('proxy_host')->end()
                        ->scalarNode('proxy_port')->end()
                        ->scalarNode('proxy_login')->end()
                        ->scalarNode('proxy_password')->end()
                        ->scalarNode('timeout')->end()
                        ->scalarNode('ssl_cert')->end()
                        ->scalarNode('ssl_key')->end()
                        ->scalarNode('ssl_keypassword')->end()
                        ->scalarNode('ssl_cainfo')->end()
                        ->scalarNode('ssl_capath')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
