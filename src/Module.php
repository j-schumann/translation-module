<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;

/**
 * Module bootstrapping.
 */
class Module implements
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
                'TranslationModule\Controller\Entry' => function ($sm) {
                    return new Controller\EntryController($sm);
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
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Import' => function ($sm) {
                    $form = new Form\Import();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Language' => function ($sm) {
                    $form = new Form\Language();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\LanguageFieldset' => function ($sm) {
                    $form = new Form\LanguageFieldset();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Module' => function ($sm) {
                    $form = new Form\Module();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\Settings' => function ($sm) {
                    $form = new Form\Settings();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    $form->setTranslationService($sm->get('TranslationModule\Service\Translation'));
                    return $form;
                },
                'TranslationModule\Form\Entry' => function ($sm) {
                    $form = new Form\Entry();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\EntryFieldset' => function ($sm) {
                    $form = new Form\EntryFieldset();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    $form->setFormElementManager($sm->get('FormElementManager'));
                    return $form;
                },
                'TranslationModule\Form\EntryFilter' => function ($sm) {
                    $form = new Form\EntryFilter();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
                'TranslationModule\Form\TranslationFieldset' => function ($sm) {
                    $form = new Form\TranslationFieldset();
                    $form->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->get('MvcTranslator'));
                    return $form;
                },
            ],
        ];
    }
}
