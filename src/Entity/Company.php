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
 * @ORM\Table(name="investform_Company")
 */
class Company extends \WebCMS\Entity\Entity
{

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $zipCity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ico;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dic;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var datetime $created
     * 
     * @gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\OneToMany(targetEntity="Businessman", mappedBy="company") 
     */
    private $businessmen;


    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }
    
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }
    
    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    public function getZipCity()
    {
        return $this->zipCity;
    }
    
    public function setZipCity($zipCity)
    {
        $this->zipCity = $zipCity;
        return $this;
    }

    public function getIco()
    {
        return $this->ico;
    }
    
    public function setIco($ico)
    {
        $this->ico = $ico;
        return $this;
    }

    public function getDic()
    {
        return $this->dic;
    }
    
    public function setDic($dic)
    {
        $this->dic = $dic;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }
    
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }
    
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function getActive()
    {
        return $this->active;
    }
    
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }
    
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Gets the value of businessmen.
     *
     * @return mixed
     */
    public function getBusinessmen()
    {
        return $this->businessmen;
    }

    /**
     * Sets the value of businessmen.
     *
     * @param mixed $businessmen the businessmen
     *
     * @return self
     */
    public function setBusinessmen($businessmen)
    {
        $this->businessmen = $businessmen;
        return $this;
    }

}
