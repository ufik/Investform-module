<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

use WebCMS\InvestformModule\Common\PdfPrinter;

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
        $grid->addColumnText('name', 'Name')->setCustomRender(function($item) {
            return $item->getAddress()->getName() . ' ' . $item->getAddress()->getLastname();
        });
        $grid->addColumnText('company', 'Company')->setCustomRender(function($item) {
            return $item->getCompany();
        });
        $grid->addColumnText('contract', 'Contract')->setCustomRender(function($item) {
            return $item->getBirthdateNumber() ? 'Sent' : 'Not sent';
        });

        $grid->setOperation(array('Download' => 'Download (zip)'), function($operation, $id) {

        });

        $grid->addActionHref("update", 'Edit', 'update', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("send", 'Send', 'send', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));
        $grid->addActionHref("download", 'Download', 'download', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));

        return $grid;
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
        $form->addSelect('investmentLength', 'Investment length', array(3 => 3, 5 => 5));

        $address = $form->addContainer('Address');
        $address->addText('name', 'Name:');
        $address->addText('lastname', 'Lastname:');
        $address->addText('street', 'Street:');
        $address->addText('postcode', 'Postcode:');
        $address->addText('city', 'City:');

        $postalAddress = $form->addContainer('PostalAddress');
        $postalAddress->addText('name', 'Name:');
        $postalAddress->addText('lastname', 'Lastname:');
        $postalAddress->addText('street', 'Street:');
        $postalAddress->addText('postcode', 'Postcode:');
        $postalAddress->addText('city', 'City:');

        $address->setDefaults($this->investment->getAddress()->toArray());
        $postalAddress->setDefaults($this->investment->getPostalAddress()->toArray());

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
        $this->investment->setRegistrationNumber($values->registrationNumber);
        $this->investment->setInvestment($values->investment);
        $this->investment->setInvestmentLength($values->investmentLength);

        $address = $this->investment->getAddress();
        $address->setName($values->Address->name);
        $address->setLastname($values->Address->lastname);
        $address->setStreet($values->Address->street);
        $address->setPostcode($values->Address->postcode);
        $address->setCity($values->Address->city);

        $postalAddress = $this->investment->getPostalAddress();
        $postalAddress->setName($values->PostalAddress->name);
        $postalAddress->setLastname($values->PostalAddress->lastname);
        $postalAddress->setStreet($values->PostalAddress->street);
        $postalAddress->setPostcode($values->PostalAddress->postcode);
        $postalAddress->setCity($values->PostalAddress->city);

        $this->em->flush();

        $this->flashMessage('Contract has been updated.', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionUpdate($id, $idPage)
    {
        $this->reloadContent();

        $this->investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);

        $this->template->idPage = $idPage;
    }

    public function actionDownload($id)
    {        
        $investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->find($id);
        $pdfPrinter = new PdfPrinter($investment);

        $this->sendResponse($pdfPrinter->printPdf(true));
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