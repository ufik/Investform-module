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

    public function actionActiveCompanies($idPage)
    {
        $this->reloadContent();

        $this->companies = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->findBy(array(
            'active' => true
        ));
    }

    public function renderActiveCompanies($idPage)
    {
        $this->template->idPage = $idPage;
        $this->template->numberOfCompanies = count($this->companies);
    }

    public function actionInactiveCompanies($idPage)
    {
        $this->reloadContent();

        $this->companies = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->findBy(array(
            'active' => false
        ));
    }

    public function renderInactiveCompanies($idPage)
    {
        $this->template->idPage = $idPage;
        $this->template->numberOfCompanies = count($this->companies);
    }

    protected function createComponentActiveGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Company", null, array(
            'active = true',
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('street', 'Street and number')->setSortable();

        $grid->addColumnText('zipCity', 'Zip and city')->setSortable();

        $grid->addActionHref("deactivate", 'Deactivate', 'deactivate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'grey')));
        $grid->addActionHref("detail", 'Company detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'green')));

        return $grid;
    }

    protected function createComponentInactiveGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Company", null, array(
            'active = false',
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setSortable();

        $grid->addColumnText('street', 'Street and number')->setSortable();

        $grid->addColumnText('zipCity', 'Zip and city')->setSortable();

        $grid->addActionHref("activate", 'Activate', 'activate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'grey')));
        $grid->addActionHref("detail", 'Company detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'green')));

        return $grid;
    }

    public function actionDeactivate($id, $idPage, $inDetail = false)
    {

        $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($id);
        $this->company->setActive(false);

        $this->businessmen = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findBy(array(
            'company' => $this->company
        ));

        if ($this->businessmen) {
            foreach ($this->businessmen as $businessman) {
                $businessman->setActive(false);
            }
        }

        $this->em->flush();

        $this->flashMessage('Company has been deactivated', 'success');

        if ($inDetail) {
            $this->forward('detail', array(
                'id' => $id,
                'idPage' => $this->actualPage->getId()
            ));
        } else {
            $this->forward('activeCompanies', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
    }

    public function actionActivate($id, $idPage, $inDetail = false)
    {

        $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($id);
        $this->company->setActive(true);

        $this->businessmen = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findBy(array(
            'company' => $this->company
        ));

        if ($this->businessmen) {
            foreach ($this->businessmen as $businessman) {
                $businessman->setActive(true);
            }
        }

        $this->em->flush();

        $this->flashMessage('Company has been activated', 'success');

        if ($inDetail) {
            $this->forward('detail', array(
                'id' => $id,
                'idPage' => $this->actualPage->getId()
            ));
        } else {
            $this->forward('inactiveCompanies', array(
                'idPage' => $this->actualPage->getId()
            ));
        }
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

        if ($this->company) {
            $form->setDefaults($this->company->toArray());
        }

        $form->addSubmit('save', 'Save new company');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        if (!$this->company) {
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
        $this->company->setActive(true);    

        $this->em->flush();

        $this->flashMessage('Company has been updated.', 'success');

        $this->forward('detail', array(
            'id' => $this->company->getId(),
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDetail($id, $idPage)
    {
        $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($id);
        $this->businessmen = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findBy(array(
            'company' => $this->company
        ));
    }

    public function renderDetail($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
        $this->template->businessmen = $this->businessmen;
        $this->template->company = $this->company;
    }

    protected function createComponentBusinessmenGrid($name)
    {

        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Businessman", null, array(
            'company = '.$this->company->getId()
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Name')->setCustomRender(function($item) {
            if ($item->getName()) {
                return $item->getName() . ' ' . $item->getLastname();
            } else {
                return $item->getBusinessname();
            }
        });

        $grid->addColumnText('businessId', 'Business ID');

        $grid->addColumnText('company', 'Company')->setCustomRender(function($item) {
            if ($item->getCompany()) {
                return $item->getCompany()->getName();
            }
        });

        $grid->addColumnText('active', 'Active')->setCustomRender(function($item) {
            if ($item->getActive()) {
                return 'Yes';
            } else {
                return 'No';
            }
        });

        $grid->addActionHref("changeActive", 'Change active state', 'changeActive', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'green')));
        $grid->addActionHref("businessmanDetail", 'Businessman detail', 'businessmanDetail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'green')));

        return $grid;
    }

    public function actionChangeActive($id, $idPage)
    {
        $businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->find($id);

        if (!$businessman->getActive()) {

            $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($businessman->getCompany()->getId());

            if ($this->company->getActive()) {
                $businessman->setActive(true);

                $this->em->flush();

                $this->flashMessage('Active state has been changed', 'success');
            } else {

                $this->flashMessage('Active state cannot be changed. Company is inactive.', 'error');
            }

        } else {
            $businessman->setActive(false);

            $this->em->flush();

            $this->flashMessage('Active state has been changed', 'success');
        }


        
        $this->forward('detail', array(
            'id' => $businessman->getCompany()->getId(),
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionBusinessmanDetail($id, $idPage)
    {
        $this->forward('Businessman:detail', array(
            'id' => $id,
            'idPage' => $this->actualPage->getId()
        ));
    }

    protected function createComponentInvestmentsGrid($name)
    {

        $businessmenIds = array();
        $investments = array();

        if ($this->businessmen) {
            foreach ($this->businessmen as $businessman) {
                $businessmenIds[] = $businessman->getId();
            }
        }

        if (count($businessmenIds)) {
            $investments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
                'businessman' =>  $businessmenIds
            ));
        }

        $grid = new \Grido\Grid($this, $name);
        $grid->setModel($investments);

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addFilterDateRange('created', 'Created');

        $grid->addColumnDate('created', 'Created')->setDateFormat(\Grido\Components\Columns\Date::FORMAT_DATETIME);

        $grid->addColumnText('client_name', 'Client name')->setCustomRender(function($item) {
            return $item->getAddress()->getName().' '.$item->getAddress()->getLastname();
        });

        $grid->addColumnText('businessman_name', 'Businessman name')->setCustomRender(function($item) {
            if ($item->getBusinessman()->getName()) {
                return $item->getBusinessman()->getName() . ' ' . $item->getBusinessman()->getLastname();
            } else {
                return $item->getBusinessman()->getBusinessname();
            }
        });

        $grid->addColumnText('businessId', 'Business Id')->setCustomRender(function($item) {
            return $item->getBusinessman()->getBusinessId();
        });

        $grid->addColumnText('investment', 'Investment');
        $grid->addColumnText('contractSend', 'Contract send')->setCustomRender(function($item) {
            if ($item->getContractSend()) {
                return 'Yes';
            } else {
                return 'No';
            }
        });
        $grid->addColumnText('contractPaid', 'Contract paid')->setCustomRender(function($item) {
            if ($item->getContractPaid()) {
                return 'Yes';
            } else {
                return 'No';
            }
        });

        $grid->addActionHref("sendContract", 'Send', 'sendContract', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'purple')));
        $grid->addActionHref("downloadContract", 'Download', 'downloadContract', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'purple')));
        $grid->addActionHref("updateContract", 'Edit', 'updateContract', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'green')));

        return $grid;
    }

    public function actionUpdateContract($id, $idPage)
    {
        $this->forward('Investform:update', array(
            'id' => $id,
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionDownloadContract($id, $idPage)
    {
        $this->forward('Investform:download', array(
            'id' => $id,
            'idPage' => $this->actualPage->getId()
        ));
    }

    public function actionSendContract($id, $idPage)
    {
        $this->forward('Investform:send', array(
            'id' => $id,
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
