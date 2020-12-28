<?php

/** @file
 * This file is part of Google Chart PHP library.
 *
 * Copyright (c) 2010 Rémi Lanvin <remi@cloudconnected.fr>
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 */

require_once dirname(__FILE__).'/../GoogleChartMarker.php';

/**
 * A Line marker.
 *
 * This class implement Line Markers feature (@c chm=D).
 *
 * @par Example
 * @include marker_line.php
 *
 * @see GoogleChartMarker
 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_line_markers
 */
class GoogleChartLineMarker extends GoogleChartMarker
{
	/**
	 * @var string size parameter.
	 */
	protected $size = '2';

	/**
	 * @var array which_points parameter.
	 */
	protected $points = null;

	/**
	 * Set the size of the line.
	 *
	 * @param $size (int)
	 * @return $this
	 */
	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * Set which points in the serie are used to draw the line.
	 *
	 * @param $start (int) (optional) at which point start
	 * @param $stop (int) (optional) at which point stop
	 * @return $this
	 */
	public function setPoints($start = null, $stop = null)
	{
		if ( $start === null && $stop === null ) {
			$this->points = null;
		}
		else {
			$this->points = array(
				'start' => $start,
				'stop' => $stop
			);
		}
		return $this;
	}

	/**
	 * Compute the parameter value.
	 *
	 * @note For internal use only.
	 * @param $index (int) index of the data serie.
	 * @return string
	 */
	public function compute($index, $chart_type = null)
	{
		if ( $index === null )
			throw new LogicException('Line marker requires one data serie.');

		$points = 0;
		if ( is_array($this->points) ) {
			$points = $this->points['start'].':'.$this->points['stop'];
		}

		$str = sprintf(
			'D,%s,%d,%s,%d',
			$this->color,
			$index,
			$points,
			$this->size
		);

		if ( $this->z_order !== null )
			$str .= ','.$this->z_order;

		return $str;
	}
}
