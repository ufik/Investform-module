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
		
		$templatePath = APP_DIR . '/../zajistenainvestice-kalkulace.pdf';
		$length = $this->investment->getInvestmentLength();

		$company = $this->investment->getCompany();
		$name = $this->investment->getAddress()->getName() . ' ' . $this->investment->getAddress()->getLastname() . (!empty($company) ? ' / ' . $company : '');
		$fieldData = array(
			'name' => $name,
		    'investmentAmount' => number_format($this->investment->getInvestment(), 0, ",", ".") . ',- Kč',
		    'investmentAmountGraph' => number_format($this->investment->getInvestment(), 0, ",", ".") . ',- Kč',
		    'address' => $this->investment->getAddress()->getAddressString(),
		    'bankAccountNumber' => $this->investment->getBankAccount(),
		    'email' => $this->investment->getEmail(),
		    'telephoneNumber' => $this->investment->getPhone(),
		    'investmentLength' => $length . ' ' . ($length == '3' ? 'roky' : 'let'), // TODO move to settings
		    'incomeAfterTaxes' => number_format($fvoa->getTotalProfit(), 0, ",", ".") . ',- Kč',
		    'incomeBeforeTaxes' => number_format($fvoa->getTotalProfit(), 0, ",", ".") . ',- Kč'
		);

		return $this->processPdf($response, $templatePath, $fieldData, $this->investment, $this->investment->getHash());
	}

	public function printPdfContract($response = false)
	{
		$fvoa = new FutureValueOfAnnuityCalculator($this->investment->getInvestment(), $this->investment->getInvestmentLength());
		
		$templatePath = APP_DIR . "/../zajistenainvestice-smlouva_{$this->investment->getInvestmentLength()}lety-dluhopis.pdf";
		$bNumber = $this->investment->getBirthdateNumber();
		$postalAddress = ($this->investment->getPostalAddress() ? $this->investment->getPostalAddress()->getName() . ' ' . $this->investment->getPostalAddress()->getLastname() . ', ' . $this->investment->getPostalAddress()->getAddressString() : '-');

		$company = $this->investment->getCompany();
		$name = $this->investment->getAddress()->getName() . ' ' . $this->investment->getAddress()->getLastname() . (!empty($company) ? ' / ' . $company : '');
		$id = (!empty($bNumber) ? str_replace('/', '', $bNumber) : $this->investment->getRegistrationNumber());
		$fieldData = array(
		    'name' => $name,
		    'identificationNumber' => $id,
		    'address' => $this->investment->getAddress()->getAddressString(),
		    'mailingAddress' => $postalAddress,
		    'bankAccountNumber' => $this->investment->getBankAccount(),
		    'email' => $this->investment->getEmail(),
		    'paymentAmount' => number_format($fvoa->getPurchaseAmount(), 0, ',', '.') . ',- Kč',
		    'paymentBankAccount' => '2110773767/2700', // TODO move to settings
		    'telephoneNumber' => $this->investment->getPhone(),
		    'paymentVariableSymbol' => $id,
			'amountOfBonds' => $this->investment->getInvestment() / 100000, // TODO move to settings
			'pin' => $this->investment->getPin()
		);

		return $this->processPdf($response, $templatePath, $fieldData, $this->investment, $this->investment->getContractHash());
	}

	private function processPdf($response, $templatePath, $fieldData, $investment, $hash)
	{
		$pdf = new \FPDM($templatePath);
		$pdf->Load($fieldData, true); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
		$pdf->Merge();

		$contractPath = WWW_DIR . '/upload/contracts';
		if (!file_exists($contractPath)) {
			mkdir($contractPath);
		}

		$output = $this->getPdfContent($pdf);
		file_put_contents($contractPath . '/' . $hash . '.pdf', $output);

		if ($response) {
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="smlouva.pdf"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($contractPath . '/' . $hash . '.pdf'));
			header('Accept-Ranges: bytes');

			echo $output;

			die();
		} else {
			return $output;
		}
	}

	private function getPdfContent($pdf)
	{
		ob_start();
		
		$pdf->Output();

		$pdf = ob_get_contents();
		ob_clean();

		return $pdf;
	}
}
