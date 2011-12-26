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
use Venne\Doctrine\ORM\BaseEntity;
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
class NavigationEntity extends BaseEntity {


	const TYPE_URL = "url";
	const TYPE_DIR = "dir";
	const TYPE_PAGE = "page";
	const TYPE_LINK = "link";


	/** @var string */
	protected $_link;

	/** @var bool */
	protected $_active;



	public function __construct($name = "")
	{
		$this->name = $name;
		$this->active = true;
		$this->order = 0;
		$this->url = "";
		$this->link = "";
		$this->type = self::TYPE_LINK;
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
	 */
	protected $parent;

	/** @Column(type="string") */
	protected $name;

	/** @Column(type="string") */
	protected $type;

	/** @Column(type="string") */
	protected $link;

	/** @Column(type="string") */
	protected $url;

	/**
	 * @var \App\CoreModule\PageEntity
	 * @ManyToOne(targetEntity="\App\CoreModule\PageEntity")
	 * @JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;



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



	public function getPage()
	{
		return $this->page;
	}



	public function setPage($page)
	{
		$this->page = $page;
	}



	public function getType()
	{
		return $this->type;
	}



	public function setType($type)
	{
		$this->type = $type;
	}



	public function getLink()
	{
		return $this->link;
	}



	public function setLink($link)
	{
		$this->link = $link;
	}



	public function getUrl()
	{
		return $this->url;
	}



	public function setUrl($url)
	{
		$this->url = $url;
	}



	/**
	 * Is link active
	 * @param \Nette\Application\UI\Presenter $presenter
	 * @return bool
	 */
	public function isActive(\Nette\Application\UI\Presenter $presenter)
	{
		if ($this->_active === NULL) {
			if ($presenter->isUrlCurrent($this->makeLink($presenter))) {
				$this->_active = true;
			} else {
				foreach ($this->childrens as $item) {
					if ($item->isActive($presenter)) {
						$this->_active = true;
						break;
					}
				}
				$this->_active = $this->_active === NULL ? false : true;
			}
		}
		return $this->_active;
	}



	/**
	 * Make link
	 * @param \Nette\Application\UI\Presenter $presenter
	 * @return type 
	 */
	public function makeLink(\Nette\Application\UI\Presenter $presenter)
	{
		if (!$this->_link) {
			if ($this->type == self::TYPE_URL) {
				$this->_link = $this->url;
			} else if ($this->type == self::TYPE_DIR) {
				$item = $this->childrens[0];
				if ($item) {
					$this->_link = $item->makeLink($presenter);
				} else {
					$this->_link = $presenter->template->basePath;
				}
			} else if ($this->type == self::TYPE_PAGE) {
				$this->_link = $presenter->link(":" . $this->page->type, array("url" => $this->page->url));
			} else if ($this->type == self::TYPE_LINK) {
				$this->_link = $presenter->link($this->link);
			}
		}
		return $this->_link;
	}

}
