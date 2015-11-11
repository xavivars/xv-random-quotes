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
	
	private $link_author;
	private $replace_spaces_author;
	
	private $before_quote;
	private $after_quote;
	
	private $before_author;
	private $after_author;
	
	private $before_source;
	private $after_source;
	
	public function __construct($id, $text, $author, $source) {
		$this->id = $id;
		$this->text	 = $text;
		$this->author	 = $author;
		$this->text	 = $text;
		
		$this->link_author = false;
		$this->replace_spaces_author = false;
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
	
	public function enable_link_author( $link ) {
		$this->link_author = $link;
	}
	
	public function enable_author_space_replacement( $replacement) {
		$this->replace_spaces_author = $replacement;
	}
	
	public function set_before_quote( $before_quote ) {
		$this->before_quote = $before_quote;
	}
	
	public function set_after_quote( $after_quote ) {
		$this->after_quote = $after_quote;
	}
	
	public function set_before_author( $before_author ) {
		$this->before_author = $before_author;
	}
	
	public function set_after_author( $after_author) {
		$this->after_author = $after_author;
	}
	
	public function set_before_source ( $before_source ) {
		$this->before_source = $before_source;
	}
	
	public function set_after_source( $after_source ) {
		$this->after_source = $after_source;
	}
	
	public function render() {
		
		?>
		<div id="wp_quotes">
			<div class="wp_quotes_quote">
				“<?= $this->render_text() ?>”
			</div>
			<?= $this->render_author() ?>
		</div>
		<?php
		
	}
	
	private function render_text() {
		return nl2br($this->text);
	}
	
	private function render_author() {
		
		$author = '';
		
		if ( $this->author ) {
			$author = $this->author;
			if ( $this->link_author && 
				 !preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i",$this->author ) ) {
				
				if ( $this->replace_spaces_author ){
					$author = str_replace( " ", $this->replace_spaces_author, $author );
				}

				$search = array( '"', '&', '%AUTHOR%' );
				$replace = array( '%22','&amp;', $author );
				$href = str_replace( $search , $replace , $this->link_author );

				/*$linkto = str_replace('%AUTHOR%',$Author,$linkto);*/
				$author = '<a href="' . $href . '">' . $this->author . '</a>';
			}
			
			
			$author = $this->before_author . $author . $this->after_author;
		}
		
		return $author;
	}
}
