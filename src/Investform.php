<?php

/**
 * This file is part of the Investform module for webcms2.
 * Copyright (c) @see LICENSE
 */

namespace WebCMS\InvestformModule;

/**
 * Description of investform
 *
 * @author Tomas Voslar <tomas.voslar@webcook.cz>
 */
class Investform extends \WebCMS\Module
{
	/**
	 * [$name description]
	 * @var string
	 */
    protected $name = 'Investform';
    
    /**
     * [$author description]
     * @var string
     */
    protected $author = 'Tomas Voslar';
    
    /**
     * [$presenters description]
     * @var array
     */
    protected $presenters = array(
		array(
		    'name' => 'Investform',
		    'frontend' => TRUE,
		    'parameters' => FALSE
		),
		array(
		    'name' => 'Settings',
		    'frontend' => FALSE
		)
    );

    public function __construct() 
    {
	
    }
}
