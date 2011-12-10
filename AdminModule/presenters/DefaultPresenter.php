<?php

namespace App\NavigationModule\AdminModule;

use Nette\Utils\Html;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class DefaultPresenter extends \Venne\Application\UI\AdminPresenter {


	/** @persistent */
	public $id;


	/**
	 * @privilege read
	 */
	public function startup()
	{
		parent::startup();
		
		$this->addPath("Navigation", $this->link(":Navigation:Admin:Default:"));
		$this->template->menu = $this->context->navigationService->getRootItems();
		$this->template->dep = Null;
	}


	/**
	 * @privilege create
	 */
	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Navigation:Admin:Default:create"));
	}


	/**
	 * @privilege edit
	 */
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
				if ($item->childrens)
					$this->formRecursion($form, $item->childrens);
			}
		}
	}


	public function formSaveRecursion($form, $menu)
	{
		foreach ($menu as $key => $item) {
			if ($form["delete_" . $item->id]->isSubmittedBy()) {
				$this->context->navigationRepository->delete($this->context->navigationRepository->find($item->id));
				$this->flashMessage("Menu item has been deleted", "success");
				$this->redirect("this");
			}
			if ($form["settings_" . $item->id]->isSubmittedBy()) {
				$this->redirect("edit", array("id" => $item->id));
			}

			if ($item->childrens)
				$this->formSaveRecursion($form, $item->childrens);
		}
	}


	/**
	 * @privilege edit
	 */
	public function handleSave()
	{
		$this->formSaveRecursion($this["form"], $this->template->menu);
	}


	/**
	 * @allowed(administration-navigation-edit)
	 */
	public function handleSortSave()
	{
		$data = array();
		$val = $this["formSort"]->getValues();
		$hash = explode("&", $val["hash"]);
		foreach ($hash as $item) {
			$item = explode("=", $item);
			$depend = $item[1];
			if ($depend == "root")
				$depend = Null;
			$id = \substr($item[0], 5, -1);
			if (!isset($data[$depend]))
				$data[$depend] = array();
			$order = count($data[$depend]) + 1;
			$data[$depend][] = array("id" => $id, "order" => $order, "navigation_id" => $depend);
		}
		$this->context->navigationService->setStructure($data);
		$this->flashMessage("Structure has been saved.", "success");
		$this->redirect("this");
	}


	public function createComponentFormMenu($name)
	{
		$repository = $this->context->navigationRepository;
		$em = $this->context->doctrineContainer->entityManager;
		$mapper = $this->context->doctrineContainer->entityFormMapper;
		$entity = $repository->createNew();
		
		$form = new \App\NavigationModule\NavigationForm($entity, $mapper, $em, $this->context->scannerService);
		$form->setSuccessLink("default");
		$form->setFlashMessage("Navigation has been created");
		return $form;
	}


	public function createComponentFormMenuEdit($name)
	{
		$form = new \App\NavigationModule\NavigationForm($this->context->scannerService, $this->context->navigationService->repository->find($this->getParam("id")));
		$form->setSuccessLink("this");
		$form->setFlashMessage("Navigation has been updated");
		return $form;
	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->setTitle("Venne:CMS | Navigation administration");
		$this->setKeywords("navigation administration");
		$this->setDescription("Navigation administration");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}


	public function renderDefault()
	{
		$this->template->form = $this["form"];
	}


	public function renderCreate()
	{
		
	}

}