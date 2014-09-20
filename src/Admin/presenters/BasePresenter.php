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
class BasePresenter extends \AdminModule\BasePresenter
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
}