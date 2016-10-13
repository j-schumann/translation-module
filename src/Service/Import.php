<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Service;

use TranslationModule\Entity\Language as LanguageEntity;
use TranslationModule\Entity\Module as ModuleEntity;
use TranslationModule\Entity\Entry as EntryEntity;
use TranslationModule\Entity\Translation as TranslationEntity;

/**
 * Allows to import JSON files containing translations previously exported (on this
 * or another instance). Offers options to overwrite or keep local changes or cleanup
 * not imported local entries.
 */
class Import
{
    /**
     * The translationService instance.
     *
     * @var Translation
     */
    protected $service = null;

    /**
     * List of all (used) modules in the system.
     *
     * @var array
     */
    protected $modules = [];

    /**
     * List of all (used) languages in the system.
     *
     * @var array
     */
    protected $languages = [];

    /**
     * List of all messages and counts produced by the last import.
     *
     * @var array
     */
    protected $result = [];

    /**
     * Whether or not imported languages are automatically created if they don't exist.
     *
     * @var bool
     */
    protected $createLanguages = false;

    /**
     * Whether or not imported modules are automatically created if they don't exist.
     *
     * @var bool
     */
    protected $createModules = false;

    /**
     * Whether or not entries from the "live" module should be skipped.
     *
     * @var bool
     */
    protected $skipLiveModule = true;

    /**
     * Whether or not all entries/details are overwritten, even if the local timestamp is
     * newer, e.g. for restoring backups.
     *
     * @var bool
     */
    protected $overwriteAll = false;

    /**
     * Whether or not entries that weren't imported are deleted.
     *
     * @var bool
     */
    protected $deleteNotImported = false;

    /**
     * Class constructor - stores the dependency.
     *
     * @param Translation $service
     */
    public function __construct(Translation $service)
    {
        $this->service = $service;
    }

    /**
     * Imports the given file into the database using the current settings.
     *
     * @param string $filename
     *
     * @return array
     */
    public function importFile($filename)
    {
        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $result               = $this->importArray([]);
            $result['messages'][] = 'JSON in the given file could not be decoded!';

            return $result;
        }

        return $this->importArray($data);
    }

    /**
     * Imports the decoded data into the database.
     *
     * @param array $data
     *
     * @return array
     */
    public function importArray(array $data)
    {
        $this->result = [
            'importedEntries'      => 0,
            'importedTranslations' => 0,
            'deletedEntries'       => 0,
            'deletedTranslations'  => 0,
            'createdEntries'       => 0,
            'createdTranslations'  => 0,
            'createdModules'       => [],
            'createdLanguages'     => [],
            'skippedModules'       => [],
            'skippedLanguages'     => [],
            'messages'             => [],
        ];

        foreach ($data as $entry) {
            $this->importEntry($entry);
        }

        // commit all updates to the database
        $this->service->getEntityManager()->flush();

        if ($this->deleteNotImported) {
            $this->deleteOldEntries($data);
            $this->service->getEntityManager()->flush();
        }

        if ($this->result['createdEntries']) {
            $this->result['messages'][] = ['message.translation.entriesCreated', $this->result['createdEntries']];
        }
        if ($this->result['createdTranslations']) {
            $this->result['messages'][] = ['message.translation.translationsCreated', $this->result['createdTranslations']];
        }
        if ($this->result['importedEntries'] - $this->result['createdEntries']) {
            $this->result['messages'][] = ['message.translation.entriesUpdated', $this->result['importedEntries'] - $this->result['createdEntries']];
        }
        if ($this->result['importedTranslations'] - $this->result['createdTranslations']) {
            $this->result['messages'][] = ['message.translation.translationsUpdated', $this->result['importedTranslations'] - $this->result['createdTranslations']];
        }
        if ($this->result['deletedEntries']) {
            $this->result['messages'][] = ['message.translation.entriesDeleted', $this->result['deletedEntries']];
        }
        if ($this->result['deletedTranslations']) {
            $this->result['messages'][] = ['message.translation.translationsDeleted', $this->result['deletedTranslations']];
        }
        if (count($this->result['createdModules'])) {
            $this->result['messages'][] = ['message.translation.modulesCreated', implode(', ', $this->result['createdModules'])];
        }
        if (count($this->result['createdLanguages'])) {
            $this->result['messages'][] = ['message.translation.languagesCreated', implode(', ', $this->result['createdLanguages'])];
        }
        if (count($this->result['skippedModules'])) {
            $this->result['messages'][] = ['message.translation.modulesSkipped', implode(', ', $this->result['skippedModules'])];
        }
        if (count($this->result['skippedLanguages'])) {
            $this->result['messages'][] = ['message.translation.languagesSkipped', implode(', ', $this->result['skippedLanguages'])];
        }
        if (!count($this->result['messages'])) {
            $this->result['messages'][] = ['message.translation.noImportUpdates'];
        }

        return $this->result;
    }

    /**
     * Imports the given entry by creating the module (if allowed), updating the local
     * version and updating the translations belonging to this entry.
     *
     * @param array $row
     *
     * @return bool true if the entry was imported, else false
     */
    protected function importEntry(array $row)
    {
        // check for all required field, if one is missing the import is broken
        if (empty($row['string']) || empty($row['module'])
            || empty($row['updatedAt']) || empty($row['translations'])
        ) {
            $this->messages[] = ['message.translation.import.jsonInvalid',
                json_encode($row), ];

            return false;
        }

        $entry = $this->service->getEntryRepository()
                ->findOneBy(['string' => $row['string']]);

        // do not create new "live" entries if not allowed.
        // allow updates to entries that are moved from another module into "live" or
        // from "live" into another module.
        if (!$entry && $row['module'] === 'live' && $this->skipLiveModule) {
            $this->result['skippedModules']['live'] = 'live';

            return false;
        }

        $entry = $this->updateEntry($row, $entry);
        if (!$entry) {
            // the module could not be created or the entry is in the "live" module
            // -> skip the translations
            return false;
        }

        foreach ($row['translations'] as $translation) {
            $this->updateTranslation($translation, $entry);
        }

        return true;
    }

    /**
     * Updates a single translation entry from the given import row.
     *
     * @param array $entry
     *
     * @return EntryEntity|bool the imported entry or false if the translations
     *                           should not be updated
     */
    protected function updateEntry(array $row, EntryEntity $entry = null)
    {
        $module = $this->importModule($row['module']);
        if (!$module) {
            // module not found and not created -> also skip the translations
            return false;
        }

        if ($entry) {
            // prohibit updates of entries from the "live" module, allow if the module
            // changes from or to "live"!
            if ($this->skipLiveModule && $row['module'] === 'live'
                && $entry->getModule()->getName() === 'live'
            ) {
                $this->result['skippedModules']['live'] = 'live';
                // return false so also the translations are not updated
                return false;
            }

            // do not update if the local version is newer
            if (!$this->overwriteAll
                && $row['updatedAt'] <= $entry->getUpdatedAt()->format('Y-m-d H:i:s')
            ) {
                // the entry will not be updated but the translations may be
                return $entry;
            }
        } else {
            $entry = new EntryEntity();
            // string + module are required
            $entry->setString($row['string']);
            $entry->setModule($module);

            $this->service->getEntityManager()->persist($entry);
            // flush here, we need the entry->id for the translation references
            $this->service->getEntityManager()->flush();
            ++$this->result['createdEntries'];
        }

        $entry->setModule($module);
        $entry->setContext(empty($row['context']) ? null : $row['context']);
        $entry->setOccurrences(empty($row['occurrences']) ? null : $row['occurrences']);
        $entry->setParams(empty($row['params']) ? null : $row['params']);
        // tell the Timestampable extension: this date is already in UTC!
        $entry->setUpdatedAt(
                new \DateTime($row['updatedAt'], new \DateTimeZone('UTC')));

        ++$this->result['importedEntries'];

        return $entry;
    }

    /**
     * Updates or creates the given translation.
     *
     * @param array $row
     * @param EntryEntity $entry
     *
     * @return bool
     */
    protected function updateTranslation(array $row, EntryEntity $entry)
    {
        if (empty($row['language']) || empty($row['locale'])
            || empty($row['updatedAt']) || empty($row['translation'])
        ) {
            $this->messages[] = ['message.translation.import.jsonInvalid',
                json_encode($row), ];

            return false;
        }

        $language = $this->importLanguage($row);
        if (!$language) {
            // language not found and not created -> skip the translation
            return false;
        }

        $translation = null;
        foreach ($entry->getTranslations() as $localTranslation) {
            // there is a translation for the current language -> update it
            if ($localTranslation->getLanguage()->getName() == $row['language']) {
                $translation = $localTranslation;
                break;
            }
        }

        if ($translation) {
            // do not update if the local version is newer
            if (!$this->overwriteAll &&
                $row['updatedAt'] <= $translation->getUpdatedAt()->format('Y-m-d H:i:s')
            ) {
                return false;
            }
        } else {
            $translation = new TranslationEntity();
            $translation->setEntry($entry);
            $translation->setLanguage($language);
            $this->service->getEntityManager()->persist($translation);
            ++$this->result['createdTranslations'];
        }

        $translation->setTranslation($row['translation']);
        // tell the Timestampable extension: this date is already in UTC!
        $translation->setUpdatedAt(
                new \DateTime($row['updatedAt'], new \DateTimeZone('UTC')));

        ++$this->result['importedTranslations'];

        return true;
    }

    /**
     * Loads or creates the language defined in the given import entry.
     *
     * @param array $translationEntry
     *
     * @return LanguageEntity|false the existing or created language or false if it
     *                              does not exist and should not be created
     */
    protected function importLanguage(array $translationEntry)
    {
        // we queried for that language before -> return false or the language instance
        if (isset($this->languages[$translationEntry['language']])) {
            return $this->languages[$translationEntry['language']];
        }

        // we query for the name, there may be multiple languages with the same locale
        $language = $this->service->getLanguageRepository()
                    ->findOneBy(['name' => $translationEntry['language']]);

        if (!$language) {
            if (!$this->createLanguages) {
                $this->result['skippedLanguages'][$translationEntry['locale']]
                        = $translationEntry['language'];

                return $this->languages[$translationEntry['language']] = false;
            }

            $language = new LanguageEntity();
            // name + locale are required
            $language->setName($translationEntry['language']);
            $language->setLocale($translationEntry['locale']);
            $this->service->getEntityManager()->persist($language);

            // flush here, we need the language->id for the references
            $this->service->getEntityManager()->flush();
            $this->result['createdLanguages'][$translationEntry['locale']]
                    = $translationEntry['language'];
        }

        // update the locale if it changed
        $language->setLocale($translationEntry['locale']);

        return $this->languages[$translationEntry['language']] = $language;
    }

    /**
     * Loads the module for the import entry or creates it if allowed.
     *
     * @param string $moduleName
     *
     * @return ModuleEntity|false the existing or created Module or false if it
     *                            does not exist and should not be created
     */
    protected function importModule($moduleName)
    {
        // we queried for that module before -> return false or the module instance
        if (isset($this->modules[$moduleName])) {
            return $this->modules[$moduleName];
        }

        $module = $this->service->getModuleRepository()
                    ->findOneBy(['name' => $moduleName]);

        if (!$module) {
            if (!$this->createModules) {
                $this->result['skippedModules'][$moduleName] = $moduleName;

                return $this->modules[$moduleName] = false;
            }

            $module = new ModuleEntity();
            $module->setName($moduleName);
            $this->service->getEntityManager()->persist($module);

            // flush here, we need the module->id for the references
            $this->service->getEntityManager()->flush();
            $this->result['createdModules'][$moduleName] = $moduleName;
        }

        return $this->modules[$moduleName] = $module;
    }

    /**
     * Deletes all not imported Entries from the database. Only for modules that
     * were imported (e.g. if we only imported the module "translation" only entries
     * from that module are deleted).
     *
     * @param array $data
     */
    protected function deleteOldEntries(array $data)
    {
        $entries = $this->service->getEntryRepository()->findAll();

        foreach ($entries as $entry) {
            $moduleName = $entry->getModule()->getName();

            // the entry's module was not imported -> don't delete this entry, maybe
            // we imported only entries of another module
            if (empty($this->modules[$moduleName])) {
                continue;
            }

            // the live module probably contains entries created for objects in this
            // instance so they are probably not imported -> ignore
            if (!$this->skipLiveModule && $moduleName === 'live') {
                continue;
            }

            $match = false;
            foreach ($data as $row) {
                // the entries match e.g. were imported -> don't delete the entry...
                if ($row['string'] == $entry->getString()) {
                    // ... but delete not imported translations for this entry
                    $this->deleteOldTranslations($row, $entry);

                    $match = true;
                    break;
                }
            }

            if (!$match) {
                // also deletes all their translations
                $this->service->getEntityManager()->remove($entry);
                ++$this->result['deletedEntries'];
            }
        }
    }

    /**
     * Checks all translations of the given entry if they were imported (are within
     * the given import entry), if not they are deleted.
     * Called by deleteOldEntries for each imported entry, all not imported entries
     * are deleted completely and their translations with them.
     *
     * @param array        $row
     * @param EntryEntity $entry
     */
    protected function deleteOldTranslations(array $row, EntryEntity $entry)
    {
        foreach ($entry->getTranslations() as $translation) {
            $languageName = $translation->getLanguage()->getName();

            // the translations language was not imported -> don't delete this entry,
            // maybe we imported only entries of another language
            if (empty($this->languages[$languageName])) {
                continue;
            }

            $match = false;
            foreach ($row['translations'] as $entryTranslation) {
                // the entries match e.g. were imported -> don't delete the translation
                if ($entryTranslation['language'] == $languageName) {
                    $match = true;
                    break;
                }
            }

            if (!$match) {
                $this->service->getEntityManager()->remove($translation);
                ++$this->result['deletedTranslations'];
            }
        }
    }

    /**
     * Allows to set multiple options at once.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (isset($options['skipLiveModule'])) {
            $this->setSkipLiveModule($options['skipLiveModule']);
        }
        if (isset($options['deleteNotImported'])) {
            $this->setDeleteNotImported($options['deleteNotImported']);
        }
        if (isset($options['createLanguages'])) {
            $this->setCreateLanguages($options['createLanguages']);
        }
        if (isset($options['createModules'])) {
            $this->setCreateModules($options['createModules']);
        }
        if (isset($options['overwriteAll'])) {
            $this->setOverwriteAll($options['overwriteAll']);
        }
    }

    /**
     * (Re-)Sets whether or not entries from the live module are imported.
     *
     * @param bool $value
     */
    public function setSkipLiveModule($value = true)
    {
        $this->skipLiveModule = (bool) $value;
    }

    /**
     * (Re-)Sets whether or not entries that weren't imported are deleted.
     *
     * @param bool $value
     */
    public function setDeleteNotImported($value = false)
    {
        $this->deleteNotImported = (bool) $value;
    }

    /**
     * (Re-)Sets whether or not imported languages are automatically created if they don't
     * exist.
     *
     * @param bool $value
     */
    public function setCreateLanguages($value = false)
    {
        $this->createLanguages = (bool) $value;
    }

    /**
     * (Re-)Sets whether or not imported modules are automatically created if they don't
     * exist.
     *
     * @param bool $value
     */
    public function setCreateModules($value = false)
    {
        $this->createModules = (bool) $value;
    }

    /**
     * (Re-)Sets whether or not all entries/translations are overwritten, even if the
     * local timestamp is newer, e.g. for restoring backups.
     *
     * @param bool $value
     */
    public function setOverwriteAll($value = false)
    {
        $this->overwriteAll = (bool) $value;
    }
}
