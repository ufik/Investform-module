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
use WebCMS\Entity\User;

/**
 * Description of
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class BusinessmanPresenter extends BasePresenter
{
    
    private $businessman;

    private $businessmen;

    private $company;

    private $investments;

    private $openInvestments;

    private $openInvestmentsAmount;

    private $closedInvestments;

    private $closedInvestmentsAmount;

    private $user;

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
        $this->user = $this->getUser();
    }

    public function renderDefault($idPage)
    {
    	$this->reloadContent();

        $roles = $this->user->getIdentity()->getRoles();
        if (count(array_intersect(array('superadmin', 'admin'), $roles)) > 0) {
            $this->template->show = true;
        } else {
            $this->businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findOneBy(array(
                'user' => $this->user->getIdentity()->getId()
            ));

            $this->investments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
                'businessman' => $this->businessman
            ));

            $this->openInvestments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
                'businessman' => $this->businessman,
                'contractSend' => false
            ));
            $this->openInvestmentsAmount = 0;
            foreach ($this->openInvestments as $investment) {
                $this->openInvestmentsAmount += $investment->getInvestment();
            }

            $this->closedInvestments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
                'businessman' => $this->businessman,
                'contractSend' => true
            ));
            $this->closedInvestmentsAmount = 0;
            foreach ($this->closedInvestments as $investment) {
                $this->closedInvestmentsAmount += $investment->getInvestment();
            }
            $this->template->show = false;
            $this->template->businessman = $this->businessman;
            $this->template->urlCode = $this->presenter->getHttpRequest()->url->baseUrl.$this->actualPage->getSlug().'/?bcode=';
            $this->template->investments = $this->investments;
            $this->template->openInvestments = count($this->openInvestments);
            $this->template->closedInvestments = count($this->closedInvestments);
            $this->template->openInvestmentsAmount = $this->openInvestmentsAmount;
            $this->template->closedInvestmentsAmount = $this->closedInvestmentsAmount;
        }

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
        $this->template->idPage = $idPage;
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
        $this->template->idPage = $idPage;
        $this->template->numberOfBusinessmen = count($this->businessmen);
    }

    protected function createComponentActiveGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Businessman", null, array(
            'active = true',
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Firstname')->setCustomRender(function($item) {
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

        $grid->addActionHref("deactivate", 'Deactivate', 'deactivate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'grey')));
        $grid->addActionHref("detail", 'Businessman detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'green')));

        return $grid;
    }

    protected function createComponentInactiveGrid($name)
    {
        $grid = $this->createGrid($this, $name, "\WebCMS\InvestformModule\Entity\Businessman", null, array(
            'active = false',
        ));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        $grid->addColumnText('name', 'Firstname')->setCustomRender(function($item) {
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

        $grid->addActionHref("activate", 'Activate', 'activate', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax', 'grey')));
        $grid->addActionHref("detail", 'Businessman detail', 'detail', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'green')));

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

        $this->company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($this->businessman->getCompany()->getId());

        if ($this->company->getActive()) {
            $this->businessman->setActive(true);

            $this->em->flush();

            $this->flashMessage('Businessman has been activated', 'success');

        } else {
            $this->flashMessage('Active state cannot be changed. Company is inactive.', 'error');
        }

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

        $form->addText('name', 'Firstname');
        $form->addText('lastname', 'Lastname');
        $form->addText('businessname', 'Business name')
            ->addConditionOn($form['name'], ~Form::FILLED)
            ->addRule(Form::FILLED, 'Fill in name or business name.');

        $users = $this->em->getRepository('\WebCMS\Entity\User')->findAll();
        $usersForSelect = array();
        if ($users) {
            $usersForSelect[] = "";
            foreach ($users as $user) {
                $usersForSelect[$user->getId()] = $user->getUsername();
            }
        }

        $form->addSelect('user', 'User')->setItems($usersForSelect);

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

            $form->addText('businessIdDisabled', 'Generated businessman ID')
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
        $sendEmail = false;

        if(!$this->businessman){
            $this->businessman = new Businessman;
            $this->businessman->setActive(true);
            $this->em->persist($this->businessman);
            $sendEmail = true;
        }

        $this->businessman->setName($values->name);
        $this->businessman->setLastname($values->lastname);
        $this->businessman->setBusinessname($values->businessname);
        $this->businessman->setStreet($values->street);
        $this->businessman->setZipCity($values->zipCity);
        $this->businessman->setEmail($values->email);
        $this->businessman->setPhone($values->phone);

        if ($values->user) {
            $user = $this->em->getRepository('\WebCMS\Entity\User')->find($values->user);
            $this->businessman->setUser($user);
        }
        

        $company = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Company')->find($values->company);
        $this->businessman->setCompany($company);

        if (isset($values->businessId)) {
            $this->businessman->setBusinessId($values->businessId);
        } else {
            $this->businessman->setBusinessId($values->businessIdDisabled);
        }
        
        $this->businessman->setBusinessUrl($values->businessUrl);        

        //send email
        if($sendEmail){
            $mail = new \Nette\Mail\Message;
            $mail->addTo($values->email);
            
            $domain = str_replace('www.', '', $this->getHttpRequest()->url->host);
            
            if($domain !== 'localhost') $mail->setFrom('no-reply@' . $domain);
            else $mail->setFrom('no-reply@test.cz'); // TODO move to settings

            $mailBody = '<h2><u>Vaše obchodní údaje</u></h2>';
            $mailBody .= '<table><tbody>';
            $mailBody .= '<tr><td><strong>Jméno: </strong></td><td>'.$values->name.' '.$values->lastname.'</td></tr>';
            $mailBody .= '<tr><td><strong>Email: </strong></td><td><a href="mailto:'.$values->email.'">'.$values->email.'</a></td></tr>';
            $mailBody .= '<tr><td><strong>Tel. číslo: </strong></td><td>'.$values->phone.'</td></tr>';
            $mailBody .= '<tr><td><strong>Obchodní URL: </strong></td><td><a href="https://www.zajistenainvestice.cz/obchodnici?bcode='.$values->businessUrl.'">www.zajistenainvestice.cz</td></tr>';
            $mailBody .= '</tbody></table>';

            if (isset($user)) {

                $mailBody .= '<h3><u>Vaše přístupové údaje</u></h3>';
                $mailBody .= '<table><tbody>';
                $mailBody .= '<tr><td><strong>Přístup: </strong></td><td><a href="https://www.zajistenainvestice.cz/admin">www.zajistenainvestice.cz/admin</a></td></tr>';
                $mailBody .= '<tr><td><strong>Login: </strong></td><td>'.$user->getUsername().'</td></tr>';
                $mailBody .= '<tr><td><strong>Heslo: </strong></td><td>'.$this->settings->get('Businessman password', 'InvestformModule', 'text')->getValue().'</td></tr>';
                $mailBody .= '</tbody></table>';

            }

            $mail->setSubject('Byl Vám založen účet na www.zajistenainvestice.cz');
            $mail->setHtmlBody($mailBody);

            try {
                $mail->send();  
                $this->flashMessage('Email has been sent', 'success');
            } catch (\Exception $e) {
                $this->flashMessage('Cannot send email.', 'danger');                    
            }
        }

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

        $this->openInvestments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
            'businessman' => $this->businessman,
            'contractSend' => true,
            'contractClosed' => false,
            'contractPaid' => false
        ));
        $this->openInvestmentsAmount = 0;
        foreach ($this->openInvestments as $investment) {
            $this->openInvestmentsAmount += $investment->getInvestment();
        }

        $this->closedInvestments = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findBy(array(
            'businessman' => $this->businessman,
            'contractSend' => true,
            'contractClosed' => true,
            'contractPaid' => false
        ));
        $this->closedInvestmentsAmount = 0;
        foreach ($this->closedInvestments as $investment) {
            $this->closedInvestmentsAmount += $investment->getInvestment();
        }
    }

    public function renderDetail($idPage)
    {
        $this->reloadContent();
        $this->template->idPage = $idPage;
        $this->template->urlCode = $this->presenter->getHttpRequest()->url->baseUrl.$this->actualPage->getSlug().'/?bcode=';
        $this->template->businessman = $this->businessman;
        $this->template->investments = $this->investments;
        $this->template->openInvestments = count($this->openInvestments);
        $this->template->closedInvestments = count($this->closedInvestments);
        $this->template->openInvestmentsAmount = $this->openInvestmentsAmount;
        $this->template->closedInvestmentsAmount = $this->closedInvestmentsAmount;
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
        $grid->addColumnText('contractSend', 'Contract send')->setCustomRender(function($item) {
            if ($item->getContractSend()) {
                return 'Yes';
            } else {
                return 'No';
            }
        });
        $grid->addColumnText('contractClosed', 'Contract closed')->setCustomRender(function($item) {
            return $item->getContractClosed() ? 'Yes' : 'No';
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
            'idPage' => $this->actualPage->getId(),
            'from' => 'businessman'
        ));
    }

    
}
