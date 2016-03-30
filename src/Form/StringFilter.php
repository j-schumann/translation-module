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
 * Form to filter / search translation strings.
 */
class StringFilter extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $sr     = $this->getEntityManager()->getRepository('TranslationModule\Entity\String');
        $module = $sr->getFormElementDefinition('module');
        unset($module['attributes']['required']);

        $this->add([
            'type'    => 'Fieldset',
            'name'    => 'stringFilter',
            'options' => [
                'label' => 'form.translation.stringFilter.label',
            ],
            'elements' => [
                 [
                    'spec' => [
                        'type'    => 'Zend\Form\Element\Text',
                        'name'    => 'stringSearch',
                        'options' => [
                            'label' => 'form.translation.stringSearch.label',
                        ],
                        'attributes' => [
                            'maxlength' => 255,
                        ],
                    ],
                ],
                [
                    'spec' => [
                        'type'    => 'Zend\Form\Element\Text',
                        'name'    => 'translationSearch',
                        'options' => [
                            'label' => 'form.translation.translationSearch.label',
                        ],
                        'attributes' => [
                            'maxlength' => 255,
                        ],
                    ],
                ],
                [
                    'spec' => $module,
                ],
                [
                    'spec' => [
                        'name'       => 'submit',
                        'attributes' => [
                            'type'  => 'submit',
                            'value' => 'form.submit',
                            'id'    => 'submit',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $sr = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\String');
        $moduleSpec               = $sr->getInputSpecification('module');
        $moduleSpec['required']   = false;
        $moduleSpec['allowEmpty'] = true;
        unset($moduleSpec['validators']['notEmpty']);

        $inputSpec = [
            'required'   => false,
            'allowEmpty' => true,
            'filters'    => [
                ['name' => 'Zend\Filter\StringTrim'],
                ['name' => 'Zend\Filter\StripTags'],
                ['name' => 'Zend\Filter\StripNewlines'],
            ],
            'validators' => [
                [
                    'name'    => 'Zend\Validator\StringLength',
                    'options' => [
                        'max'      => 255,
                        'messages' => [
                            \Zend\Validator\StringLength::TOO_LONG => \Vrok\Doctrine\FormHelper::ERROR_TOOLONG,
                        ],
                    ],
                ],
            ],
        ];

        return [
            'stringFilter' => [
                'type' => 'Zend\InputFilter\InputFilter',

                'module'            => $moduleSpec,
                'stringSearch'      => $inputSpec,
                'translationSearch' => $inputSpec,
            ],
        ];
    }
}
