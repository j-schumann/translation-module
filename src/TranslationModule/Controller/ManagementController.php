<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Contains functions to create translation files from the database or import/export
 * all or selected modules/languages.
 */
class ManagementController extends AbstractActionController
{
    public function indexAction()
    {

    }

    /**
     * (Re-)creates the translation files from the current state of the database.
     *
     * @return ViewModel|Response
     */
    public function buildAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage('message.translation.management.confirmBuild');

        $viewModel = $this->createViewModel(array(
            'form'   => $form,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $ts = $this->getServiceLocator()->get('TranslationModule\Service\Translation');
        $ts->generateTranslationFiles();

        $this->flashMessenger()
                ->addSuccessMessage('message.translation.management.filesBuilt');
        return $this->redirect()->toRoute('translation');
    }

    /**
     * Shows a form to select the modules and languages to export, sends the export-file
     * to the browser.
     *
     * @return ViewModel
     */
    public function exportAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Export');

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
        $ts = $this->getServiceLocator()->get('TranslationModule\Service\Translation');
        $language = empty($data['language'])
            ? null
            : $ts->getLanguageRepository()->find($data['language']);
        $module = empty($data['module'])
            ? null
            : $ts->getModuleRepository()->find($data['module']);
        $ts->export($language, $module);
        // export() directly sends to the browser and exits
    }

    /**
     * Shows a form to select the modules and languages to export, sends the export-file
     * to the browser.
     *
     * @return ViewModel
     */
    public function importAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('TranslationModule\Form\Import');

        $viewModel = $this->createViewModel(array(
            'form'   => $form,
            'result' => null,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $post = array_merge_recursive(
            $this->request->getPost()->toArray(),
            $this->request->getFiles()->toArray()
        );
        $isValid = $form->setData($post)->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $data = $form->getData();

        $ts = $this->getServiceLocator()->get('TranslationModule\Service\Translation');
        $import = $ts->createImport($data);
        $result = $import->importFile($data['jsonfile']['tmp_name']);

        foreach($result['messages'] as $message) {
            $this->flashMessenger()->addInfoMessage($this->translate($message));
        }

        return $viewModel;
    }
}
