<?php

namespace App\Presenters;

use App\Model\GpsPath;
use Nette;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	/** @var Nette\Database\Context @inject */
	public $database;


	public function renderDefault()
	{

		$gpxs = $this->database->query('SELECT * FROM gpx ORDER BY time DESC')->fetchAll();
		$this->template->gpxs = $gpxs;
	}

	public function actionBench()
	{
		$gpxs = $this->database->query('SELECT * FROM gpx ORDER BY time DESC')->fetchAll();

		foreach ($gpxs as $r)
		{
			$path = new GpsPath($r->gpx);
			$ascentDescent = $path->getAscentDescent();
			$this->database->query('UPDATE gpx SET ', array(
				'ascent' => $ascentDescent['ascent'],
				'descent' => $ascentDescent['descent'],
				'duration' => $path->getDuration(),
				'length' => $path->getLength(),
				'classification' => Nette\Neon\Neon::encode($path->getClassification()),
				'time' => Nette\Utils\DateTime::from($path->getStartPoint()->time),
				'points' => json_encode($path->getLatLons()),
			), 'WHERE id = ?', $r->id);

		}

		$this->terminate();
	}



	public function handleUpload()
	{

		$file = $this->getHttpRequest()->getFile('upl');

		$path = new GpsPath($file->contents);
		$ascentDescent = $path->getAscentDescent();

		$this->database->query('INSERT INTO gpx ', array(
			'name' => $file->name,
			'gpx' => $file->contents,
			'ascent' => $ascentDescent['ascent'],
			'descent' => $ascentDescent['descent'],
			'duration' => $path->getDuration(),
			'length' => $path->getLength(),
			'classification' => Nette\Neon\Neon::encode($path->getClassification()),
			'time' => Nette\Utils\DateTime::from($path->getStartPoint()->time),
			'points' => json_encode($path->getLatLons()),
		));


	}
}
