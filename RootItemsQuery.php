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
use Venne\Doctrine\ORM\QueryObjectBase;

/**
 * @author Josef Kříž
 */
class RootItemsQuery extends QueryObjectBase {



	/**
	 * @param Venne\Doctrine\IQueryable $dao
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(\Venne\Doctrine\IQueryable $dao)
	{
		return $dao->createQueryBuilder('u')
						->select('u')
						->where('u.parent is null')
						->orderBy('u.order', 'ASC');
	}

}

