<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	public function createTemplate()
	{
		$tpl = parent::createTemplate();
		$tpl->registerHelper("secsToTime", function ($secs)
		{
			$hours = floor($secs / 60 / 60);
			$minutes = str_pad(floor(($secs - 3600*$hours) / 60), 2, "0", STR_PAD_LEFT);
			return $hours . ":$minutes";
		});

		return $tpl;
	}
}
