<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

use Nette\Forms\Form;
use WebCMS\InvestformModule\Common\PdfPrinter;
use WebCMS\InvestformModule\Common\EmailSender;
use WebCMS\InvestformModule\Entity\Address;

/**
 * Description of
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class InvestformPresenter extends BasePresenter
{
    private $investment;

    protected function startup()
    {
    	parent::startup();
    }

    protected function beforeRender()
    {
	   parent::beforeRender();
    }

    protected function createComponentGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Investment");

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);
        $grid->addFilterDateRange('created', 'Created');

        $grid->addColumnDate('created', 'Created', \Grido\Components\Columns\Date::FORMAT_DATETIME)
            ->setSortable();
        $grid->addColumnNumber('id', 'Contract id')->setSortable();

        $grid->addColumnText('pin', 'Business Id')->setCustomRender(function($item) {
            if ($item->getBusinessman()) {
                return $item->getBusinessman()->getBusinessId();
            } else {
                return $item->getPin();
            }
        });

        $grid->addColumnText('name', 'Name')->setCustomRender(function($item) {
            return $item->getAddress()->getName() . ' ' . $item->getAddress()->getLastname();
        });
        $grid->addColumnText('company', 'Company')->setCustomRender(function($item) {
            return $item->getCompany();
        });
        //TODO prekladac
        $grid->addColumnText('demand', 'Demand')->setCustomRender(function($item) {
            return "Odesláno";
        });
        $grid->addColumnText('contract', 'Contract')->setCustomRender(function($item) {
            return $item->getContractSend() ? 'Odesláno' : 'Neodesláno';
        });
        $grid->addColumnText('contractClosed', 'Contract closed')->setCustomRender(function($item) {
            return $item->getContractClosed() ? 'Yes' : 'No';
        });
        $grid->addColumnText('contractPaid', 'Contract paid')->setCustomRender(function($item) {
            return $item->getContractPaid() ? 'Yes' : 'No';
        });
        $grid->addColumnText('clientContacted', 'Client contacted')->setCustomRender(function($item) {
            return $item->getClientContacted() ? 'Yes' : 'No';
        });

        $grid->addActionHref("send", 'Send', 'send', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'purple')));
        $grid->addActionHref("download", 'Download', 'download', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'purple')));
        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'green')));
        $grid->addActionHref("delete", 'Delete', 'delete', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));
        $grid->addActionHref("closed", 'Contract closed', 'closed', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'green')));
        $grid->addActionHref("paid", 'Contract Paid', 'paid', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'green')));
        $grid->addActionHref("contacted", 'Contacted', 'contacted', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'green')));

        // $operations = array('downloadGrid' => 'Download', 'deleteGrid' => 'Delete');
        // $grid->setOperation($operations, $this->handleGridOperations)
        //     ->setConfirm('deleteGrid', 'Are you sure you want to delete %i items?');

        return $grid;
    }

    /**
     * Common handler for grid operations.
     * @param string $operation
     * @param array $id
     */
    public function handleGridOperations($operation, $id)
    {
        if (!$id) {
            $this->flashMessage('No rows selected.', 'error');
        }

        $this->forward($operation, array(
            'idPage' => $this->actualPage->getId(),
            'id' => $id
        ));
    }

    public function actionDownloadGrid()
    {
        $rows = $this->getParameter('id');

        // $zipSubfolder = 's' . date('Y-m-d-H-i-s');

        // foreach ($rows as $key => $value) {
        //     $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($value);

        //     $zip = new PdfPrinter($investment);            

        //     $zip->savePdfToZip($zipSubfolder);
        // }
        
        $this->flashMessage("Done.", 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDeleteGrid()
    {
        $rows = $this->getParameter('id');
        
        foreach ($rows as $key => $value) {
            $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($value);

            $this->em->remove($investment);
        }

        $this->em->flush();

        $this->flashMessage("Contracts has been deleted", 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function createComponentForm($name)
    {
        $form = $this->createForm();

        $form->addText('phone', 'Phone');
        $form->addText('email', 'Email');
        $form->addText('birthdateNumber', 'Birthdate number');
        $form->addText('company', 'Company');
        $form->addText('registrationNumber', 'Registration number');
        $form->addText('investment', 'Investment amount');
        $form->addText('bankAccount', 'Bank account');
        $form->addSelect('investmentLength', 'Investment length', array(3 => 3, 5 => 5));
        $form->addText('pin', 'Pin');

        $address = $form->addContainer('Address');
        $address->addText('name', 'Name:');
        $address->addText('lastname', 'Lastname:');
        $address->addText('street', 'Street:');
        $address->addText('postcode', 'Postcode:');
        $address->addText('city', 'City:');

        $postalAddress = $form->addContainer('PostalAddress');
        $postalAddress->addText('name', 'Name:');
        $postalAddress->addText('lastname', 'Lastname:')
            ->addConditionOn($form['PostalAddress']['name'], Form::FILLED)
            ->addRule(Form::FILLED, 'Lastname is mandatory.');
        $postalAddress->addText('street', 'Street:')
            ->addConditionOn($form['PostalAddress']['name'], Form::FILLED)
            ->addRule(Form::FILLED, 'Street is mandatory.');
        $postalAddress->addText('postcode', 'Postcode:')
            ->addConditionOn($form['PostalAddress']['name'], Form::FILLED)
            ->addRule(Form::FILLED, 'Postcode is mandatory.');
        $postalAddress->addText('city', 'City:')
            ->addConditionOn($form['PostalAddress']['name'], Form::FILLED)
            ->addRule(Form::FILLED, 'City is mandatory.');

        if (is_object($this->investment->getAddress())) {
            $address->setDefaults($this->investment->getAddress()->toArray());    
        }
        
        if (is_object($this->investment->getPostalAddress())) {
            $postalAddress->setDefaults($this->investment->getPostalAddress()->toArray());
        }

        $form->addSubmit('save', 'Save');
        $form->setDefaults($this->investment->toArray());

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        $this->investment->setPhone($values->phone);
        $this->investment->setEmail($values->email);
        $this->investment->setBirthdateNumber($values->birthdateNumber);
        $this->investment->setCompany($values->company);
        $this->investment->setPin($values->pin);
        $this->investment->setBankAccount(str_replace('_', '', $values->bankAccount));
        $this->investment->setRegistrationNumber($values->registrationNumber);
        $this->investment->setInvestment($values->investment);
        $this->investment->setInvestmentLength($values->investmentLength);

        if (!empty($values->pin)) {
            //check if businessman exists
            $businessman = $this->em->getRepository('WebCMS\InvestformModule\Entity\Businessman')->findOneBy(array(
                'businessId' => $values->pin
            ));
            if ($businessman) {
                $this->investment->setBusinessman($businessman);
            }
        }

        $address = $this->investment->getAddress();
        $address->setName($values->Address->name);
        $address->setLastname($values->Address->lastname);
        $address->setStreet($values->Address->street);
        $address->setPostcode($values->Address->postcode);
        $address->setCity($values->Address->city);

        $postalAddress = $this->investment->getPostalAddress();
        if(!is_object($postalAddress)) {
            $postalAddress = new Address;
            
            $this->investment->setPostalAddress($postalAddress);
            $this->em->persist($postalAddress);
        }

        $postalAddress->setName($values->PostalAddress->name);
        $postalAddress->setLastname($values->PostalAddress->lastname);
        $postalAddress->setStreet($values->PostalAddress->street);
        $postalAddress->setPostcode($values->PostalAddress->postcode);
        $postalAddress->setCity($values->PostalAddress->city);

        $this->em->flush();

        $this->flashMessage('Contract has been updated.', 'success');

        $roles = $this->user->getIdentity()->getRoles();
        if (count(array_intersect(array('superadmin', 'admin'), $roles)) > 0) {
            $this->forward('default', array(
                'idPage' => $this->actualPage->getId()
            ));
        } else {
            $this->forward('Businessman:default', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
        
    }

    public function actionUpdate($id, $idPage)
    {
        $this->reloadContent();

        $this->investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);

        $this->template->idPage = $idPage;
    }

    public function actionSend($id, $idPage, $from = NULL)
    {
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);

        if ($investment->getBirthdateNumber()) {
            
            $emailSender = new EmailSender($this->settings, $investment, 'contract');
            $emailSender->send();

            $investment->setContractSend(true);
            $this->em->flush();

            $this->flashMessage('Contract has been sent to the client\'s email address.', 'success');

        } else {

            $this->flashMessage("Please fill client's birthdate number.", 'error');

        }

        if ($from == 'businessman') {
            
            $this->forward('Businessman:detail', array(
                'id' => $investment->getBusinessman()->getId(),
                'idPage' => $idPage
            ));

        } elseif ($from == 'company') {
            
            //TODO

        } else {

            $this->forward('default', array(
                'idPage' => $this->actualPage->getId()
            ));

        }
        
    }

    public function actionDelete($id)
    {
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);

        $this->em->remove($investment);
        $this->em->flush();

        $this->flashMessage('Investment has been removed.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDownload($id)
    {        
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);
        $pdfPrinter = new PdfPrinter($investment);

        $this->sendResponse($pdfPrinter->printPdfContract(true, $investment->getInvestmentDate()));
    }

    public function actionContacted($id, $idPage)
    {
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);
        $investment->setClientContacted($investment->getClientContacted() ? false : true);

        $this->em->flush();

        $this->flashMessage('Parameter has been changed.', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionPaid($id, $idPage)
    {
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);

        if ($investment->getContractClosed()) {
            $investment->setContractPaid($investment->getContractPaid() ? false : true);

            $this->em->flush();

            $this->flashMessage('Parameter has been changed.', 'success');
        } else {
            $this->flashMessage('Contract must be closed first.', 'error');
        }
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionClosed($id, $idPage)
    {
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);

        if ($investment->getContractSend()) {
            $investment->setContractClosed($investment->getContractClosed() ? false : true);

            $this->em->flush();

            $this->flashMessage('Parameter has been changed.', 'success');
        } else {
            $this->flashMessage('Contract must be sent first.', 'error');
        }
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDefault($idPage)
    {

    }

    public function renderDefault($idPage)
    {
    	$this->reloadContent();
    	$this->template->idPage = $idPage;
    }
}
