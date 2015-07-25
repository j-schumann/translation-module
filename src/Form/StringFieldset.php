<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use TranslationModule\Entity\String;
use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to create or edit a translation string.
 */
class StringFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('string');

        $languageRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Language');
        $stringRepository = $this->getEntityManager()
                ->getRepository(String::class);

        // id for ObjectExists
        $this->add($stringRepository->getFormElementDefinition('id'));

        $this->add($stringRepository->getFormElementDefinition('string'));
        $this->add($stringRepository->getFormElementDefinition('context'));
        $this->add($stringRepository->getFormElementDefinition('occurrences'));
        $this->add($stringRepository->getFormElementDefinition('params'));
        $this->add($stringRepository->getFormElementDefinition('module'));

        $languages = $languageRepository->findAll();
        $this->add([
            'type' => 'Fieldset',
            'name' => 'translations',
        ]);

        foreach ($languages as $language) {
            $translation = $this->getServiceLocator()
                    ->get('TranslationModule\Form\TranslationFieldset');
            $translation->setLanguage($language);
            $this->get('translations')->add($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()->getRepository(String::class);
        $spec       = $repository->getInputFilterSpecification();

        // remove or will be set to 0000-00-00 because the InputFilter will return null
        unset($spec['updatedAt']);

        return $spec;
    }
}
