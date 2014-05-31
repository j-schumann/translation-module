<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace TranslationModule\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

class ManagementController extends AbstractActionController
{
    public function indexAction()
    {

    }

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
}
