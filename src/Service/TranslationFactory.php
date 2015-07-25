<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TranslationFactory implements FactoryInterface
{
    /**
     * Creates an instance of the translation service, injects the dependencies.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Translation
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $translation = new Translation();
        $translation->setServiceLocator($serviceLocator);

        $configuration = $serviceLocator->get('Config');
        $translation->setOptions(isset($configuration['translator'])
            ? $configuration['translator']
            : []
        );

        return $translation;
    }
}
