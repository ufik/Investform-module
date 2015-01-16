<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

use Nette\Forms\Form;

/**
 * Description of
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class BusinessmanPresenter extends BasePresenter
{
    
    private $businessman;

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

        //generated values
        $form->addText('businessId', 'Generated businessman ID')
            ->setValue(mt_rand(10000,99999))
            ->setDisabled();

        $form->addText('businessUrl', 'Generated businessman URL')
            ->setValue(bin2hex(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM)))
            ->setDisabled();

        $form->addSubmit('save', 'Save new businessman');

        $form->onSuccess[] = callback($this, 'formSubmitted');

        return $form;
    }

    public function formSubmitted($form)
    {
        $values = $form->getValues();

        $this->businessman = new Businessman;
        $this->businessman->setName($values->name);
        $this->businessman->setLastname($values->lastname);
        $this->businessman->setStreet($values->street);
        $this->businessman->setZipCity($values->zipCity);
        $this->businessman->setEmail($values->email);
        $this->businessman->setTelephone($values->telephone);

        $this->businessman->setBusinessId($values->businessId);
        $this->businessman->setBusinessUrl($values->businessUrl);

        $this->businessman->setActive(true);

        $this->em->flush();

        $this->flashMessage('Businessman has been updated.', 'success');
        $this->forward('default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }

    
}
