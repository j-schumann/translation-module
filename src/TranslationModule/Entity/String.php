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
 * Translation entry that can be translated into different languages and holds
 * additional information about parameters, occurences etc.
 *
 * @ORM\Entity
 * @ORM\Table(name="translation_strings")
 * @ORM\Entity(repositoryClass="TranslationModule\Entity\StringRepository")
 */
class String extends Entity
{
    use \Vrok\Doctrine\Traits\AutoincrementId;
    use \Vrok\Doctrine\Traits\ModificationDate;

    /**
     * Initialize collection for lazy loading.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

// <editor-fold defaultstate="collapsed" desc="string">
    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $string;

    /**
     * Returns the string.
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Sets the string.
     *
     * @param string $string
     * @return self
     */
    public function setString($string)
    {
        $this->string = $string;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="context">
    /**
     * @var string
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $context;

    /**
     * Returns the context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the context.
     *
     * @param string $context
     * @return self
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="params">
    /**
     * @var string
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $params;

    /**
     * Returns the parameters.
     *
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the parameters.
     *
     * @param string $params
     * @return self
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="occurences">
    /**
     * @var string
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $occurences;

    /**
     * Returns the occurences.
     *
     * @return string
     */
    public function getOccurences()
    {
        return $this->occurences;
    }

    /**
     * Sets the occurences.
     *
     * @param string $occurences
     * @return self
     */
    public function setOccurences($occurences)
    {
        $this->occurences = $occurences;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="module">
    /**
     * @var Module
     * @ORM\ManyToOne(targetEntity="Module", inversedBy="strings")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $module;

    /**
     * Retrieve the module this entry belongs to.
     *
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Sets the module.
     *
     * @param Module $module
     * @return self
     */
    public function setModule(Module $module)
    {
        // remove from old module
        if ($this->module && $this->module !== $module) {
            $this->module->removeString($this);
        }

        $this->module = $module;
        $this->module->addString($this);

        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="translations">
    /**
     * @ORM\OneToMany(targetEntity="Translation", mappedBy="string", cascade={"persist", "remove"})
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
     * Called by $translation->setString to keep the collection consistent.
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
     * Called by $translation->setString to keep the collection consistent.
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
