<?php
/**
 * @copyright   (c) 2015, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;
use \Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to set the system options like default locale and language variants.
 */
class Settings extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('translation-settings');

        $service = $this->getServiceLocator()->getServiceLocator()
                ->get('TranslationModule\Service\Translation');
        /* @var $service \TranslationModule\Service\Translation */

        $locales = $service->getLocales();
        $this->add(array(
            'type'    => 'Zend\Form\Element\Select',
            'name'    => 'defaultLocale',
            'options' => array(
                'label'         => 'form.translation.defaultLocale.label',
                'value_options' => $locales,
            ),
            'attributes' => [
                'required' => 'required',
            ],
        ));

        $elements = [];
        foreach($locales as $locale) {
            $languages = $service->getLanguageNames($locale);

            $elements[] = [
                'spec' => [
                    'type'    => 'Zend\Form\Element\Select',
                    'name'    => $locale,
                    'options' => [
                        'label'         => ['form.translation.useLanguage.label', $locale],
                        'value_options' => $languages,
                    ],
                    'attributes' => [
                        'required' => 'required',
                    ],
                ],
            ];
        }

        $this->add(array(
            'type'     => 'Fieldset',
            'name'     => 'languageVariants',
            'options'  => [
                'label' => 'form.translation.languageVariants.label',
            ],
            'elements' => $elements,
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'form.submit',
                'id'    => 'submit',
            ),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $service = $this->getServiceLocator()->getServiceLocator()
                ->get('TranslationModule\Service\Translation');
        /* @var $service \TranslationModule\Service\Translation */

        $locales = $service->getLocales();

        $variants = ['type' => 'Zend\InputFilter\InputFilter'];
        foreach($locales as $locale) {
            $languages = $service->getLanguageNames($locale);
            $variants[$locale] = [
                'required'   => true,
                'allowEmpty' => false,
                'validators' => [
                    [
                        'name'    => 'Zend\Validator\InArray',
                        'options' => [
                            'haystack' => array_keys($languages),
                        ],
                    ],
                ],
            ];
        }

        return [
            'defaultLocale' => [
                'required'   => true,
                'allowEmpty' => false,
                'validators' => [
                    [
                        'name'    => 'Zend\Validator\InArray',
                        'options' => [
                            'haystack' => $locales,
                        ],
                    ],
                ],
            ],

            'languageVariants' => $variants,
        ];
    }
}
