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

use \Venne\Developer\Module\Service\IRouteService;
use \App\CoreModule\NavigationEntity;

/**
 * @author Josef Kříž
 */
class Module extends \Venne\Module\AutoModule {



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

	public function configure(\Venne\DI\Container $container, \App\CoreModule\CmsManager $manager)
	{
		parent::configure($container, $manager);
		$manager->addService("navigation", function() use ($container) {
					return new NavigationService($container, "navigation", $container->doctrineContainer->entityManager);
				});
		$manager->addRepository("navigation", function() use ($container) {
					return $container->doctrineContainer->entityManager->getRepository("\\App\\NavigationModule\\NavigationEntity");
				});
		$manager->addEventListener("navigationListener", function() use ($container) {
					return new NavigationListener($container->cacheStorage);
				}, array("listener")
		);
		$manager->addEventSubscriber(new ContentExtensionSubscriber($container->navigationService, $container->navigationRepository));
		$manager->addEventListener(array(\App\CoreModule\Events::onAdminMenu), $this);
		
		$manager->addElement("navigation", function(){
			return new NavigationElement;
		});
	}





	public function setSubscribers(\Venne\DI\Container $container, \Doctrine\Common\EventManager $evm)
	{
		parent::setSubscribers($container, $evm);
		$evm->addEventSubscriber(new ContentExtensionSubscriber($container->navigationService, $container->navigationRepository));
	}



	public function setListeners(\Venne\DI\Container $container, \Doctrine\Common\EventManager $evm)
	{
		$evm->addEventListener(array(\App\CoreModule\Events::onAdminMenu), $this);
	}



	public function onAdminMenu($menu)
	{
		$nav = new NavigationEntity("Navigation");
		$nav->setLink(":Navigation:Admin:Default:");
		$nav->setMask(":Navigation:Admin:*:*");
		$menu->addNavigation($nav);
	}



	public function setHooks(\Venne\DI\Container $container, \App\HookModule\Manager $manager)
	{
		//$manager->addHook("admin\\menu", \callback($container->navigationService, "hookAdminMenu"));
		$manager->addHookExtension(\App\HookModule\Manager::EXTENSION_CONTENT, new \App\NavigationModule\NavigationContentExtension($container->navigationService));
	}

}
