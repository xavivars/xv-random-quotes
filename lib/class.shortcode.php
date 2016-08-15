<?php

if ( ! defined( 'XV_RANDOM_QUOTES' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class XV_RandomQuotes_Shortcode {

	private $_repository;
	private $_renderer;

	public function __construct() {

        $this->_repository = new XV_RandomQuotes_Repository();
		$this->_renderer = new XV_RandomQuotes_QuoteRenderer();


		if ( function_exists( 'add_shortcode' ) ) {

			add_shortcode('random-quotes', array($this, 'random_quotes'));
            add_shortcode('random-quote', array($this, 'random_quote'));


			// Legacy shortcodes
			add_shortcode( 'stray-id', array($this, 'stray_id_shortcode') );
			add_shortcode( 'stray-random', array($this, 'stray_random_shortcode') );
			add_shortcode( 'stray-all', array($this, 'stray_all_shortcode' ) );
		}
	}

	private function show_deprecated_notice($version, $deprecated, $replacement) {
		if ( WP_DEBUG) {
			trigger_error( sprintf( __( '%2$s shortcode is <strong>deprecated</strong> since version %1$s! Use %3$s instead.' ), $version, $deprecated, $replacement ) );
		}
	}

	private function deprecated_shortcode($old, $replacement) {
		$version_with_new_shortcodes = '1.33';
        $this->show_deprecated_notice($version_with_new_shortcodes, $old, $replacement );
	}

	// [random-quote]
	public function random_quote($atts) {

		$quote = $this->_repository->get_quote(array('random' => true));

		return $this->_renderer->get_rendered_content($quote);
	}

	// [random-quotes show="random|all|id"]
	public function random_quotes($atts) {

	    $atts = $this->consolidate_atts($atts);

        if (isset($atts['amount']) && $atts['amount'] == 1) {
            $quote = $this->_repository->get_quote($atts);
        } else {
            $quote = $this->_repository->get_quotes($atts);
        }

        return $this->_renderer->get_rendered_content($quote);
	}

	private function consolidate_atts( $atts ) {

	    $default_atts = array('random' => true, 'amount' => 1);

	    if (!isset($atts['show'])) {
	        return $default_atts;
        }

	    if(($atts['show'] == 'all')) {
            $atts = array( 'amount' => -1);
        } else if (is_numeric($atts['show']) && ((int)$atts['show'])>0){
            $atts = array( 'random' => false, 'quoteId' => (int)$atts['show'], 'amount' => 1);
        } else {
            return $default_atts;
        }

        return $atts;
    }

	// [stray-random]
	public function stray_random_shortcode($atts) {

		$this->deprecated_shortcode( 'stray-random', 'random-quote');

		$quote = $this->_repository->get_quote(array( 'random' => true));

		return $this->_renderer->get_rendered_content($quote);
	}

	//this is a SHORTCODE [stray-all]
	function stray_all_shortcode( $atts, $content = null ) {

		extract( shortcode_atts( array(
			"categories"    => 'all',
			"sequence"      => true,
			"linkphrase"    => '',
			"widgetid"      => '',
			"noajax"        => true,
			"rows"          => 10,
			"timer"         => '',
			"offset"        => 0,
			"fullpage"      => true,
			"orderby"       => 'quoteID',
			"sort"          => 'ASC',
			"disableaspect" => false,
			"user"          => ''
		), $atts ) );

		return get_stray_quotes( $categories, $sequence, $linkphrase, $rows, $timer, $noajax, $offset, $widgetid, $fullpage, $orderby, $sort, '', $disableaspect, $user );
	}

	//this is a SHORTCODE [stray-id]
	function stray_id_shortcode( $atts, $content = null ) {

		extract( shortcode_atts( array(
			"id"            => '1',
			"linkphrase"    => '',
			"noajax"        => true,
			"disableaspect" => false
		), $atts ) );


		return get_stray_quotes( '', true, $linkphrase, '', '', $noajax, '', '', '', '', '', $id, $disableaspect );
	}


}

new XV_RandomQuotes_Shortcode();
