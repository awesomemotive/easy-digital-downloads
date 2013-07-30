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

require_once 'GoogleChartApi.php';
require_once 'GoogleChartData.php';
require_once 'GoogleChartAxis.php';
require_once 'GoogleChartMarker.php';

/**
 * A chart.
 *
 * This class represent a chart. It provides a bunch of setters to customize it.
 * When creating a new chart, you need to specify 3 things:
 * - type of the chart (see http://code.google.com/apis/chart/docs/gallery/chart_gall.html)
 * - width
 * - height
 *
 * Then you need to add data to that chart using GoogleChartData class.
 *
 * Depending on the type of chart, you can also add one or more axis using GoogleChartAxis class.
 *
 * @par Line chart example
 *
 * @include line_chart.php
 *
 * @par Work around for unimplemented features
 *
 * You can override any parameter by setting its value in the class.
 * For example, to following code will override the background:
 *
 * \code
 *   $chart = new GoogleChart('lc', 500, 200);
 *   $chart->chf = 'b,s,cccccc';
 *   var_dump($chart->getQuery());
 * \endcode
 *
 * You can use this method for working with features that are currently
 * not implemented in the library (or buggy).
 */
class GoogleChart extends GoogleChartApi
{
	const AUTOSCALE_OFF = false;
	const AUTOSCALE_VALUES = true;

	const BACKGROUND = 'bg';
	const CHART_AREA = 'c';

	const TEXT = 't';
	const SIMPLE_ENCODING = 's';
	const EXTENDED_ENCODING = 'e';

	/**
	 * Store the type of the chart as string.
	 */
	protected $type = '';

	/**
	 * Width
	 */
	protected $width = '';

	/**
	 * Height
	 */
	protected $height = '';

	/**
	 * List of all data series (GoogleChartData)
	 */
	protected $data = array();

	/**
	 * Data format (text, simple encoding or extended encoding)
	 */
	protected $data_format = self::TEXT;

	/**
	 * Data format have different separator character
	 */
	protected $data_separator = array(
		self::TEXT => '|',
		self::SIMPLE_ENCODING => ',',
		self::EXTENDED_ENCODING => ','
	);

	/**
	 * List of all axes (GoogleChartAxis)
	 */
	protected $axes = array();

	/**
	 * List of all markers (GoogleChartMarker) @c chm parameter
	 */
	protected $markers = array();

	/**
	 * List of dynamic markers (GooglechartIcon). @c chem parameter
	 */
	protected $dynamic_markers = array();

	protected $grid_lines = null;

	protected $chts = false;
	protected $title = null;
	protected $title_color = '000000';
	protected $title_size = '12';

	protected $autoscale = true;
	protected $scale = null;

	protected $legend_position = null;
	protected $legend_label_order = null;
	protected $legend_skip_empty = true;

	protected $fills = null;

	protected $_compute_data_label = false;

	//~ protected $chma = false;
	protected $margin = null;
	protected $legend_size = null;

	/**
	 * Create a new chart.
	 *
	 * @param $type (string)
	 *   Google chart type.
	 * @param $width (int)
	 * @param $height (int)
	 *
	 * @see http://code.google.com/apis/chart/docs/gallery/chart_gall.html
	 */
	public function __construct($type, $width, $height)
	{
		$this->type = $type;
		$this->width = $width;
		$this->height = $height;

		//~ $this->setAutoscale(self::AUTOSCALE_Y_AXIS);
		//~ $this->setQueryMethod(self::POST);
	}

	/**
	 * Set the data format used by the chart.
	 * Default is GoogleChart::TEXT (basic text format).
	 * @since 0.5
	 */
	public function setDataFormat($format)
	{
		if ( $format !== self::TEXT && $format !== self::SIMPLE_ENCODING && $format !== self::EXTENDED_ENCODING ) {
			throw new InvalidArgumentException('Invalid data format');
		}

		$this->data_format = $format;
	}

	/**
	 * Add a data serie to the chart.
	 *
	 * @param $data (GoogleChartData)
	 * @see GoogleChartData
	 */
	public function addData(GoogleChartData $data)
	{
		if ( $data->hasIndex() )
			throw new LogicException('Invalid data serie. This data serie has already been added.');

		$index = array_push($this->data, $data);
		$data->setIndex($index - 1);
		return $this;
	}

	/**
	 * Add a visible axis to the chart.
	 *
	 * @param $axis (GoogleChartAxis)
	 * @see GoogleChartAxis
	 */
	public function addAxis(GoogleChartAxis $axis)
	{
		$this->axes[] = $axis;

		return $this;
	}

	/**
	 * Add a marker to the chart.
	 *
	 * @param $marker (GoogleChartMarker)
	 * @see GoogleChartShapeMarker, GoogleChartTextMarker, GoogleChartLineMarker
	 * @return $this
	 */
	public function addMarker(GoogleChartMarker $marker)
	{
		$this->markers[] = $marker;

		return $this;
	}

	/**
	 * Add a dynamic icon marker to the chart.
	 *
	 * Dynamic icon marker are different than regular marker. Technically, they
	 * are defined using @c chem parameter instead of @c chm for regular marker.
	 *
	 * @param $marker (GoogleChartIcon)
	 * @return $this
	 */
	public function addDynamicMarker(GoogleChartIcon $marker)
	{
		$this->dynamic_markers[] = $marker;

		return $this;
	}

/**
 * @name Scaling
 */
//@{
	/**
	 * Set autoscaling mode.
	 *
	 * Autoscaling is a feature provided by this library. Because Google Chart
	 * default scale is 0:100, most of the time your data will not appears the
	 * way you want. So you need to set a scale for the chart.
	 *
	 * @see http://code.google.com/p/googlechartphplib/wiki/Autoscaling
	 *
	 * @see http://code.google.com/apis/chart/docs/data_formats.html#data_scaling
	 *
	 * @param $autoscale (bool)
	 * @return $this
	 */
	public function setAutoscale($autoscale)
	{
		if ( $autoscale !== true && $autoscale !== false ) {
			throw new InvalidArgumentException('Invalid autoscale mode.');
		}

		$this->autoscale = $autoscale;
		return $this;
	}

	/**
	 * Set a global scale for the chart.
	 * Turns off autoscaling.
	 * @since 0.5
	 * @param $min (int)
	 * @param $max (int)
	 * @return $this
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
	 * Return the scale.
	 * Note that after the chart has been computed, this function will returns
	 * the actual scale computed by the chart.
	 *
	 * @return array
	 */
	public function getScale()
	{
		return $this->scale;
	}

	/**
	 * Compute the @c chds parameter.
	 * @internal
	 */
	public function computeChds()
	{
		if ( $this->scale === null ) {
			throw new LogicException('Cannot compute scale that has not been set');
		}
		return $this->scale['min'].','.$this->scale['max'];
	}
//@}

/**
 * @name Chart title and style (chtt, chts)
 */
//@{
	/**
	 * Set the chart title (@c chtt).
	 *
	 * @param $title (string)
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_chart_title
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Returns chart title setted by setTitle().
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/** @internal
	 * Compute @c chtt parameter (chart title).
	 *
	 * @return string or null if parameter is not needed
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_chart_title
	 */
	public function computeChtt()
	{
		if ( $this->title === null )
			return null;

		return str_replace(array("\r","\n"), array('','|'), $this->title);
	}

	/**
	 * Set the color of the title (@c chts).
	 *
	 * @param $color (string in ) The title color, in RRGGBB hexadecimal format. Default color is black.
	 *
	 * @since 0.4
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_chart_title
	 */
	public function setTitleColor($color)
	{
		$this->chts = true;
		$this->title_color = $color;
		return $this;
	}

	/**
	 * Returns the title color.
	 *
	 * If no title color has been set using setTitleColor(), it will returns
	 * the default title color.
	 *
	 * @since 0.4
	 * @return string in RRGGBB format
	 */
	public function getTitleColor()
	{
		return $this->title_color;
	}

	/**
	 * Set the font size of the title (@c chts).
	 *
	 * @param $size (int) Font size of the title, in points.
	 *
	 * @since 0.4
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_chart_title
	 */
	public function setTitleSize($size)
	{
		$this->chts = true;
		$this->title_size = $size;
		return $this;
	}

	/**
	 * Returns the title size.
	 *
	 * If no title size has been set using setTitleSize(), it will returns the
	 * default title color.
	 *
	 * @since 0.4
	 * @return string
	 */
	public function getTitleSize()
	{
		return $this->title_size;
	}

	/** @internal
	 * Compute @c chts parameter.
	 *
	 * @since 0.4
	 * @return string or null if the parameter is not needed
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_chart_title
	 */
	public function computeChts()
	{
		return $this->title_color.','.$this->title_size;
	}

	/**
	 * @internal
	 * Return true if chts parameter is needed
	 * Little trick here, if no title is set, then chts is not needed, even
	 * if specified
	 * @return bool
	 */
	public function hasChts()
	{
		return $this->chts;
	}

//@}

/**
 * @name Chart Legend Text and Style (@c chdl, @c chdlp, @c chma)
 */
//@{

	/**
	 * Set position of the legend box (@c chdlp).
	 *
	 * The parameter is not checked so you can pass whatever you want. This way,
	 * if the Google Chart API evolves, this library will still works. However,
	 * be warned that you chart may not be displayed as expected if you pass wrong
	 * parameter.
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_legend
	 *
	 * @param $position (string)
	 *   One of the following: 'b', 'bv', 't', 'tv', 'r', 'l'
	 *   (read Google Chart Documentation for details).
	 * @return $this
	 */
	public function setLegendPosition($position)
	{
		$this->legend_position = $position;
		return $this;
	}

	/**
	 * Set labels order inside the legend box (chdlp).
	 *
	 * The parameter is not checked so you can pass whatever you want. This way,
	 * if the Google Chart API evolves, this library will still works. However,
	 * be warned that you chart may not be displayed as expected if you pass wrong
	 * parameter.
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_legend
	 *
	 * @param $label_order (string)
	 *   One of the following: 'l', 'r', 'a', or a list of numbers
	 *   separated by commas (read Google Chart Documentation for details).
	 * @return $this
	 */
	public function setLegendLabelOrder($label_order)
	{
		$this->legend_label_order = $label_order;
		return $this;
	}

	/**
	 * Set if empty legends entries shoud be skipped in the legend or not.
	 *
	 * @param $skip_empty (bool)
	 */
	public function setLegendSkipEmpty($skip_empty)
	{
		$this->legend_skip_empty = (bool) $skip_empty;
		return $this;
	}

	/**
	 * Size of the legend box (@c chma).
	 *
	 * @since 0.5
	 * @param $width (int)
	 * @param $height (int)
	 */
	public function setLegendSize($width, $height)
	{
		$this->legend_size = array(
			'width' => $width,
			'heigh' => $height
		);
		return $this;
	}

	/**
	 * @internal
	 * @since 0.4
	 */
	public function computeChdlp()
	{
		$str = '';
		if ( $this->legend_position !== null ) {
			$str .= $this->legend_position;
		}
		if ( $this->legend_skip_empty === true ) {
			$str .= 's';
		}
		if ( $this->legend_label_order !== null ) {
			$str .= '|'.$this->legend_label_order;
		}
		return $str;
	}

	/**
	 * @internal
	 * @since 0.4
	 */
	public function hasChdlp()
	{
		return $this->legend_skip_empty === true || $this->legend_position !== null || $this->legend_label_order !== null;
	}
//@}

	/**
	 * Specify solid or dotted grid lines on the chart. (@c chg)
	 *
	 * @param $x_axis_step_size
	 * @param $y_axis_step_size
	 * @param $dash_length
	 * @param $space_length
	 * @param $x_offset
	 * @param $y_offset
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_grid_lines
	 */
	public function setGridLines($x_axis_step_size, $y_axis_step_size, $dash_length = false,
	                             $space_length = false, $x_offset = false, $y_offset = false)
	{
		$this->grid_lines = $x_axis_step_size.','.$y_axis_step_size;
		if ( $dash_length !== false ) {
			$this->grid_lines .= ','.$dash_length;
			if ( $space_length !== false ) {
				$this->grid_lines .= ','.$space_length;
				if ( $x_offset !== false ) {
					$this->grid_lines .= ','.$x_offset;
					if ( $y_offset !== false ) {
						$this->grid_lines .= ','.$y_offset;
					}
				}
			}
		}
		return $this;
	}

/**
 * @name Gradient, Solid and Stripped Fills (chf)
 */
//@{
	/**
	 * Set a solid background (fill) for an area (@c chf).
	 *
	 * @param $color (string) RGB color
	 * @param $area One of the following:
	 * - GoogleChart::BACKGROUND for the whole background
	 * - GoogleChart::CHART_AREA for only the chart area background
	 * @return $this
	 */
	public function setFill($color, $area = self::BACKGROUND)
	{
		if ( $area != self::BACKGROUND && $area != self::CHART_AREA ) {
			throw new InvalidArgumentException('Invalid fill area.');
		}

		$this->fills[$area] = $area.',s,'.$color;
		return $this;
	}

	/**
	 * Set the opacity for solid background (fill).
	 *
	 * @param $opacity (int) Between 0 (transparent) and 100 (opaque)
	 * @return $this
	 */
	public function setOpacity($opacity)
	{
		if ( $opacity < 0 || $opacity > 100 ) {
			throw new InvalidArgumentException('Invalid opacity (must be between 0 and 100).');
		}

		// 100% = 255
		$opacity = str_pad(dechex(round($opacity * 255 / 100)), 8, 0, STR_PAD_LEFT);

		// Opacity doesn't work with other backgrounds
		$this->fills[self::BACKGROUND] = 'a,s,'.$opacity;

		return $this;
	}

	/**
	 * Gradient fill.
	 *
	 * @param $angle (int)
	 *  A number specifying the angle of the gradient
	 *  from 0 (horizontal) to 90 (vertical).
	 * @param $colors (array)
	 *  An array of color of the fill. Each color can be a
	 *  string in RRGGBB hexadecimal format, or an array of two values: RRGGBB
	 *  color, and color centerpoint.
	 *
	 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_gradient_fills
	 */
	public function setGradientFill($angle, array $colors, $area = self::BACKGROUND)
	{
		if ( $angle < 0 || $angle > 90 ) {
			throw new InvalidArgumentException('Invalid angle (must be between 0 and 90).');
		}

		if ( ! isset($colors[1]) ) {
			throw new InvalidArgumentException('You must specify at least 2 colors to create a gradient fill.');
		}

		if ( $area != self::BACKGROUND && $area != self::CHART_AREA ) {
			throw new InvalidArgumentException('Invalid area.');
		}

		$tmp = array();
		$i = 0;
		$n = sizeof($colors);
		for ( $i = 0; $i < $n; $i++ ) {
			$centerpoint = null;
			$color = null;

			if ( is_array($colors[$i]) ) {
				$c = $colors[$i];
				if ( ! isset($c[0]) ) {
					throw new InvalidArgumentException('Each color must be an array of the color code in RRGGBB and the color centerpoint.');
				}
				$color = $c[0];
				if ( isset($c[1]) ) {
					$centerpoint = $c[1];
				}
			}
			else {
				$color = $colors[$i];
			}
			// No color centerpoint, try to calculate a good one:
			if ( ! $centerpoint ) {
				$centerpoint = $i / ($n-1);
			}
			$tmp[] = $color.','.round($centerpoint,2);
		}

		$this->fills[$area] = $area.',lg,'.$angle.','.implode(',',$tmp);
	}

	/**
	 * Striped fill.
	 * @todo
	 */
	public function setStripedFill($angle, array $colors, $area = self::BACKGROUND)
	{

	}
//@}

/**
 * @name Chart Margins
 */
//@{

	/**
	 * Set margin around the charts (@c chma).
	 *
	 * This function works like the CSS property "margin" :
	 * - If you specify only one parameter, then this value is used for all.
	 * - If you specify 2 parameters, then first is "top/bottom" and second is "left/right"
	 * - If you specify 4 parameters, then they are: top, right, bottom, left (tips: it's clockwise).
	 *
	 * @since 0.5
	 *
	 * @param $top (float)
	 * @param $right (float)
	 * @param $bottom (float)
	 * @param $left (float)
	 * @return $this
	 */
	public function setMargin($top, $right = null, $bottom = null, $left = null)
	{
		// If only one value, then all have the same values
		if ( $left === null && $right === null && $bottom === null ) {
			$this->margin = array(
				'left' => (float) $top,
				'right' => (float) $top,
				'top' => (float) $top,
				'bottom' => (float) $top
			);
		}
		elseif ( $left === null && $bottom === null ) {
			$this->margin = array(
				'left' => (float) $right,
				'right' => (float) $right,
				'top' => (float) $top,
				'bottom' => (float) $top
			);
		}
		else {
			$this->margin = array(
				'left' => (float) $left,
				'right' => (float) $right,
				'top' => (float) $top,
				'bottom' => (float) $bottom
			);
		}
		return $this;
	}

	/**
	 * @internal
	 */
	public function computeChma()
	{
		$str = '';
		if ( $this->margin ) {
			$str = implode(',',$this->margin);
		}
		if ( $this->legend_size ) {
			$str .= '|'.implode(',',$this->legend_size);
		}
		return $str;
	}

	/**
	 * @internal
	 */
	public function hasChma()
	{
		return $this->margin !== null || $this->legend_size !== null;
	}

//@}

/**
 * @name URL creation
 */
//@{

	/**
	 * Compute the whole query as an array.
	 * @internal
	 * Shouldn't be overrided, but who knows?
	 */
	protected function computeQuery()
	{
		$q = array(
			'cht' => $this->type,
			'chs' => $this->width.'x'.$this->height
		);

		$this->compute($q);

		$q = array_merge($q, $this->parameters);

		return $q;
	}

	/**
	 * Compute the whole query as an array.
	 * @internal
	 * To be overrided by child classes.
	 */
	protected function compute(array & $q)
	{
		if ( $this->grid_lines ) {
			$q['chg'] = $this->grid_lines;
		}
		if ( $this->fills ) {
			$q['chf'] = implode('|',$this->fills);
		}

		if ( $this->hasChma() ) {
			$q['chma'] = $this->computeChma();
		}
		$this->computeTitle($q);

		$this->computeScale($q);
		$this->computeData($q);
		$this->computeMarkers($q);
		$this->computeAxes($q);
	}

	/**
	 * Compute title related parameters (chtt and chts)
	 * @internal
	 */
	protected function computeTitle(array & $q)
	{
		if ( $this->title ) {
			$q['chtt'] = $this->computeChtt();

			if ( $this->hasChts() ) {
				$q['chts'] = $this->computeChts();
			}
		}
	}

	/**
	 * @internal
	 * @since 0.5
	 */
	protected function computeScale(array & $q)
	{
		if ( ! $this->autoscale )
			return $this;

		$value_min = 0;
		$value_max = 0;

		foreach ( $this->data as $i => $d ) {
			$values = $d->getValues();
			if ( $values === null || empty($values) )
				continue;

			$max = max($values);
			$min = min($values);
			if ( $max > $value_max ) {
				$value_max = $max;
			}
			if ( $min < $value_min ) {
				$value_min = $min;
			}
		}

		if ( $value_min > 0 )
			$value_min = 0;

		$this->scale = array('min' => $value_min, 'max' => $value_max);
		return $this;
	}

	/**
	 * Compute data series.
	 *
	 * @note This function is too long. I think it needs a redesign, but for the
	 * moment I have no idea how to make it shorter.
	 *
	 * @internal
	 */
	protected function computeData(array & $q)
	{
		$data = array();

		$colors = array();
		$colors_needed = false;

		$styles = array();
		$styles_needed = false;

		$fills = array();

		$scales = array();
		$scale_needed = false;

		$legends = array();
		$legends_needed = false;

		if ( $this->_compute_data_label ) {
			$labels = array();
		}

		foreach ( $this->data as $i => $d ) {
			// Data serie values and scale
			if ( $d->hasValues() ) {
				$data[] = $d->computeChd($this->data_format, $this->scale);
				// Compute per-data scale only if autoscale if off
				if ( ! $this->autoscale && ! $this->scale ) {
					$scales[] = $d->computeChds();
					if ( $d->hasCustomScale() ) {
						$scale_needed = true;
					}
				}
			}

			// Data serie color (chco)
			$colors[] = $d->computeChco();
			if ( $colors_needed == false && $d->hasChco() ) {
				$colors_needed = true;
			}

			// Data serie style (chls)
			$styles[] = $d->computeChls();
			if ( $styles_needed == false && $d->hasChls() ) {
				$styles_needed = true;
			}

			$tmp = $d->computeChm($i);
			if ( $tmp ) {
				$fills[] = $tmp;
			}

			$legends[] = $d->getLegend();
			if ( $legends_needed == false && $d->hasCustomLegend() ) {
				$legends_needed = true;
			}

			if ( $this->_compute_data_label ) {
				$labels[] = $d->computeChl();
			}
		}
		if ( ! isset($data[0]) )
			return;

		$q['chd'] = $this->data_format.':'.implode($this->data_separator[$this->data_format],$data);

		if ( $colors_needed ) {
			$q['chco'] = implode(',',$colors);
		}
		if ( $styles_needed ) {
			$q['chls'] = implode('|',$styles);
		}

		if ( $this->_compute_data_label ) {
			$tmp = rtrim(implode('|',$labels),'|');
			if ( $tmp ) {
				$q['chl'] = $tmp;
			}
		}

		if ( $this->scale ) {
			$q['chds'] = $this->computeChds();
		}
		elseif ( $scale_needed && isset($scales[0]) ) {
			$q['chds'] = implode(',', $scales);
		}

		// Legends
		if ( $legends_needed ) {
			$q['chdl'] = implode('|',$legends);
			if ( $this->hasChdlp() ) {
				$q['chdlp'] = $this->computeChdlp();
			}
		}

		if ( isset($fills[0]) )
			$q['chm'] = implode('|',$fills);

		return $this;
	}


	/**
	 * Compute the markers.
	 * @internal
	 * This function loops through the lists of the markers.
	 */
	protected function computeMarkers(array & $q)
	{
		$markers = array();
		$dynamic_markers = array();
		$additional_data = array();

		$nb_data_series = sizeof($this->data);
		$current_index = $nb_data_series;

		$array = $this->markers + $this->dynamic_markers;
		foreach ( $array as $m ) {
			$data = $m->getData();

			$index = null;
			if ( $data ) {
				// Get the data serie index
				$index = $data->getIndex();
				if ( $index === null ) {
					$additional_data[] = $data->computeChd($this->data_format);
					$index = $current_index;
					$current_index += 1;
				}
			}

			// Now $index contains the correct data serie index
			$tmp = $m->compute($index, $this->type);
			if ( $tmp === null )
				continue; // Ignore empty markers

			if ( $m instanceof GoogleChartMarker ) {
				$markers[] = $tmp;
			}
			else {
				$dynamic_markers[] = $tmp;
			}
		}

		if ( isset($markers[0]) ) {
			$q['chm'] = (isset($q['chm']) ? $q['chm'].'|' : '').implode('|',$markers);
		}

		if ( isset($dynamic_markers[0]) ) {
			$q['chem'] = implode('|',$dynamic_markers);
		}

		// Append every additional_data to 'chd'
		if ( isset($additional_data[0]) ) {
			$q['chd'] = $this->data_format.$nb_data_series.substr($q['chd'],1).$this->data_separator[$this->data_format].implode($this->data_separator[$this->data_format],$additional_data);
		}
	}

	/**
	 * Compute axes.
	 * @internal
	 */
	protected function computeAxes(array & $q)
	{
		$axes = array();
		$labels = array();
		$ranges = array();
		$tick_marks = array();
		$styles = array();
		$label_positions = array();
		foreach ( $this->axes as $i => $a ) {
			$axes[] = $a->getName();
			if ( $a->hasCustomLabels() ) {
				$labels[] = sprintf($a->getLabels(), $i);
			}

			$tmp = $a->getRange();
			if ( $tmp !== null ) {
				$ranges[] = sprintf($tmp, $i);
			}

			$tmp = $a->getTickMarks();
			if ( $tmp !== null ) {
				$tick_marks[] = sprintf($tmp, $i);
			}

			$tmp = $a->computeChxs($i, $this->type);
			if ( $tmp !== null ) {
				$styles[] = $tmp;
			}

			if ( $a->hasChxp() ) {
				$label_positions[] = $a->computeChxp($i);
			}
		}
		if ( isset($axes[0]) ) {
			$q['chxt'] = implode(',',$axes);
			if ( isset($labels[0]) ) {
				$q['chxl'] = implode('|',$labels);
			}
			if ( isset($ranges[0]) ) {
				$q['chxr'] = implode('|', $ranges);
			}
			if ( isset($tick_marks[0]) ) {
				$q['chxtc'] = implode('|', $tick_marks);
			}
			if ( isset($styles[0]) ) {
				$q['chxs'] = implode('|', $styles);
			}
			if ( isset($label_positions[0]) ) {
				$q['chxp'] = implode('|',$label_positions);
			}
		}

		return $this;
	}

//@}


}

/** @example line_chart.php
 * A basic example of how to work with line chart.
 */
/** @example line_chart_sin_cos.php
 * Another line chart example, with multiple data series.
 */
/**
 * @example line_chart_full.php
 * Another line chart example with plenty of options enabled.
 */
