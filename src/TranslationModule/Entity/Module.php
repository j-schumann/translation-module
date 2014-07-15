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
 * A module bundles all translation entries that belong to a specific (and maybe
 * optional) part of the application.
 *
 * @ORM\Entity
 * @ORM\Table(name="translation_modules")
 * @ORM\Entity(repositoryClass="TranslationModule\Entity\ModuleRepository")
 */
class Module extends Entity
{
    use \Vrok\Doctrine\Traits\AutoincrementId;

    /**
     * Initialize collection for lazy loading.
     */
    public function __construct()
    {
        $this->strings = new ArrayCollection();
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
     * Sets the modules name.
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
// <editor-fold defaultstate="collapsed" desc="strings">
    /**
     * @ORM\OneToMany(targetEntity="String", mappedBy="module", fetch="EXTRA_LAZY")
     */
    protected $strings;

    /**
     * Retrieve the Strings assigned to this Module.
     *
     * @return Collection
     */
    public function getStrings()
    {
        return $this->strings;
    }

    /**
     * Adds the given string to the collection.
     * Called by $string->setModule to keep the collection consistent.
     *
     * @param String $string
     * @return boolean  false if the String was already in the collection, else
     *     true
     */
    public function addString(String $string)
    {
        if ($this->strings->contains($string)) {
            return false;
        }
        return $this->strings->add($string);
    }

    /**
     * Removes the given string from the collection.
     * Called by $string->setModule to keep the collection consistent.
     *
     * @param String $string
     * @return boolean     true if the String was in the collection and was
     *     removed, else false
     */
    public function removeString(String $string)
    {
        return $this->strings->removeElement($string);
    }

    /**
     * Proxies to addTranslation for multiple elements.
     *
     * @param Collection $strings
     */
    public function addStrings($strings)
    {
        foreach($strings as $string) {
            $this->addString($string);
        }
    }

    /**
     * Proxies to removeString for multiple elements.
     *
     * @param Collection $strings
     */
    public function removeStrings($strings)
    {
        foreach($strings as $string) {
            $this->removeString($string);
        }
    }
// </editor-fold>
}
