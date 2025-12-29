<?php
/**
 * Settings Page for Random Quotes
 *
 * @package XVRandomQuotes
 */

namespace XVRandomQuotes\Admin;

/**
 * Class Settings
 *
 * Manages the plugin settings page and options.
 */
class Settings {

	/**
	 * Settings group name
	 *
	 * @var string
	 */
	const SETTINGS_GROUP = 'xv_quotes_settings';

	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'xv-quotes-settings';

	/**
	 * Option names for all settings
	 */
	const OPTION_USE_NATIVE_STYLING = 'xv_quotes_use_native_styling';
	const OPTION_BEFORE_ALL = 'xv_quotes_before_all';
	const OPTION_AFTER_ALL = 'xv_quotes_after_all';
	const OPTION_BEFORE_QUOTE = 'xv_quotes_before_quote';
	const OPTION_AFTER_QUOTE = 'xv_quotes_after_quote';
	const OPTION_BEFORE_AUTHOR = 'xv_quotes_before_author';
	const OPTION_AFTER_AUTHOR = 'xv_quotes_after_author';
	const OPTION_BEFORE_SOURCE = 'xv_quotes_before_source';
	const OPTION_AFTER_SOURCE = 'xv_quotes_after_source';
	const OPTION_IF_NO_AUTHOR = 'xv_quotes_if_no_author';
	const OPTION_PUT_QUOTES_FIRST = 'xv_quotes_put_quotes_first';
	const OPTION_LINKTO = 'xv_quotes_linkto';
	const OPTION_SOURCELINKTO = 'xv_quotes_sourcelinkto';
	const OPTION_AUTHORSPACES   = 'xv_quotes_authorspaces';
	const OPTION_SOURCESPACES   = 'xv_quotes_sourcespaces';

	// AJAX Settings
	const OPTION_AJAX           = 'xv_quotes_ajax';
	const OPTION_LOADER         = 'xv_quotes_loader';
	const OPTION_BEFORE_LOADER  = 'xv_quotes_before_loader';
	const OPTION_AFTER_LOADER   = 'xv_quotes_after_loader';
	const OPTION_LOADING        = 'xv_quotes_loading';

	/**
	 * Initialize settings
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_checkbox_options' ) );
	}

	/**
	 * Handle checkbox options that aren't submitted when unchecked
	 */
	public function handle_checkbox_options() {
		// Only process on settings page save
		if ( ! isset( $_POST['option_page'] ) || $_POST['option_page'] !== self::SETTINGS_GROUP ) {
			return;
		}

		// Check nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], self::SETTINGS_GROUP . '-options' ) ) {
			error_log( 'XV Quotes Settings: Nonce verification failed' );
			return;
		}

		error_log( 'XV Quotes Settings: Processing checkbox options' );
		error_log( 'POST data: ' . print_r( $_POST, true ) );

		// List of checkbox options
		$checkbox_options = array(
			self::OPTION_USE_NATIVE_STYLING,
			self::OPTION_PUT_QUOTES_FIRST,
			self::OPTION_AJAX,
		);

		// For each checkbox, if not present in POST, set to '0'
		foreach ( $checkbox_options as $option ) {
			$is_set = isset( $_POST[ $option ] );
			$value = $is_set ? $_POST[ $option ] : 'not set';
			error_log( "XV Quotes Settings: Option {$option} - isset: " . ( $is_set ? 'YES' : 'NO' ) . ", value: {$value}" );
			
			if ( ! isset( $_POST[ $option ] ) ) {
				update_option( $option, '0' );
				error_log( "XV Quotes Settings: Set {$option} to '0' (unchecked)" );
			}
		}
	}

	/**
	 * Add settings page to admin menu
	 */
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=xv_quote',
			__( 'Quote Settings', 'stray-quotes' ),
			__( 'Settings', 'stray-quotes' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register all settings
	 */
	public function register_settings() {
		// Register Native Styling Setting
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_USE_NATIVE_STYLING,
			array(
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			)
		);

		// Register Display Settings (HTML Wrappers)
		$html_wrapper_options = array(
			self::OPTION_BEFORE_ALL,
			self::OPTION_AFTER_ALL,
			self::OPTION_BEFORE_QUOTE,
			self::OPTION_AFTER_QUOTE,
			self::OPTION_BEFORE_AUTHOR,
			self::OPTION_AFTER_AUTHOR,
			self::OPTION_BEFORE_SOURCE,
			self::OPTION_AFTER_SOURCE,
			self::OPTION_IF_NO_AUTHOR,
		);

		foreach ( $html_wrapper_options as $option ) {
			register_setting(
				self::SETTINGS_GROUP,
				$option,
				array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'wp_kses_post',
				)
			);
		}

		// Register Put Quotes First checkbox
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_PUT_QUOTES_FIRST,
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			)
		);

		// Register Link Settings
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_LINKTO,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_url_template' ),
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_SOURCELINKTO,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_url_template' ),
			)
		);

		// Register Space Replacement Settings
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_AUTHORSPACES,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_SOURCESPACES,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Register AJAX Settings
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_AJAX,
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_LOADER,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_BEFORE_LOADER,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_AFTER_LOADER,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_LOADING,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Add settings sections
		add_settings_section(
			'xv_quotes_native_styling',
			__( 'Quote Display Mode', 'stray-quotes' ),
			array( $this, 'render_native_styling_section' ),
			self::PAGE_SLUG
		);

		add_settings_section(
			'xv_quotes_display',
			__( 'Custom HTML Wrappers', 'stray-quotes' ),
			array( $this, 'render_display_section' ),
			self::PAGE_SLUG
		);

		add_settings_section(
			'xv_quotes_links',
			__( 'Author & Source Links', 'stray-quotes' ),
			array( $this, 'render_links_section' ),
			self::PAGE_SLUG
		);

		// Add settings fields
		$this->add_native_styling_fields();
		$this->add_display_fields();
		$this->add_link_fields();

		// Add AJAX section
		add_settings_section(
			'xv_quotes_ajax',
			__( 'AJAX Settings', 'stray-quotes' ),
			array( $this, 'render_ajax_section' ),
			self::PAGE_SLUG
		);
		$this->add_ajax_fields();
	}

	/**
	 * Add native styling fields
	 */
	private function add_native_styling_fields() {
		add_settings_field(
			self::OPTION_USE_NATIVE_STYLING,
			__( 'Use Native WordPress Quote Styling', 'stray-quotes' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG,
			'xv_quotes_native_styling',
			array(
				'label_for'   => self::OPTION_USE_NATIVE_STYLING,
				'option_name' => self::OPTION_USE_NATIVE_STYLING,
				'description' => __( 'Enable to use WordPress native quote block styling. When enabled, quotes will be displayed using the standard WordPress quote block format with <code>&lt;blockquote class="wp-block-quote"&gt;</code>. Disable to use custom HTML wrappers below.', 'stray-quotes' ),
			)
		);
	}

	/**
	 * Add display settings fields
	 */
	private function add_display_fields() {
		// Quote Area
		add_settings_field(
			self::OPTION_BEFORE_ALL,
			__( 'Before Quote Area', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_BEFORE_ALL,
				'option_name' => self::OPTION_BEFORE_ALL,
				'description' => __( 'HTML or other elements before the quote area. Example: <code>&lt;div class="quote-wrapper"&gt;</code>', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		add_settings_field(
			self::OPTION_AFTER_ALL,
			__( 'After Quote Area', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_AFTER_ALL,
				'option_name' => self::OPTION_AFTER_ALL,
				'description' => __( 'HTML or other elements after the quote area. Example: <code>&lt;/div&gt;</code>', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		// Quote Text
		add_settings_field(
			self::OPTION_BEFORE_QUOTE,
			__( 'Before Quote Text', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_BEFORE_QUOTE,
				'option_name' => self::OPTION_BEFORE_QUOTE,
				'description' => __( 'HTML or other elements before the quote text. Example: <code>&amp;#8220;</code> (opening quote)', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		add_settings_field(
			self::OPTION_AFTER_QUOTE,
			__( 'After Quote Text', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_AFTER_QUOTE,
				'option_name' => self::OPTION_AFTER_QUOTE,
				'description' => __( 'HTML or other elements after the quote text. Example: <code>&amp;#8221;</code> (closing quote)', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		// Author
		add_settings_field(
			self::OPTION_BEFORE_AUTHOR,
			__( 'Before Author', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_BEFORE_AUTHOR,
				'option_name' => self::OPTION_BEFORE_AUTHOR,
				'description' => __( 'HTML or other elements before the author. Example: <code>&lt;br/&gt;by&amp;nbsp;</code>', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		add_settings_field(
			self::OPTION_AFTER_AUTHOR,
			__( 'After Author', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_AFTER_AUTHOR,
				'option_name' => self::OPTION_AFTER_AUTHOR,
				'description' => __( 'HTML or other elements after the author.', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		// Source
		add_settings_field(
			self::OPTION_BEFORE_SOURCE,
			__( 'Before Source', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_BEFORE_SOURCE,
				'option_name' => self::OPTION_BEFORE_SOURCE,
				'description' => __( 'HTML or other elements before the source. Example: <code>,&lt;em&gt;&amp;nbsp;</code>', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		add_settings_field(
			self::OPTION_AFTER_SOURCE,
			__( 'After Source', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_AFTER_SOURCE,
				'option_name' => self::OPTION_AFTER_SOURCE,
				'description' => __( 'HTML or other elements after the source. Example: <code>&lt;/em&gt;</code>', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		add_settings_field(
			self::OPTION_IF_NO_AUTHOR,
			__( 'Before Source (No Author)', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_IF_NO_AUTHOR,
				'option_name' => self::OPTION_IF_NO_AUTHOR,
				'description' => __( 'HTML or other elements before the source when there is no author. Overrides "Before Source" field. Example: <code>&lt;br/&gt;source:&amp;nbsp;</code>', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);

		// Put Quotes First
		add_settings_field(
			self::OPTION_PUT_QUOTES_FIRST,
			__( 'Quote Before Author/Source', 'stray-quotes' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG,
			'xv_quotes_display',
			array(
				'label_for'   => self::OPTION_PUT_QUOTES_FIRST,
				'option_name' => self::OPTION_PUT_QUOTES_FIRST,
				'description' => __( 'Display the quote text before author and source.', 'stray-quotes' ),
				'class'       => 'xv-quotes-legacy-setting',
			)
		);
	}

	/**
	 * Add link settings fields
	 */
	private function add_link_fields() {
		add_settings_field(
			self::OPTION_LINKTO,
			__( 'Author Link Template', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_links',
			array(
				'label_for'   => self::OPTION_LINKTO,
				'option_name' => self::OPTION_LINKTO,
				'description' => __( 'Link the author to a URL of your choice. Use <code>%AUTHOR%</code> as a variable. Example: <code>http://www.google.com/search?q="%AUTHOR%"</code> or <code>http://en.wikipedia.org/wiki/%AUTHOR%</code>', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_AUTHORSPACES,
			__( 'Author URL Space Replacement', 'stray-quotes' ),
			array( $this, 'render_small_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_links',
			array(
				'label_for'   => self::OPTION_AUTHORSPACES,
				'option_name' => self::OPTION_AUTHORSPACES,
				'description' => __( 'Replace spaces in author name with this character for URLs (e.g., <code>_</code> or <code>+</code>).', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_SOURCELINKTO,
			__( 'Source Link Template', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_links',
			array(
				'label_for'   => self::OPTION_SOURCELINKTO,
				'option_name' => self::OPTION_SOURCELINKTO,
				'description' => __( 'Link the source to a URL of your choice. Use <code>%SOURCE%</code> as a variable. Example: <code>http://www.google.com/search?q="%SOURCE%"</code> or <code>http://en.wikipedia.org/wiki/%SOURCE%</code>', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_SOURCESPACES,
			__( 'Source URL Space Replacement', 'stray-quotes' ),
			array( $this, 'render_small_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_links',
			array(
				'label_for'   => self::OPTION_SOURCESPACES,
				'option_name' => self::OPTION_SOURCESPACES,
				'description' => __( 'Replace spaces in source name with this character for URLs (e.g., <code>_</code> or <code>+</code>).', 'stray-quotes' ),
			)
		);
	}

	/**
	 * Add AJAX settings fields
	 */
	private function add_ajax_fields() {
		add_settings_field(
			self::OPTION_AJAX,
			__( 'Disable AJAX', 'stray-quotes' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG,
			'xv_quotes_ajax',
			array(
				'label_for'   => self::OPTION_AJAX,
				'option_name' => self::OPTION_AJAX,
				'description' => __( 'Check to disable AJAX dynamic loading entirely. When unchecked, AJAX can still be disabled from widgets, shortcodes, or template tags.', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_LOADER,
			__( 'Loader Link Text', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_ajax',
			array(
				'label_for'   => self::OPTION_LOADER,
				'option_name' => self::OPTION_LOADER,
				'description' => __( 'The link text used to dynamically load another quote. HTML not allowed. If empty, clicking the quote itself will reload it. Example: <code>New quote &amp;raquo;</code>', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_BEFORE_LOADER,
			__( 'Before Loader', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_ajax',
			array(
				'label_for'   => self::OPTION_BEFORE_LOADER,
				'option_name' => self::OPTION_BEFORE_LOADER,
				'description' => __( 'HTML or other elements before the quote loader. Example: <code>&lt;p align="left"&gt;</code>', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_AFTER_LOADER,
			__( 'After Loader', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_ajax',
			array(
				'label_for'   => self::OPTION_AFTER_LOADER,
				'option_name' => self::OPTION_AFTER_LOADER,
				'description' => __( 'HTML or other elements after the quote loader. Example: <code>&lt;/p&gt;</code>', 'stray-quotes' ),
			)
		);

		add_settings_field(
			self::OPTION_LOADING,
			__( 'Loading Message', 'stray-quotes' ),
			array( $this, 'render_text_field' ),
			self::PAGE_SLUG,
			'xv_quotes_ajax',
			array(
				'label_for'   => self::OPTION_LOADING,
				'option_name' => self::OPTION_LOADING,
				'description' => __( 'The message displayed while a new quote is being loaded. Example: <code>loading...</code>', 'stray-quotes' ),
			)
		);
	}

	/**
	 * Render native styling section description
	 */
	public function render_native_styling_section() {
		echo '<p>' . esc_html__( 'Choose how quotes should be displayed on your site.', 'stray-quotes' ) . '</p>';
	}

	/**
	 * Render display settings section description
	 */
	public function render_display_section() {
		echo '<p>' . esc_html__( 'Customize HTML wrappers for quote display. These settings only apply when native styling is disabled.', 'stray-quotes' ) . '</p>';
	}

	/**
	 * Render links section description
	 */
	public function render_links_section() {
		echo '<p>' . esc_html__( 'Configure automatic linking for authors and sources.', 'stray-quotes' ) . '</p>';
	}

	/**
	 * Render AJAX section description
	 */
	public function render_ajax_section() {
		echo '<p>' . esc_html__( 'Default settings for the dynamic quote loader.', 'stray-quotes' ) . '</p>';
	}

	/**
	 * Render checkbox field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$option_name = $args['option_name'];
		$value       = get_option( $option_name, false );
		$class       = isset( $args['class'] ) ? $args['class'] : '';
		
		error_log( "XV Quotes Settings: Rendering checkbox {$option_name}, current value: " . print_r( $value, true ) );
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<label>
				<input type="checkbox" 
					   id="<?php echo esc_attr( $args['label_for'] ); ?>" 
					   name="<?php echo esc_attr( $option_name ); ?>" 
					   value="1" 
					   <?php checked( '1', $value ); ?> />
				<?php if ( isset( $args['description'] ) ) : ?>
					<span class="description"><?php echo wp_kses_post( $args['description'] ); ?></span>
				<?php endif; ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Render text field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$option_name = $args['option_name'];
		$value       = get_option( $option_name, '' );
		$class       = isset( $args['class'] ) ? $args['class'] : '';
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<input type="text" 
				   id="<?php echo esc_attr( $args['label_for'] ); ?>" 
				   name="<?php echo esc_attr( $option_name ); ?>" 
				   value="<?php echo esc_attr( $value ); ?>" 
				   class="regular-text" />
			<?php if ( isset( $args['description'] ) ) : ?>
				<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render small text field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_small_text_field( $args ) {
		$option_name = $args['option_name'];
		$value       = get_option( $option_name, '' );
		?>
		<input type="text" 
			   id="<?php echo esc_attr( $args['label_for'] ); ?>" 
			   name="<?php echo esc_attr( $option_name ); ?>" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="small-text" 
			   maxlength="1" />
		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Sanitize checkbox input
	 *
	 * @param mixed $value Input value.
	 * @return string Sanitized value as string ('1' or '0').
	 */
	public function sanitize_checkbox( $value ) {
		error_log( 'XV Quotes Settings: sanitize_checkbox called with value: ' . print_r( $value, true ) );
		
		// WordPress doesn't send unchecked checkboxes, so null/empty means unchecked
		if ( empty( $value ) ) {
			error_log( 'XV Quotes Settings: sanitize_checkbox returning 0 (empty value)' );
			return '0';
		}
		
		// Return string '1' for checked, '0' for unchecked
		$result = ( '1' === $value || 1 === $value || true === $value ) ? '1' : '0';
		error_log( 'XV Quotes Settings: sanitize_checkbox returning ' . $result );
		return $result;
	}

	/**
	 * Sanitize URL template
	 *
	 * @param string $value Input value.
	 * @return string Sanitized value.
	 */
	public function sanitize_url_template( $value ) {
		$value = sanitize_text_field( $value );
		
		// Allow empty or 'http://'
		if ( empty( $value ) || 'http://' === $value ) {
			return '';
		}

		// Basic URL validation
		if ( ! preg_match( '#^(https?|ftp)://(\S+)#i', $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show success message if settings were saved
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'xv_quotes_messages',
				'xv_quotes_message',
				__( 'Settings saved successfully.', 'stray-quotes' ),
				'updated'
			);
		}

		settings_errors( 'xv_quotes_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'stray-quotes' ) );
				?>
			</form>
		</div>

		<script type="text/javascript">
		(function($) {
			$(document).ready(function() {
				// Toggle legacy settings visibility based on native styling checkbox
				function toggleLegacySettings() {
					var isNative = $('#<?php echo esc_js( self::OPTION_USE_NATIVE_STYLING ); ?>').is(':checked');
					if (isNative) {
						$('.xv-quotes-legacy-setting').closest('tr').hide();
					} else {
						$('.xv-quotes-legacy-setting').closest('tr').show();
					}
				}

				// Run on page load
				toggleLegacySettings();

				// Run when checkbox changes
				$('#<?php echo esc_js( self::OPTION_USE_NATIVE_STYLING ); ?>').on('change', toggleLegacySettings);
			});
		})(jQuery);
		</script>
		<?php
	}
}
