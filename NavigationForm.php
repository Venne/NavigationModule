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
use Nette\Utils\Html;

/**
 * @author Josef Kříž
 */
class NavigationForm extends \Venne\Forms\EntityForm {


	/** @var \App\CoreModule\ScannerService */
	protected $scannerService;



	/**
	 * @param object $entity
	 * @param Mapping\EntityFormMapper $mapper
	 */
	public function __construct(\Venne\Forms\Mapping\EntityFormMapper $mapper, \Doctrine\ORM\EntityManager $entityManager, \App\CoreModule\ScannerService $scannerService, $entity)
	{
		$this->scannerService = $scannerService;
		parent::__construct($mapper, $entityManager, $entity);
	}



	public function startup()
	{
		parent::startup();

		$data = $this->scannerService->getLinksOfModulesPresenters();

		$this->addGroup("Item");
		$this->addHidden("id");
		$this->addText("name", "Name");
		$this->addSelect("type", "Type")
				->setItems(array("url", "dir", "page", "link"), false)
				->setDefaultValue("link");

		$this->addManyToOne("parent", "Parent");

		$this->addText("url", "URL")->addCondition(self::URL, "ahoj");
		$this->addText("link", "Nette link")->addCondition(self::URL, "ahoj");
		$this->addManyToOne("page", "Page");

		
	}



	/**
	 * @param Nette\Forms\Controls\SubmitButton $button
	 */
	public function add(\Nette\Forms\Controls\SubmitButton $button)
	{
		$button->parent->createOne();
	}



	public function setup()
	{
		parent::setup();

		if ($this->getPresenter()->isAjax()) {
			$this["action"]->addOnSubmitCallback(array($this->getPresenter(), "invalidateControl"), "form");
		}
	}



	public function getValuesAction($form, $dependentSelectBoxName)
	{
		$module = explode(":", $form["presenter"]->getValue());
		$presenter = $module[count($module) - 1];
		unset($module[count($module) - 1]);
		$module = implode(":", $module);

		$actions = array();
		$data = $this->scannerService->getLinksOfActions($module, $presenter);
		foreach ($data as $item) {
			$actions[$item] = $item;
		}

		return $actions;
	}



	public function getValuesParams($form, $dependentSelectBoxName)
	{
		$module = explode(":", $form["presenter"]->getValue());
		$presenter = $module[count($module) - 1];
		unset($module[count($module) - 1]);
		$module = implode(":", $module);


		if (!$presenter) {
			return array();
		}

		$params = array();
		$data = $this->scannerService->getLinksOfParams($module, $presenter);
		foreach ($data as $item) {
			$params[$item] = $item;
		}

		return array("" => "") + $params;
	}

}
