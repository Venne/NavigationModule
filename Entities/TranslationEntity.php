<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\NavigationModule\Entities;

use Venne\Doctrine\ORM\BaseEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="navigationTranslation")
 *
 * @property NavigationEntity $navigation
 * @property \App\CoreModule\Entities\LanguageEntity $language
 * @property string $name
 */
class TranslationEntity extends BaseEntity {


	/**
	 * @ManyToOne(targetEntity="NavigationEntity")
	 */
	protected $navigation;

	/**
	 * @ManyToOne(targetEntity="\App\CoreModule\Entities\LanguageEntity")
	 */
	protected $language;

	/**
	 * @column(type="string")
	 */
	protected $translation;



	public function __construct($navigation, $language = NULL)
	{
		$this->navigation = $navigation;
		$this->language = $language;
		$this->translation = $navigation->name;
	}



	public function getTranslation()
	{
		return $this->translation;
	}



	public function setTranslation($translation)
	{
		$this->translation = $translation;
	}



	public function getLanguage()
	{
		return $this->language;
	}



	public function setLanguage($language)
	{
		$this->language = $language;
	}

}
