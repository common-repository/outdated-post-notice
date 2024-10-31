<?php
/**
 * Outdated Post Notice.
 *
 * @package   FSOutdatedPostNotice
 * @author    Firdaus Zahari <fake@fsylum.net>
 * @license   GPL-2.0+
 * @link      http://fsylum.net
 * @copyright 2014 Firdaus Zahari
 */

/**
 * @package FSOutdatedPostNotice
 * @author  Firdaus Zahari <fake@fsylum.net>
 */
class FSOutdatedPostNotice {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    const VERSION = '1.0.0';

    /**
     * Plugin slug, used in multiple scenario
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'fs-outdated-post-notice';

	/**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    /**
     * Slug of the plugin settings name.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $settings_name = 'fs-outdated-post-notice-settings';

    /**
	 * Slug of the plugin option name.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $option_name = 'fs-outdated-post-notice-opts';

    /**
     * Option stored in db
     *
     * @since    1.0.0
     */
    protected $options = null;

    /**
     * Initial styles stored in db
     *
     * @since    1.0.0
     */
    protected $styles = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

        $this->options = get_option( $this->option_name );

        if( $this->options ) {

            $this->styles = '-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;padding: 10px;';
            $this->styles .= 'background: ' . $this->options['bg_color'] . ';';
            $this->styles .= 'border: 1px solid ' . $this->options['border_color'] . ';';
            $this->styles .= 'color: ' . $this->options['text_color'] . ';';

        }

        if( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

    		// Load admin style sheet and JavaScript.
    		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    		// Add the options page and menu item.
    		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

    		// Add an action link pointing to the options page.
    		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
            add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

            // Register plugin settings
            add_action( 'admin_init', array( $this, 'register_setting' ) );

            // Add meta boxes
            if( isset( $this->options['display_for'] ) && ! empty( $this->options['display_for'] ) ) {
                foreach( $this->options['display_for'] as $post_type ) {
                    add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_boxes' ) );
                }
            }

            add_action( 'save_post', array( $this, 'save_post' ) );
        }

        add_filter( 'the_content', array( $this, 'the_content' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.min.css', __FILE__ ), array(), FSOutdatedPostNotice::VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.min.js', __FILE__ ), array( 'iris' ), FSOutdatedPostNotice::VERSION );

        $template = $this->get_templating_data();
        wp_localize_script( $this->plugin_slug . '-admin-script', 'fs_outdated_post_notice_var', $template );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Outdated Post Notice Settings', $this->plugin_slug ),
			__( 'Outdated Post Notice', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
     * @since    1.0.0
     */
    public function add_action_links( $links ) {

        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
            ),
            $links
        );

    }

    /**
     * Register plugin settings
     *
     * @since    1.0.0
     */
    public function register_setting() {
        register_setting( $this->settings_name, $this->option_name );
        add_settings_section( $this->settings_name . '-main', '', array( $this, 'add_settings_section_cb' ), $this->plugin_slug  );
        add_settings_field( $this->option_name . '-display-for', 'Display notice for', array( $this, 'add_settings_field_cb' ), $this->plugin_slug, $this->settings_name . '-main', array( 'section' => 'display-for' ) );
        add_settings_field( $this->option_name . '-background-color', 'Background color', array( $this, 'add_settings_field_cb' ), $this->plugin_slug, $this->settings_name . '-main', array( 'section' => 'background-color' ) );
        add_settings_field( $this->option_name . '-text-color', 'Text color', array( $this, 'add_settings_field_cb' ), $this->plugin_slug, $this->settings_name . '-main', array( 'section' => 'text-color' ) );
        add_settings_field( $this->option_name . '-preview', 'Preview', array( $this, 'add_settings_field_cb' ), $this->plugin_slug, $this->settings_name . '-main', array( 'section' => 'preview' ) );
    }

    /**
     * Optional callback for add_settings_section
     *
     * @since    1.0.0
     */
    public function add_settings_section_cb() {}

    /**
     * Render form inputs on settings page
     *
     * @since    1.0.0
     */
    public function add_settings_field_cb( $args ) {
        extract( $args );

        switch( $section ) {

            case 'display-for':
                $post_types = $this->get_post_types();

                foreach( $post_types as $post ) {
                    echo "<label><input type='checkbox' name='{$this->option_name}[display_for][]' value='{$post->name}'" . ( ( in_array( $post->name, (array) $this->options['display_for'] ) ) ? ' checked' : '' ) . ">{$post->labels->name}</label><br>";
                }
                break;

            case 'background-color':
                $bg_color     = ( $this->options['bg_color'] ) ? $this->options['bg_color'] : '#f2dede';
                $border_color = ( $this->options['border_color'] ) ? $this->options['border_color'] : '#decaca';

                echo "<input type='text' class='fs-color-picker' data-el='bg-color' value='{$bg_color}' name='{$this->option_name}[bg_color]'>";
                echo "<input type='hidden' class='fs-border-color' name='{$this->option_name}[border_color]' value='{$border_color}'>";
                break;

            case 'text-color':
                $text_color = ( $this->options['text_color'] ) ? $this->options['text_color'] : '#a94442';
                echo "<input type='text' class='fs-color-picker' data-el='text-color' value='{$text_color}' name='{$this->option_name}[text_color]'>";
                break;

            case 'preview':
                echo '<p class="outdated-post-notice">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus laoreet magna ut convallis varius.</p>';
        }
    }

    /**
     * Return all needed post types
     *
	 * @since    1.0.0
     *
     * @return array Post types
     */
    private function get_post_types() {
        $args = array(
                    'public' => true
                );

        $post_types = get_post_types( $args, 'objects' );
        unset( $post_types['attachment'] ); //DO NOT WANT!
        return $post_types;
    }

	/**
     * Register custom meta boxes
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fs_outdated_post_notice',
            'Outdated Post Notice',
            array( $this, 'add_meta_box_cb' ),
            'post',
            'normal',
            'high'
        );
    }

    /**
     * Output the meta boxes where applicable
     *
	 * @since    1.0.0
     */
    public function add_meta_box_cb( $post ) {

        wp_nonce_field( plugin_basename( __FILE__ ), 'outdated_post_notice_nonce' );

        $_enabled = (bool) get_post_meta( $post->ID, 'outdated_post_notice_enabled', true );
        $_message = get_post_meta( $post->ID, 'outdated_post_notice_message', true );
        $_days    = (int) get_post_meta( $post->ID, 'outdated_post_notice_days', true );
        $_tmpl    = $this->get_templating_data();

        $html = array();

        /* Enable toggle */
        $html[] = '<label>';
            $html[] = '<input type="checkbox" name="outdated_post_notice_enabled" value="1" class="outdated-post-notice-enabled" id="outdated-post-notice-enabled"' . ( $_enabled ? ' checked' : '' ) . '>';
        $html[] = 'Enable outdated post notice for this post after </label>';

        $html[] = '<input type="number" min="1" step="1" class="outdated-post-notice-days" name="outdated_post_notice_days" value="' . $_days . '"' . ( $_enabled ? '' : ' readonly="readonly"' ) . ' required> days or more.';
        /* Enable toggle */

        /* Templating option */
        $html[] = '<h4>Templating options</h4>';
        $html[] = '<p>Placing this variable in your message will output the corresponding value. As an example, typing your message as<br><code>This post was last updated {days} days ago</code><br>will output<br><code>This post was last updated ' . $_tmpl['{days}'] . ' days ago</code></p>';

        $html[] = '<h4>Available variables with its real time value</h4>';
        foreach( $_tmpl as $_code => $_tpl ) {
            $html[] = '<code>' . $_code . '</code>: ' . $_tpl;
            $html[] = '<br>';
        }
        $html[] = '<br>';
        /* End Templating Option */

        /* Message input */
        $html[] = '<textarea class="outdated-post-notice-message" name="outdated_post_notice_message" placeholder="' . 'Enter your outdated post message here. HTML tags can be used.' . '" style="width:100%;"' . ( $_enabled ? '' : ' readonly="readonly"' ) . ' required>' . $_message . '</textarea>';
        /* Message input */

        /* Preview */
        $html[] = '<h4>Preview</h4>';
        $html[] = '<p class="outdated-post-notice-preview" style="' . $this->styles . '">';

            if( '' == $_message ) {
                $html[] = 'Please write your notice first.';
            } else {
                $html[] = $_message;
            }

        $html[] = '</p>';
        /* Preview */

        echo implode( '', $html );
    }

    /**
     * Save custom meta key to the database
     *
     * @since    1.0.0
     *
     * @param  int $post_id Post ID
     *
     * @return void
     */
    public function save_post( $post_id ) {

        if( isset( $_POST['outdated_post_notice_nonce'] ) && isset( $_POST['post_type'] ) ) {

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            if( ! wp_verify_nonce( $_POST['outdated_post_notice_nonce'], plugin_basename( __FILE__ ) ) ) {
                return;
            }

            if( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }

            $_enabled = isset( $_POST['outdated_post_notice_enabled'] ) ? (int) $_POST['outdated_post_notice_enabled'] : 0;
            $_message = isset( $_POST['outdated_post_notice_message'] ) ? $_POST['outdated_post_notice_message'] : '';
            $_days    = isset( $_POST['outdated_post_notice_days'] ) ? $_POST['outdated_post_notice_days'] : '';

            update_post_meta( $post_id, 'outdated_post_notice_enabled', $_enabled );
            update_post_meta( $post_id, 'outdated_post_notice_message', $_message );
            update_post_meta( $post_id, 'outdated_post_notice_days', $_days );
        }
    }

    /**
     * Append notice to post content where applicable
     *
     * @since    1.0.0
     *
     * @param  string $content Post content
     *
     * @return string          Post content
     */
    public function the_content( $content ) {

        $_enabled =(bool) get_post_meta( get_the_ID(), 'outdated_post_notice_enabled', true );
        $_message = get_post_meta( get_the_ID(), 'outdated_post_notice_message', true );
        $_days    = (int) get_post_meta( get_the_ID(), 'outdated_post_notice_days', true );
        $_tmpl    = $this->get_templating_data();

        $difference = $this->get_templating_data( get_the_ID() );
        $difference = $difference['{days}'];

        if(
            $_enabled &&
            '' !== $_message &&
            $difference >= $_days &&
            $difference !== 0
        ) {

            $post_message = '<p style="' . $this->styles . '">';
                $post_message .= $_message;
            $post_message .= '</p>';

            $post_message = str_replace( array_keys( $_tmpl ), array_values( $_tmpl ), $post_message );

            if( is_singular() && is_main_query() ) {
                $content = $post_message . $content;
            }
        }

        return $content;
    }

    private function get_templating_data() {

        $date_now   = new DateTime( current_time('mysql') );
        $date_old   = new DateTime( get_the_modified_time() );
        $difference = $date_old->diff( $date_now );
        $difference = $difference->days;

        $data = array(
                    '{days}'          => $difference,
                    '{human_diff}'    => human_time_diff( get_the_modified_time('U'), current_time('timestamp') ),
                    '{last_modified}' => get_the_modified_time()
                );

        return $data;
    }

}