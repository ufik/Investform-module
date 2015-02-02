<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

use Nette\Forms\Form;
use WebCMS\InvestformModule\Entity\Businessman;
use WebCMS\InvestformModule\Entity\Company;
use WebCMS\InvestformModule\Entity\Investment;

/**
 * Description of
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class CompanyPresenter extends BasePresenter
{
    
    private $company;

    private $companies;

    protected function startup()
    {
    	parent::startup();
    }

    protected function beforeRender()
    {
	   parent::beforeRender();
    }

    public function actionDefault($idPage)
    {

    }

    public function renderDefault($idPage)
    {
    	$this->reloadContent();
    	$this->template->idPage = $idPage;
    }

    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($id);
        }
    }

    public function renderUpdate($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
    }

    public function createComponentForm($name)
    {
        $form = $this->createForm('form-submit', 'default', null);

        $form->addText('name', 'Name')->setRequired('Name is mandatory.');
        $form->addText('lastname', 'Lastname')->setRequired('Lastname is mandatory.');
        $form->addText('street', 'Street and number')->setRequired('Street and number is mandatory.');
        $form->addText('zipCity', 'Zip and city')->setRequired('Zip and city is mandatory.');
        $form->addText('email', 'Email')
            ->addRule(Form::EMAIL, 'This email is not valid.')
            ->setRequired('Email is mandatory.');
        $form->addText('phone', 'Phone')->setRequired('Phone is mandatory.');

        $form->addSubmit('save', 'Save new businessman');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if(!$this->company){
            $this->company = new Company;
            $this->em->persist($this->company);
        }

        $this->company->setName($values->name);
        $this->company->setLastname($values->lastname);
        $this->company->setStreet($values->street);
        $this->company->setZipCity($values->zipCity);
        $this->company->setEmail($values->email);
        $this->company->setPhone($values->phone);     

        $this->em->flush();

        $this->flashMessage('Company has been updated.', 'success');

        $this->forward('detail', array(
            'id' => $this->company->getId(),
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDetail($id, $idPage)
    {
        $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->find($id);
    }

    public function renderDetail($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
        $this->template->company = $this->company;
    }

    
}
