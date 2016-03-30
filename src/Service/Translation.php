<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Service;

use Doctrine\Common\Persistence\ObjectManager;
use TranslationModule\Entity\Language as LanguageEntity;
use TranslationModule\Entity\Module as ModuleEntity;
use TranslationModule\Entity\String as StringEntity;
use TranslationModule\Entity\Translation as TranslationEntity;
use Zend\I18n\Translator\TextDomain;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Contains processes for creating and managing Translation objects and their
 * associated actions.
 */
class Translation implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * Directory where the translation files build from the database are stored.
     *
     * @var string
     */
    protected $translationDir = 'data/translations';

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Class constructor - stores the ServiceLocator instance.
     * We inject the locator directly as not all services are lazy loaded
     * but some are only used in rare cases.
     * @todo lazyload all required services and include them in the factory
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve the stored service manager instance.
     *
     * @return ServiceLocatorInterface
     */
    private function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Creates a new Language from the given form data.
     *
     * @param array $formData
     *
     * @return LanguageEntity
     */
    public function createLanguage(array $formData)
    {
        $objectManager = $this->getEntityManager();

        $language           = new LanguageEntity();
        $languageRepository = $objectManager
                ->getRepository('TranslationModule\Entity\Language');
        $languageRepository->updateInstance($language, $formData);
        $objectManager->flush();

        return $language;
    }

    /**
     * Creates a new Module from the given form data.
     *
     * @param array $formData
     *
     * @return ModuleEntity
     */
    public function createModule(array $formData)
    {
        $objectManager = $this->getEntityManager();

        $module           = new ModuleEntity();
        $moduleRepository = $objectManager
                ->getRepository('TranslationModule\Entity\Module');
        $moduleRepository->updateInstance($module, $formData);
        $objectManager->flush();

        return $module;
    }

    /**
     * Retrieve the repository for all translation languages.
     *
     * @return \TranslationModule\Entity\LanguageRepository
     */
    public function getLanguageRepository()
    {
        $em = $this->getEntityManager();

        return $em->getRepository('TranslationModule\Entity\Language');
    }

    /**
     * Retrieve the repository for all translation modules.
     *
     * @return \TranslationModule\Entity\ModuleRepository
     */
    public function getModuleRepository()
    {
        $em = $this->getEntityManager();

        return $em->getRepository('TranslationModule\Entity\Module');
    }

    /**
     * Retrieve the repository for all translation strings.
     *
     * @return \TranslationModule\Entity\StringRepository
     */
    public function getStringRepository()
    {
        $em = $this->getEntityManager();

        return $em->getRepository('TranslationModule\Entity\String');
    }

    /**
     * Retrieve the repository for all translations.
     *
     * @return \TranslationModule\Entity\TranslationRepository
     */
    public function getTranslationRepository()
    {
        $em = $this->getEntityManager();

        return $em->getRepository('TranslationModule\Entity\Translation');
    }

    /**
     * Retrieves a list of all locales currently available with one or more
     * languages.
     *
     * @return array [localeA => localeA, localeB => localeB, ...]
     */
    public function getLocales()
    {
        $result = $this->getLanguageRepository()
            ->createQueryBuilder('l')
            ->select('DISTINCT l.locale')
            ->getQuery()
            ->getArrayResult();

        $locales = [];
        foreach ($result as $locale) {
            $locales[$locale['locale']] = $locale['locale'];
        }

        return $locales;
    }

    /**
     * Returns a list of all configured locales indexed by the language id.
     *
     * @todo order and select only the necessary columns in DQL
     *
     * @param string $locale (optional) if set only languages with that locale are returned
     *
     * @return array array(languageId => locale)
     */
    public function getLocalesById($locale = null)
    {
        $languages = $locale
            ? $this->getLanguageRepository()->findBy(['locale' => $locale])
            : $this->getLanguageRepository()->findAll();

        $list = [];
        foreach ($languages as $language) {
            $list[$language->getId()] = $language->getLocale();
        }

        asort($list);

        return $list;
    }

    /**
     * Returns the filename of the translation file specified by the locale/
     * module combination.
     *
     * @param string       $locale
     * @param ModuleEntity $module
     *
     * @return string
     */
    public function getFilename($locale, ModuleEntity $module)
    {
        // de_DE-module.php
        return $this->getTranslationDir().DIRECTORY_SEPARATOR
            .$locale.'-'.$module->getName().'.php';
    }

    /**
     * Allows to generates multiple translation files at once.
     *
     * @param string $locale
     * @param int    $moduleId
     */
    public function generateTranslationFiles($locale = null, $moduleId = null)
    {
        $locales = $locale
            ? [$locale]
            : $this->getLocales();

        $repository = $this->getModuleRepository();
        $modules    = $moduleId
            ? [$repository->find($moduleId)]
            : $repository->findAll();

        foreach ($modules as $module) {
            foreach ($locales as $locale) {
                $this->generateTranslationFile($locale, $module);
            }
        }
    }

    /**
     * Generates the single translation file holding all entries for the given
     * locale and module combination.
     *
     * @param string       $locale
     * @param ModuleEntity $module
     *
     * @throws Exception\RuntimeException
     */
    public function generateTranslationFile($locale, ModuleEntity $module)
    {
        $fileName = $this->getFilename($locale, $module);
        $handle   = fopen($fileName, 'w', false);
        if (!$handle) {
            throw new Exception\RuntimeException('Translation: file "'.$fileName
                .'" could not be created or overwritten!');
        }

        $translations  = $this->getTranslationsByLocale($locale, $module);
        $partialHelper = $this->getServiceLocator()->get('viewhelpermanager')
                ->get('partial');
        $partial = $partialHelper('translation-module/partials/array.template.phtml', [
            'translations' => $translations,
        ]);

        fwrite($handle, $partial);
        fclose($handle);

        // ensure the files are writeable from console and webserver
        chmod($fileName, 0666);

        $this->clearTranslationCache();
    }

    /**
     * Removes all translation files for the given locale.
     *
     * @param string $locale
     */
    public function clearLocaleFiles($locale)
    {
        $modules = $this->getModuleRepository()->findAll();
        foreach ($modules as $module) {
            $filename = $this->getFilename($locale, $module);
            unlink($filename);
        }
    }

    /**
     * Removes all translation files for the given module.
     *
     * @param ModuleEntity $module
     */
    public function clearModuleFiles(ModuleEntity $module)
    {
        $locales = $this->getLocales();
        foreach ($locales as $locale) {
            $filename = $this->getFilename($locale, $module);
            unlink($filename);
        }
    }

    /**
     * Clears the Translators cache, e.g. after translation files were generated
     * or settings changed.
     */
    public function clearTranslationCache()
    {
        // @todo we want to flush only the translations but memcache does not
        // support tags, iterate over all languages and tell the translator
        // to remove the cache elements:
        //  $cacheId = 'Zend_I18n_Translator_Messages_' . md5($textDomain . $locale);
        // but we can not know all used textdomains.
        // just remove all entries for textDomain = default?
        // In ein event auslagern?
        $cache = $this->getServiceLocator()->get('MvcTranslator')->getCache();
        if ($cache instanceof \Zend\Cache\Storage\FlushableInterface) {
            $cache->flush();
        }
    }

    /**
     * Loads all translation entries from the database for the given language
     * and module combination.
     *
     * @param LanguageEntity $language
     * @param ModuleEntity   $module   If null, all modules are fetched.
     *
     * @return array array(string => translation)
     */
    public function getTranslations(LanguageEntity $language,
            ModuleEntity $module = null)
    {
        $qb = $this->getStringRepository()->createQueryBuilder('s');
        $qb->leftJoin('s.translations', 't')
            ->select('s.string, t.translation')
            ->where($qb->expr()->eq('t.language', $language->getId()))

            // NULL means inherit from parent language, so we don't need it here
            // If it is an empty string we want to use it, maybe we don't want
            // to output some phrases in some languages
            ->andWhere('t.translation IS NOT NULL');

        if ($module) {
            $qb->andWhere($qb->expr()->eq('s.module', $module->getId()));
        }

        $result = $qb->getQuery()->getArrayResult();

        $entries = [];
        foreach ($result as $row) {
            $entries[$row['string']] = $row['translation'];
        }

        return $entries;
    }

    /**
     * Loads all translation entries from the database for the given locale and
     * module combination.
     * Includes inheritance, used for translation file generation.
     *
     * @param string       $locale
     * @param ModuleEntity $module If null, all modules are fetched.
     *
     * @return array
     */
    public function getTranslationsByLocale($locale, $module = null)
    {
        $language = $this->getLanguageForLocale($locale);

        return $this->getTranslationsByLanguage($language, $module);
    }

    /**
     * Similar to getTranslations() but includes inheritance.
     *
     * @param LanguageEntity $language
     * @param ModuleEntity   $module   If null, all modules are fetched.
     *
     * @return array
     */
    public function getTranslationsByLanguage(LanguageEntity $language,
            ModuleEntity $module = null)
    {
        $strings = [];

        $parent = $language->getParent();
        if ($parent && $parent->getLocale() == $language->getLocale()) {
            $strings = $this->getTranslationsByLanguage($parent, $module);
        }

        // the merge overwrites entries from the parent languages with the
        // current language
        return array_merge($strings, $this->getTranslations($language, $module));
    }

    /**
     * Return the language to use (as base) for the given locale.
     *
     * @param string $locale
     *
     * @return LanguageEntity language or null if none found
     */
    public function getLanguageForLocale($locale)
    {
        $metaService  = $this->getServiceLocator()->get('Vrok\Service\Meta');
        $useLanguages = $metaService->getValue('translation.useLanguages') ?: [];

        // check if there is a default language for the given code and if this language
        // still exists!
        if (isset($useLanguages[$locale])
            && $language = $this->getLanguageRepository()->find($useLanguages[$locale])
        ) {
            return $language;
        }

        $repository = $this->getLanguageRepository();
        $language   = $repository->findOneBy(['locale' => $locale]);

        if ($language) {
            $useLanguages[$locale] = $language->getId();
            $metaService->setValue('translation.useLanguages', $useLanguages);
        }

        return $language;
    }

    /**
     * Returns a list of all configured language names indexed by their Id.
     *
     * @todo filter in DQL
     *
     * @param string $locale (optional) if set only languages with that locale are returned
     *
     * @return array array(languageId => languageName)
     */
    public function getLanguageNames($locale = null)
    {
        $languages = $locale
            ? $this->getLanguageRepository()->findBy(['locale' => $locale])
            : $this->getLanguageRepository()->findAll();

        $list = [];
        foreach ($languages as $language) {
            /* @var $language LanguageEntity */
            $list[$language->getId()] = $language->getName();
        }

        asort($list);

        return $list;
    }

    /**
     * Returns a list of all available translation modules.
     *
     * @todo filter in DQL
     *
     * @return array array(moduleId => moduleName)
     */
    public function getModuleNames()
    {
        $modules = $this->getModuleRepository()->findAll();
        $list    = [];
        foreach ($modules as $module) {
            $list[$module->getId()] = $module->getName;
        }

        asort($list);

        return $list;
    }

    /**
     * Generates an JSON file holding all (matching) translation entries for import
     * in other instances or for backup.
     *
     * @param LanguageEntity $language (optional) if given only entries for that language
     *                                 are exported
     * @param ModuleEntity   $module   (optional) if given only entries for that module are
     *                                 exported
     */
    public function export($language = null, $module = null)
    {
        $filename = 'translation';
        $data     = [];

        $qb = $this->getStringRepository()->createQueryBuilder('s');
        $qb->leftJoin('s.translations', 't')
            // NULL means inherit from parent language, so we don't need it here
            // If it is an empty string we want to use it, maybe we don't want
            // to output some phrases in some languages
            ->where('t.translation IS NOT NULL');

        if ($language) {
            // we do not filter the translations by language_id as we use
            // $string->getTranslations() afterwards which will return all anyways
            $filename .= '_'.preg_replace('/\s+/', '', $language->getName());
        }

        if ($module) {
            $qb->andWhere('s.module = :moduleId')
               ->setParameter('moduleId', $module->getId());
            $filename .= '_'.$module->getName();
        }

        $result = $qb->getQuery()->getResult();
        foreach ($result as $string) {
            /* @var $string StringEntity */
            $entry = [
                'string'       => $string->getString(),
                'context'      => $string->getContext(),
                'params'       => $string->getParams(),
                'occurrences'  => $string->getOccurrences(),
                'module'       => $string->getModule()->getName(),
                'updatedAt'    => $string->getUpdatedAt()->format('Y-m-d H:i:s'),
                'translations' => [],
            ];

            foreach ($string->getTranslations() as $translation) {
                /* @var $translation TranslationEntity */
                if ($language && $language->getId() != $translation->getLanguage()->getId()) {
                    continue;
                }

                $entry['translations'][] = [
                    'locale'      => $translation->getLanguage()->getLocale(),
                    'language'    => $translation->getLanguage()->getName(),
                    'translation' => $translation->getTranslation(),
                    'updatedAt'   => $translation->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            }

            $data[] = $entry;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $filename .= date('-Ymd').'.json';

        header('Content-Type: text/json');
        header('Content-Length: '.strlen($json));
        header('Content-Disposition: attachment;filename='.$filename);

        echo $json;
        exit;
    }

    /**
     * Loads all translation files from the configured storage directory and
     * returns them as TextDomain.
     * If no files are found null is returned.
     *
     * @param string $locale
     *
     * @return TextDomain
     */
    public function loadMessages($locale)
    {
        $files = scandir(getcwd().DIRECTORY_SEPARATOR.$this->getTranslationDir());
        if (!$files) {
            // @todo warning? error log?
            return;
        }

        $loader       = new \Zend\I18n\Translator\Loader\PhpArray();
        $domainObject = null;

        foreach ($files as $filename) {
            if (!preg_match('/^'.$locale.'-/', $filename)) {
                continue;
            }

            $new = $loader->load($locale,
                $this->getTranslationDir().DIRECTORY_SEPARATOR.$filename);
            if (!$domainObject) {
                $domainObject = $new;
            } else {
                $domainObject->merge($new);
            }
        }

        return $domainObject;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'translator',
            \Vrok\I18n\Translator\Translator::EVENT_LOAD_MESSAGES,
            [$this, 'onLoadMessages'],
            $priority
        );
    }

    /**
     * When the translator tries to load the messages for a given locale
     * we inject our translations to overwrite any other loaded translations
     * from other modules etc.
     *
     * @param EventInterface $e
     */
    public function onLoadMessages(EventInterface $e)
    {
        $locale = $e->getParam('locale');

        return $this->loadMessages($locale);
    }

    /**
     * Retrieve the entity manager.
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }

    /**
     * Creates a new Import instance.
     *
     * @param array $options
     *
     * @return Import
     */
    public function createImport(array $options = [])
    {
        $import = new Import($this);
        $import->setOptions($options);

        return $import;
    }

    /**
     * Allows to set multiple options at once.
     *
     * @todo support ArrayObject etc
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (isset($options['translation_dir'])) {
            $this->setTranslationDir($options['translation_dir']);
        }
    }

    /**
     * Retrieve the current storage directory for translation files built from
     * the DB.
     *
     * @return string
     */
    public function getTranslationDir()
    {
        return $this->translationDir;
    }

    /**
     * Sets a new storage directory for translation files.
     *
     * @param string $dir
     */
    public function setTranslationDir($dir)
    {
        $this->translationDir = $dir;
    }
}
