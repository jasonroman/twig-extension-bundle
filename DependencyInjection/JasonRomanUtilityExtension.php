<?php

namespace NAB\EnterpriseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 * 
 */
class NABEnterpriseExtension extends Extension
{ 
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // loop through each of the bundle's configuration values and set each as a container parameter
        foreach ($config as $key => $value)
        {
            // set as a basic parameter, regardless if this is an array value or not
            $container->setParameter('nab_enterprise.'.$key, $value);

            // for arrays, also walk through one level and set keyed parameters based on the keys of that array
            if (is_array($config[$key])) {
                array_walk($value, array($this, 'setArrayParameters'), array('parentKey' => $key, 'container' => $container));
            }
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // load the branding service if branding is enabled and brand/application are defined
        if ($config['branding']['enabled'] && isset($config['branding']['brand']) && isset($config['branding']['application'])) {
            $loader->load('branding.xml');
        }
    }

    /**
     * Sets a container parameter key/value pair for the parent array's key
     *   - format:  nab_enterprise.<parentKey>.<key> = $value
     *   - example: nab_enterprise.merchant.statements = $value
     * 
     * Note that the $value be an array, so if you retrieve that parameter you will be returned an array
     * 
     * @param mixed $value
     * @param string $key
     * @param array $params
     */
    public function setArrayParameters($value, $key, $params)
    {
        $params['container']->setParameter('nab_enterprise.'.$params['parentKey'].'.'.$key, $value);
    }
}
