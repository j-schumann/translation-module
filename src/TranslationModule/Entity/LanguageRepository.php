<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use Vrok\Doctrine\EntityRepository;

/**
 * Holds functions to work with and manage translation languages.
 */
class LanguageRepository extends EntityRepository
{
    use \Vrok\Doctrine\Traits\GetById;

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

            case 'parent':
                $definition['options']['find_method'] = array(
                    'name'   => 'getPotentialParents',
                    'params' => array(
                        'languageId' => 0,
                    ),
                );

                // @todo - validator to prevent setting a parent which in turn
                // has the current element as parent/grandparent/etc
                break;
        }

        return $definition;
    }

    /**
     * Get a list of languages that can be set as parents for the language given
     * by its ID.
     * Does no deep-check for circular references!
     *
     * @param int $languageId
     * @return Collection
     */
    public function getPotentialParents($languageId)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT l FROM TranslationModule\Entity\Language l'
            . ' WHERE l.id <> :id AND (l.parent <> :parent OR l.parent IS NULL)'
            . ' ORDER BY l.name ASC');
        $query->setParameters(array(
            'id'     => (int)$languageId,
            'parent' => (int)$languageId
        ));
        return $query->getResult();
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
