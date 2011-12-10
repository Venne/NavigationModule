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

use Venne;

/**
 * @author Josef Kříž
 */
class NavigationListener implements \Doctrine\Common\EventSubscriber {


	/** @var \Nette\Caching\Cache */
	protected $cache;


	public function __construct(\Nette\Caching\IStorage $cacheStorage)
	{
		$this->cache = new \Nette\Caching\Cache($cacheStorage);
	}


	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			\Doctrine\ORM\Events::onFlush
		);
	}


	/**
	 * @param \Doctrine\ORM\Event\OnFlushEventArgs $args 
	 */
	public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $args)
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Process updated entities
		foreach (array("Updates", "Deletions", "Insertions") as $item) {
			foreach ($uow->{"getScheduledEntity" . $item}() as $entity) {
				if ($entity instanceof NavigationEntity || $entity instanceof NavigationKeyEntity) {
					$this->cache->clean(array(
						\Nette\Caching\Cache::TAGS => array("NavigationModule")
					));
				}
				break;
			}
		}
	}

}
