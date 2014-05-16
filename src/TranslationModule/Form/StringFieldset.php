<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to create or edit a translation string.
 */
class StringFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->setName('string');

        $languageRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Language');
        $stringRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\String');

        $this->add($stringRepository->getFormElementDefinition('id'));
        $this->add($stringRepository->getFormElementDefinition('string'));
        $this->add($stringRepository->getFormElementDefinition('context'));
        $this->add($stringRepository->getFormElementDefinition('occurences'));
        $this->add($stringRepository->getFormElementDefinition('params'));
        $this->add($stringRepository->getFormElementDefinition('module'));

        $languages = $languageRepository->findAll();
        $this->add(array(
            'type'     => 'Fieldset',
            'name'     => 'translations',
        ));

        foreach($languages as $language) {
            $translation = $this->getServiceLocator()
                    ->get('TranslationModule\Form\TranslationFieldset');
            $translation->setLanguage($language);
            $this->get('translations')->add($translation);
        }
    }

    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\String');
        return $repository->getInputFilterSpecification();
    }
}
