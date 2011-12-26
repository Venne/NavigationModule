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

use Nette\Caching\Cache;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\CoreModule\PageEntity;

/**
 * @author Josef Kříž
 */
class NavigationSubscriber implements EventSubscriber {


	/** @var Cache */
	protected $cache;



	public function __construct($cacheStorage)
	{
		$this->cache = new Cache($cacheStorage);
	}



	public function getSubscribedEvents()
	{
		return array(
			\Doctrine\ORM\Events::onFlush
		);
	}



	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		$entities = $uow->getScheduledEntityInsertions() + $uow->getScheduledEntityUpdates() + $uow->getScheduledEntityUpdates();

		foreach ($entities AS $entity) {
			if ($entity instanceof NavigationEntity || $entity instanceof PageEntity) {

				$this->cache->clean(array(
					Cache::TAGS => array(Module::CACHE_TAG),
				));

				break;
			}
		}
	}

}
