<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
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

        $file = array(
            'type'    => 'Zend\Form\Element\File',
            'name'    => 'jsonfile',
            'options' => array(
                'label' => 'form.translation.jsonfile.label',
            ),
            'attributes' => array(
                'required' => 'required',
                'accept'   => 'text/json',
            ),
        );
        $this->add($file);

        $overwrite = array(
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'overwriteAll',
            'options' => array(
                'label'       => 'form.translation.overwriteAll.label',
                'description' => 'form.translation.overwriteAll.description',
            ),
            'attributes' => array(
            ),
        );
        $this->add($overwrite);

        $createLanguages = array(
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'createLanguages',
            'options' => array(
                'label'       => 'form.translation.createLanguages.label',
                'description' => 'form.translation.createLanguages.description',
            ),
        );
        $this->add($createLanguages);

        $createModules = array(
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'createModules',
            'options' => array(
                'label'       => 'form.translation.createModules.label',
                'description' => 'form.translation.createModules.description',
            ),
        );
        $this->add($createModules);

        $deleteNotImported = array(
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'deleteNotImported',
            'options' => array(
                'label'       => 'form.translation.deleteNotImported.label',
                'description' => 'form.translation.deleteNotImported.description',
            ),
        );
        $this->add($deleteNotImported);

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
