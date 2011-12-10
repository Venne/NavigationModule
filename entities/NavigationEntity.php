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
use Venne\ContentExtension\ContentExtensionEntity;
use App\CoreModule\PageEntity;

/**
 * @author Josef Kříž
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="navigation")
 * 
 * @property NavigationEntity $childrens
 * @property NavigationEntity $parent
 * @property bool $active
 * @property string $name
 */
class NavigationEntity extends ContentExtensionEntity {



	public function __construct(PageEntity $page, $name)
	{
		parent::__construct($page);
		$this->name = $name;
		$this->active = true;
		$this->order = 0;
		$this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
	}



	public function __toString()
	{
		return $this->name;
	}

	/**
	 * @Column(type="integer", name="`order`")
	 */
	protected $order;

	/**
	 *  @Column(type="boolean")
	 */
	protected $active;

	/**
	 * @OneToMany(targetEntity="navigationEntity", mappedBy="parent")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $childrens;

	/**
	 * @ManyToOne(targetEntity="navigationEntity", inversedBy="id")
	 * @JoinColumn(name="navigation_id", referencedColumnName="id", onDelete="CASCADE")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $parent;

	/** @Column(type="string") */
	protected $name;



	/**
	 * @return string 
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $name 
	 */
	public function setName($name)
	{
		$this->name = $name;
	}



	/**
	 * @return string 
	 */
	public function getActive()
	{
		return $this->active;
	}



	/**
	 * @param string $active 
	 */
	public function setActive($active)
	{
		$this->active = $active;
	}



	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildrens()
	{
		return $this->childrens;
	}



	/**
	 * @param \App\StaModule\NavigationEntity $childrens 
	 */
	public function addChildren($childrens)
	{
		$this->childrens[] = $childrens;
	}



	/**
	 * @return NavigationEntity
	 */
	public function getParent()
	{
		return $this->parent;
	}



	/**
	 * @param NavigationEntity $parent 
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
	}



	public function getOrder()
	{
		return $this->order;
	}



	public function setOrder($order)
	{
		$this->order = $order;
	}



	public function getLink($presenter)
	{
		$presenterName = ":" . $this->page->type;
	
		if ($presenter->context->params["website"]["multilang"]) {
			return $presenter->link($presenterName, array("url" => $this->page->getUrl(), "lang" => $this->page->language[0]->alias));
		}

		
		return $presenter->link($presenterName, array("url" => $this->page->getUrl(), "lang"=>NULL));
	}

}
