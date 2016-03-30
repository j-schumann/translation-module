<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Service;

use TranslationModule\Entity\Language as LanguageEntity;
use TranslationModule\Entity\Module as ModuleEntity;
use TranslationModule\Entity\String as StringEntity;
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
     * Whether or not all strings/details are overwritten, even if the local timestamp is
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
            'importedStrings'      => 0,
            'importedTranslations' => 0,
            'deletedStrings'       => 0,
            'deletedTranslations'  => 0,
            'createdStrings'       => 0,
            'createdTranslations'  => 0,
            'createdModules'       => [],
            'createdLanguages'     => [],
            'skippedModules'       => [],
            'skippedLanguages'     => [],
            'messages'             => [],
        ];

        foreach ($data as $entry) {
            $this->importString($entry);
        }

        // commit all updates to the database
        $this->service->getEntityManager()->flush();

        if ($this->deleteNotImported) {
            $this->deleteOldStrings($data);
            $this->service->getEntityManager()->flush();
        }

        if ($this->result['createdStrings']) {
            $this->result['messages'][] = ['message.translation.stringsCreated', $this->result['createdStrings']];
        }
        if ($this->result['createdTranslations']) {
            $this->result['messages'][] = ['message.translation.translationsCreated', $this->result['createdTranslations']];
        }
        if ($this->result['importedStrings'] - $this->result['createdStrings']) {
            $this->result['messages'][] = ['message.translation.stringsUpdated', $this->result['importedStrings'] - $this->result['createdStrings']];
        }
        if ($this->result['importedTranslations'] - $this->result['createdTranslations']) {
            $this->result['messages'][] = ['message.translation.translationsUpdated', $this->result['importedTranslations'] - $this->result['createdTranslations']];
        }
        if ($this->result['deletedStrings']) {
            $this->result['messages'][] = ['message.translation.stringsDeleted', $this->result['deletedStrings']];
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
     * @param array $entry
     *
     * @return bool true if the string was imported, else false
     */
    protected function importString(array $entry)
    {
        // check for all required field, if one is missing the import is broken
        if (empty($entry['string']) || empty($entry['module'])
            || empty($entry['updatedAt']) || empty($entry['translations'])
        ) {
            $this->messages[] = ['message.translation.import.jsonInvalid',
                json_encode($entry), ];

            return false;
        }

        $string = $this->service->getStringRepository()
                ->findOneBy(['string' => $entry['string']]);

        // do not create new "live" entries if not allowed.
        // allow updates to entries that are moved from another module into "live" or
        // from "live" into another module.
        if (!$string && $entry['module'] === 'live' && $this->skipLiveModule) {
            $this->result['skippedModules']['live'] = 'live';

            return false;
        }

        $string = $this->updateString($entry, $string);
        if (!$string) {
            // the module could not be created or the string is in the "live" module
            // -> skip the translations
            return false;
        }

        foreach ($entry['translations'] as $translation) {
            $this->updateTranslation($translation, $string);
        }

        return true;
    }

    /**
     * Updates a single translation string from the given import entry.
     *
     * @param array $entry
     *
     * @return StringEntity|bool the imported string or false if the translations
     *                           should not be updated
     */
    protected function updateString(array $entry, StringEntity $string = null)
    {
        $module = $this->importModule($entry['module']);
        if (!$module) {
            // module not found and not created -> also skip the translations
            return false;
        }

        if ($string) {
            // prohibit updates of strings from the "live" module, allow if the module
            // changes from or to "live"!
            if ($this->skipLiveModule && $entry['module'] === 'live'
                && $string->getModule()->getName() === 'live'
            ) {
                $this->result['skippedModules']['live'] = 'live';
                // return false so also the translations are not updated
                return false;
            }

            // do not update if the local version is newer
            if (!$this->overwriteAll
                && $entry['updatedAt'] <= $string->getUpdatedAt()->format('Y-m-d H:i:s')
            ) {
                // the string will not be updated but the translations may be
                return $string;
            }
        } else {
            $string = new StringEntity();
            // string + module are required
            $string->setString($entry['string']);
            $string->setModule($module);

            $this->service->getEntityManager()->persist($string);
            // flush here, we need the string->id for the translation references
            $this->service->getEntityManager()->flush();
            ++$this->result['createdStrings'];
        }

        $string->setModule($module);
        $string->setContext(empty($entry['context']) ? null : $entry['context']);
        $string->setOccurrences(empty($entry['occurrences']) ? null : $entry['occurrences']);
        $string->setParams(empty($entry['params']) ? null : $entry['params']);
        // tell the Timestampable extension: this date is already in UTC!
        $string->setUpdatedAt(
                new \DateTime($entry['updatedAt'], new \DateTimeZone('UTC')));

        ++$this->result['importedStrings'];

        return $string;
    }

    /**
     * Updates or creates the given translation.
     *
     * @param array  $entry
     * @param String $string
     *
     * @return bool
     */
    protected function updateTranslation(array $entry, StringEntity $string)
    {
        if (empty($entry['language']) || empty($entry['locale'])
            || empty($entry['updatedAt']) || empty($entry['translation'])
        ) {
            $this->messages[] = ['message.translation.import.jsonInvalid',
                json_encode($entry), ];

            return false;
        }

        $language = $this->importLanguage($entry);
        if (!$language) {
            // language not found and not created -> skip the translation
            return false;
        }

        $translation = null;
        foreach ($string->getTranslations() as $localTranslation) {
            // there is a translation for the current language -> update it
            if ($localTranslation->getLanguage()->getName() == $entry['language']) {
                $translation = $localTranslation;
                break;
            }
        }

        if ($translation) {
            // do not update if the local version is newer
            if (!$this->overwriteAll &&
                $entry['updatedAt'] <= $translation->getUpdatedAt()->format('Y-m-d H:i:s')
            ) {
                return false;
            }
        } else {
            $translation = new TranslationEntity();
            $translation->setString($string);
            $translation->setLanguage($language);
            $this->service->getEntityManager()->persist($translation);
            ++$this->result['createdTranslations'];
        }

        $translation->setTranslation($entry['translation']);
        // tell the Timestampable extension: this date is already in UTC!
        $translation->setUpdatedAt(
                new \DateTime($entry['updatedAt'], new \DateTimeZone('UTC')));

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
        // we queried for that module before -> return false or the module instance
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
     * Deletes all not imported Strings from the database. Only for modules that
     * were imported (e.g. if we only imported the module "translation" only entries
     * from that module are deleted).
     *
     * @param array $data
     */
    protected function deleteOldStrings(array $data)
    {
        $strings = $this->service->getStringRepository()->findAll();

        foreach ($strings as $string) {
            $moduleName = $string->getModule()->getName();

            // the strings module was not imported -> don't delete this entry, maybe
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
            foreach ($data as $entry) {
                // the entries match e.g. were imported -> don't delete the string...
                if ($entry['string'] == $string->getString()) {
                    // ... but delete not imported translations for this string
                    $this->deleteOldTranslations($entry, $string);

                    $match = true;
                    break;
                }
            }

            if (!$match) {
                // also deletes all their translations
                $this->service->getEntityManager()->remove($string);
                ++$this->result['deletedStrings'];
            }
        }
    }

    /**
     * Checks all translations of the given string if they were imported (are within
     * the given import entry), if not they are deleted.
     * Called by deleteOldStrings for each imported entry, all not imported entries
     * are deleted completely and their translations with them.
     *
     * @param array        $entry
     * @param StringEntity $string
     */
    protected function deleteOldTranslations(array $entry, StringEntity $string)
    {
        foreach ($string->getTranslations() as $translation) {
            $languageName = $translation->getLanguage()->getName();

            // the translations language was not imported -> don't delete this entry,
            // maybe we imported only entries of another language
            if (empty($this->languages[$languageName])) {
                continue;
            }

            $match = false;
            foreach ($entry['translations'] as $entryTranslation) {
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
     * (Re-)Sets whether or not all strings/translations are overwritten, even if the
     * local timestamp is newer, e.g. for restoring backups.
     *
     * @param bool $value
     */
    public function setOverwriteAll($value = false)
    {
        $this->overwriteAll = (bool) $value;
    }
}
