<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
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

        $this->add([
            'type'    => 'TranslationModule\Form\StringFieldset',
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.submit',
                'id'    => 'submit',
            ],
        ]);
    }
}
