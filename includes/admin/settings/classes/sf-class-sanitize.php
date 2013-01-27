<?php

/**
 * Sanitize filters
 *
 * @author Matt Gates <http://mgates.me>
 * @package WordPress
 */


if ( ! class_exists( 'SF_Sanitize' ) ) {

	class SF_Sanitize
	{


		/**
		 * Hooks
		 */
		function __construct()
		{
			add_filter( 'geczy_sanitize_text', 'sanitize_text_field' );
			add_filter( 'geczy_sanitize_number', array( $this, 'sanitize_number_field' ) );
			add_filter( 'geczy_sanitize_textarea', array( $this, 'sanitize_textarea' ) );
			add_filter( 'geczy_sanitize_checkbox', array( $this, 'sanitize_checkbox' ) );
			add_filter( 'geczy_sanitize_radio', array( $this, 'sanitize_enum' ), 10, 2 );
			add_filter( 'geczy_sanitize_select', array( $this, 'sanitize_enum' ), 10, 2 );
			add_filter( 'geczy_sanitize_single_select_page', array( $this, 'sanitize_select_pages' ), 10, 2 );
		}


		/**
		 * Numeric sanitization
		 *
		 * @param int     $input
		 * @return int
		 */
		public function sanitize_number_field( $input )
		{
			$output = is_numeric( $input ) ? (float) $input : false;
			return $input;
		}


		/**
		 * Textarea sanitization
		 *
		 * @param string  $input
		 * @return string
		 */
		public function sanitize_textarea( $input )
		{
			global $allowedposttags;
			$output = wp_kses( $input, $allowedposttags );
			return $output;
		}


		/**
		 * Checkbox sanitization
		 *
		 * @param int     $input
		 * @return int
		 */
		public function sanitize_checkbox( $input )
		{
			$output = $input ? 1 : 0;
			return $output;
		}


		/**
		 * Array sanitization
		 *
		 * @param unknown $input
		 * @param array   $option
		 * @return bool
		 */
		public function sanitize_enum( $input, $option )
		{
			$output = array_key_exists( $input, $option['options'] ) ? $input : false;
			return $output;
		}


		/**
		 * Select box for pages sanitize
		 *
		 * @param int     $input
		 * @param int     $option
		 * @return int
		 */
		public function sanitize_select_pages( $input, $option )
		{
			$output = get_page( $input ) ? (int) $input : 0;
			return $output;
		}


	}


}
