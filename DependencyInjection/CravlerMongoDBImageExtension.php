<?php

/*
 * Copyright (c) 2012 "Cravler", http://github.com/cravler
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Cravler\Bundle\MongoDBImageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Cravler <http://github.com/cravler>
 */
class CravlerMongoDBImageExtension extends Extension
{
    protected $resources = array(
        'manager' => 'manager.xml'
    );

    /**
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @throws \InvalidArgumentException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $definition = new Definition('Cravler\Bundle\MongoDBImageBundle\Extension\CravlerMongoDBImageTwigExtension');
        $definition->addTag('twig.extension');
        $container->setDefinition('cravler_mongo_db_image_twig_extension', $definition);

        $this->loadDefaults($container);
        
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $driver = 'gd';
        if (isset($config['driver'])) {
            $driver = strtolower($config['driver']);
        }
        if (!in_array($driver, array('gd', 'imagick', 'gmagick'))) {
            throw new \InvalidArgumentException('Invalid imagine driver specified');
        }
        $container->setAlias('cravler.mongodb.imagine', new Alias('cravler.mongodb.imagine.'.$driver));

        $variables = array(
            'document',
            'manager',
        );
        foreach ($variables as $attribute) {
            $container->setParameter('cravler_mongo_db_image.' . $attribute . '.class', $config[$attribute]);
        }

        $this->remapParameters($config, $container,
            array(
                'allowed_file_types',
                'width.min',
                'height.min',
                'width.max',
                'height.max',
            )
        );
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $map
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name) {
            $value     = &$config;
            $paramName = 'cravler_mongo_db_image';
            $parts = explode('.', $name);

            $found = true;
            foreach ($parts as $name) {
                $paramName .= '.' . $name;
                if (isset($value[$name])) {
                    $value = &$value[$name];
                }
                else {
                    $found = false;
                }
            }

            if ($found) {
                $container->setParameter($paramName, $value);
            }
            else {
                $container->setParameter($paramName, null);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/cravler_mongo_db_image';
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadDefaults($container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        foreach ($this->resources as $resource) {
            $loader->load($resource);
        }
    }

    /**
     * {@inheritDoc}
     */
    function getAlias()
    {
        return 'cravler_mongo_db_image';
    }
}
