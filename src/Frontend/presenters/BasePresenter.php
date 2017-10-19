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
class BasePresenter extends \FrontendModule\BasePresenter
{
	protected $amountItems = array();

	protected function startup() 
    {
		parent::startup();

		foreach(range(200000, 4000000, 100000) as $number) {
			$this->amountItems[$number] = \WebCMS\Helpers\SystemHelper::price($number, '%.0n');
		}
	}
}
