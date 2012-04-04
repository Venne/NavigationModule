<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace NavigationModule\Subscribers;

use Doctrine\Common\EventSubscriber;
use Venne\ContentExtension\Events;
use Nette\DI\Container;
use NavigationModule\Entities\NavigationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationSubscriber implements EventSubscriber
{


	/** @var \NavigationModule\Service */
	protected $service;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $repository;



	/**
	 * @param Container $context
	 */
	public function __construct(Container $context)
	{
		$this->service = $context->navigation->navigationService;
		$this->repository = $context->navigation->navigationRepository;
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::onContentExtensionCreate,
			Events::onContentExtensionLoad,
			Events::onContentExtensionSave,
			Events::onContentExtensionRemove
		);
	}



	public function onContentExtensionSave(\Venne\ContentExtension\EventArgs $args)
	{
		$form = $args->form;
		$page = $args->page;

		if (!$form->entity->translationFor) {
			$values = $form->getContentExtensionContainer("navigation")->getValues();

			$menu = $page->id ? $this->repository->findOneBy(array("page" => $page->id,)) : NULL;

			if (!$menu) {
				if ($values["use"]) {
					$entity = $this->repository->createNew(array($values["name"]));
					$entity->type = NavigationEntity::TYPE_PAGE;
					$entity->name = $values["name"];
					$entity->parent = $values["navigation_id"] ? $this->repository->find($values["navigation_id"]) : NULL;
					$entity->page = $page;

					$this->repository->save($entity);
				}
			} else {
				if ($values["use"]) {
					$entity = $this->repository->findOneBy(array("page" => $page->id));
					$entity->name = $values["name"];
					$entity->parent = $values["navigation_id"] ? $this->repository->find($values["navigation_id"]) : NULL;
					$this->repository->update($entity);
				} else {
					$entity = $this->repository->findOneBy(array("page" => $page->id));
					$this->repository->delete($entity);
				}
			}
		}
	}



	public function onContentExtensionCreate(\Venne\ContentExtension\EventArgs $args)
	{
		$form = $args->form;

		if (!$form->entity->translationFor) {
			$container = $form->addContentExtensionContainer("navigation", "Navigation settings");

			$container->addCheckbox("use", "Create navigation item");
			$container->addText("name", "Navigation name")->getControlPrototype()->onclick();
			$container->addSelect("navigation_id", "Navigation parent")->setItems($this->service->getCurrentList())->setPrompt("root");

			$form->setCurrentGroup();
		}
	}



	public function onContentExtensionLoad(\Venne\ContentExtension\EventArgs $args)
	{
		$form = $args->form;
		$page = $args->page;

		if (!$form->entity->translationFor) {
			$container = $form->getContentExtensionContainer("navigation");

			$menu = $this->repository->findOneBy(array("page" => $page->id, "type" => NavigationEntity::TYPE_PAGE));
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

}
