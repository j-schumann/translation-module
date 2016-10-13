<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Form;

use TranslationModule\Entity\Entry as EntryEntity;
use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Form to create or edit a translation entry.
 */
class EntryFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $formElementManager = null;

    /**
     * @param ServiceLocatorInterface $sl
     */
    public function setFormElementManager(ServiceLocatorInterface $sl)
    {
        $this->formElementManager = $sl;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('entry');

        $languageRepository = $this->getEntityManager()
                ->getRepository('TranslationModule\Entity\Language');
        $entryRepository = $this->getEntityManager()
                ->getRepository(EntryEntity::class);

        // id for ObjectExists
        $this->add($entryRepository->getFormElementDefinition('id'));

        $this->add($entryRepository->getFormElementDefinition('string'));
        $this->add($entryRepository->getFormElementDefinition('context'));
        $this->add($entryRepository->getFormElementDefinition('occurrences'));
        $this->add($entryRepository->getFormElementDefinition('params'));
        $this->add($entryRepository->getFormElementDefinition('module'));

        $languages = $languageRepository->findAll();
        $this->add([
            'type' => 'Fieldset',
            'name' => 'translations',
        ]);

        foreach ($languages as $language) {
            $translation = $this->formElementManager
                    ->get('TranslationModule\Form\TranslationFieldset');
            $translation->setLanguage($language);
            $this->get('translations')->add($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()->getRepository(EntryEntity::class);
        $spec       = $repository->getInputFilterSpecification();

        // remove or will be set to 0000-00-00 because the InputFilter will return null
        unset($spec['updatedAt']);

        return $spec;
    }
}
