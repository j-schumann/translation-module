<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to set the export options.
 */
class Export extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('translation-export');

        $translationRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Translation');
        $stringRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\String');

        $module = $stringRepository->getFormElementDefinition('module');
        unset($module['attributes']['required']);
        $module['options']['empty_option'] = 'view.all';
        $this->add($module);

        $language = $translationRepository->getFormElementDefinition('language');
        unset($language['attributes']['required']);
        $language['options']['empty_option'] = 'view.all';
        $this->add($language);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.submit',
                'id'    => 'submit',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $stringRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\String');
        $moduleSpec               = $stringRepository->getInputSpecification('module');
        $moduleSpec['required']   = false;
        $moduleSpec['allowEmpty'] = true;

        $translationRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Translation');
        $languageSpec               = $translationRepository->getInputSpecification('language');
        $languageSpec['required']   = false;
        $languageSpec['allowEmpty'] = true;

        return [
            $moduleSpec,
            $languageSpec,
        ];
    }
}
