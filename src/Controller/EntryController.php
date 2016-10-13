<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as SessionContainer;

/**
 * Handles CRUD of translation entries.
 */
class EntryController extends AbstractActionController
{
    /**
     * Lists all existing translation entries.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $sessionContainer = new SessionContainer(__CLASS__);
        $form             = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\EntryFilter');
        if ($sessionContainer['entryFilter']) {
            $form->setData([
                'entryFilter' => $sessionContainer['entryFilter'],
            ]);
        }
        if ($this->request->isPost()) {
            $isValid = $form->setData($this->request->getPost())->isValid();
            if ($isValid) {
                $data                             = $form->getData();
                $sessionContainer['entryFilter'] = $data['entryFilter'];
            }
        }

        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Entry');

        $qb = $repository->createQueryBuilder('s');
        $qb->leftJoin('s.translations', 't');

        if ($sessionContainer['entryFilter']
            && !empty($sessionContainer['entryFilter']['stringSearch'])
        ) {
            $qb->andWhere($qb->expr()->like('s.string', ':stringSearch'));
            $qb->setParameter('stringSearch',
                '%'.$sessionContainer['entryFilter']['stringSearch'].'%');
        }

        if ($sessionContainer['entryFilter']
            && !empty($sessionContainer['entryFilter']['translationSearch'])
        ) {
            $qb->andWhere($qb->expr()->like('t.translation', ':translationSearch'));
            $qb->setParameter('translationSearch',
                '%'.$sessionContainer['entryFilter']['translationSearch'].'%');
        }

        if ($sessionContainer['entryFilter']
            && !empty($sessionContainer['entryFilter']['module'])
        ) {
            $qb->andWhere('s.module = :module');
            $qb->setParameter('module', (int) $sessionContainer['entryFilter']['module']);
        }

        $qb->orderBy('s.string');

        $entries = $qb->getQuery()->getResult();

        return $this->createViewModel([
            'form'    => $form,
            'entries' => $entries,
        ]);
    }

    /**
     * Shows a form to enter the data for a new translation entry.
     *
     * @return ViewModel
     */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Entry');

        $viewModel = $this->createViewModel([
            'form' => $form,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $data = $form->getData();

        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Entry');
        $entry     = new \TranslationModule\Entity\Entry();
        $repository->updateInstance($entry, $data['entry']);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.entry.created');

        return $this->redirect()->toRoute('translation/entry/create');
    }

    /**
     * Shows a form with the current language data to allow editing.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $entry = $this->getEntityFromParam('TranslationModule\Entity\Entry');
        if (!$entry instanceof \TranslationModule\Entity\Entry) {
            $this->getResponse()->setStatusCode(404);

            return $this->createViewModel(['message' => $entry]);
        }

        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Entry');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Entry');
        $form->setData(['entry' => $repository->getInstanceData($entry)]);

        $viewModel = $this->createViewModel([
            'form'   => $form,
            'entry' => $entry,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }
        $data = $form->getData();
        $repository->updateInstance($entry, $data['entry']);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.entry.edited');

        return $this->redirect()->toRoute('translation/entry');
    }

    /**
     * Shows a confirmation form before deleting the requested entry.
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $entry = $this->getEntityFromParam('TranslationModule\Entity\Entry');
        if (!$entry instanceof \TranslationModule\Entity\Entry) {
            $this->getResponse()->setStatusCode(404);

            return $this->createViewModel(['message' => $entry]);
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(['message.entry.language.confirmDelete',
            $entry->getString(), ]);

        $viewModel = $this->createViewModel([
            'form'   => $form,
            'entry' => $entry,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $em->remove($entry);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.entry.deleted');

        return $this->redirect()->toRoute('translation/entry');
    }
}
