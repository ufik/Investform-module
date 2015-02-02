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
class CompanyPresenter extends BasePresenter
{
	private $id;

	private $businessman;

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

	public function actionDefault($id)
    {	
		if (isset($_GET['bcode'])) {
			
			$this->businessman = $this->em->getRepository('\WebCMS\InvestformModule\Entity\Businessman')->findOneBy(array(
                'businessUrl' => $_GET['bcode']
            ));

			if ($this->businessman) {
	            $this->businessmanSession = $this->getSession('businessman');
				$this->businessmanSession->id = $this->businessman->getId();

				$this->flashMessage('Your business code has been saved.', 'success');
			} else {
				$this->flashMessage('Wrong business code.', 'error');
			}

		}



		$this->redirect(':Frontend:Homepage:');
	}

}
