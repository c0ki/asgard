<?php

namespace Core\SearchengineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Core\SearchengineBundle\Component\Search\SearchClientException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SearchengineExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerExtension($this);
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $loaderSearchEngine = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loaderSearchEngine->load('services.yml');
        $loaderSearchEngine->load('searchengine.yml');

//        var_dump($container->getExtensionConfig('searchengine'));

        $defaultClient = $config['default_client'];
        if (empty($config['clients'])) {
            $config['clients'] = array();
        } elseif (count($config['clients']) === 1) {
            $defaultClient = key($config['clients']);
        }

        $clients = array();
        foreach ($config['clients'] as $name => $clientOptions) {
            $clientName = sprintf('searchengine.client.%s', $name);
            $queryName = sprintf('searchengine.query.%s', $name);
            if (isset($clientOptions['type'])) {
                $clientClass = 'Core\SearchengineBundle\Component\Search\\' . ucfirst($clientOptions['type']) . '\SearchClient';
                $queryClass = 'Core\SearchengineBundle\Component\Search\\' . ucfirst($clientOptions['type']) . '\SearchQuery';
                unset($clientOptions['type']);
            } else {
                throw new SearchClientException("Search client type is mandatory");
            }

            $clientDefinition = new Definition($clientClass, array($clientOptions));
            $clients[$name] = new Reference($clientName);
            $container->setDefinition($clientName, $clientDefinition);

            $queryDefinition = new Definition($queryClass);
            $container->setDefinition($queryName, $queryDefinition);

//            $container->getDefinition($clientName)->addMethodCall('setLogger', array('@logger'));
//            $clientDefinition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher')));
            if ($name == $defaultClient) {
                $container->setAlias('searchengine.client', $clientName);
                $container->setAlias('searchengine.query', $queryName);
            }
        }
        $registry = $container->getDefinition('search.client_registery');
        $registry->replaceArgument(0, $clients);
        if (in_array($defaultClient, array_keys($clients))) {
            $registry->replaceArgument(1, $defaultClient);
        }

    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $reflected = new \ReflectionClass($this);
        $namespace = $reflected->getNamespaceName();

        $class = $namespace . '\\SearchengineConfiguration';
        if (class_exists($class)) {
            $r = new \ReflectionClass($class);
            $container->addResource(new FileResource($r->getFileName()));

            if (!method_exists($class, '__construct')) {
                $configuration = new $class();

                return $configuration;
            }
        }
    }

}
