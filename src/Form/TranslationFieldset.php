<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use TranslationModule\Entity\Language;
use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to create or edit a translation.
 */
class TranslationFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @var \TranslationModule\Entity\Language
     */
    protected $language = null;

    /**
     * When getting the fieldset from the formElementManager we don't know
     * the language yet, set it now.
     *
     * @param \TranslationModule\Entity\Language $language
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;
        $this->setName($this->language->getId());
        $this->get('translation')->setLabel($this->language->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $translationRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Translation');

        $this->add($translationRepository->getFormElementDefinition('translation'));
        $this->add([
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'isNull',
            'options' => [
                'label'           => 'TranslationModule.Entity.Translation.isNull.label',
                'unchecked_value' => '',
            ],
            'attributes' => [
                'value' => '1', // checked by default
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilterSpecification()
    {
        $translationRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Translation');

        return [
            $translationRepository->getInputSpecification('translation'),
        ];
    }
}
