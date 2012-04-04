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
use CoreModule\Events\AdminEvents;
use CoreModule\Events\AdminEventArgs;
use CoreModule\Entities\NavigationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminSubscriber implements EventSubscriber {


	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(AdminEvents::onAdminMenu,);
	}



	/**
	 * onAdminMenu event.
	 *
	 * @param AdminEventArgs $args
	 */
	public function onAdminMenu(AdminEventArgs $args)
	{
		$nav = new NavigationEntity("Navigation");
		$nav->setLink(":Navigation:Admin:Default:");
		$nav->setMask(":Navigation:Admin:*:*");
		$args->addNavigation($nav);
	}

}
