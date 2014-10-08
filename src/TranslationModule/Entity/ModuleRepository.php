<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use Vrok\Doctrine\EntityRepository;

/**
 * Holds functions to work with and manage translation modules.
 */
class ModuleRepository extends EntityRepository
{

    /**
     * Returns a form element specification to use with the form factory.
     *
     * @param string $fieldName
     * @return array
     */
    public function getFormElementDefinition($fieldName)
    {
        $definition = parent::getFormElementDefinition($fieldName);
        switch ($fieldName) {
            case 'id':
                $definition['type'] = 'hidden';
                $definition['options']['allowEmpty'] = true;
                break;

            case 'name':
                $definition['options']['description'] =
                    $this->getTranslationString('name').'.description';
                break;
        }

        return $definition;
    }

    /**
     * Returns the validators&filters for the given field to use in an input filter.
     *
     * @param string $fieldName
     * @return array
     */
    public function getInputSpecification($fieldName)
    {
        $spec = parent::getInputSpecification($fieldName);

        switch ($fieldName) {
            case 'name':
                $spec['validators']['stringLength']['options']['messages'] =
                    array(\Zend\Validator\StringLength::TOO_LONG =>
                        $this->getTranslationString('name').'.tooLong',);

                $spec['validators']['alNum'] = array(
                    'name'    => 'Zend\Validator\Regex',
                    'options' => array(
                        'pattern'  => '/^[0-9a-zA-Z]+$/',
                        'messages' => array(\Zend\Validator\Regex::NOT_MATCH =>
                            $this->getTranslationString('name').'.notAlNum',)
                    ),
                );

                $spec['validators']['uniqueObject'] = array(
                    'name'    => 'DoctrineModule\Validator\UniqueObject',
                    'options' => array(
                        'use_context'       => true,
                        'object_repository' => $this,
                        'fields'            => 'name',
                        'object_manager'    => $this->getEntityManager(),
                        'messages' => array(
                            \DoctrineModule\Validator\UniqueObject::ERROR_OBJECT_NOT_UNIQUE =>
                                $this->getTranslationString('name').'.notUnique',
                        )
                    ),
                );
                break;
        }

        return $spec;
    }
}
