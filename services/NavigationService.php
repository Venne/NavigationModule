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
use Nette\Object;

/**
 * @author Josef Kříž
 */
class NavigationService extends Object {


	/** @var array() */
	protected $path = array();

	/** @var \Venne\Modules\Navigation */
	protected $rootItems;

	/** @var \Venne\Modules\Navigation */
	protected $frontRootItems;

	/** @var \Venne\DI\Container */
	protected $context;

	/** @var \Doctrine\ORM\EntityManager */
	public $entityManager;



	/**
	 * @inject(context, entityManager)
	 * @param \Venne\DI\Container $context
	 * @param type $moduleName
	 * @param \Doctrine\ORM\EntityManager $entityManager 
	 */
	public function __construct(\Venne\DI\Container $context, $moduleName, \Doctrine\ORM\EntityManager $entityManager)
	{
		$this->context = $context;
		$this->entityManager = $entityManager;
	}



	/**
	 * @return \Venne\Doctrine\ORM\BaseRepository 
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository("\\App\\NavigationModule\\NavigationEntity");
	}



	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @param \Nette\Http\Request $httpRequest
	 * @return \Venne\Modules\Navigation
	 */
	public function getRootItems()
	{
		if (!isset($this->rootItems)) {
			$repo = $this->getRepository();

			$this->rootItems = $repo->findBy(array("parent" => NULL), array("order" => "ASC"));
		}
		return $this->rootItems;
	}


	/**
	 * @param \Nette\Http\Request $httpRequest
	 * @param bool $without
	 * @param int $layer
	 * @param int $depend
	 * @return array
	 */
	public function getCurrentList($without = Null, $layer = 0, $depend = Null)
	{
		$em = $this->getEntityManager();
		$data = array();
		$text = "";
		if (!$depend)
			$menu = $em->createQuery('SELECT u FROM \App\NavigationModule\NavigationEntity u WHERE u.parent IS NULL')
					->getResult();
		else
			$menu = $em->createQuery('SELECT u FROM \App\NavigationModule\NavigationEntity u WHERE u.parent= :depend ')
					->setParameters(array("depend" => $depend))
					->getResult();
		for ($i = 0; $i <= $layer; $i++) {
			$text .= "--";
		}
		foreach ($menu as $item) {
			if ($item->id != $without) {
				$data[$item->id] = $text . "- " . $item->name;
				$data += $this->getList($without, $layer + 1, $item->id);
			}
		}
		return $data;
	}



	/**
	 * @param \Nette\Http\Request $httpRequest
	 * @param int $layer
	 * @param int $depend
	 * @return array
	 */
	public function getList($without = Null, $layer = 0, $depend = Null)
	{
		$em = $this->getEntityManager();
		$data = array();
		$text = "";
		if (!$depend)
			$menu = $em->createQuery('SELECT u FROM \App\NavigationModule\NavigationEntity u WHERE u.parent IS NULL')->getResult();
		else
			$menu = $em->createQuery('SELECT u FROM \App\NavigationModule\NavigationEntity u WHERE u.parent= :depend ')
					->setParameters(array("depend" => $depend))
					->getResult();
		for ($i = 0; $i <= $layer; $i++) {
			$text .= "--";
		}
		foreach ($menu as $item) {
			if ($item->id != $without) {
				$data[$item->id] = $text . "- " . $item->name;
				$data += $this->getList($without, $layer + 1, $item->id);
			}
		}
		return $data;
	}



	/**
	 * Save structure
	 * @param array $data
	 */
	public function setStructure($data)
	{
		foreach ($data as $item) {
			foreach ($item as $item2) {
				$entity = $this->getRepository()->find($item2["id"]);
				$entity->parent = $this->getRepository()->find($item2["navigation_id"]);
				$entity->order = $item2["order"];
			}
		}
		$this->getEntityManager()->flush();
	}



	/**
	 * @param integer $website_id
	 * @param integer $parent_id 
	 * @return integer
	 */
	public function getOrderValue($parent_id = NULL)
	{
		if ($parent_id) {
			$query = $this->getEntityManager()->createQuery('SELECT MAX(u.order) FROM \App\NavigationModule\NavigationEntity u WHERE u.parent = ?2')->setParameter(2, $parent_id);
		} else {
			$query = $this->getEntityManager()->createQuery('SELECT MAX(u.order) FROM \App\NavigationModule\NavigationEntity u WHERE u.parent is NULL');
		}
		return $query->getSingleScalarResult() + 1;
	}



	public function addModuleItem($moduleName, $moduleItemId, $name, $parent_id, $paramsArray, $withoutFlush = false)
	{
		$entity = new NavigationEntity;
		$entity->order = $this->getOrderValue($parent_id);
		$entity->moduleName = $moduleName;
		$entity->moduleItemId = $moduleItemId;
		$entity->name = $name;
		if ($parent_id) {
			$entity->parent = $this->getRepository()->find($parent_id);
		}
		$entity->type = NavigationEntity::TYPE_LINK;
		$this->getEntityManager()->persist($entity);

		foreach ($paramsArray as $key => $value) {
			$entityKey = new NavigationKeyEntity;
			$entityKey->navigation = $entity;
			$entityKey->key = $key;
			$entityKey->val = $value;
			$this->getEntityManager()->persist($entityKey);
		}

		if (!$withoutFlush) {
			$this->getEntityManager()->flush();
		}
	}



	public function updateModuleItem(\App\NavigationModule\NavigationEntity $menuEntity, $moduleName, $moduleItemId, $name, $parent_id, $paramsArray, $withoutFlush = false)
	{
		$menuEntity->moduleName = $moduleName;
		$menuEntity->moduleItemId = $moduleItemId;
		$menuEntity->name = $name;
		if ($parent_id) {
			$menuEntity->parent = $this->getRepository()->find($parent_id);
		}

		foreach ($menuEntity->keys as $value) {
			$this->getEntityManager()->remove($value);
			unset($value);
		}

		foreach ($paramsArray as $key => $value) {
			$entityKey = new NavigationKeyEntity;
			$entityKey->key = $key;
			$entityKey->val = $value;
			$menuEntity->keys = $entityKey;
			$this->getEntityManager()->persist($entityKey);
		}

		if (!$withoutFlush) {
			$this->getEntityManager()->flush();
		}
	}



	public function delete(\Venne\Doctrine\ORM\BaseEntity $entity, $withoutFlush = false)
	{
		$query = $this->getEntityManager()->createQuery('SELECT u FROM \App\NavigationModule\NavigationEntity u WHERE u.parent = ?1 AND u.order > ?2')->setParameter(1, isset($entity->parent->id) ? $entity->parent->id : NULL)->setParameter(2, $entity->order);
		foreach ($query->getResult() as $item) {
			$item->order = $item->order - 1;
		}
		return parent::delete($entity, $withoutFlush);
	}

}

