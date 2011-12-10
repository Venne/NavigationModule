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
class NavigationElement extends \Venne\Application\UI\Element {



	public function startup()
	{
		$this->template->type = "navigation";
		if ($this->key == "main" || $this->key == "tree" || $this->key == "submain") {
			$this->template->startDepth = isset($this->params["startDepth"]) ? $this->params["startDepth"] : 0;
			$this->template->maxDepth = isset($this->params["maxDepth"]) ? $this->params["maxDepth"] : 9999;
			$this->template->followActive = isset($this->params["followActive"]) ? $this->params["followActive"] : true;

			if ($this->key == "main") {
				$this->template->maxDepth = 1;
			}

			if ($this->key == "submain") {
				$this->template->startDepth = 1;
				$this->template->maxDepth = 1;
			}
		} else {
			$this->template->type = "path";
		}
	}



	public function getItems()
	{
		if ($this->key == "main" || $this->key == "tree" || $this->key == "submain") {
			return $this->getContext()->navigationRepository->findBy(array("parent" => NULL));
		} else if ($this->key == "path") {
			return $this->presenter->getPaths();
		}
	}

}
