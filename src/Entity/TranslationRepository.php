<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use Vrok\Doctrine\EntityRepository;

/**
 * Holds functions to work with and manage translations.
 */
class TranslationRepository extends EntityRepository
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
        }

        return $spec;
    }
}
