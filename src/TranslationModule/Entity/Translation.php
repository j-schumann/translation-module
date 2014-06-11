<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vrok\Doctrine\Entity;

/**
 * Object that represents the translation of an Entry into a specific language.
 *
 * @ORM\Entity
 * @ORM\Table(name="translation_translations")
 * @ORM\Entity(repositoryClass="TranslationModule\Entity\StringRepository")
 */
class Translation extends Entity
{
    use \Vrok\Doctrine\Traits\ModificationDate;

// <editor-fold defaultstate="collapsed" desc="translation">
    /**
     * @var string
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected $translation;

    /**
     * Returns the translation.
     *
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Sets the translation.
     *
     * @param string $translation
     * @return self
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;
        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="string">
    /**
     * @var String
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="String", inversedBy="translations")
     * @ORM\JoinColumn(name="string_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $string;

    /**
     * Retrieve the String this translation belongs to.
     *
     * @return String
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Sets the string.
     *
     * @param String $string
     * @return self
     */
    public function setString(String $string)
    {
        // remove from old string
        if ($this->string && $this->string !== $string) {
            $this->string->removeTranslation($this);
        }

        $this->string = $string;
        $this->string->addTranslation($this);

        return $this;
    }
    // </editor-fold>
// <editor-fold defaultstate="collapsed" desc="language">
    /**
     * @var Language
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Language", inversedBy="translations")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $language;

    /**
     * Retrieve the language this translation belongs to.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the translated language.
     *
     * @param Language $language
     * @return self
     */
    public function setLanguage(Language $language)
    {
        // remove from old language
        if ($this->language && $this->language !== $language) {
            $this->language->removeTranslation($this);
        }

        $this->language = $language;
        $this->language->addTranslation($this);

        return $this;
    }
// </editor-fold>
}
