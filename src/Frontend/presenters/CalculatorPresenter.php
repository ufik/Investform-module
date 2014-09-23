<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\InvestformModule;

use WebCMS\InvestformModule\Common\FutureValueOfAnnuityCalculator;

/**
 * Description of InvestformPresenter
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class CalculatorPresenter extends \FrontendModule\BasePresenter
{
	private $id;

	private $fvoa;
	
	protected function startup() 
    {
		parent::startup();
	}

	protected function beforeRender()
    {
		parent::beforeRender();	
	}
	
	public function createComponentForm()
	{
		$form = $this->createForm('form-submit');

		$amountItems = array();
		foreach(range(100000, 2000000, 100000) as $number) {
			$amountItems[$number] = $number;
		}

		$form->addSelect('amount', 'Amount', $amountItems)
			->setAttribute('placeholder', 'Type investement amount');
		$form->addSelect('length', 'Length', array(3 => 3, 5 => 5));

		$form->addSubmit('calculate', 'Calculate');

		$form->onSuccess[] = callback($this, 'formSubmitted');

		return $form;
	}

	public function formSubmitted($form)
	{
		$values = $form->getValues();

		$this->fvoa = new FutureValueOfAnnuityCalculator($values->amount, $values->length);
	}

	public function renderDefault($id)
    {	
    	if (!is_object($this->fvoa)) {
    		$this->fvoa = new FutureValueOfAnnuityCalculator(0, 3);
    	}

    	$this->template->fvoa = $this->fvoa;
    	$this->template->id = $id;
	}
}
