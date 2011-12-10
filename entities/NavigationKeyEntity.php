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

use Venne\ORM\Column;

/**
 * @author Josef Kříž
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="navigationKey")
 * 
 * @property string $val
 * @property string $key
 * @property /Venne/CMS/Models/Navigation $navigation
 */
class NavigationKeyEntity extends \Venne\Doctrine\ORM\BaseEntity {


	/**
	 *  @Column(type="string")
	 */
	protected $val;

	/**
	 *  @Column(name="`key`", type="string")
	 */
	protected $key;

	/**
	 * @ManyToOne(targetEntity="navigationEntity", inversedBy="id")
	 * @JoinColumn(name="navigation_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $navigation;



	public function __toString()
	{
		return $this->val;
	}



	/**
	 * @return string 
	 */
	public function getKey()
	{
		return $this->key;
	}



	/**
	 * @param string $key 
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}



	/**
	 * @return string 
	 */
	public function getVal()
	{
		return $this->val;
	}



	/**
	 * @param string $val 
	 */
	public function setVal($val)
	{
		$this->val = $val;
	}



	/**
	 * @return NavigationEntity 
	 */
	public function getNavigation()
	{
		return $this->navigation;
	}



	/**
	 * @param NavigationEntity $navigation 
	 */
	public function setNavigation($navigation)
	{
		$this->navigation = $navigation;
	}

}
