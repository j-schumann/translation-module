<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
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
 * @ORM\Entity(repositoryClass="TranslationModule\Entity\TranslationRepository")
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
     *
     * @return self
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="entry">
    /**
     * @var Entry
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="translations")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $entry;

    /**
     * Retrieve the Entry this translation belongs to.
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Sets the entry.
     *
     * @param Entry $entry
     *
     * @return self
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;

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
     *
     * @return self
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;

        return $this;
    }
// </editor-fold>
}
