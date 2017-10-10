<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TranslationFactory implements FactoryInterface
{
    /**
     * Creates an instance of the translation service, injects the dependencies.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return Translation
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $translation = new Translation($container);

        $configuration = $container->get('Config');
        $translation->setOptions(isset($configuration['translator'])
            ? $configuration['translator']
            : []);

        return $translation;
    }
}
