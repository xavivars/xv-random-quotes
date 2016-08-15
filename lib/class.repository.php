<?php

if ( ! defined( 'XV_RANDOM_QUOTES' ) ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden' );
        exit();
    }

require_once plugin_dir_path( __FILE__ ).'/class.constants.php' ;
require_once plugin_dir_path( __FILE__ ).'/class.quote.php' ;
	
/**
 * Manages data access of the quotes
 *
 * @author xavi
 */
class XV_RandomQuotes_Repository {
	
	private $default_args;
	private $plugin_options;
	
	public function __construct() {
		$this->default_args = NULL;
		$this->plugin_options = NULL;
	}
	
	private function get_default_args($create = false) {
		if($this->default_args == NULL) {
			
			if ($this->plugin_options == NULL){
				$this->plugin_options = get_option(XV_RandomQuotes_Constants::PLUGIN_OPTIONS);
			}
		
			$this->default_args  = array(
				'random' => true,
				'reloadtext' =>
							isset($plugin_options[XV_RandomQuotes_Constants::DEFAULT_RELOAD_TEXT_OPTION]) ? 
									$plugin_options[XV_RandomQuotes_Constants::DEFAULT_RELOAD_TEXT_OPTION] : '',
				'amount' => 1,
				'timer' => 0,
				'ajax' => true,
				'offset' => 0,
				'widgetid' => null,
				'fullpage' => null,
				'orderby' => 'quoteID',
				'sort' => 'ASC',
				'quoteId' => null,
				'disableaspect' => true,
				'contributor' => null,
				'visible' => true
			);

            if ($create) {
                $this->default_args['categories'] = isset($plugin_options[XV_RandomQuotes_Constants::DEFAULT_CATEGORY_OPTION]) ?
                                $plugin_options[XV_RandomQuotes_Constants::DEFAULT_CATEGORY_OPTION] : array('default');
            }

			if ( isset($this->default_args['categories']) && !is_array( $this->default_args['categories'] )) {
				$this->default_args['categories'] = array( $this->default_args['categories'] );
			}
		}
		
		return $this->default_args;
	}
	
	public function get_quote( $args ) {
		
		$args['amount'] = 1;
		
		$quotes = $this->get_quotes( $args );
		
		return $quotes[0];
	}
	
	public function get_quotes( $args ) {
		
		global $wpdb;
		
		$quotes = array();
				
		$args = $this->prepare_args( $args );
		
		$query = $this->get_sql_query($args);
		
		$results = $wpdb->get_results($query);
		
		foreach ( $results as $result ) {
			$quote = new XV_RandomQuotes_Quote( $result->quoteID, $result->quote, $result->author, $result->source );
			
			array_push( $quotes, $quote );
		}
		
		return $quotes;
	}
	
	private function prepare_args( $args ) {
		
		if ( isset($args['categories']) && ! is_array( $args['categories'] ) ) {
				$args['categories'] = array( $args['categories'] );
		}
		
		if ( isset($args['categories']) && count($args['categories']) == 0 ) {
			unset ( $args['categories'] );
		}

		if ( isset($args['quoteId']) && !is_array($args['quoteId']) && is_numeric($args['quoteId'])) {
		    $args['quoteId'] = array( $args['quoteId']);
        }

		$args = array_merge ( $this->get_default_args(), $args );
		
		return $args;
	}
	
	private function get_sql_query($args) {
		
		global $wpdb;
		
		$conditions = array();
		
		/**
		 *
				'reloadtext' =>
							isset($plugin_options[XV_RandomQuotes_Constants::DEFAULT_RELOAD_TEXT_OPTION]) ? 
									$plugin_options[XV_RandomQuotes_Constants::DEFAULT_RELOAD_TEXT_OPTION] : '',
				'timer' => 0,
				'ajax' => true,
				'widgetid' => null,
				'disableaspect' => true,
				'contributor' => null,
				'visible' => true
		 * 
		 * 
		 */
		
		if( isset( $args['categories']) ) {
			$conditions[] =	$this->create_in_condition( 'category', $args['categories'] );
		}

		if( isset($args['quoteId']) && is_array($args['quoteId']) ) {
			$conditions[] = $this->create_in_condition('quoteID', $args['quoteId']);
		}

		if ( count( $conditions ) > 0) {
			$sql_conditions = ' AND ' . implode( ' AND ' , $conditions);
		} else {
			$sql_conditions = '';
		}
		
		if( $args['random'] ) {
			$orderby = ' ORDER BY RAND() ';
		} else {
			$orderby = " ORDER BY `${args['orderby']}` ${args['sort']} ";
		}

		if ( $args['amount'] > 1 && $args['offset'] > 0) {
			$limit = " LIMIT ${args['offset']}, ${args['amount']}";
		} else if ( $args['amount'] > 1) {
				$limit = " LIMIT ${args['amount']}";
		} else if ($args['amount'] == -1) {
		    $limit = '';
        } else {
			$limit = ' LIMIT 1 ';
		}
		
		return "SELECT `quoteID`,`quote`,`author`,`source` FROM `"
		. XV_RandomQuotes_Constants::DB_TABLE . "` WHERE `visible`='yes' " 
		. $sql_conditions
		. $orderby
		. $limit;
	}
	
	private function create_in_condition( $key, $values, $numeric = false ) {
		
		global $wpdb;

        if ($numeric) {
            $base_sql = $key . ' IN ( ' . implode( ', ', array_fill( 0, count( $values ), '%d') ) . ' ) ';
        } else {
            $base_sql = $key . ' IN ( ' . implode( ', ', array_fill( 0, count( $values ), '%s') ) . ' ) ';
        }


		// Call $wpdb->prepare passing the values of the array as separate arguments
		return call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $base_sql ), $values) );
	}
}
