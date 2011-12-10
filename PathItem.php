<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\NavigationModule;

use Venne;

/**
 * @author Josef Kříž
 */
class PathItem {


	/** @var string */
	protected $name;
	/** @var string */
	protected $url;


	/**
	 * @param string $name 
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}


	/**
	 * @return string 
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

}
