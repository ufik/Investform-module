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
class BusinessmanPresenter extends BasePresenter
{
    
    private $businessman;

    private $businessmen;

    private $investments;

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

    public function actionActiveBusinessmen($idPage)
    {
        $this->reloadContent();

        $this->businessmen = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findBy(array(
            'active' => true
        ));
    }

    public function renderActiveBusinessmen($idPage)
    {
        $this->template->numberOfBusinessmen = count($this->businessmen);
    }

    public function actionInactiveBusinessmen($idPage)
    {
        $this->reloadContent();

        $this->businessmen = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findBy(array(
            'active' => false
        ));
    }

    public function renderInactiveBusinessmen($idPage)
    {
        $this->template->numberOfBusinessmen = count($this->businessmen);
    }

    protected function createComponentActiveGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Businessman", null, array(
            'active = true',
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setCustomRender(function($item) {
            return $item->getName() . ' ' . $item->getLastname();
        });

        $grid->addColumnText('businessId', 'Business ID');

        $grid->addActionHref("deactivate", 'Deactivate', 'deactivate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("detail", 'Businessman detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));

        return $grid;
    }

    protected function createComponentInactiveGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Businessman", null, array(
            'active = false',
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setCustomRender(function($item) {
            return $item->getName() . ' ' . $item->getLastname();
        });

        $grid->addColumnText('businessId', 'Business ID');

        $grid->addActionHref("activate", 'Activate', 'activate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("detail", 'Businessman detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));

        return $grid;
    }

    public function actionDeactivate($id, $idPage, $inDetail = false)
    {

        $this->businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->find($id);
        $this->businessman->setActive(false);

        $this->em->flush();

        $this->flashMessage('Businessman has been deactivated', 'success');

        if ($inDetail) {
            $this->forward('detail', array(
                'id' => $id,
                'idPage' => $this->actualPage->getId()
            ));
        } else {
            $this->forward('activeBusinessmen', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    public function actionActivate($id, $idPage, $inDetail = false)
    {

        $this->businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->find($id);
        $this->businessman->setActive(true);

        $this->em->flush();

        $this->flashMessage('Businessman has been activated', 'success');

        if ($inDetail) {
            $this->forward('detail', array(
                'id' => $id,
                'idPage' => $this->actualPage->getId()
            ));
        } else {
            $this->forward('inactiveBusinessmen', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    public function actionUpdate($id, $idPage)
    {
        if ($id) {
            $this->businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->find($id);
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

        $companies = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->findAll();
        $companiesForSelect = array();
        if ($companies) {
            $companiesForSelect[] = "";
            foreach ($companies as $company) {
                $companiesForSelect[$company->getId()] = $company->getName();
            }
        }

        $form->addSelect('company', 'Company')->setItems($companiesForSelect);

        if ($this->businessman) {
            $form->setDefaults($this->businessman->toArray());

            $form->addText('businessIdDisabled', 'Generated businessman ID')
                ->setValue($this->businessman->getBusinessId())
                ->setDisabled();

            $form->addText('businessUrlDisabled', 'Generated businessman URL')
                ->setValue($this->presenter->getHttpRequest()->url->baseUrl.$this->actualPage->getSlug().'/?bcode='.$this->businessman->getBusinessUrl())
                ->setDisabled();

            $form->addHidden('businessId', $this->businessman->getBusinessId());
            $form->addHidden('businessUrl', $this->businessman->getBusinessUrl());
        } else {
            //generated values        
            $exists = true;
            while($exists){
                $businessId = mt_rand(10000,99999);

                $businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findBy(array(
                    'businessId' => $businessId
                ));

                if (!$businessman) $exists = false;
            }

            $form->addText('businessId', 'Generated businessman ID')
                ->setValue($businessId);

            $businessUrl = bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM));
            $form->addText('businessUrlDisabled', 'Generated businessman URL')
                ->setValue($this->presenter->getHttpRequest()->url->baseUrl.$this->actualPage->getSlug().'/?bcode='.$businessUrl)
                ->setDisabled();
            $form->addHidden('businessUrl', $businessUrl);

        }

        $form->addSubmit('save', 'Save new businessman');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if(!$this->businessman){
            $this->businessman = new Businessman;
            $this->businessman->setActive(true);
            $this->em->persist($this->businessman);
        }

        $this->businessman->setName($values->name);
        $this->businessman->setLastname($values->lastname);
        $this->businessman->setStreet($values->street);
        $this->businessman->setZipCity($values->zipCity);
        $this->businessman->setEmail($values->email);
        $this->businessman->setPhone($values->phone);

        $company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($values->company);
        $this->businessman->setCompany($company);

        $this->businessman->setBusinessId($values->businessId);
        $this->businessman->setBusinessUrl($values->businessUrl);        

        $this->em->flush();

        $this->flashMessage('Businessman has been updated.', 'success');

        $this->forward('detail', array(
            'id' => $this->businessman->getId(),
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDetail($id, $idPage)
    {
        $this->businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->find($id);
        $this->investments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
            'businessman' => $this->businessman
        ));
    }

    public function renderDetail($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
        $this->template->urlCode = $this->presenter->getHttpRequest()->url->baseUrl.$this->actualPage->getSlug().'/?bcode=';
        $this->template->businessman = $this->businessman;
        $this->template->investments = $this->investments;
    }

    protected function createComponentInvestmentsGrid($name)
    {

        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Investment", null, array(
            'businessman = '.$this->businessman->getId()
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addFilterDateRange('created', 'Created');

        $grid->addColumnDate('created', 'Created')->setDateFormat(\Grido\Components\Columns\Date::FORMAT_DATETIME);

        $grid->addColumnText('client_name', 'Client name')->setCustomRender(function($item) {
            return $item->getAddress()->getName().' '.$item->getAddress()->getLastname();
        });

        $grid->addColumnText('investment', 'Investment');
        $grid->addColumnText('conractSend', 'Conract send')->setCustomRender(function($item) {
            if ($item->getContractSend()) {
                return 'Yes';
            } else {
                return 'No';
            }
        });

        

        // $grid->addActionHref("activate", 'Activate', 'activate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        // $grid->addActionHref("detail", 'Businessman detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));

        return $grid;
    }

    
}
