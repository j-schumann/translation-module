<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;

/**
 * Form to create or edit a translation language.
 */
class Language extends Form
{
    public function init()
    {
        $this->add(array(
            'type'    => 'TranslationModule\Form\LanguageFieldset',
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
