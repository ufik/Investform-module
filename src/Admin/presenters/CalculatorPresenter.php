<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace AdminModule\InvestformModule;

use Nette\Forms\Form;
use WebCMS\InvestformModule\Common\PdfPrinter;
use WebCMS\InvestformModule\Common\EmailSender;
use WebCMS\InvestformModule\Entity\Address;

/**
 * Description of
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class CalculatorPresenter extends BasePresenter
{
    protected function startup()
    {
    	parent::startup();
    }

    protected function beforeRender()
    {
	   parent::beforeRender();

       $this->forward('Investform:default', array(
            'idPage' => $this->actualPage->getId()
        ));
    }
}