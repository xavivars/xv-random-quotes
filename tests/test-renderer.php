<?php

class RendererTests extends WP_UnitTestCase {

	const ARBITRARY_TEXT = "ARBITRARY TEXT";
	const ARBITRARY_AUTHOR = "John Smith";

	function get_render_content($quote) {
		ob_start();

		$quote->render();

		$output = ob_get_clean();

		return $output;
	}


	function test_quote_render_author() {

		$quote = new XV_RandomQuotes_Quote(1, self::ARBITRARY_TEXT, self::ARBITRARY_AUTHOR, '');

		$this->assertContains(self::ARBITRARY_AUTHOR, $this->get_render_content($quote));
	}


}