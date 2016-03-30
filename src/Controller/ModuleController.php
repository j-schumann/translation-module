<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Handles CRUD of translation modules.
 */
class ModuleController extends AbstractActionController
{
    /**
     * Lists all existing translation modules.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Module');
        $modules    = $repository->findAll();

        return $this->createViewModel(['modules' => $modules]);
    }

    /**
     * Shows a form to enter the data for a new translation module.
     *
     * @return ViewModel
     */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Module');

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

        $moduleService = $this->getServiceLocator()
                ->get('TranslationModule\Service\Translation');
        $module = $moduleService->createModule($form->getData());

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.module.created');

        return $this->redirect()->toRoute('translation/module');
    }

    /**
     * Shows a form with the current module data to allow editing.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $module = $this->getEntityFromParam('TranslationModule\Entity\Module');
        if (!$module instanceof \TranslationModule\Entity\Module) {
            $this->getResponse()->setStatusCode(404);

            return $this->createViewModel(['message' => $module]);
        }

        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Module');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Module');
        $form->setData($repository->getInstanceData($module));

        $viewModel = $this->createViewModel([
            'form'   => $form,
            'module' => $module,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $repository->updateInstance($module, $form->getData());
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.module.edited');

        return $this->redirect()->toRoute('translation/module');
    }

    /**
     * Shows a confirmation form before deleting the requested module.
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $module = $this->getEntityFromParam('TranslationModule\Entity\Module');
        if (!$module instanceof \TranslationModule\Entity\Module) {
            $this->getResponse()->setStatusCode(404);

            return $this->createViewModel(['message' => $module]);
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(['message.translation.module.confirmDelete',
            $module->getName(), ]);

        $viewModel = $this->createViewModel([
            'form'   => $form,
            'module' => $module,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $em->remove($module);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.module.deleted');

        return $this->redirect()->toRoute('translation/module');
    }
}
