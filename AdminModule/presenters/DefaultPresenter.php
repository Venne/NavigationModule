<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


namespace App\NavigationModule\AdminModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DefaultPresenter extends \App\CoreModule\Presenters\AdminPresenter {


	/** @persistent */
	public $id;



	public function startup()
	{
		parent::startup();

		$this->addPath("Navigation", $this->link(":Navigation:Admin:Default:"));
		$this->template->menu = $this->context->navigation->navigationService->getRootItems();
		$this->template->dep = Null;
	}



	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Navigation:Admin:Default:create"));
	}



	public function actionEdit()
	{
		$this->addPath("edit" . " (" . $this->id . ")", $this->link(":Navigation:Admin:Default:edit"));
	}



	public function createComponentForm($name)
	{
		$form = new \Venne\Application\UI\Form($this, $name);
		$this->formRecursion($form, $this->template->menu);
		$form->onSuccess[] = array($this, "handleSave");
		return $form;
	}



	public function createComponentFormSort()
	{
		$form = new \Venne\Application\UI\Form($this, "formSort");
		$form->addHidden("hash");
		$form->addSubmit("Save", "Save")->onClick[] = array($this, "handleSortSave");
		return $form;
	}



	public function formRecursion($form, $menu)
	{
		if ($menu) {
			foreach ($menu as $item) {
				$form->addSubmit("settings_" . $item->id, "Settings");
				$form->addSubmit("delete_" . $item->id, "Delete")->getControlPrototype()->class = "grey";
				if ($item->childrens) $this->formRecursion($form, $item->childrens);
			}
		}
	}



	public function formSaveRecursion($form, $menu)
	{
		foreach ($menu as $key => $item) {
			if ($form["delete_" . $item->id]->isSubmittedBy()) {
				$this->context->navigation->navigationRepository->delete($this->context->navigation->navigationRepository->find($item->id));
				$this->flashMessage("Menu item has been deleted", "success");
				$this->redirect("this");
			}
			if ($form["settings_" . $item->id]->isSubmittedBy()) {
				$this->redirect("edit", array("id" => $item->id));
			}

			if ($item->childrens) $this->formSaveRecursion($form, $item->childrens);
		}
	}



	public function handleSave()
	{
		$this->formSaveRecursion($this["form"], $this->template->menu);
	}



	public function handleSortSave()
	{
		$data = array();
		$val = $this["formSort"]->getValues();
		$hash = explode("&", $val["hash"]);
		foreach ($hash as $item) {
			$item = explode("=", $item);
			$depend = $item[1];
			if ($depend == "root") $depend = Null;
			$id = \substr($item[0], 5, -1);
			if (!isset($data[$depend])) $data[$depend] = array();
			$order = count($data[$depend]) + 1;
			$data[$depend][] = array("id" => $id, "order" => $order, "navigation_id" => $depend);
		}
		$this->context->navigation->navigationService->setStructure($data);
		$this->flashMessage("Structure has been saved.", "success");
		$this->redirect("this");
	}



	public function createComponentFormMenu($name)
	{
		$repository = $this->context->navigation->navigationRepository;
		$entity = $repository->createNew();

		$form = $this->context->navigation->createNavigationForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($repository)
		{
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Navigation has been created");
			$form->getPresenter()->redirect("default");
		};
		return $form;
	}



	public function createComponentFormMenuEdit($name)
	{
		$repository = $this->context->navigation->navigationRepository;
		$entity = $repository->find($this->getParam("id"));

		$form = $this->context->navigation->createNavigationForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save")->onClick[] = function($button) use ($repository)
		{
			$form = $button->form;
			$repository->update($form->entity);
			$form->getPresenter()->flashMessage("Navigation has been updated");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function renderDefault()
	{
		$this->template->form = $this["form"];
	}



	public function renderCreate()
	{

	}

}