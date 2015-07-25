<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as SessionContainer;

/**
 * Handles CRUD of translation strings.
 */
class StringController extends AbstractActionController
{
    /**
     * Lists all existing translation strings.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $sessionContainer = new SessionContainer(__CLASS__);
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\StringFilter');
        if ($sessionContainer['stringFilter']) {
            $form->setData([
                'stringFilter' => $sessionContainer['stringFilter']
            ]);
        }
        if ($this->request->isPost()) {
            $isValid = $form->setData($this->request->getPost())->isValid();
            if ($isValid) {
                $data = $form->getData();
                $sessionContainer['stringFilter'] = $data['stringFilter'];
            }
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\String');

        $qb = $repository->createQueryBuilder('s');
        $qb->leftJoin('s.translations', 't');

        if ($sessionContainer['stringFilter']
            && !empty($sessionContainer['stringFilter']['stringSearch'])
        ) {
            $qb->andWhere($qb->expr()->like('s.string', ':stringSearch'));
            $qb->setParameter('stringSearch',
                '%'.$sessionContainer['stringFilter']['stringSearch'].'%');
        }

        if ($sessionContainer['stringFilter']
            && !empty($sessionContainer['stringFilter']['translationSearch'])
        ) {
            $qb->andWhere($qb->expr()->like('t.translation', ':translationSearch'));
            $qb->setParameter('translationSearch',
                '%'.$sessionContainer['stringFilter']['translationSearch'].'%');
        }

        if ($sessionContainer['stringFilter']
            && !empty($sessionContainer['stringFilter']['module'])
        ) {
            $qb->andWhere('s.module = :module');
            $qb->setParameter('module', (int)$sessionContainer['stringFilter']['module']);
        }

        $qb->orderBy('s.string');

        $strings = $qb->getQuery()->getResult();

        return $this->createViewModel([
            'form'    => $form,
            'strings' => $strings,
        ]);
    }

    /**
     * Shows a form to enter the data for a new translation string.
     *
     * @return ViewModel
     */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\String');

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

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\String');
        $string = new \TranslationModule\Entity\String;
        $repository->updateInstance($string, $data['string']);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.string.created');
        return $this->redirect()->toRoute('translation/string/create');
    }

    /**
     * Shows a form with the current language data to allow editing.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $string = $this->getEntityFromParam('TranslationModule\Entity\String');
        if (!$string instanceof \TranslationModule\Entity\String) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(['message' => $string]);
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\String');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\String');
        $form->setData(['string' => $repository->getInstanceData($string)]);

        $viewModel = $this->createViewModel([
            'form'   => $form,
            'string' => $string,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }
        $data = $form->getData();
        $repository->updateInstance($string, $data['string']);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.string.edited');
        return $this->redirect()->toRoute('translation/string');
    }

    /**
     * Shows a confirmation form before deleting the requested string.
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $string = $this->getEntityFromParam('TranslationModule\Entity\String');
        if (!$string instanceof \TranslationModule\Entity\String) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(['message' => $string]);
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(['message.string.language.confirmDelete',
            $string->getString()]);

        $viewModel = $this->createViewModel([
            'form'   => $form,
            'string' => $string,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $em->remove($string);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.string.deleted');
        return $this->redirect()->toRoute('translation/string');
    }
}
