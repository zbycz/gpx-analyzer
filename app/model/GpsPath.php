<?php

namespace App\Model;

use Nette\Object;
use Nette\Utils\DateTime;


class GpsPath extends Object
{

	public $points = array();

	public function __construct($gpx)
	{
		$xml = new \SimpleXMLElement($gpx);

		foreach($xml->trk->trkseg as $trkseg)
		foreach ($trkseg->trkpt as $trkpt)
		{
			$point = (object)array(
				'lat' => (double)$trkpt['lat'],
				'lon' => (double)$trkpt['lon'],
				'ele' => (float)$trkpt->ele->__toString(),
				'time' => strtotime($trkpt->time->__toString()),
			);
			$this->points[] = $point;
		}

	}


	public function getAscentDescent()
	{
		$ascent = $descent = 0.0;
		$prev = NULL;
		foreach ($this->points as $r)
		{
			if (!isset($prev))
				$prev = $r;

			$diff = $r->ele - $prev->ele;
			if ($diff > 0)
				$ascent += $diff;
			else
				$descent += -1 * $diff;

			$prev = $r;
		}

		return array("ascent" => $ascent, "descent" => $descent);
	}

	public function getStartPoint()
	{
		return !empty($this->points) ? $this->points[0] : NULL;
	}

	public function getEndPoint()
	{
		return !empty($this->points) ? end($this->points) : NULL;
	}

	public function getPoints()
	{
		return $this->points;
	}

	public function getDuration()
	{
		return $this->endPoint->time - $this->startPoint->time;
	}

	public function getLength()
	{
		$distance = 0.0;
		$prev = NULL;
		foreach ($this->points as $r)
		{
			if (!isset($prev))
				$prev = $r;

			$distance += self::getTwoPointsDistance($prev->lat, $prev->lon, $r->lat, $r->lon);

			$prev = $r;
		}

		return $distance;

	}

	public static function getTwoPointsDistance($lat1, $lon1, $lat2, $lon2)
	{
		$lat1 = deg2rad($lat1);
		$lon1 = deg2rad($lon1);

		$lat2 = deg2rad($lat2);
		$lon2 = deg2rad($lon2);

		$dlon = $lon2 - $lon1;
		$dlat = $lat2 - $lat1;

		// Haversine Formula
		$sinlat = sin($dlat / 2);
		$sinlon = sin($dlon / 2);
		$a = ($sinlat * $sinlat) + cos($lat1) * cos($lat2) * ($sinlon * $sinlon);
		$c = 2 * asin(min(1, sqrt($a)));

		$earth = 6371.0 * 1000;
		return $earth * $c;
	}


	public function getClassification()
	{

		$classification = array(
			'car' => array(
				'treshold' => 40,
				'duration' => 0,
				'length' => 0,
			),
			'bike' => array(
				'treshold' => 8,
				'duration' => 0,
				'length' => 0,
			),
			'walking' => array(
				'treshold' => 2,
				'duration' => 0,
				'length' => 0,
			),
		);

		$prev = NULL;
		$bufferDist = 0.0;
		$bufferDt = 0.0;
		//$bufferMaxRadius = 0.0;
		foreach($this->points as $point)
		{
			if (empty($prev))
			{
				$prev = $point;
				continue;
			}


			// spočítáme vzdálenost, čas
			$distance = self::getTwoPointsDistance($prev->lat, $prev->lon, $point->lat, $point->lon);
			$dt = $point->time - $prev->time;

			//měříme si největší odchylku od prvního bodu v segmentu
			//$bufferMaxRadius = max($bufferMaxRadius, $distance);

			//zvětšíme délku bufferu
			$bufferDist += $distance;
			$bufferDt += $dt;

			// plníme buffer než bude segment delší než X vteřin
			if ($bufferDt < 60)
			{
				$prev = $point;
				continue;
			}

			// tohle je očividně pauza v měření - tenhle segment vyhodíme, tj.nebudu měřit rychlost
			if ($bufferDt > 5 * 60)
			{
				$bufferDist = 0.0;
				$bufferDt = 0.0;
				$prev = $point;
				continue;
			}

			// celý buffer se pohyboval na okruhu menším než X metrů = GPS na místě (potažmo v místnosti)
			//if ($bufferMaxRadius < 20)

			// určíme rychlost ke klasifikaci
			$speed = $bufferDt > 0 ? ($bufferDist / $bufferDt * 3.6) : 99999999;


			// tenhle segment klasifikuju
			foreach($classification as &$r)
			{
				if ($speed > $r['treshold'])
				{
					$r['duration'] += $bufferDt;
					$r['length'] += $bufferDist;
					break;
				}
			}

			$bufferDist = 0.0;
			$bufferDt = 0.0;
			$prev = $point;
		}

		$max = 0;
		$result = false;
		foreach($classification as $key=>$x){
			if ($x['length'] > $max){
				$max = $x['length'];
				$result = $key;
			}
		}

		return array('result' => $result) + $classification;
	}


	public function getLatLons()
	{
		$latlons = array();
		foreach($this->points as $i => $r)
		{
			if($i % 20)
				$latlons[] = [$r->lat, $r->lon];
		}
		return $latlons;
	}
}

