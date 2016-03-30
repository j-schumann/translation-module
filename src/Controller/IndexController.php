<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    /**
     * Shows the number of languages, modules and translations in the database
     * and links to the important actions.
     */
    public function indexAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $sr = $em->getRepository('TranslationModule\Entity\String');
        $lr = $em->getRepository('TranslationModule\Entity\Language');
        $mr = $em->getRepository('TranslationModule\Entity\Module');

        return $this->createViewModel([
            'stringCount'   => $sr->count(),
            'languageCount' => $lr->count(),
            'moduleCount'   => $mr->count(),
        ]);
    }
}
