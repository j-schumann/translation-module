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
     *
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
     * @return String[]
     */
    public function getStrings()
    {
        return $this->strings;
    }

    /**
     * Required for the hydrator.
     *
     * @param array|ArrayCollection $elements
     */
    public function addStrings($elements)
    {
        foreach ($elements as $element) {
            $this->strings->add($element);
        }
    }

    /**
     * Required for the hydrator.
     *
     * @param array|ArrayCollection $elements
     */
    public function removeStrings($elements)
    {
        foreach ($elements as $element) {
            $this->strings->removeElement($element);
        }
    }
// </editor-fold>
}
