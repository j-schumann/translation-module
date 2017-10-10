<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use DoctrineModule\Validator\UniqueObject;
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
     *
     * @return array
     */
    public function getFormElementDefinition($fieldName)
    {
        $definition = parent::getFormElementDefinition($fieldName);
        switch ($fieldName) {
            case 'id':
                $definition['type']                  = 'hidden';
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
     *
     * @return array
     */
    public function getInputSpecification($fieldName)
    {
        $spec = parent::getInputSpecification($fieldName);

        switch ($fieldName) {
            case 'name':
                $spec['validators']['stringLength']['options']['messages'] = [
                    \Zend\Validator\StringLength::TOO_LONG => $this->getTranslationString('name').'.tooLong',
                ];

                $spec['validators']['alNum'] = [
                    'name'    => 'Zend\Validator\Regex',
                    'options' => [
                        'pattern'  => '/^[0-9a-zA-Z]+$/',
                        'messages' => [
                            \Zend\Validator\Regex::NOT_MATCH => $this->getTranslationString('name').'.notAlNum',
                        ],
                    ],
                ];

                $spec['validators']['uniqueObject'] = [
                    'name'    => 'DoctrineModule\Validator\UniqueObject',
                    'options' => [
                        'use_context'       => true,
                        'object_repository' => $this,
                        'fields'            => 'name',
                        'object_manager'    => $this->getEntityManager(),
                        'messages'          => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE =>
                                $this->getTranslationString('name').'.notUnique',
                        ],
                    ],
                ];
                break;
        }

        return $spec;
    }
}
