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
 * A data serie.
 *
 * This class implement every feature that is directly related to a data serie
 * or its representation in the chart.
 *
 * Some method won't work for all charts, but won't produce an error.
 */
class GoogleChartData
{
	/**
	 * An array of the values of the data serie.
	 */
	protected $values = null;
	/**
	 *  The name of the data serie to be displayed as legend.
	 */
	protected $legend = null;
	/**
	 * The label of the values of the data serie. Pie Chart only.
	 */
	protected $labels = null;

	/**
	 * Indicate if the color has been overridden.
	 * This variable is used to minimize the request. If no custom color has
	 * been providen, then the @c cho parameter is not triggered.
	 */
	protected $chco = false;
	/**
	 * Color of the data serie (string or array)
	 * Default color by Google Chart API is ffcc33
	 */
	protected $color = 'ffcc33';

	/**
	 * Indicate if @c chls parameter is needed
	 */
	protected $chls = false;

	/**
	 * Thickness of the line. Line Chart only. (@c chls)
	 */
	protected $thickness = 2;

	/**
	 * Length of the dash. Line Chart only. (@c chls)
	 */
	protected $dash_length = null;

	/**
	 * Length of the spaces between dashes. Line Chart only. (@c chls)
	 */
	protected $space_length = null;

	/**
	 *  Line fill values (to fill area below a line). (@c chm)
	 */
	protected $fill = null;

	protected $fill_slices = array();

	/**
	 *  bool Whether to calculate scale automatically or not.
	 */
	protected $autoscale = true;
	/**
	 *  array The scale, as specified by the user with setScale
	 */
	protected $scale = null;

	/**
	 *  int Holds the index of the data serie in the chart. Null if not added.
	 */
	protected $index = null;

	/**
	 * Create a new data serie.
	 */
	public function __construct($values)
	{
		if ( $values !== null && ! is_array($values) )
			throw new InvalidArgumentException('Invalid values (must be an array or null)');

		$this->values = $values;
	}

	/**
	 * Returns the values of this dataserie
	 * @return array (or null)
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @since 0.5
	 */
	public function hasValues()
	{
		return $this->values !== null && ! empty($this->values);
	}

	/**
	 * @since 0.5
	 */
	public function computeChd($encoding = GoogleChart::TEXT, $scale = null)
	{
		// If scale is null, it means that there is not "global" scale for the chart
		// Hence we need to determine the scale for this data only
		if ( $scale === null ) {
			$scale = $this->getScale();
		}

		switch ( $encoding ) {
			case GoogleChart::TEXT :
				return self::encodeText($this->values, $scale['min'], $scale['max']);
			case GoogleChart::SIMPLE_ENCODING :
				return self::encodeSimple($this->values, $scale['min'], $scale['max']);
			case GoogleChart::EXTENDED_ENCODING :
				return self::encodeExtended($this->values, $scale['min'], $scale['max']);
			default:
				throw new InvalidArgumentException('Invalid encoding format');
		}
	}


/**
 * @name Pie Chart Labels @c chl
 */
//@{
	/**
	 * @since 0.5
	 */
	public function setLabelsAuto()
	{
		return $this->setLabels(array_keys($this->values));
	}

	/**
	 * @since 0.5
	 */
	public function setLabels($labels)
	{
		$n = sizeof($labels);
		$v = sizeof($this->values);
		if ( $n > $v ) {
			throw new InvalidArgumentException('Invalid labels, to many labels');
		}
		elseif ( $n < $v ) {
			$labels += array_fill(0, $v-$n, '');
		}

		$this->labels = $labels;
		return $this;
	}

	/**
	 * Return labels set by setLabels()
	 * @return array();
	 */
	public function getLabels()
	{
		return $this->labels;
	}

	/**
	 * Compute @c chl parameter.
	 *
	 * Only for Pie Chart.
	 *
	 * If the chart has no label, this function returns a string containing
	 * an empty label for each value (example "|" for 2 values, "||" for 3, etc.).
	 * This way, labels are always in sync with the values. The case happens
	 * with a concentric chart, if the inner chart (first data serie) doesn't
	 * have label, but the outer chart (second data serie) has.
	 *
	 * @internal
	 * @since 0.5
	 */
	public function computeChl()
	{
		if ( ! $this->values )
			return '';

		if ( $this->labels === null ) {
			return str_repeat('|',sizeof($this->values)-1);
		}
		return implode('|',$this->labels);
	}
//@}

	/**
	 * Set the index of the data serie in the chart.
	 *
	 * @internal
	 * @note Used by GoogleChart when calling GoogleChart::addData()
	 * @param $index (int)
	 * @return $this
	 */
	public function setIndex($index)
	{
		if ( ! is_int($index) )
			throw new InvalidArgumentException('Invalid index (must be an integer)');

		$this->index = (int) $index;
		return $this;
	}

	/**
	 * Return the index of the data serie in the chart (null if not in a chart).
	 *
	 * @return int or null
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * Returns true if the data serie has an index, false otherwise.
	 *
	 * @return bool
	 */
	public function hasIndex()
	{
		return $this->index !== null;
	}

	/**
	 * Enable/disabled autoscaling.
	 * @param $autoscale (bool)
	 * @return $this
	 */
	public function setAutoscale($autoscale)
	{
		$this->autoscale = $autoscale;
		return $this;
	}

	/**
	 * Set the scale of this data serie.
	 * When using this function, be sure your turned off global autoscaling.
	 * @see http://code.google.com/p/googlechartphplib/wiki/Autoscaling
	 * @param $min (int)
	 * @param $max (int)
	 */
	public function setScale($min, $max)
	{
		$this->setAutoscale(false);
		$this->scale = array(
			'min' => $min,
			'max' => $max
		);
		return $this;
	}

	/**
	 * @since 0.5
	 */
	public function getScale()
	{
		if ( $this->autoscale == true ) {
			if ( ! empty($this->values) ) {
				$n = min($this->values);
				if ( $n > 0 )
					$n = 0;
				return array('min' => $n, 'max' => max($this->values));
			}
		}

		if ( $this->scale === null ) {
			return array('min' => 0, 'max' => 100);
		}

		return $this->scale;
	}

	/**
	 * @since 0.5
	 */
	public function computeChds()
	{
		$scale = $this->getScale();
		return $scale['min'].','.$scale['max'];
	}

	/**
	 * @since 0.5
	 */
	public function hasCustomScale()
	{
		return $this->scale !== null || $this->autoscale;
	}

	/**
	 * Chart Legend (chdl)
	 *
	 * @param $legend (string)
	 */
	public function setLegend($legend)
	{
		$this->legend = $legend;
		return $this;
	}

	/**
	 * Return the legend.
	 * @return string
	 */
	public function getLegend()
	{
		return $this->legend;
	}

	/**
	 * Return true if a legend has been set
	 * @return bool
	 */
	public function hasCustomLegend()
	{
		return $this->legend !== null;
	}

/**
 * @name Data Serie Color (@c chco).
 */
//@{
	/**
	 * Set the serie color.
	 * Color can be an array for bar charts and pie charts.
	 *
	 * @param $color (mixed) a RRGGBB string, or an array for Bar Chart and Pie Chart
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_series_color
	 */
	public function setColor($color)
	{
		$this->chco = true;
		$this->color = $color;
		return $this;
	}

	/**
	 * Return the serie colors.
	 * @return color
	 */
	public function getColor()
	{
		return $this->color;
	}

	/**
	 * Compute the @c cho parameter.
	 * @internal
	 * @return string
	 */
	public function computeChco()
	{
		if ( is_array($this->color) )
			return implode('|',$this->color);

		return $this->color;
	}

	/**
	 * Return true if parameter @chco is needed
	 * @return true
	 */
	public function hasChco()
	{
		return $this->chco;
	}
//@}

/**
 * @name Line fill (chm). Line and radar charts only.
 */
//@{
	/**
	 * Line fill
	 *
	 * @param $color (string) RRGGBB color. Supports transparency if you uses
	 * RRGGBBAA format.
	 *
	 * @param $end_line (int) On a multi-line chart, if you want to fill only
	 * between two lines, you can specify the index of the line at which to stop
	 * the filling.
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_line_fills
	 */
	public function setFill($color, $end_line = null)
	{
		$this->fill = array(
			'color' => $color,
			'end_line' => $end_line
		);
	}

	/**
	 * @since 0.5
	 */
	public function addFillSlice($color, $start = null, $stop = null)
	{
		if ( $start !== null && ! is_numeric($start) ) {
			throw new InvalidArgumentException('Invalid start index (must be NULL or numeric)');
		}
		if ( $stop !== null && ! is_numeric($stop) ) {
			throw new InvalidArgumentException('Invalid stop index (must be NULL or numeric)');
		}

		$this->fill_slices[] = array(
			'color' => $color,
			'start' => $start === null ? null : intval($start),
			'stop' => $stop === null ? null : intval($stop)
		);
	}

	/**
	 * @todo Move to compute*
	 */
	public function computeChm($index)
	{
		if ( $this->fill === null && ! isset($this->fill_slices[0]) )
			return null;

		$fill = array();

		if ( $this->fill !== null ) {
			$fill[] = sprintf(
				'%s,%s,%d,%d,0',
				$this->fill['end_line'] === null ? 'B' : 'b',
				$this->fill['color'],
				$index,
				$this->fill['end_line']
			);
		}

		if ( isset($this->fill_slices[0]) ) {
			foreach ( $this->fill_slices as $f ) {
				$fill[] = sprintf(
					'B,%s,%d,%s:%s,0',
					$f['color'],
					$index,
					$f['start'],
					$f['stop']
				);
			}
		}
		return implode('|',$fill);
	}
//@}


/**
 * @name Line styles (chls).
 */
// @{

	/**
	 * Set the thickness of the line (Line Chart only).
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_line_styles
	 * @since 0.5
	 */
	public function setThickness($thickness)
	{
		$this->chls = true;

		$this->thickness = $thickness;
		return $this;
	}

	/**
	 * @since 0.5
	 */
	public function getThickness()
	{
		return $this->thickness;
	}

	/**
	 * @since 0.5
	 */
	public function setDash($dash_length, $space_length = null)
	{
		$this->chls = true;

		$this->dash_length = $dash_length;
		$this->space_length = $space_length;
		return $this;
	}

	/**
	 * @internal
	 * @since 0.5
	 */
	public function computeChls()
	{
		$str = $this->thickness;
		if ( $this->dash_length !== null ) {
			$str .= ','.$this->dash_length;
			if  ( $this->space_length !== null ) {
				$str .= ','.$this->space_length;
			}
		}
		return $str;
	}

	/**
	 * @internal
	 * @since 0.5
	 */
	public function hasChls()
	{
		return $this->chls;
	}
//@}

	/**
	 * @internal
	 * @since 0.5
	 */
	static public function encodeText(array $values)
	{
		foreach ( $values as & $v ) {
			if ( $v === null ) {
				$v = '_';
			}
			else {
				// We can't rely on PHP's default display for float values, as
				// Float are actually displayed differently depending on the
				// Current locale.
				$v = number_format($v, 2, '.', '');
			}
		}
		return implode(',',$values);
	}

	/**
	 * @internal
	 * @since 0.5
	 */
	static public function encodeSimple(array $values, $min = null, $max = null)
	{
		if ( $min === null ) {
			$min = min($values);
			// By default, we only want a min if there is negative values
			if ( $min > 0 ) {
				$min = 0;
			}
		}
		if ( $max === null ) {
			$max = max($values);
		}
		$max = $max + abs($min);

		$map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$str = '';

		foreach ( $values as $v ) {
			if ( $v === null ) {
				$str .= '_';
				continue;
			}

			$n = round(61 * (($v - $min) / $max));
			if ( $n > 61 ) {
				$str .= '9';
			}
			elseif ( $n < 0 ) {
				$str .= '_';
			}
			else {
				$str .= $map[$n];
			}
		}
		return $str;
	}

	/**
	 * @internal
	 * @since 0.5
	 */
	static public function encodeExtended(array $values, $min = null, $max = null)
	{
		if ( $min === null ) {
			$min = min($values);
			// By default, we only want a min if there is negative values
			if ( $min > 0 ) {
				$min = 0;
			}
		}
		if ( $max === null ) {
			$max = max($values);
		}
		$max = $max + abs($min);
		if ( $max == 0 )
			return '';

		$map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
		$str = '';

		foreach ( $values as $v ) {
			if ( $v === null ) {
				$str .= '__';
				continue;
			}

			$n = floor(64 * 64 * (($v - $min) / $max));
			if ( $n > (64*64 - 1) ) {
				$str .= '..';
			}
			elseif ( $n < 0 ) {
				$str .= '__';
			}
			else {
				$q = floor($n / 64);
				$r = $n - 64 * $q;
				$str .= $map[$q].$map[$r];
			}
		}
		return $str;
	}

	/**
	* linear regression function
	*
	* @param $data array Points to calculate
	* @returns array() m=>slope, b=>intercept
	*/
	static public function calculateLinearRegression($data)
	{
		// Calculate number points
		$n = count($data);

		$x = array_keys($data);
		$y = array_values($data);

		// Calculate sums
		$x_sum = array_sum($x);
		$y_sum = array_sum($y);

		$xx_sum = 0;
		$xy_sum = 0;

		for($i = 0; $i < $n; $i++) {

			$xy_sum+=($x[$i]*$y[$i]);
			$xx_sum+=($x[$i]*$x[$i]);

		}

		// Calculate slope
		$m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

		// Calculate intercept
		$b = ($y_sum - ($m * $x_sum)) / $n;

		// Return result
		return array($m, $b);
	}

	/**
	 * Function that creates a new GoogleChartData element with trend points based
	 * on the current values.
	 *
	 * @return GoogleChartData Trend data
	 */
	public function createTrendData()
	{
		if(!$this->hasValues())
			return null;

		list($slope, $intercept) = self::calculateLinearRegression(array_values($this->values));

		$n = sizeof($this->values);
		$array = array();
		$v = $intercept;

		for ( $i = 1; $i <= $n; $i++ ) {
			$v += $slope;
			$array[] = round($v,2);
		}

		return new self($array);
	}

	/**
	 * Function that returns a LineMarker that indicates the trend of the contained
	 * data.
	 *
	 * @return GoogleChartLineMarker Trend line
	 */
	public function createTrendMarker()
	{
		if(!$this->hasValues())
			return null;

		$marker = new GoogleChartLineMarker();
		$marker->setData($this->createTrendData());

		return $marker;
	}
}
