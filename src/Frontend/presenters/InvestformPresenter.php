<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\InvestformModule;

use Nette\Forms\Form;
use WebCMS\InvestformModule\Entity\Investment;
use WebCMS\InvestformModule\Entity\Address;
use WebCMS\InvestformModule\Common\PdfPrinter;
use WebCMS\InvestformModule\Common\EmailSender;
use WebCMS\InvestformModule\Entity\Businessman;
use Nette\Mail\Message;

/**
 * Description of InvestformPresenter
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class InvestformPresenter extends BasePresenter
{
	private $id;

	/* \Nette\Http\Session */
	public $businessmanSession;
	
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
		$form->addText('date', 'Date')->setRequired('Date is mandatory.');
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
		$form->addText('bankAccountPrefix', 'Bank account')
			->addRule(callback($this, 'validateBankAccount'), "Bank acccount prefix is not valid`.");
		$form->addText('bankAccount', 'Bank account')
			->setRequired('Bank account is mandatory.')
			->addRule(callback($this, 'validateBankAccount'), "Bank acccount is not valid`.");
		$form->addSelect('investmentAmount', 'Investment amount', $this->amountItems)
			->setRequired('Amount of investment is mandatory.');
		$form->addSelect('investmentLength', 'Investment length', array(3 => 'Tříletý', 5 => 'Pětiletý'))
			->setRequired('Investment length is mandatory.');

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = callback($this, 'formSubmitted');

		return $form;
	}

	public function createComponentStep2Form($name)
	{
		$parameters = $this->getParameter('parameters');
		$form = $this->createForm('step2Form-submit', 'default', null, array(
			'2',
			'hash' => $parameters[1]
		));

		$investment = $this->em->getRepository('WebCMS\InvestformModule\Entity\Investment')->findOneByHash($parameters[1]);
		$companyNumber = $investment->getRegistrationNumber();
		if (empty($companyNumber)) {
			$form->addText('birthdateNumber', 'Birthdate number')
				->setRequired('Birthdate number is mandatory.')
				->addRule(callback($this, 'validateBirthdateNumber'), "Birthdate can contain just numbers.");
		} else {
			$form->addHidden('birthdateNumber');
		}

		if (isset($this->businessmanSession->id)) {

			$form->addHidden('pin', $this->businessmanSession->id);

			$form['pin']->getControlPrototype()->setClass('session');
		} else {
			$form->addText('pin', 'Pin number');
		}
		

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

	public function validateBirthdateNumber($control)
	{
		$rc = $control->getValue();

	    if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
	        return false;
	    }

	    list(, $year, $month, $day, $ext, $c) = $matches;

	    if ($c === '') {
	        return $year < 54;
	    }

	    $mod = ($year . $month . $day . $ext) % 11;
	    if ($mod === 10) $mod = 0;
	    if ($mod !== (int) $c) {
	        return false;
	    }

	    $year += $year < 54 ? 2000 : 1900;

	    if ($month > 70 && $year > 2003) $month -= 70;
	    elseif ($month > 50) $month -= 50;
	    elseif ($month > 20 && $year > 2003) $month -= 20;

	    if (!checkdate($month, $day, $year)) {
	        return false;
	    }

	    return true;
	}

	public function validateBankAccount($control)
	{
		$number = $control->getValue();

		$parts = explode('/', $number);
		$baNumber = $parts[0];

		$parts = explode('-', $baNumber);

		foreach ($parts as $part) {
			if (!$this->validatePart($part)) {
				return false;
			}
		}

		return true;
	}

	/**
	 *    A  B C D  E  F G H I J
	 * _________________________
	 * s  6  3 7 9  10 5 8 4 2 1
	 * n  10 9 8 7  6  5 4 3 2 1
	 * 
	 * S = J *1 I *2 H *4 G *8 F*5 E *10 D*9 C*7 B*3 A *6
	 * 
	 * @param  [type] $part [description]
	 * @return [type]       [description]
	 */
	private function validatePart($part)
	{
		$scales = array(1, 2, 4, 8, 5, 10, 9, 7, 3, 6);
		$toValidate = array_reverse(str_split($part));

		$controlSum = 0;
		for ($i=0; $i < count($toValidate); $i++) { 
			$controlSum += $toValidate[$i] * $scales[$i];
		}
		
		return $controlSum % 11 === 0;
	}

	public function actionDefault($id)
    {	
    	$parameters = $this->getParameter();
    	$parameters = $parameters['parameters'];

    	$this->businessmanSession = $this->getSession('businessman');

    	if (array_key_exists(0, $parameters) && $parameters[0] === '2') {
    		$hash = $parameters[1];

    		$investment = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Investment')->findOneByHash($hash);
    		$this->id = $investment->getId();

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
		$investment->setInvestmentDate(new \Datetime(date('Y-m-d', strtotime($values->date))));
		$investment->setInvestment($values->investmentAmount);
		$investment->setInvestmentLength($values->investmentLength);
		$investment->setRegistrationNumber($values->registrationNumber);
		$investment->setCompany($values->company);
		$investment->setAddress($address);

		$investment->setContractSend(false);
		$investment->setContractPaid(false);
		$investment->setContractClosed(false);
		$investment->setClientContacted(false);

		if (!empty($values->bankAccountPrefix)) {
			$bankAccount = str_replace('_', '', $values->bankAccountPrefix).'-'.str_replace('_', '', $values->bankAccount);
		} else {
			$bankAccount = str_replace('_', '', $values->bankAccount);
		}
		$investment->setBankAccount($bankAccount);

		if (isset($this->businessmanSession->id)) {
			$businessman = $this->em->getRepository('WebCMS\InvestformModule\Entity\Businessman')->find($this->businessmanSession->id);
			$investment->setBusinessman($businessman);
		}

		$this->em->persist($investment);
		$this->em->flush();

		$investment->getHash();
		$this->em->flush();

		$this->sendPdf($investment, 'form');

		$infoEmail = $this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue();
		// if (!empty($infoEmail)) {
		// 	$mail = new Message;
		// 	$mail->setFrom($infoEmail)
		// 	    ->addTo($infoEmail)
		// 	    ->setSubject($this->settings->get('Notification subject', 'InvestformModule', 'text')->getValue())
		// 	    ->setHTMLBody($this->settings->get('Notification body', 'InvestformModule', 'textarea')->getValue());

		// 	$mail->send();
		// }

		$this->redirect('default', array(
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr,
			'parameters' => array(
				'2',
				'hash' => $investment->getHash()
			)
		));
	}

	public function step2formSubmitted($form)
	{
		$values = $form->getValues();
		
		$investment = $this->em->getRepository('WebCMS\InvestformModule\Entity\Investment')->find($values->idUser);
		$investment->setBirthdateNumber($values->birthdateNumber);

		if (isset($this->businessmanSession->id)) {
			$businessman = $this->em->getRepository('WebCMS\InvestformModule\Entity\Businessman')->find($this->businessmanSession->id);
			$investment->setBusinessman($businessman);
		} else {
			//check if businessman exists
			$businessman = $this->em->getRepository('WebCMS\InvestformModule\Entity\Businessman')->findOneBy(array(
				'businessId' => $values->pin
			));
			if ($businessman) {
				$investment->setBusinessman($businessman);
			}
			$investment->setPin($values->pin);
		}

		$investment->setContractSend(true);

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

		$this->sendPdf($investment, 'contract');
		$this->em->flush();

		$this->redirect('default', array(
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr,
			'parameters' => array(
				'final'
			)
		));
	}

	public function handlegetNetIncome($amount, $length, $date)
	{
		$from = strtotime($date);
		if ($length == 5) {
			$to = strtotime('2019-10-30');
		} else {
			$to = strtotime('2017-10-30');
		}

		$length = ($to - $from) / 60 / 60 / 24 / 365;

		$fvoa = new \WebCMS\InvestformModule\Common\FutureValueOfAnnuityCalculator($amount, $length);

		$this->payload->profit = \WebCMS\Helpers\SystemHelper::price($fvoa->getTotalProfit(), '%.0n');
		$this->sendPayload();
	}

	public function sendPdf($investment, $type)
	{
		$emailSender = new EmailSender($this->settings, $investment, $type);
		$emailSender->send();
	}

	public function renderDefault($id)
    {	
		$this->template->id = $id;
	}
}
