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

/**
 * A dynamic icon.
 */
abstract class GoogleChartIcon extends GoogleChartApi
{
	/**
	 * @var GoogleChartData Will hold the data serie.
	 */
	protected $data = null;

/**
 * @name Freestanding icon functions
 */
//@{
	/**
	 * @internal
	 */
	protected function computeQuery()
	{
		$q = array();

		$q['chld'] = $this->computeChld();
		$q['chst'] = $this->computeChst();

		$q = array_merge($q, $this->parameters);

		return $q;
	}
//@}

/**
 * @name Icon as dynamic marker functions.
 */
//@{

	public function setData(GoogleChartData $data)
	{
		$this->data = $data;
		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	/**
	 * @internal
	 */
	public function compute($index = 0, $chart_type = null)
	{
		$str = 'y;';

		// Remove the "d_" for "s" parameter
		$tmp = $this->computeChst();
		if ( $tmp[0] == 'd' && $tmp[1] == '_' )
			$tmp = substr($tmp,2);
		$str .= 's='.$tmp;


		// Escape the "d" parameter
		$str .= ';d='.$this->computeChld(',',',','@');

		$str .= ';ds='.$index;

		return $str;
	}
//@}
}
