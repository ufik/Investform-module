<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Common;

/**
 * 
 */
class FutureValueOfAnnuityCalculator
{
	private $amount;

	private $length;

	private $rates;

	private $rate;

	public function __construct($amount, $length)
	{
		$this->amount = $amount;
		$this->length = $length;

		$this->rates = array(
			3 => new Rate(3, 0.07, 1.02),
			5 => new Rate(5, 0.09, 1.03)
		);

		$this->rate = $this->rates[$this->length];
	}

	public function getProfit()
	{
		$periods = $this->length * 365;
		$periodicPaymentAmount = $this->amount / $periods;
		$rate = pow(1 + $this->rate->getRate(), 1 / 365) - 1;

		return $periodicPaymentAmount * ( ( pow(( 1 + $rate ), $periods) -1 ) / $rate ) - $this->amount;
	}

	public function getTotalProfit()
	{
		return $this->amount + $this->getNetIncome();
	}

	public function getNetIncome()
	{
		return $this->getProfit() - $this->getProfitVat();
	}

	public function getEffectiveEvaluation()
	{
		return $this->amount / $this->getPurchaseAmount();
	}

	public function getTotalAmount()
	{
		return $this->amount + $this->getProfit();
	}

	public function getPurchaseAmount()
	{
		return $this->amount + $this->getCharge();
	}

	public function getProfitVat()
	{
		return $this->getProfit() * 0.15;
	}

	public function getCharge()
	{
		return $this->amount * ($this->rate->getCharge() - 1);
	}

    /**
     * Gets the value of amount.
     *
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the value of amount.
     *
     * @param mixed $amount the amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets the value of length.
     *
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Sets the value of length.
     *
     * @param mixed $length the length
     *
     * @return self
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Gets the value of rates.
     *
     * @return mixed
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * Sets the value of rates.
     *
     * @param mixed $rates the rates
     *
     * @return self
     */
    public function setRates($rates)
    {
        $this->rates = $rates;

        return $this;
    }

    /**
     * Gets the value of rate.
     *
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Sets the value of rate.
     *
     * @param mixed $rate the rate
     *
     * @return self
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }
}