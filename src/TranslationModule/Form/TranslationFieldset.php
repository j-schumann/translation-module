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
 * Form to create or edit a translation.
 */
class TranslationFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @var \TranslationModule\Entity\Language
     */
    protected $language = null;

    public function setLanguage(\TranslationModule\Entity\Language $language)
    {
        $this->language = $language;
        $this->setName($this->language->getId());
        //$this->get('language')->setValue($this->language->getId());
        $this->get('translation')->setLabel($this->language->getName());
    }

    public function init()
    {
        $translationRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Translation');

        $this->add($translationRepository->getFormElementDefinition('translation'));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'isNull',
            'options' => array(
                'label'           => 'TranslationModule.Entity.Translation.isNull.label',
                'unchecked_value' => '',
            ),
        ));

//        $this->add($translationRepository->getFormElementDefinition('language'));
//        $this->add($translationRepository->getFormElementDefinition('string'));
    }

    public function getInputFilterSpecification()
    {
        $translationRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Translation');
        return array(
            $translationRepository->getInputSpecification('translation'),
        );
    }
}