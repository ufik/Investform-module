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

	public function printPdfForm($response = false)
	{
		$fvoa = new FutureValueOfAnnuityCalculator($this->investment->getInvestment(), $this->investment->getInvestmentLength());
		
		$template = new FileTemplate(APP_DIR . '/templates/investform-module/Investform/form.latte');
		
		$template_path = APP_DIR . '/../zajistenainvestice-kalkulace_form-new-font.pdf';
		$output_path = WWW_DIR . '/example.pdf';
		$field_data = array(
		    "name" => $this->investment->getAddress()->getName()
		);

		$this->processPdf($response, $output_path);
	}

	public function printPdfContract($response = false)
	{
		$fvoa = new FutureValueOfAnnuityCalculator($this->investment->getInvestment(), $this->investment->getInvestmentLength());
		
		$templatePath = APP_DIR . '/../zajistenainvestice-kalkulace_form-new-font.pdf';
		$outputPath = WWW_DIR . '/example.pdf';
		$fieldData = array(
		    "name" => $this->investment->getAddress()->getName()
		);

		\PHPPDFFill\PDFFill::make($templatePath, $fieldData)->save_pdf($outputPath);

		$this->processPdf($response, $outputPath);
	}

	private function processPdf($response, $pdfPath)
	{
		if ($response) {
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="smlouva.pdf"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($pdfPath));
			header('Accept-Ranges: bytes');

			@readfile($pdfPath);
			die();
		} else {
			return file_get_contents($pdfPath);
		}
	}
}
