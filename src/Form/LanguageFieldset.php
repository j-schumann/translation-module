<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use TranslationModule\Entity\Language;
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
            $parent                             = $this->get('parent');
            $findMethod                         = $parent->getOption('find_method');
            $findMethod['params']['languageId'] = $data['id'];
            // use setOptions instead of setOption to trigger the proxy update
            $parent->setOptions(['find_method' => $findMethod]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('language');

        $repository = $this->getEntityManager()->getRepository(Language::class);

        // the ID field is hidden, we need it for the UniqueObject validator
        $this->add($repository->getFormElementDefinition('id'));
        $this->add($repository->getFormElementDefinition('name'));
        $this->add($repository->getFormElementDefinition('locale'));
        $this->add($repository->getFormElementDefinition('parent'));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()->getRepository(Language::class);

        return $repository->getInputFilterSpecification();
    }
}
