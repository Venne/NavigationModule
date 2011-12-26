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

use Doctrine\Common\EventSubscriber;
use Venne\ContentExtension\Events;

/**
 * @author Josef Kříž
 */
class ContentExtensionSubscriber implements EventSubscriber {



	public function getSubscribedEvents()
	{
		return array(
			Events::onCreate,
			Events::onLoad,
			Events::onSave,
			Events::onRemove
		);
	}

	/** @var \NavigationModule\Service */
	protected $service;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $repository;



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\App\NavigationModule\NavigationService $service, $repository)
	{
		$this->service = $service;
		$this->repository = $repository;
	}



	public function onSave(\Venne\ContentExtension\EventArgs $args)
	{
		$form = $args->form;
		$page = $args->page;

		$values = $form->getContentExtensionContainer("navigation")->getValues();

		$menu = $this->repository->findOneBy(
				array(
					"page" => $page->id,
				)
		);

		if (!$menu) {
			if ($values["use"]) {
				$entity = $this->repository->createNew(array($values["name"]));
				$entity->type = NavigationEntity::TYPE_PAGE;
				$entity->name = $values["name"];
				$entity->parent = $this->repository->find($values["navigation_id"]);
				$entity->page = $page;
				$this->repository->save($entity);
			}
		} else {
			if ($values["use"]) {
				$entity = $this->repository->findOneBy(array("page" => $page->id));
				$entity->name = $values["name"];
				$entity->parent = $this->repository->find($values["navigation_id"]);
				$this->repository->update($entity);
			} else {
				$entity = $this->repository->findOneBy(array("page" => $page->id));
				$this->repository->delete($entity);
			}
		}
	}



	public function onCreate(\Venne\ContentExtension\EventArgs $args)
	{
		$form = $args->form;

		$container = $form->addContentExtensionContainer("navigation", "Navigation settings");

		$container->addCheckbox("use", "Create navigation item");
		$container->addText("name", "Navigation name")->getControlPrototype()->onclick();
		$container->addSelect("navigation_id", "Navigation parent")
				->setItems($this->service->getCurrentList())
				->setPrompt("root");

		$form->setCurrentGroup();
	}



	public function onLoad(\Venne\ContentExtension\EventArgs $args)
	{
		$form = $args->form;
		$page = $args->page;

		$container = $form->getContentExtensionContainer("navigation");

		$menu = $this->repository->findOneBy(
				array(
					"page" => $page->id,
					"type" => NavigationEntity::TYPE_PAGE
				)
		);
		if ($menu) {
			$container["use"]->setValue(true);
			$container["name"]->setValue($menu->name);

			if (!$menu->parent) {
				$container["navigation_id"]->setValue(NULL);
			} else {
				$container["navigation_id"]->setValue($menu->parent->id);
			}
		} else {
			$container["use"]->setValue(false);
		}
	}

}
