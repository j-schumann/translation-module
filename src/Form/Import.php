<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use Vrok\Form\Form;

/**
 * Form to define the input options and select a JSON file.
 */
class Import extends Form
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('translation-import');

        $file = [
            'type'    => 'Zend\Form\Element\File',
            'name'    => 'jsonfile',
            'options' => [
                'label' => 'form.translation.jsonfile.label',
            ],
            'attributes' => [
                'required' => 'required',
                'accept'   => 'text/json',
            ],
        ];
        $this->add($file);

        $overwrite = [
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'overwriteAll',
            'options' => [
                'label'       => 'form.translation.overwriteAll.label',
                'description' => 'form.translation.overwriteAll.description',
            ],
            'attributes' => [],
        ];
        $this->add($overwrite);

        $createLanguages = [
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'createLanguages',
            'options' => [
                'label'       => 'form.translation.createLanguages.label',
                'description' => 'form.translation.createLanguages.description',
            ],
        ];
        $this->add($createLanguages);

        $createModules = [
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'createModules',
            'options' => [
                'label'       => 'form.translation.createModules.label',
                'description' => 'form.translation.createModules.description',
            ],
        ];
        $this->add($createModules);

        $deleteNotImported = [
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'deleteNotImported',
            'options' => [
                'label'       => 'form.translation.deleteNotImported.label',
                'description' => 'form.translation.deleteNotImported.description',
            ],
        ];
        $this->add($deleteNotImported);

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
