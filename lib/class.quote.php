<?php

/**
 * Quote object
 *
 * @author xavi
 */
class XV_RandomQuotes_Quote {
	
	private $id;
	private $text;
	private $author;
	private $source;

	public function __construct($id, $text, $author, $source) {
		$this->id = $id;
		$this->text	 = $text;
		$this->author	 = $author;
		$this->text	 = $text;
	}
	
	# Getters and setters
	public function get_id() {
		return $this->id;
	}

	public function get_text() {
		return $this->text;
	}

	public function get_author() {
		return $this->author;
	}

	public function get_source() {
		return $this->source;
	}
}
