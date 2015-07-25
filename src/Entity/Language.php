<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
        $this->children     = new ArrayCollection();
    }

// <editor-fold defaultstate="collapsed" desc="children">
    /**
     * @ORM\OneToMany(targetEntity="Language", mappedBy="parent")
     * */
    protected $children;

    /**
     * Retrieve the languages inheriting from this one.
     *
     * @return Language[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Required for the hydrator.
     *
     * @param array|ArrayCollection $elements
     */
    public function addChildren($elements)
    {
        foreach ($elements as $element) {
            $this->children->add($element);
        }
    }

    /**
     * Required for the hydrator.
     *
     * @param array|ArrayCollection $elements
     */
    public function removeChildren($elements)
    {
        foreach ($elements as $element) {
            $this->children->removeElement($element);
        }
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
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
// </editor-fold>
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
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

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
     *
     * @return self
     */
    public function setParent(Language $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="translations">
    /**
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="language", cascade={"remove"}, fetch="EXTRA_LAZY")
     */
    protected $translations;

    /**
     * Retrieve the Translations for this Language.
     *
     * @return Translation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Required for the hydrator.
     *
     * @param array|ArrayCollection $elements
     */
    public function addTranslations($elements)
    {
        foreach ($elements as $element) {
            $this->translations->add($element);
        }
    }

    /**
     * Required for the hydrator.
     *
     * @param array|ArrayCollection $elements
     */
    public function removeTranslations($elements)
    {
        foreach ($elements as $element) {
            $this->translations->removeElement($element);
        }
    }
// </editor-fold>
}
