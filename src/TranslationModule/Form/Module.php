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
 * Form to create or edit a translation module.
 */
class Module extends Form implements InputFilterProviderInterface
{
    public function init()
    {
        $moduleRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Module');

        // the ID field is hidden, we need it for the UniqueObject validator
        $this->add($moduleRepository->getFormElementDefinition('id'));
        $this->add($moduleRepository->getFormElementDefinition('name'));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'form.submit',
                'id'    => 'submit',
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Module');
        return $repository->getInputFilterSpecification();
    }
}
