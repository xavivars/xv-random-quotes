<?php

if ( ! defined( 'XV_RANDOM_QUOTES' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

require_once plugin_dir_path( __FILE__ ).'/class.constants.php' ;
require_once plugin_dir_path( __FILE__ ).'/class.quote.php' ;

class XV_RandomQuotes_QuoteRenderer
{
    private $link_author;
    private $replace_spaces_author;

    private $before_quote;
    private $after_quote;

    private $before_author;
    private $after_author;

    private $before_source;
    private $after_source;

    public function __construct()
    {
        $this->link_author = false;
        $this->replace_spaces_author = false;

        $this->add_formatting_info();
    }

    public function add_formatting_info( ) {
        $plugin_options = get_option(XV_RandomQuotes_Constants::PLUGIN_OPTIONS);

        $this->set_before_quote( $plugin_options['stray_quotes_before_quote'] );
        $this->set_after_quote( $plugin_options['stray_quotes_after_quote'] );

        $this->set_before_author( $plugin_options['stray_quotes_before_author'] );
        $this->set_after_author( $plugin_options['stray_quotes_after_author'] );

        $this->set_before_source( $plugin_options['stray_quotes_before_source'] );
        $this->set_after_source( $plugin_options['stray_quotes_after_source'] );
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

    function get_rendered_content($quote) {
        ob_start();

        $this->render($quote);

        $output = ob_get_clean();

        return $output;
    }

    public function render( $quote ) {

        ?>
        <div id="wp_quotes">
            <div class="wp_quotes_quote">
                “<?= $this->render_text( $quote ) ?>”
            </div>
            <?= $this->render_author( $quote ) ?>
        </div>
        <?php

    }

    private function render_text( $quote ) {
        return nl2br( $quote->get_text() );
    }

    private function render_author( $quote ) {

        $author = '';

        if ( $quote->get_author() ) {
            $author = $quote->get_author();
            if ( $this->link_author &&
                !preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i",$quote->get_author() ) ) {

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