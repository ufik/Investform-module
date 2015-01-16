<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\InvestformModule;

use WebCMS\InvestformModule\Entity\Businessman;


/**
 * Description of InvestformPresenter
 *
 * @author Jakub Sanda <jakub.sanda@webcook.cz>
 */
class BusinessmanPresenter extends BasePresenter
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

	public function renderDefault($id)
    {	
		$this->template->id = $id;
	}
}
