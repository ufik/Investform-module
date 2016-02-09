<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Common;

use Nette\Mail\Message;


/**
 * 
 */
class EmailSender
{
	private $settings;

	private $investment;

	private $type;

	public function __construct($settings, $investment, $type)
	{
		$this->settings = $settings;
		$this->investment = $investment;
		$this->type = $type;
	}

	public function send()
	{
		$pdfPrinter = new PdfPrinter($this->investment);

		$htmlBody = $this->settings->get(ucfirst($this->type).' Email body', 'InvestformModule', 'textarea')->getValue();
		if ($this->type == 'form') {
			$pdfPrinter->printPdfForm();
			$htmlBody = \WebCMS\Helpers\SystemHelper::replaceStatic($htmlBody, array('CONTRACT_PATH'), array(\WebCMS\Helpers\SystemHelper::$baseUrl . 'upload/contracts/' . $this->investment->getHash() . '.pdf'));
		} else {
			$pdfPrinter->printPdfContract(false, $this->investment->getInvestmentDate());
			$htmlBody = \WebCMS\Helpers\SystemHelper::replaceStatic($htmlBody, array('CONTRACT_PATH'), array(\WebCMS\Helpers\SystemHelper::$baseUrl . 'upload/contracts/' . $this->investment->getContractHash() . '.pdf'));
		}

		$mail = new Message;
		$mail->setFrom($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue())
		    ->addTo($this->investment->getEmail())
		    ->setSubject($this->settings->get(ucfirst($this->type).' Subject', 'InvestformModule', 'text')->getValue())
		    ->setHTMLBody($htmlBody);

		$mail->send();
	}
}
