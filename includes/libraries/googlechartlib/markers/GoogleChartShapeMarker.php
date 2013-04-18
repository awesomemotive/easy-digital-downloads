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
 * A Shape Marker.
 *
 * This class implement the Shape Marker feature (@c chm)
 *
 * @par Example
 * @include marker_shape.php
 *
 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_shape_markers
 */
class GoogleChartShapeMarker extends GoogleChartMarker
{
	const ARROW = 'a';
	const CROSS = 'c';
	//~ const RECTANGLE = 'C';
	const DIAMOND = 'd';
	//~ const ERROR_BAR = 'E';
	const CIRCLE = 'o';
	const SQUARE = 's';
	const X = 'x';

	protected $shape = null;

	protected $points = null;
	protected $position = null;

	protected $size = '10';

	protected $border = null;

	/**
	 * Constructor.
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_shape_markers
	 * @param $shape You can specify the shape of the marker.
	 */
	public function __construct($shape = self::CIRCLE)
	{
		$this->shape = $shape;
	}

/**
 * @name Position
 * Thoses functions are mutually exclusive (if you call more than one of them,
 * only the last one will be remembered).
 */
//@{
	/**
	 * Set a fixed position for the marker.
	 *
	 * When a marker has a fixed position, you MUST NOT associate it to a data
	 * serie (with setData()). Otherwise, the fixed position will be ignored.
	 *
	 * @param $x (float) between 0 and 1
	 * @param $y (float) between 0 and 1
	 * @return $this
	 */
	public function setFixedPosition($x, $y)
	{
		if ( $x < 0 || $x > 1 || ! is_numeric($x) )
			throw new InvalidArgumentException('Invalid x position (must be between 0 and 1)');
		if ( $y < 0 || $y > 1 || ! is_numeric($y) )
			throw new InvalidArgumentException('Invalid y position (must be between 0 and 1)');

		$this->position = array(
			'x' => $x,
			'y' => $y
		);
		return $this;
	}

	/**
	 * Displays only the selected point.
	 *
	 * @param $point (int) The index of the point to display (0 based) in the data serie
	 * @return $this
	 */
	public function setPoint($point)
	{
		$this->points = $point;
		return $this;
	}

	/**
	 * Displays a range of points (@c start:end:n or @c -n format).
	 *
	 * @return $this
	 */
	public function setPoints($start = null, $end = null, $step = null)
	{
		if ( $this->points['start'] === null && $this->points['end'] === null && $this->points['step'] === null ) {
			$this->points = null;
		}

		$this->points = array(
			'start' => $start,
			'end' => $end,
			'step' => $step
		);
		return $this;
	}

	/**
	 * Displays every nth points (@c -n format).
	 *
	 * @return $this
	 */
	public function setStep($step)
	{
		$this->points = array(
			'start' => null,
			'end' => null,
			'step' => $step
		);
		return $this;
	}
//@}

	/**
	 * Set the size of the shape.
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
	 * Set a border to the shape.
	 *
	 * To create a border to a shape with Google Chart API, you need to create
	 * another similar marker below the first one, with a different color
	 * and a slightly bigger size.
	 *
	 * This function does the job for you. Just specify a color and the size of
	 * the border, and it will create the second marker automatically.
	 *
	 * @since 0.4
	 *
	 * @param $size (int) size of the border (default is 2)
	 * @param $color (string) a color in RRGGBB format (default is white)
	 * @return $this
	 */
	public function setBorder($size = 2, $color = 'ffffff')
	{
		$this->border = array(
			'size' => $size,
			'color' => $color
		);
		return $this;
	}

	/**
	 * @internal
	 */
	public function compute($index, $chart_type = null)
	{
		if ( $index === null ) {
			if ( $this->position === null ) {
				throw new LogicException('Shape marker requires one data serie or requires to have a fixed position.');
			}

			// Fixed position marker (x:y format)
			$str = '@';
			$points = $this->position['x'].':'.$this->position['y'];
		}
		else {
			$str = '';
			// Default = all (-1 format)
			if ( $this->points === null ) {
				$points = '-1';
			}
			// Only one point (n.d format)
			elseif ( ! is_array($this->points) ) {
				$points = number_format($this->points,1);
			}
			// Step only (-n format)
			elseif ( $this->points['start'] === null && $this->points['end'] === null ) {
				$points = '-'.$this->points['step'];
			}
			// Serie (start:end:n)
			else {
				$points = $this->points['start'].':'.$this->points['end'].':'.$this->points['step'];
			}
		}

		if ( $this->border !== null ) {
			$str .= sprintf(
				'%s,%s,%d,%s,%s|',
				$this->shape,
				$this->border['color'],
				$index,
				$points,
				$this->size + $this->border['size']
			);
		}

		$str .= sprintf(
			'%s,%s,%d,%s,%s',
			$this->shape,
			$this->color,
			$index,
			$points,
			$this->size
		);

		if ( $this->z_order !== null ) {
			$str .= ','.$this->z_order;
		}
		return $str;
	}
}

/** @example marker_shape_fixed_position.php
 * An example of a shape marker with fixed position
 */
