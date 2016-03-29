<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;

/**
 * Module bootstrapping.
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ControllerProviderInterface,
    FormElementProviderInterface
{
    /**
     * Returns the modules default configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    /**
     * Return additional serviceManager config with closures that should not be
     * in the config files to allow caching of the complete configuration.
     *
     * @return array
     * @todo alle controller auf ihre dependencies prüfen und ggf direct injecten
     */
    public function getControllerConfig()
    {
        return [
            'factories' => [
                'TranslationModule\Controller\Index' => function ($sm) {
                    return new Controller\IndexController($sm);
                },
                'TranslationModule\Controller\Language' => function ($sm) {
                    return new Controller\LanguageController($sm);
                },
                'TranslationModule\Controller\Management' => function ($sm) {
                    return new Controller\ManagementController($sm);
                },
                'TranslationModule\Controller\Module' => function ($sm) {
                    return new Controller\ModuleController($sm);
                },
                'TranslationModule\Controller\String' => function ($sm) {
                    return new Controller\StringController($sm);
                },
            ],
        ];
    }

    /**
     * Return additional serviceManager config with closures that should not be in the
     * config files to allow caching of the complete configuration.
     *
     * @return array
     */
    public function getFormElementConfig()
    {
        return [
            'factories' => [
                'TranslationModule\Form\Export' => function ($sm) {
                    $form = new Form\Export();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Import' => function ($sm) {
                    $form = new Form\Import();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Language' => function ($sm) {
                    $form = new Form\Language();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\LanguageFieldset' => function ($sm) {
                    $form = new Form\LanguageFieldset();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Module' => function ($sm) {
                    $form = new Form\Module();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Settings' => function ($sm) {
                    $form = new Form\Settings();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    $form->setTranslationService($sm->getServiceLocator()->get('TranslationModule\Service\Translation'));
                    return $form;
                },
                'TranslationModule\Form\String' => function ($sm) {
                    $form = new Form\String();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\StringFieldset' => function ($sm) {
                    $form = new Form\StringFieldset();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    $form->setServiceLocator($sm);
                    return $form;
                },
                'TranslationModule\Form\StringFilter' => function ($sm) {
                    $form = new Form\StringFilter();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\TranslationFieldset' => function ($sm) {
                    $form = new Form\TranslationFieldset();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
            ],
        ];
    }

    /**
     * Link the translation service to the I18n\Translator.
     *
     * @param \Zend\EventManager\EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $eventManager   = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();

        $translationService = $serviceManager->get('TranslationModule\Service\Translation');
        $translationService->attach($eventManager);
    }
}
