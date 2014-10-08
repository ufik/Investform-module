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
		
		$templatePath = APP_DIR . '/../zajistenainvestice-kalkulace.pdf';
		$fieldData = array(
		    "name" => $this->investment->getAddress()->getName()
		);

		$outputPath = WWW_DIR . '/' . mt_rand() . '.pdf';
		\PHPPDFFill\PDFFill::make($templatePath, $fieldData)->save_pdf($outputPath);

		return $this->processPdf($response, $outputPath);
	}

	public function printPdfContract($response = false)
	{
		$fvoa = new FutureValueOfAnnuityCalculator($this->investment->getInvestment(), $this->investment->getInvestmentLength());
		
		$templatePath = APP_DIR . "/../zajistenainvestice-smlouva_{$this->investment->getInvestmentLength()}lety-dluhopis.pdf";
		$fieldData = array(
		    "name" => $this->investment->getAddress()->getName()
		);

		$outputPath = WWW_DIR . '/' . mt_rand() . '.pdf';
		\PHPPDFFill\PDFFill::make($templatePath, $fieldData)->save_pdf($outputPath);

		return $this->processPdf($response, $outputPath);
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

			unlink($pdfPath);
			die();
		} else {
			ob_start();
			@readfile($pdfPath);
			$pdf = ob_get_contents();
			ob_clean();

			unlink($pdfPath);
			return $pdf;
		}
	}
}
