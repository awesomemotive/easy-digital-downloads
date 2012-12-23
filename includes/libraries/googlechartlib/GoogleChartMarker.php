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

/**
 * A Marker.
 *
 * This in an abstract class that is used by all the Markers type.
 *
 * Marker implementation in Google Chart API is quite complex. There are many types
 * of markers (value, line, shape, candlestick and range) and each has a
 * different set of parameter and a slightly different logic. So each type has
 * its own class, that extends GoogleChartMarker.
 *
 * To display a marker, you need to set a data serie using setData() function.
 * A data serie is a GoogleChartData object. It contains points used by the
 * marker. You can provides an existing data serie (i.e. a data serie that has been
 * or will be added to the chart with GoogleChart::addData()) or a new data serie.
 * In this case, the data serie will be hidden. Please refer to Google Chart API
 * documentation about compound chart for further information.
 */
abstract class GoogleChartMarker
{
	/**
	 * @var GoogleChartData Will hold the data serie.
	 */
	protected $data = null;

/**
 * @name Common parameters to every markers
 */
//@{
	/**
	 * @var string Color of the marker
	 */
	protected $color = '4D89F9';

	/**
	 * @var float Z-order of the marker
	 */
	protected $z_order = null;
//@}

	/**
	 * Set the color of the marker.
	 *
	 * @param $color (string)
	 */
	public function setColor($color)
	{
		$this->color = $color;
		return $this;
	}

	/**
	 * Return the color.
	 *
	 * @return string
	 */
	public function getColor()
	{
		return $this->color;
	}

	public function setZOrder($z_order)
	{
		if ( $z_order < -1 || $z_order > 1 )
			throw new InvalidArgumentException('Invalid Z-order (must be between -1.0 and 1.0)');

		$this->z_order = $z_order;
		return $this;
	}

	public function getZOrder($z_order)
	{
		return $this->z_order;
	}

	public function setData(GoogleChartData $data)
	{
		$this->data = $data;
		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	abstract public function compute($index, $chart_type = null);
}
