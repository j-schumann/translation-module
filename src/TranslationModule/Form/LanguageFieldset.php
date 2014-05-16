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
 * Form to create or edit a translation language.
 */
class LanguageFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * The repository class could not know which langauge we want to update
     * so we need to set it here to only get allowed parent languages.
     *
     * @param array $data
     */
    public function populateValues($data)
    {
        parent::populateValues($data);

        if (isset($data['id'])) {
            $parent = $this->get('parent');
            $findMethod = $parent->getOption('find_method');
            $findMethod['params']['languageId'] = $data['id'];
            $parent->setOptions(array('find_method' => $findMethod));
        }
    }

    public function init()
    {
        $this->setName('language');

        $languageRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Language');

        // the ID field is hidden, we need it for the UniqueObject validator
        $this->add($languageRepository->getFormElementDefinition('id'));
        $this->add($languageRepository->getFormElementDefinition('name'));
        $this->add($languageRepository->getFormElementDefinition('locale'));
        $this->add($languageRepository->getFormElementDefinition('parent'));
    }

    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Language');
        return $repository->getInputFilterSpecification();
    }
}
