<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\InvestformModule;

use Nette\Forms\Form;
use WebCMS\InvestformModule\Entity\Investment;
use WebCMS\InvestformModule\Entity\Address;
use Nette\Mail\Message;

/**
 * Description of InvestformPresenter
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class InvestformPresenter extends \FrontendModule\BasePresenter
{
	private $id;
	
	protected function startup() 
    {
		parent::startup();
	}

	protected function beforeRender()
    {
		parent::beforeRender();	
	}
	
	public function createComponentForm($name)
	{
		$form = $this->createForm('form-submit', 'default', null);

		$form->addText('name', 'Name')->setRequired('Name is mandatory.');
		$form->addText('lastname', 'Lastname')->setRequired('Lastname is mandatory.');
		$form->addText('street', 'Street')->setRequired('Street is mandatory.');
		$form->addText('postcode', 'Postcode')
			->setRequired('Postcode is mandatory.')
			->addRule(Form::PATTERN, 'Postcode must contain 5 numbers.', '([0-9]\s*){5}');
		$form->addText('city', 'City')->setRequired('City is mandatory.');
		$form->addText('phone', 'Phone')->setRequired('Phone is mandatory.');
		$form->addText('email', 'Email')
			->addRule(Form::EMAIL, 'This email is not valid.')
			->setRequired('Email is mandatory.');
		$form->addCheckbox('invest', 'Invest');
		$form->addText('company', 'Company')
			->addConditionOn($form['invest'], Form::EQUAL, true)
	        ->addRule(Form::FILLED, 'Company name is madatory.');
		$form->addText('registrationNumber', 'Registration number')
			->addConditionOn($form['invest'], Form::EQUAL, true)
	        ->addRule(Form::FILLED, 'Registration number is mandatory.');
		$form->addText('investmentAmount', 'Investment amount')
			->setRequired('Amount of investment is mandatory.');
		$form->addSelect('investmentLength', 'Investment length', array(3, 5))
			->setRequired('Investment length is mandatory.');

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = callback($this, 'formSubmitted');

		return $form;
	}

	public function createComponentStep2Form($name)
	{
		$form = $this->createForm('step2Form-submit', 'default', null);

		$form->addText('birthdateNumber', 'Birthdate number')->setRequired('Birthdate number is mandatory.');
		$form->addCheckbox('postalAddress', 'Postal address');
		$form->addText('name', 'Name')
			->addConditionOn($form['postalAddress'], Form::EQUAL, true)
	        ->addRule(Form::FILLED, 'Name is mandatory.');
		$form->addText('lastname', 'Lastname')
			->addConditionOn($form['postalAddress'], Form::EQUAL, true)
	        ->addRule(Form::FILLED, 'Lastname is mandatory.');
		$form->addText('street', 'Street')
			->addConditionOn($form['postalAddress'], Form::EQUAL, true)
	        ->addRule(Form::FILLED, 'Street is mandatory.');
		$form->addText('postcode', 'Postcode')
			->addConditionOn($form['postalAddress'], Form::EQUAL, true)
			->addRule(Form::PATTERN, 'Postcode must contain 5 numbers.', '([0-9]\s*){5}')
	        ->addRule(Form::FILLED, 'Postcode is mandatory.');
		$form->addText('city', 'City')
			->addConditionOn($form['postalAddress'], Form::EQUAL, true)
	        ->addRule(Form::FILLED, 'City is mandatory.');
	    $form->addHidden('idUser')->setDefaultValue($this->id);

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = callback($this, 'step2formSubmitted');

		return $form;
	}

	public function actionDefault($id)
    {	
    	$parameters = $this->getParameter();
    	$parameters = $parameters['parameters'];

    	if (array_key_exists(0, $parameters) && $parameters[0] === 'step2') {
    		$this->id = $_GET['idI'];
    		$this->template->setFile(APP_DIR . '/templates/investform-module/Investform/step2.latte');
    	} else if(array_key_exists(0, $parameters) && $parameters[0] === 'final') {
    		$this->template->setFile(APP_DIR . '/templates/investform-module/Investform/final.latte');
    	}

    	$this->template->form = $this->createComponentForm('form');
	}
	
	public function formSubmitted($form)
	{
		$values = $form->getValues();

		$address = new Address;
		$address->setName($values->name);
		$address->setLastname($values->lastname);
		$address->setStreet($values->street);
		$address->setPostcode($values->postcode);
		$address->setCity($values->city);

		$this->em->persist($address);

		$investment = new Investment;
		$investment->setPhone($values->phone);
		$investment->setEmail($values->email);
		$investment->setInvestment($values->investmentAmount);
		$investment->setInvestmentLength($values->investmentLength);
		$investment->setRegistrationNumber($values->registrationNumber);
		$investment->setCompany($values->company);
		$investment->setAddress($address);

		$this->em->persist($investment);
		$this->em->flush();

		$this->redirect('default', array(
			'idI' => $investment->getId(),
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr,
			'parameters' => array(
				'step2'
			)
		));
	}

	public function step2formSubmitted($form)
	{
		$values = $form->getValues();
		$investment = $this->em->getRepository('WebCMS\InvestformModule\Entity\Investment')->find($values->idUser);
		$investment->setBirthdateNumber($values->birthdateNumber);

		if ($values->postalAddress) {
			$address = new Address;
			$address->setName($values->name);
			$address->setLastname($values->lastname);
			$address->setStreet($values->street);
			$address->setPostcode($values->postcode);
			$address->setCity($values->city);

			$this->em->persist($address);

			$investment->setPostalAddress($address);
		}

		$this->sendPdf($investment);
		$this->em->flush();

		$this->redirect('default', array(
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr,
			'parameters' => array(
				'final'
			)
		));
	}

	public function sendPdf($investment)
	{
		$mpdf = new \mPDF();
		$mpdf->WriteHTML('<p>Hallo World</p>');
		$emailAttachment = $mpdf->Output('', 'S');

		$mail = new Message;
		$mail->setFrom('Franta <franta@example.com>')
		    ->addTo('t.voslar@gmail.com')
		    ->setSubject('Potvrzení objednávky')
		    ->setHTMLBody("Dobrý den,\nvaše objednávka byla přijata.");

		$mail->addAttachment('smlouva.pdf', $emailAttachment);

		$mail->send();
	}

	public function actionPrintPdf()
	{
		$html = "<b>ahoj světe!</b>"; // HTML v UTF-8

        // Jako 1. parament PDFResponse můžeme předat html v UTF8 nebo objekt implementující rozhraní ITemplate
        $this->sendResponse(new \PdfResponse\PdfResponse($html));
	}

	public function renderDefault($id)
    {	
		$this->template->id = $id;
	}
}