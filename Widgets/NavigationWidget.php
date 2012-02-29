<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\NavigationModule\Widgets;

use Venne;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationWidget extends Control
{


	public function startup()
	{
		parent::startup();
		$this->template->path = $this->presenter->context->httpRequest->url->path;
		$this->template->lang = $this->presenter->lang;
		$this->template->startDepth = 0;
		$this->template->followActive = false;
		$this->template->type = "navigation";
	}



	protected function updateParams()
	{
		if (isset($this->params[0])) {
			foreach ($this->params[0] as $key => $param) {
				$this->template->{$key} = $param;
			}
		}
	}



	public function renderMain()
	{
		$this->template->maxDepth = 1;
		$this->updateParams();

		parent::render();
	}



	public function renderTree()
	{
		$this->template->maxDepth = 10;
		$this->updateParams();

		parent::render();
	}



	public function renderSubmain()
	{
		$this->template->startDepth = 1;
		$this->template->maxDepth = 1;
		$this->updateParams();

		parent::render();
	}



	public function renderPath()
	{
		$this->template->type = "path";

		parent::render();
	}



	public function getItems()
	{
		$repository = $this->presenter->context->navigation->navigationRepository;

		if ($this->presenter instanceof \App\CoreModule\Presenters\PagePresenter) {
			$page = $this->presenter->page->page;
			$entity = $repository->findOneBy(array("page" => $page->id));
			while (!$entity) {
				$page = $page->parent;
				if (!$page || $page->url == "") {
					break;
				}
				$entity = $repository->findOneBy(array("page" => $page->id));
			}

			if ($entity) {
				$entity->setActive(true);
			}
		}

		return $repository->findBy(array("parent" => NULL), array("order" => "asc"));
	}



	public function getPaths()
	{
		return $this->presenter->getPaths();
	}

}
