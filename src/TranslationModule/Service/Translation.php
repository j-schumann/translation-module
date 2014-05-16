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
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Contains processes for creating and managing Translation objects and their
 * associated actions.
 */
class Translation implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Stores the listeners attached to the eventmanager.
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Directory where the translation files build from the database are stored.
     *
     * @var string
     */
    protected $translationDir = 'data/translations';

    /**
     * Creates a new Language from the given form data.
     *
     * @param array $formData
     * @return LanguageEntity
     */
    public function createLanguage(array $formData)
    {
        $objectManager = $this->getEntityManager();

        $language = new LanguageEntity();
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
     * @return ModuleEntity
     */
    public function createModule(array $formData)
    {
        $objectManager = $this->getEntityManager();

        $module = new ModuleEntity();
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
     * @return array
     */
    public function getLocales()
    {
        $result = $this->getLanguageRepository()
            ->createQueryBuilder('l')
            ->select('DISTINCT l.locale')
            ->getQuery()
            ->getArrayResult();
        // @link http://stackoverflow.com/a/13969241/1341762
        // array_map use doubles memory usage but there should not be many
        // different locales...
        return array_map('current', $result);
    }

    /**
     * Returns the filename of the translation file specified by the locale/
     * module combination.
     *
     * @param string $locale
     * @param ModuleEntity $module
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
     * @param int $moduleId
     */
    public function generateTranslationFiles($locale = null, $moduleId = null)
    {
        $locales = $locale
            ? array($locale)
            : $this->getLocales();

        $repository = $this->getModuleRepository();
        $modules = $moduleId
            ? array($repository->getById($moduleId))
            : $repository->findAll();

        foreach($modules as $module) {
            foreach($locales as $locale) {
                $this->generateTranslationFile($locale, $module);
            }
        }
    }

    /**
     * Generates the single translation file holding all entries for the given
     * locale and module combination.
     *
     * @param string $locale
     * @param ModuleEntity $module
     * @throws Exception\RuntimeException
     */
    public function generateTranslationFile($locale, ModuleEntity $module)
    {
        $fileName = $this->getFilename($locale, $module);
        $handle = fopen($fileName, 'w', false);
        if (!$handle) {
            throw new Exception\RuntimeException('Translation: file "'.fileName
                .'" could not be created or overwritten!');
        }

        $translations = $this->getTranslationsByLocale($locale, $module);
        $partialHelper = $this->getServiceLocator()->get('viewhelpermanager')
                ->get('partial');
        $partial = $partialHelper('translation-module/partials/array.template.phtml', array(
            'translations' => $translations,
        ));

        fwrite($handle, $partial);
        fclose($handle);

        // ensure the files are writeable from console and webserver
        chmod($fileName, 0666);

        // @todo we want to flush only the translations but memcache does not
        // support tags, iterate over all languages and tell the translator
        // to remove the cache elements:
        //  $cacheId = 'Zend_I18n_Translator_Messages_' . md5($textDomain . $locale);
        // but we can not know all used textdomains.
        // just remove all entries for textDomain = default?
        // In ein event auslagern?
        $cache = $this->getServiceLocator()->get('translator')->getCache();
        if ($cache instanceof \Zend\Cache\Storage\FlushableInterface) {
            $cache->flush();
        }
    }

    /**
     * Removes all translation files for the given locale
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
        foreach($locales as $locale) {
            $filename = $this->getFilename($locale, $module);
            unlink($filename);
        }
    }

    /**
     * Loads all translation entries from the database for the given language
     * and module combination.
     *
     * @param LanguageEntity $language
     * @param ModuleEntity $module      If null, all modules are fetched.
     * @return array    array(string => translation)
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

        $entries = array();
        foreach($result as $row) {
            $entries[$row['string']] = $row['translation'];
        }

        return $entries;
    }

    /**
     * Loads all translation entries from the database for the given locale and
     * module combination.
     * Includes inheritance, used for translation file generation.
     *
     * @param string $locale
     * @param ModuleEntity $module  If null, all modules are fetched.
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
     * @param ModuleEntity $module     If null, all modules are fetched.
     * @return array
     */
    public function getTranslationsByLanguage(LanguageEntity $language,
            ModuleEntity $module = null)
    {
        $strings = array();

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
     * @todo metaservice implementieren
     * @param string $locale
     * @return LanguageEntity  language or null if none found
     */
    public function getLanguageForLocale($locale)
    {
        //$useLanguages = \Default_Service_Meta::get('translation.useLanguages') ?: array();

        // check if there is a default language for the given code and if this language
        // still exists!
//        if (isset($useLanguages[$code])
//            && \Model_TranslationLanguage::getById($useLanguages[$code]))
//        {
//            return $useLanguages[$code];
//        }

        $repository = $this->getLanguageRepository();
        $language = $repository->findOneBy(array('locale' => $locale));

        if ($language) {
            //$useLanguages[$locale] = $language->getId();
            //\Default_Service_Meta::set('translation.useLanguages', $useLanguages);
        }

        return $language;
    }

    /**
     * Loads all translation files from the configured storage directory and
     * returns them as TextDomain.
     * If no files are found null is returned.
     *
     * @param string $locale
     * @return TextDomain
     */
    public function loadMessages($locale)
    {
        $files = scandir(getcwd().DIRECTORY_SEPARATOR.$this->getTranslationDir());
        if (!$files) {
            // @todo warning? error log?
            return null;
        }

        $loader = new \Zend\I18n\Translator\Loader\PhpArray();
        $domainObject = null;

        foreach($files as $filename) {
            if (!preg_match('/^[a-z]{2}_[A-Z]{2}-/', $filename)) {
                continue;
            }

            $new = $loader->load($locale,
                $this->getTranslationDir().DIRECTORY_SEPARATOR.$filename);
            if (!$domainObject) {
                $domainObject = $new;
            }
            else {
                $domainObject->merge($new);
            }
        }

        return $domainObject;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'translator',
            \Vrok\I18n\Translator\Translator::EVENT_LOAD_MESSAGES,
            array($this, 'onLoadMessages')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
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
     * Allows to set multiple options at once.
     *
     * @todo support ArrayObject etc
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
