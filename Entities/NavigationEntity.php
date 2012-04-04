<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NavigationModule\Entities;

use Venne\Doctrine\ORM\BaseEntity;
use CoreModule\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
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
	protected $_active = false;

	/**
	 * @Column(type="integer", name="`order`")
	 */
	protected $order;

	/**
	 * @Column(type="boolean")
	 */
	protected $active;

	/**
	 * @OneToMany(targetEntity="NavigationEntity", mappedBy="parent")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $childrens;

	/**
	 * @ManyToOne(targetEntity="NavigationEntity", inversedBy="id")
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
	 * @var \CoreModule\Entities\PageEntity
	 * @ManyToOne(targetEntity="\CoreModule\Entities\PageEntity", cascade={"persist"})
	 * @JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @OneToMany(targetEntity="TranslationEntity", mappedBy="navigation", cascade={"persist", "remove", "detach"})
	 */
	protected $translations;



	public function __construct($name = "")
	{
		$this->name = $name;
		$this->active = true;
		$this->order = 0;
		$this->url = "";
		$this->link = "";
		$this->type = self::TYPE_LINK;
		$this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
		$this->translations = new \Doctrine\Common\Collections\ArrayCollection();
	}



	public function addTranslation()
	{
		$entity = new TranslationEntity($this);
		$this->translations->add($entity);
		return $entity;
	}



	public function __toString()
	{
		return $this->name;
	}



	public function translatedNameByAlias($alias)
	{
		foreach ($this->translations as $entity) {
			if ($entity->language && $entity->language->alias == $alias) {
				return $entity->translation;
			}
		}

		return $this->name;
	}



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
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildrens()
	{
		return $this->childrens;
	}



	/**
	 * @param \NavigationModule\Entities\NavigationEntity $childrens
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



	public function getTranslations()
	{
		return $this->translations;
	}



	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}



	public function setActive($active)
	{
		$this->_active = (bool)$active;

		if ($this->parent) {
			$this->parent->setActive($active);
		}
	}



	/**
	 * Is link active
	 *
	 * @return bool
	 */
	public function getActive()
	{
		return $this->_active;
	}



	/**
	 * Make link.
	 *
	 * @param \Nette\Application\UI\Presenter $presenter
	 * @return string
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
				$page = $this->page->getPageWithLanguageAlias($presenter->lang);
				$this->_link = $page ? $presenter->link(":" . $page->type, array("url" => $page->url)) : false;
			} else if ($this->type == self::TYPE_LINK) {
				$this->_link = $presenter->link($this->link);
			}
		}
		return ($this->_link ? : NULL);
	}

}
