<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to filter / search translation strings.
 */
class StringFilter extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $sr = $this->getEntityManager()->getRepository('TranslationModule\Entity\String');
        $module = $sr->getFormElementDefinition('module');
        unset($module['attributes']['required']);

        $this->add(array(
            'type'    => 'Fieldset',
            'name'    => 'stringFilter',
            'options' => array(
                'label' => 'form.translation.stringFilter.label',
            ),
            'elements' => array(
                 array(
                    'spec' => array(
                        'type'    => 'Zend\Form\Element\Text',
                        'name'    => 'stringSearch',
                        'options' => array(
                            'label' => 'form.translation.stringSearch.label'
                        ),
                        'attributes' => array(
                            'maxlength' => 255,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type'    => 'Zend\Form\Element\Text',
                        'name'    => 'translationSearch',
                        'options' => array(
                            'label' => 'form.translation.translationSearch.label'
                        ),
                        'attributes' => array(
                            'maxlength' => 255,
                        ),
                    ),
                ),
                array(
                    'spec' => $module,
                ),
                array(
                    'spec' => array(
                        'name'       => 'submit',
                        'attributes' => array(
                            'type'  => 'submit',
                            'value' => 'form.submit',
                            'id'    => 'submit',
                        ),
                    )
                ),
            ),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $sr = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\String');
        $moduleSpec = $sr->getInputSpecification('module');
        $moduleSpec['required'] = false;
        $moduleSpec['allowEmpty'] = true;
        unset($moduleSpec['validators']['notEmpty']);

        $inputSpec = array(
            'required' => false,
            'allowEmpty' => true,
            'filters'    => array(
                array('name' => 'Zend\Filter\StringTrim'),
                array('name' => 'Zend\Filter\StripTags'),
                array('name' => 'Zend\Filter\StripNewlines'),
            ),
            'validators' => array(
                array(
                    'name'    => 'Zend\Validator\StringLength',
                    'options' => array(
                        'max'      => 255,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG =>
                                \Vrok\Doctrine\FormHelper::ERROR_TOOLONG,
                        ),
                    ),
                ),
            ),
        );

        return array(
            'stringFilter' => array(
                'type'              => 'Zend\InputFilter\InputFilter',

                'module'            => $moduleSpec,
                'stringSearch'      => $inputSpec,
                'translationSearch' => $inputSpec,
            ),
        );
    }
}
