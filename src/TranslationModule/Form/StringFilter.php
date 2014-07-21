<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;

/**
 * Form to filter / search translation strings.
 */
class StringFilter extends Form
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
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
}
