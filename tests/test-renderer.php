<?php

class RendererTests extends WP_UnitTestCase {

	const ARBITRARY_TEXT = "ARBITRARY TEXT";
	const ARBITRARY_AUTHOR = "John Smith";

    private $renderer;

    public function __construct()
    {
        $this->renderer = new XV_RandomQuotes_QuoteRenderer();
    }

	function test_quote_render_author() {

		$quote = new XV_RandomQuotes_Quote(1, self::ARBITRARY_TEXT, self::ARBITRARY_AUTHOR, '');

		$this->assertContains(self::ARBITRARY_AUTHOR, $this->renderer->get_render_content($quote));
	}

}