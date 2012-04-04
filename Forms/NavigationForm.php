<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NavigationModule\Forms;

use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationForm extends \Venne\Forms\EntityForm {


	/** @var \CoreModule\ScannerService */
	protected $scannerService;



	/**
	 * @param object $entity
	 * @param Mapping\EntityFormMapper $mapper
	 */
	public function __construct(\Venne\Forms\Mapping\EntityFormMapper $mapper, \Doctrine\ORM\EntityManager $entityManager, \CoreModule\Services\ScannerService $scannerService)
	{
		$this->scannerService = $scannerService;
		parent::__construct($mapper, $entityManager);
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup("Item");
		$this->addHidden("id");
		$this->addText("name", "Name");
		$this->addSelect("type", "Type")->setItems(array("url", "dir", "page", "link"), false)->setDefaultValue("link");

		$this->addManyToOne("parent", "Parent");

		$this->addText("url", "URL")->addCondition(self::URL, "ahoj");
		$this->addText("link", "Nette link")->addCondition(self::URL, "ahoj");
		$this->addManyToOne("page", "Page");


		/* translations */
		$form = $this;
		$removeEvent = callback($this, 'removeTranslation');
		$factory = function (Container $container) use ($removeEvent, $form)
		{
			$container->setCurrentGroup($form->addGroup("Translation"));
			$container->addText("translation", "Translation");
			$container->addManyToOne("language", "Language");
			$container->addSubmit("_remove", "Remove")->onClick[] = function() use ($form, $container)
			{
				$form->entityManager->remove($container->entity);
				$form->entityManager->flush();
				$form->presenter->redirect("this");
			};
		};

		$container = $this->addOneToManyContainer("translations", $factory, function() use ($form)
		{
			return $form->entity->addTranslation();
		});


		$this->setCurrentGroup();
		$this->addSubmit('_addTranslation', 'Add translation')->setValidationScope(FALSE)->onClick[] = callback($this, 'addTranslation');
	}



	public function addTranslation(SubmitButton $button)
	{
		$this->entity->addTranslation();
		$this->entityManager->flush();
		$this->presenter->redirect("this");
	}



	public function removeTranslation(SubmitButton $button)
	{
		$this->entityManager->remove($button->getControl());
		$this->presenter->redirect("this");
	}



	/**
	 * @param Nette\Forms\Controls\SubmitButton $button
	 */
	public function add(\Nette\Forms\Controls\SubmitButton $button)
	{
		$button->parent->createOne();
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
