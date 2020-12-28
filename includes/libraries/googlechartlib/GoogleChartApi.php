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
 * Basic feature to query the API.
 *
 * This class implement basic features to query Google Chart API using GET or
 * POST, as well as a simple way to set/get parameters.
 *
 * Every object that behave like a chart (regular charts, freestanding icons),
 * must inherit from this class.
 *
 * @internal
 */
class GoogleChartApi
{
	/**
	 * Google Chart API base url.
	 */
	const BASE_URL = 'http://chart.apis.google.com/chart';

	// HTTPS url = https://chart.googleapis.com/chart

	/**
	 * GET method
	 */
	const GET = 0;

	/**
	 * POST method
	 */
	const POST = 1;

	/**
	 * An array to store every additional parameters for the final request.
	 * Everything written in this array will be added to the final request without
	 * processing. It can be used to override any parameter.
	 */
	protected $parameters = array();

	/**
	 * GET or POST
	 */
	protected $query_method = self::POST;

	/**
	 * Set a parameter.
	 *
	 * @param $name (string)
	 * @param $value (mixed)
	 */
	public function __set($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * Return a parameter value.
	 *
	 * @param $name (string)
	 * @return mixed
	 */
	public function __get($name)
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
	}

	/**
	 * Unset a parameter.
	 *
	 * @param $name (string)
	 */
	public function __unset($name)
	{
		unset($this->parameters[$name]);
	}

	/**
	 * Compute the whole query as an array.
	 *
	 * This function here does nothing (only returns the parameters array).
	 * It has to be overrided by child classes in order to add some logic.
	 *
	 * @internal
	 */
	protected function computeQuery()
	{
		return $this->parameters;
	}


/**
 * @name Function to query Google Chart API
 */
//@{

	/**
	 * Set whether you want the class to use GET or POST for querying the API.
	 *
	 * Default method is POST.
	 *
	 * @param $method One of the following:
	 * - GoogleChart::GET
	 * - GoogleChart::POST
	 * @return $this
	 */
	public function setQueryMethod($method)
	{
		if ( $method !== self::POST && $method !== self::GET )
			throw new Exception(sprintf(
				'Query method must be either GoogleChart::POST or GoogleChart::GET, "%s" given.',
				$method
			));

		$this->query_method = $method;
		return $this;
	}

	/**
	 * Returns the full URL.
	 *
	 * Use this method if you need to link Google's URL directly, or if you
	 * prefer to use your own library to GET the chart.
	 *
	 * @return string
	 */
	public function getUrl($escape_amp = true)
	{
		$q = $this->computeQuery();
		$url = self::BASE_URL.'?'.http_build_query($q, '', $escape_amp? '&amp;' : '&');
		return $url;
	}

	/**
	 * Returns the query parameters as an array.
	 *
	 * Use this method if you want to do the POST yourself, or for troubleshooting
	 * a chart.
	 *
	 * @return array
	 */
	public function getQuery()
	{
		return $this->computeQuery();
	}

	/**
	 * Return an HTML img tag with Google's URL.
	 *
	 * Use this for troubleshooting or rapid application development.
	 *
	 * @return string
	 */
	public function toHtml()
	{
		$str = sprintf(
			'<img src="%s" alt="" />',
			$this->getUrl()
		);
		return $str;
	}

	/**
	 * Query Google Chart and returns the image.
	 *
	 * @see setQueryMethod
	 *
	 * @return binary image
	 */
	public function getImage()
	{
		$image = null;

		switch ( $this->query_method ) {
			case self::GET:
				$url = $this->getUrl(false);
				$image = file_get_contents($url);
				break;
			case self::POST:
				$image = self::post($this->computeQuery());
				break;
		}

		return $image;
	}

	/**
	 * Returns the image as a GD resource.
	 * @return ressource or false
	 * @since 0.6
	 */
	public function getImageGD()
	{
		return imagecreatefromstring($this->getImage());
	}

	/**
	 * Shortcut for getImage().
	 */
	public function __toString()
	{
		try {
			return (string) $this->getImage();
		} catch (Exception $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
		}
	}
//@}

	/**
	 * Performs a POST.
	 */
	static public function post(array $q = array())
	{
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($q, '', '&')
			)
		));

		return file_get_contents(self::BASE_URL, false, $context);
	}

	/**
	 * Check if a color is valid RRGGBB format.
	 *
	 * @param $color (string)
	 * @return bool
	 */
	static public function validColor($color)
	{
		return preg_match('/^[0-9A-F]{6}$/i', $color);
	}

/* --------------------------------------------------------------------------
 * Debug
 * -------------------------------------------------------------------------- */

	public function getValidationUrl($escape_amp = true)
	{
		$q = $this->computeQuery();
		$q['chof'] = 'validate';
		$url = self::BASE_URL.'?'.http_build_query($q, '', $escape_amp?'&amp;':'&');
		return $url;
	}

	public function validate()
	{
		$q = $this->computeQuery();
		$q['chof'] = 'validate';
		return self::post($q);
	}
}
