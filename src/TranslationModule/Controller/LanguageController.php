<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Handles CRUD of translation languages.
 */
class LanguageController extends AbstractActionController
{
    /**
     * Lists all existing translation languages.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Language');
        $languages = $repository->findAll();

        return $this->createViewModel(array('languages' => $languages));
    }

    /**
     * Shows a form to enter the data for a new translation language.
     *
     * @return ViewModel
     */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Language');

        $viewModel = $this->createViewModel(array(
            'form' => $form,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $data = $form->getData();
        $translationService = $this->getServiceLocator()
                ->get('TranslationModule\Service\Translation');
        $language = $translationService->createLanguage($data['language']);

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.language.created');
        return $this->redirect()->toRoute('translation/language');
    }

    /**
     * Shows a form with the current language data to allow editing.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $language = $this->getEntityFromParam('TranslationModule\Entity\Language');
        if (!$language instanceof \TranslationModule\Entity\Language) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(array('message' => $language));
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('TranslationModule\Entity\Language');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Language');
        $form->setData(array('language' => $repository->getInstanceData($language)));

        $viewModel = $this->createViewModel(array(
            'form'     => $form,
            'language' => $language,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }
        $data = $form->getData();
        $repository->updateInstance($language, $data['language']);
        $em->persist($language);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.language.edited');
        return $this->redirect()->toRoute('translation/language');
    }

    /**
     * Shows a confirmation form before deleting the requested language.
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $language = $this->getEntityFromParam('TranslationModule\Entity\Language');
        if (!$language instanceof \TranslationModule\Entity\Language) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(array('message' => $language));
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setMessage(array('message.translation.language.confirmDelete',
            $language->getName()));

        $viewModel = $this->createViewModel(array(
            'form'     => $form,
            'language' => $language,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $em->remove($language);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.language.deleted');
        return $this->redirect()->toRoute('translation/language');
    }
}
