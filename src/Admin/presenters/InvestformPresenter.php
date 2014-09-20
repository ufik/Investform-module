<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

/**
 * Description of
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class InvestformPresenter extends BasePresenter
{
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
}