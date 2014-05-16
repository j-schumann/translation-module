<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vrok\Doctrine\Entity;

/**
 * Defines a language that is available for translation and display. There can
 * be multiple languages using the same locale as they may be variants for
 * different installations or regions.
 *
 * @ORM\Entity
 * @ORM\Table(name="translation_languages")
 * @ORM\Entity(repositoryClass="TranslationModule\Entity\LanguageRepository")
 */
class Language extends Entity
{
    use \Vrok\Doctrine\Traits\AutoincrementId;

    /**
     * Initialize collection for lazy loading.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

// <editor-fold defaultstate="collapsed" desc="name">
    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false, unique=true)
     */
    protected $name;

    /**
     * Returns the modules name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the language name.
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="locale">
    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    protected $locale;

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="parent">
    /**
     * @ORM\ManyToOne(targetEntity="Language", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * Retrieve the language this one inherits from.
     *
     * @return Language
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent language.
     *
     * @param Language $parent
     * @return self
     */
    public function setParent(Language $parent = null)
    {
        if ($this->parent && $this->parent !== $parent) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;
        if ($parent) {
            $this->parent->addChild($this);
        }

        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="children">
    /**
     * @ORM\OneToMany(targetEntity="Language", mappedBy="parent")
     * */
    protected $children;

    /**
     * Retrieve the languages inheriting from this one.
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Adds the given Language to the collection.
     * Called by $language->setParent to keep the collection consistent.
     *
     * @param Language $child
     * @return boolean  false if the Language was already in the collection,
     *  else true
     */
    public function addChild(Language $child)
    {
        if ($this->children->contains($child)) {
            return false;
        }
        return $this->children->add($child);
    }

    /**
     * Removes the given Language from the collection.
     * Called by $language->setParent to keep the collection consistent.
     *
     * @param Language $child
     * @return boolean     true if the Language was in the collection and was
     *     removed, else false
     */
    public function removeChild(Language $child)
    {
        return $this->children->removeElement($child);
    }

    /**
     * Proxies to addChild for multiple elements.
     *
     * @param Collection $children
     */
    public function addChildren($children)
    {
        foreach($children as $child) {
            $this->addChild($child);
        }
    }

    /**
     * Proxies to removeChild for multiple elements.
     *
     * @param Collection $children
     */
    public function removeChildren($children)
    {
        foreach($children as $child) {
            $this->removeChild($child);
        }
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="translations">
    /**
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="language", cascade={"remove"})
     */
    protected $translations;

    /**
     * Retrieve the Translations for this Language.
     *
     * @return Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Adds the given Translation to the collection.
     * Called by $translation->setLanguage to keep the collection consistent.
     *
     * @param Translation $translation
     * @return boolean  false if the Translation was already in the collection,
     *  else true
     */
    public function addTranslation(Translation $translation)
    {
        if ($this->translations->contains($translation)) {
            return false;
        }
        return $this->translations->add($translation);
    }

    /**
     * Removes the given Translation from the collection.
     * Called by $translation->setLanguage to keep the collection consistent.
     *
     * @param Translation $translation
     * @return boolean     true if the Translation was in the collection and was
     *     removed, else false
     */
    public function removeTranslation(Translation $translation)
    {
        return $this->translations->removeElement($translation);
    }

    /**
     * Proxies to addTranslation for multiple elements.
     *
     * @param Collection $translations
     */
    public function addTranslations($translations)
    {
        foreach($translations as $translation) {
            $this->addTranslation($translation);
        }
    }

    /**
     * Proxies to removeTranslation for multiple elements.
     *
     * @param Collection $translations
     */
    public function removeTranslations($translations)
    {
        foreach($translations as $translation) {
            $this->removeTranslation($translation);
        }
    }
// </editor-fold>
}
