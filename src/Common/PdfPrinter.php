<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Common;

use Nette\Templating\FileTemplate;

/**
 * 
 */
class PdfPrinter
{
	private $investment;

	public function __construct($investment)
	{
		$this->investment = $investment;
	}

	public function printPdf($response = false)
	{
		$template = new FileTemplate(APP_DIR . '/templates/investform-module/Investform/contract.latte');
		$template->investment = $this->investment;

		$html = $template->__toString();
		$mpdf = new \mPDF();
		$mpdf->WriteHTML($html);
		
		if ($response) {
			return new \PdfResponse\PdfResponse($html);	
		} else {
			$output = $mpdf->Output('', 'S');
			return $output;
		}
	}
}