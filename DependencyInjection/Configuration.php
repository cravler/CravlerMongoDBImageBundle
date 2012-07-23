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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Cravler <http://github.com/cravler>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cravler_mongo_db_image');

        $rootNode
            ->children()
                ->booleanNode('use_local_storage')->defaultValue(false)->end()
                ->scalarNode('web_dir')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('driver')->defaultValue('gd')->cannotBeEmpty()->end()
                ->scalarNode('document')->defaultValue('Cravler\Bundle\MongoDBImageBundle\Document\Image')->cannotBeEmpty()->end()
                ->scalarNode('manager')->defaultValue('Cravler\Bundle\MongoDBImageBundle\Document\ImageManager')->cannotBeEmpty()->end()
                ->arrayNode('allowed_file_types')
                    ->addDefaultsIfNotSet()
                    ->requiresAtLeastOneElement()
                    ->beforeNormalization()
                        ->ifTrue(function($v){ return !is_array($v); })
                        ->then(function($v){ return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('width')
                    ->children()
                        ->scalarNode('min')->end()
                        ->scalarNode('max')->end()
                    ->end()
                ->end()
                ->arrayNode('height')
                    ->children()
                        ->scalarNode('min')->end()
                        ->scalarNode('max')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
