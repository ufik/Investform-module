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
		$emailAttachment = $this->type == 'form' ? $pdfPrinter->printPdfForm() : $pdfPrinter->printPdfContract();

		$mail = new Message;
		$mail->setFrom($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue())
		    ->addTo($this->investment->getEmail())
		    ->setSubject($this->settings->get(ucfirst($this->type).' Subject', 'InvestformModule', 'text')->getValue())
		    ->setHTMLBody($this->settings->get(ucfirst($this->type).' Email body', 'InvestformModule', 'textarea')->getValue());

	    $name = ucfirst(\Nette\Utils\Strings::webalize($this->investment->getAddress()->getName()));
	    $lastname = ucfirst(\Nette\Utils\Strings::webalize($this->investment->getAddress()->getLastName()));

	    $filePath = $this->type == 'form' ? 
	    'zajistena-investice_Kalkulace_' . $name . '-' . $lastname . '.pdf' :
    	'zajistena-investice_Smlouva_' . $name . '-' . $lastname . '.pdf';
		file_put_contents($filePath, $emailAttachment);

		$mail->addAttachment($filePath, null, 'application/pdf');

		//$mail->send();

		unlink($filePath);
	}
}
