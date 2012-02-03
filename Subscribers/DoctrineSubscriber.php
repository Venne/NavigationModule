<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\NavigationModule\Subscribers;

use Nette\Caching\Cache;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\NavigationModule\Entities\NavigationEntity;
use App\NavigationModule\Entities\TranslationEntity;
use Nette\Caching\Storages\FileStorage;
use App\NavigationModule\Module;
use Nette\DI\Container;

/**
 * @author Josef Kříž
 */
class DoctrineSubscriber implements EventSubscriber {


	/** @var Cache */
	protected $cache;



	public function __construct(FileStorage $cacheStorage)
	{
		$this->cache = new Cache($cacheStorage);
	}



	public function getSubscribedEvents()
	{
		return array(\Doctrine\ORM\Events::onFlush);
	}



	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		$entities = $uow->getScheduledEntityInsertions() + $uow->getScheduledEntityUpdates() + $uow->getScheduledEntityDeletions();
		foreach ($entities AS $entity) {
			if ($entity instanceof NavigationEntity || $entity instanceof TranslationEntity) {
				$this->cache->clean(array(Cache::TAGS => array(Module::CACHE_TAG),));

				break;
			}
		}
	}

}