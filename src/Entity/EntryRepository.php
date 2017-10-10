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
 * Holds functions to work with and manage translation strings.
 */
class EntryRepository extends EntityRepository
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
                // no break
            case 'updatedAt':
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
            case 'string':
                $spec['validators']['uniqueObject'] = [
                    'name'    => 'DoctrineModule\Validator\UniqueObject',
                    'options' => [
                        'use_context'       => true,
                        'object_repository' => $this,
                        'fields'            => 'string',
                        'object_manager'    => $this->getEntityManager(),
                        'messages'          => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE =>
                                $this->getTranslationString('string').'.notUnique',
                        ],
                    ],
                ];
                break;

            // this field is automatically filled if empty and thus not required
            case 'updatedAt':
                $spec['required']   = false;
                $spec['allowEmpty'] = true;
                unset($spec['validators']['notEmpty']);
                break;
        }

        return $spec;
    }

    /**
     * Updates the given entry including its translations with the given form
     * data.
     *
     * @param \Vrok\Doctrine\Entity $instance
     * @param array                 $formData
     * @param array                 $changeset if given the resulting changeset of the update
     *                                         is stored in the referenced array
     *
     * @return Entity
     */
    public function updateInstance(\Vrok\Doctrine\Entity $instance, array $formData, array &$changeset = null)
    {
        $objectManager = $this->getEntityManager();
        $translations  = $formData['translations'];
        unset($formData['translations']);

        parent::updateInstance($instance, $formData, $changeset);

        if (! $instance->getId()) {
            // we need the ID or Doctrine won't persist the following
            // related translations
            $objectManager->flush();
        }

        // update all existing translations
        foreach ($instance->getTranslations() as $translation) {
            $element = $translations[$translation->getLanguage()->getId()];
            $value   = $element['isNull'] ? null : $element['translation'];
            $translation->setTranslation($value);
            $objectManager->persist($translation);
            unset($translations[$translation->getLanguage()->getId()]);
        }

        $languageRepository = $objectManager
                ->getRepository('TranslationModule\Entity\Language');

        // create translations for any new languages
        foreach ($translations as $languageId => $element) {
            $language    = $languageRepository->find($languageId);
            $translation = new Translation();
            $translation->setEntry($instance);
            $translation->setLanguage($language);
            $translation->setTranslation(
                $element['isNull'] ? null : $element['translation']
            );
            $objectManager->persist($translation);
        }

        $objectManager->flush();
    }

    /**
     * Extracts the entity data to fill form elements.
     *
     * @param Entry $instance
     *
     * @return array
     */
    public function getInstanceData(\Vrok\Doctrine\Entity $instance)
    {
        $data         = parent::getInstanceData($instance);
        $translations = $data['translations'];

        $data['translations'] = [];
        foreach ($translations as $translation) {
            $data['translations'][$translation->getLanguage()->getId()] = [
                'translation' => $translation->getTranslation(),
                'isNull'      => $translation->getTranslation() === null,
            ];
        }

        return $data;
    }
}
