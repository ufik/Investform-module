<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="investform_Address")
 */
class Address extends \WebCMS\Entity\Entity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $lastname;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $street;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $postcode;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $city;

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of lastname.
     *
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Sets the value of lastname.
     *
     * @param mixed $lastname the lastname
     *
     * @return self
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Gets the value of street.
     *
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the value of street.
     *
     * @param mixed $street the street
     *
     * @return self
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Gets the value of postcode.
     *
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Sets the value of postcode.
     *
     * @param mixed $postcode the postcode
     *
     * @return self
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Gets the value of city.
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the value of city.
     *
     * @param mixed $city the city
     *
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }
}