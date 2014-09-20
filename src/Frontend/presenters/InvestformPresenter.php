<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace FrontendModule\InvestformModule;

/**
 * Description of InvestformPresenter
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class InvestformPresenter extends \FrontendModule\BasePresenter
{
    /**
     * Assigned page.
     * @var WebCMS\Entity\Page
     */
	private $page;
	
	protected function startup() 
    {
		parent::startup();
	}

	protected function beforeRender()
    {
		parent::beforeRender();	
	}
	
	public function actionDefault($id)
    {	
		
	}
	
	public function renderDefault($id)
    {	
		$this->template->page = $this->page;
		$this->template->id = $id;
	}
}