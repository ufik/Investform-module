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

	public function __construct($settings, $investment)
	{
		$this->settings = $settings;
		$this->investment = $investment;
	}

	public function send()
	{
		$pdfPrinter = new PdfPrinter($this->investment);
		$emailAttachment = $pdfPrinter->printPdf();

		$mail = new Message;
		$mail->setFrom($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue())
		    ->addTo($this->investment->getEmail())
		    ->setSubject($this->settings->get('Subject', 'InvestformModule', 'text')->getValue())
		    ->setHTMLBody($this->settings->get('Email body', 'InvestformModule', 'textarea')->getValue());

		$mail->addAttachment('contract.pdf', $emailAttachment);

		$mail->send();
	}
}