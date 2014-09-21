<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Common;

/**
 * 
 */
class Rate
{
	private $length;

	private $rate;

	private $charge;

	public function __construct($length, $rate, $charge)
	{
		$this->length = $length;
		$this->rate = $rate;
		$this->charge = $charge;
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

    /**
     * Gets the value of charge.
     *
     * @return mixed
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Sets the value of charge.
     *
     * @param mixed $charge the charge
     *
     * @return self
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;

        return $this;
    }
}