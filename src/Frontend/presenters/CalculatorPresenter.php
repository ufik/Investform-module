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
class CalculatorPresenter extends BasePresenter
{
	private $id;

	private $fvoa;

	private $year;

	protected function startup() 
    {
		parent::startup();

		foreach(range(200000, 3000000, 100000) as $number) {
			$amountItems[$number] = \WebCMS\Helpers\SystemHelper::price($number);
		}


	}

	protected function beforeRender()
    {
		parent::beforeRender();	
	}
	
	public function createComponentForm()
	{
		$form = $this->createForm('form-submit');

		$form->addSelect('amount', 'Amount', $this->amountItems)
			->setAttribute('placeholder', 'Type investement amount');
		$form->addSelect('length', 'Length', array(3 => 'Tříletý', 5 => 'Pětiletý'));
		$form->addText('date', 'Date')->setRequired('Date is mandatory.');

		$form->addHidden('secured');

		$form->addSubmit('calculate', 'Calculate');

		$form->onSuccess[] = callback($this, 'formSubmitted');

		return $form;
	}

	public function formSubmitted($form)
	{
		$values = $form->getValues();

		$from = strtotime($values->date);
		
		if ($values->length == 5) {
			if ($values->secured) {
				$year = 2020;
				$to = strtotime('2020-11-30');
			} else {
				$year = 2019;
				$to = strtotime('2019-11-30');
			}
			
		} else {
			$year = 2017;
			$to = strtotime('2017-10-30');
		}

		$length = ($to - $from) / 60 / 60 / 24 / 365;

		$this->year = $year;
		$this->fvoa = new FutureValueOfAnnuityCalculator($values->amount, $length);
	}

	public function renderDefault($id)
    {	
    	if (!is_object($this->fvoa)) {
    		$this->fvoa = new FutureValueOfAnnuityCalculator(0, 3);
    		$this->year = "";
    	}

    	$this->template->year = $this->year;
    	$this->template->fvoa = $this->fvoa;
    	$this->template->id = $id;
	}
}
