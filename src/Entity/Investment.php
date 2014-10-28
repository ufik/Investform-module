<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="investform_Investment")
 */
class Investment extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $phone;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $birthdateNumber;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $company;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $registrationNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bankAccount;

	/**
	 * @ORM\Column(type="decimal", scale=2)
	 */
	private $investment;

	/**
	 * @ORM\Column(type="smallint")
	 */
	private $investmentLength;

    /**
     * @ORM\Column(type="date")
     */
    private $investmentDate;

	/**
     * @ORM\OneToOne(targetEntity="Address") 
     */
	private $address;

	/**
     * @ORM\OneToOne(targetEntity="Address") 
     */
	private $postalAddress;

    /**
     * @var datetime $created
     * 
     * @gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $hash;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $pin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $contractSend;

    /**
     * Gets the value of phone.
     *
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets the value of phone.
     *
     * @param mixed $phone the phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets the value of email.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the value of email.
     *
     * @param mixed $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the value of company.
     *
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Sets the value of company.
     *
     * @param mixed $company the company
     *
     * @return self
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Gets the value of registrationNumber.
     *
     * @return mixed
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * Sets the value of registrationNumber.
     *
     * @param mixed $registrationNumber the registration number
     *
     * @return self
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    /**
     * Gets the value of investment.
     *
     * @return mixed
     */
    public function getInvestment()
    {
        return $this->investment;
    }

    /**
     * Sets the value of investment.
     *
     * @param mixed $investment the investment
     *
     * @return self
     */
    public function setInvestment($investment)
    {
        $this->investment = $investment;

        return $this;
    }

    /**
     * Gets the value of investmentLength.
     *
     * @return mixed
     */
    public function getInvestmentLength()
    {
        $from = strtotime($this->investmentDate);
        if ($this->investmentLength == 5) {
            $to = strtotime('2019-10-30');
        } else {
            $to = strtotime('2017-10-30');
        }

        return ($to - $from) / 60 / 60 / 24 / 365;
    }

    /**
     * Sets the value of investmentLength.
     *
     * @param mixed $investmentLength the investment length
     *
     * @return self
     */
    public function setInvestmentLength($investmentLength)
    {
        $this->investmentLength = $investmentLength;

        return $this;
    }

    /**
     * Gets the value of birthdateNumber.
     *
     * @return mixed
     */
    public function getBirthdateNumber()
    {
        return $this->birthdateNumber;
    }

    /**
     * Sets the value of birthdateNumber.
     *
     * @param mixed $birthdateNumber the birthdate number
     *
     * @return self
     */
    public function setBirthdateNumber($birthdateNumber)
    {
        $this->birthdateNumber = $birthdateNumber;

        return $this;
    }

    /**
     * Gets the value of address.
     *
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the value of address.
     *
     * @param mixed $address the address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets the value of postalAddress.
     *
     * @return mixed
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * Sets the value of postalAddress.
     *
     * @param mixed $postalAddress the postal address
     *
     * @return self
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    /**
     * Gets the value of created.
     *
     * @return datetime $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the value of created.
     *
     * @param datetime $created $created the created
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Gets the value of hash.
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash = md5($this->id . $this->created->format('Y-m-d H:i:s'));
    }

    /**
     * Gets the value of hash.
     *
     * @return mixed
     */
    public function getContractHash()
    {
        return $this->hash = md5('contract' . $this->id . $this->created->format('Y-m-d H:i:s'));
    }

    /**
     * Gets the value of bankAccount.
     *
     * @return mixed
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Sets the value of bankAccount.
     *
     * @param mixed $bankAccount the bank account
     *
     * @return self
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Gets the value of pin.
     *
     * @return mixed
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Sets the value of pin.
     *
     * @param mixed $pin the pin
     *
     * @return self
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Gets the value of contractSend.
     *
     * @return mixed
     */
    public function getContractSend()
    {
        return $this->contractSend;
    }

    /**
     * Sets the value of contractSend.
     *
     * @param mixed $contractSend the contract send
     *
     * @return self
     */
    public function setContractSend($contractSend)
    {
        $this->contractSend = $contractSend;

        return $this;
    }

    /**
     * Gets the value of investmentDate.
     *
     * @return mixed
     */
    public function getInvestmentDate()
    {
        return $this->investmentDate;
    }

    /**
     * Sets the value of investmentDate.
     *
     * @param mixed $investmentDate the investment date
     *
     * @return self
     */
    public function setInvestmentDate($investmentDate)
    {
        $this->investmentDate = $investmentDate;

        return $this;
    }
}
