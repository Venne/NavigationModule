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

use Venne\Developer\Module\Service\IRouteService;
use App\CoreModule\NavigationEntity;
use Nette\DI\ContainerBuilder;

/**
 * @author Josef Kříž
 */
class Module extends \Venne\Module\AutoModule {


	const CACHE_TAG = "App\NavigationModule";



	public function getName()
	{
		return "navigation";
	}



	public function getDescription()
	{
		return "Module for managing navigation";
	}



	public function getVersion()
	{
		return "2.0";
	}



	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$container->addDefinition("navigationControl")
				->setClass("App\NavigationModule\NavigationControl")
				->setShared(false)
				->setAutowired(false)
				->addTag("control");

		$container->addDefinition("navigationFormControl")
				->setParameters(array("entity"))
				->setClass("App\NavigationModule\NavigationForm", array("@entityFormMapper", "@entityManager", "@scannerService", "%entity%"))
				->setShared(false)
				->setAutowired(false)
				->addTag("control");

		$container->addDefinition("navigationService")
				->setClass("App\NavigationModule\NavigationService", array("@container", "navigation", "@entityManager"))
				->addTag("service");

		$container->addDefinition("navigationRepository")
				->setClass("Venne\Doctrine\ORM\BaseRepository")
				->setFactory("@entityManager::getRepository", array("\\App\\NavigationModule\\NavigationEntity"))
				->addTag("repository")
				->setAutowired(false);
	}



	public function configure(\Nette\DI\Container $container, \App\CoreModule\CmsManager $manager)
	{
		parent::configure($container, $manager);

		$manager->addEventListener("navigationListener", function() use ($container) {
					return new NavigationListener($container->cacheStorage);
				}, array("listener")
		);
		$manager->addEventSubscriber(new ContentExtensionSubscriber($container->navigationService, $container->navigationRepository));
		$manager->addEventListener(array(\App\CoreModule\Events::onAdminMenu), $this);
		$manager->addEventSubscriber(new NavigationSubscriber($container->cacheStorage));
	}



	public function onAdminMenu($menu)
	{
		$nav = new NavigationEntity("Navigation");
		$nav->setLink(":Navigation:Admin:Default:");
		$nav->setMask(":Navigation:Admin:*:*");
		$menu->addNavigation($nav);
	}

}
