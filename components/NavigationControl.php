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

/**
 * @author Josef Kříž
 */
class NavigationControl extends \Venne\Application\UI\Control {



	public function startup()
	{
		parent::startup();
		$this->template->startDepth = 0;
		$this->template->followActive = false;
	}



	protected function updateParams()
	{
		if (isset($this->params[0])) {
			foreach ($this->params[0] as $key => $param) {
				$this->template->{$key} = $param;
			}
		}
	}



	protected function viewMain()
	{
		$this->view = "menu";
		$this->template->maxDepth = 1;
		$this->updateParams();
	}



	protected function viewTree()
	{
		$this->view = "menu";
		$this->template->maxDepth = 10;
		$this->updateParams();
	}



	protected function viewSubmain()
	{
		$this->view = "menu";
		$this->template->startDepth = 1;
		$this->template->maxDepth = 1;
		$this->updateParams();
	}



	protected function viewPath()
	{
		$this->template->type = "path";
	}



	public function getItems()
	{
		return $this->presenter->context->navigationRepository->findBy(array("parent" => NULL), array("order"=>"asc"));
	}



	public function getPaths()
	{
		return $this->presenter->getPaths();
	}

}
