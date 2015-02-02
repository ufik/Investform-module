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

    private $businessmen;

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

    protected function createComponentCompanyGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Company");

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('street', 'Street and number')->setSortable();

        $grid->addColumnText('zipCity', 'Zip and city')->setSortable();

        $grid->addActionHref("detail", 'Company detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));

        return $grid;
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
        $form->addText('street', 'Street and number');
        $form->addText('zipCity', 'Zip and city');
        $form->addText('ico', 'Ico');
        $form->addText('dic', 'Dic');
        $form->addText('email', 'Email')
            ->addRule(Form::EMAIL, 'This email is not valid.')
            ->setRequired('Email is mandatory.');
        $form->addText('phone', 'Phone')->setRequired('Phone is mandatory.');

        $form->addSubmit('save', 'Save new company');

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
        $this->company->setStreet($values->street);
        $this->company->setZipCity($values->zipCity);
        $this->company->setIco($values->ico);
        $this->company->setDic($values->dic);
        $this->company->setEmail($values->email);
        $this->company->setPhone($values->phone);     

        $this->em->flush();

        $this->flashMessage('Company has been updated.', 'success');

        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDetail($id, $idPage)
    {
        $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($id);
    }

    public function renderDetail($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
        $this->template->company = $this->company;
    }

    
}
