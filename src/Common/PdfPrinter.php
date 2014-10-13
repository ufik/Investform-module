<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Common;

require(APP_DIR . '/fpdm/fpdm.php');

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
		$length = $this->investment->getInvestmentLength();
		$fieldData = array(
		    'investmentAmount' => $this->investment->getInvestment(),
		    'investmentAmountGraph' => $this->investment->getInvestment(),
		    'investmentLength' => $length . ' ' . ($length === 3 ? 'roky' : 'let'), // TODO move to settings
		    'incomeAfterTaxes' => \WebCMS\Helpers\SystemHelper::price($fvoa->getNetIncome()),
		    'incomeBeforeTaxes' => \WebCMS\Helpers\SystemHelper::price($fvoa->getProfit())
		);

		$outputPath = WWW_DIR . '/' . mt_rand() . '.pdf';
		\PHPPDFFill\PDFFill::make($templatePath, $fieldData)->save_pdf($outputPath);

		return $this->processPdf($response, $outputPath);
	}

	public function printPdfContract($response = false)
	{
		$fvoa = new FutureValueOfAnnuityCalculator($this->investment->getInvestment(), $this->investment->getInvestmentLength());
		
		$templatePath = APP_DIR . "/../zajistenainvestice-smlouva_{$this->investment->getInvestmentLength()}lety-dluhopis1.pdf";
		$bNumber = $this->investment->getBirthdateNumber();
		$postalAddress = ($this->investment->getPostalAddress() ? $this->investment->getPostalAddress()->getAddressString() : '-');

		$fieldData = array(
		    'name' => $this->investment->getAddress()->getName() . ' ' . $this->investment->getAddress()->getLastname(),
		    'identificationNumber' => $bNumber,
		    'address' => $this->investment->getAddress()->getAddressString(),
		    'mailingAddress' => $postalAddress,
		    'bankAccountNumber' => $this->investment->getBankAccount(),
		    'email' => $this->investment->getEmail(),
		    'paymentAmount' => \WebCMS\Helpers\SystemHelper::price($fvoa->getPurchaseAmount()),
		    'paymentBankAccount' => '2110773767/2700', // TODO move to settings
		    'paymentVariableSymbol' => (!empty($bNumber) ? 
		    									$bNumber :
		    									$this->investment->getRegistrationNumber()),
			'amountOfBonds' => $this->investment->getInvestment() / 100000, // TODO move to settings
			'pin' => $this->investment->getPin()
		);

		$pdf = new \FPDM($templatePath);
		$pdf->Load($fieldData, true); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
		$pdf->Merge();
		$pdf->Output();

		//$outputPath = WWW_DIR . '/' . mt_rand() . '.pdf';
		//\PHPPDFFill\PDFFill::make($templatePath, $fieldData)->save_pdf($outputPath);

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
