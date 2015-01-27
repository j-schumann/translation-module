<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;

/**
 * Form to create or edit a translation string.
 */
class String extends Form
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('translation-string');
        
        $this->add(array(
            'type'    => 'TranslationModule\Form\StringFieldset',
            'options' => array(
                'use_as_base_fieldset' => true
            )
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
}
