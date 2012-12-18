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

require_once dirname(__FILE__).'/../GoogleChartIcon.php';

/**
 * A "Fun style notes with text and optional title".
 */
class GoogleChartIconNote extends GoogleChartIcon
{
	const LARGE = 1;
	const SMALL = 2;

	const LEFT = 'l';
	const CENTER = 'h';
	const RIGHt = 'r';

	protected $title = null;
	protected $text = null;
	protected $type = null;

	protected $size = self::LARGE;
	protected $text_color = '000000';
	protected $text_alignment = self::CENTER;

	public function __construct($text, $note_type = 'sticky_y')
	{
		$this->setText($text);
		$this->setType($note_type);
	}

	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	public function setType($note_type)
	{
		$this->type = $note_type;
		return $this;
	}

	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}

	public function setTextColor($color)
	{
		$this->text_color = $color;
		return $this;
	}

	public function setTextAlignment($align)
	{
		$this->text_alignment = $align;
		return $this;
	}

	public function computeChst()
	{
		if ( $this->title === null )
			return 'd_fnote';
		else
			return 'd_fnote_title';
	}

	public function computeChld($separator = '|', $eol='|', $escape = '')
	{
		$str = $this->type
			.$separator.$this->size
			.$separator.$this->text_color
			.$separator.$this->text_alignment
			.$separator.($this->title ? $this->title.($this->text?$separator:'') : '');

		$str .= str_replace(
			array("\n","\r", '|','@','=',',',';'),
			array($eol, '', $escape.'|', $escape.'@', $escape.'=', $escape.',', $escape.';'),
			$this->text
		);

		return $str;
	}
}
