<?php
/**
 * The core plugin class.
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 *
 * @since      1.0.0
 * @package    IJcgs
 * @subpackage IJcgs/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class IJcgs {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      IJcgs_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {
		
		if( defined( 'WPGSI_VERSION' ) ){
			$this->version = WPGSI_VERSION;
		} else {
			$this->version = '3.6.1';
		}

		$this->plugin_name = 'ijcgs';

		$this->load_dependencies();

		$this->set_locale();

		$this->define_admin_hooks();

		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - IJcgs_Loader. Orchestrates the hooks of the plugin.
	 * - IJcgs_i18n. Defines internationalization functionality.
	 * - IJcgs_Admin. Defines all hooks for the admin area.
	 * - IJcgs_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	*/
	private function load_dependencies(){
		/**
		 * The class for common methods that are used in many different Classes.
		*/ 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ijcgs-common.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ijcgs-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ijcgs-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ijcgs-events.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ijcgs-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ijcgs-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ijcgs-update.php';

		/**
		 * The class responsible for defining all actions that occur in the Inclued Google .
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ijcgs-google-sheet.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ijcgs-public.php';
		
		$this->loader = new IJcgs_Loader();
	}
	

	/**
	 * Define the locale for this plugin for internationalization.
	 * Uses the IJcgs_i18n class in order to set the domain and to register the hook with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	*/
	private function set_locale() {
		$plugin_i18n = new IJcgs_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 * @since    1.0.0
	 * @access   private
	*/
	private function define_admin_hooks(){
		$common 	  	= new IJcgs_common($this->get_plugin_name(), $this->get_version() );																# Common class object 
		$googleSheet  	= new IJcgs_Google_Sheet( $this->get_plugin_name(), $this->get_version(), $common );												# GOOGLE Service Account and Sheet API 
		
		$ijcgs_events 	= new IJcgs_Events( $this->get_plugin_name(), $this->get_version(),  $common );														# Event class object 												    
		$this->loader->add_action( 'user_register', 	 	 		  			$ijcgs_events, 'ijcgs_wordpress_newUser', 100, 1 );							# New User Event [user_register]
		$this->loader->add_action( 'profile_update',		 		  			$ijcgs_events, 'ijcgs_wordpress_profileUpdate', 100, 2 );					# Update User Event [profile_update]
		$this->loader->add_action( 'delete_user', 			 		  			$ijcgs_events, 'ijcgs_wordpress_deleteUser', 100, 1 );  					# Delete User Event [delete_user]
		$this->loader->add_action( 'wp_login', 			   	 		  			$ijcgs_events, 'ijcgs_wordpress_userLogin', 100, 2 );						# User Logged In  [wp_login]
		$this->loader->add_action( 'clear_auth_cookie', 	 		  			$ijcgs_events, 'ijcgs_wordpress_userLogout', 100, 1 );						# User Logged Out [wp_logout] 
		$this->loader->add_action( 'save_post', 			 		  			$ijcgs_events, 'ijcgs_wordpress_post', 100, 3 );							# Wordpress Post  || Fires once a post has been saved. || 3 param 1.post_id 2.post 3.updates
		$this->loader->add_action( 'comment_post', 			 		  			$ijcgs_events, 'ijcgs_wordpress_comment', 100, 3 );							# Wordpress comment_post  || Fires once a comment_post has been saved TO DB.
		$this->loader->add_action( 'edit_comment', 			 		  			$ijcgs_events, 'ijcgs_wordpress_edit_comment', 100, 2 );					# Wordpress comment_post  || Fires once a comment_post has been saved TO DB.
		$this->loader->add_action( 'transition_post_status', 		  			$ijcgs_events, 'ijcgs_woocommerce_product', 100, 3 );						# WooCommerce  Product save_post_product
		$this->loader->add_action( 'woocommerce_order_status_changed',			$ijcgs_events, 'ijcgs_woocommerce_order_status_changed', 100, 3 );			# Woocommerce Order Status Changed
		$this->loader->add_action( 'woocommerce_new_order', 	 	  			$ijcgs_events, 'ijcgs_woocommerce_new_order_admin', 100, 1 );				# WooCommerce New Order
		$this->loader->add_action( 'woocommerce_thankyou', 	 	  				$ijcgs_events, 'ijcgs_woocommerce_new_order_checkout', 100, 1 );			# WooCommerce New Order
		$this->loader->add_action( 'wpcf7_before_send_mail', 		  			$ijcgs_events, 'ijcgs_cf7_submission');										# CF7 Submission a New Form 
		$this->loader->add_action( 'ninja_forms_after_submission',    			$ijcgs_events, 'ijcgs_ninja_forms_after_submission', 100, 1 );				# Ninja form Submission a New Form 
		$this->loader->add_action( 'frm_after_create_entry', 		  			$ijcgs_events, 'ijcgs_formidable_after_save', 30, 2 );						# formidable after create form data entry to DB
		$this->loader->add_action( 'wpforms_process', 		  		  			$ijcgs_events, 'ijcgs_wpforms_process', 30, 3 );							# formidable after create form data entry to DB
		$this->loader->add_action( 'weforms_entry_submission', 		    		$ijcgs_events, 'ijcgs_weforms_entry_submission', 100, 4  );					# weforms after create form data entry to DB				
		$this->loader->add_action( 'gform_after_submission', 		    		$ijcgs_events, 'ijcgs_gravityForms_after_submission', 100, 2  );			# gravityForms after form submission			
		$this->loader->add_action( 'forminator_custom_form_submit_field_data', 	$ijcgs_events, 'ijcgs_forminator_custom_form_submit_field_data', 100, 2 );	# forminator custom form submit field data		
		$this->loader->add_action( 'fluentform_before_submission_confirmation', $ijcgs_events, 'ijcgs_fluentform_before_submission_confirmation', 20, 3);	# fluent form submit field data	
		$this->loader->add_action( 'admin_notices',  							$ijcgs_events, 'ijcgs_event_notices');	

		$plugin_admin = new IJcgs_Admin( $this->get_plugin_name(), $this->get_version(), $googleSheet, $common );
		$this->loader->add_action( 'admin_enqueue_scripts', 					$plugin_admin, 'ijcgs_enqueue_styles',50 );									# enqueue style sheet 
		$this->loader->add_action( 'admin_enqueue_scripts', 					$plugin_admin, 'ijcgs_enqueue_scripts',50 );								# enqueue_scripts Javascript 
		$this->loader->add_action( 'admin_menu', 								$plugin_admin, 'ijcgs_admin_menu' );										# Menu Page
		$this->loader->add_action( 'admin_post_ijcgs_Integration', 				$plugin_admin, 'ijcgs_save_integration' );									# save integration
		$this->loader->add_action( 'wp_ajax_ijcgs_WorksheetColumnsTitle',		$plugin_admin, 'ijcgs_WorksheetColumnsTitle' );								# AJAX  || function name is [ ijcgs_ajax ] this Will Handle 2nd Part of Connection Form 
		$this->loader->add_action( 'ijcgs_khatas',  							$plugin_admin, 'ijcgs_SendToGS', 10, 4 );									# Core event Function 
		$this->loader->add_action( 'admin_notices',  							$plugin_admin, 'ijcgs_admin_notices');										# Admin notice For test And Debug 
		
		$ijcgs_settings = new IJcgs_Settings( $this->get_plugin_name(), $this->get_version(), $ijcgs_events, $googleSheet, $common );
		$this->loader->add_action( 'admin_menu', 					 			$ijcgs_settings, 'ijcgs_settings_menu' );									# Sub-Menu Page
		$this->loader->add_action( 'admin_post_google_settings',	 			$ijcgs_settings, 'ijcgs_google_settings' );									# Settings Form Submission || Save google Forms  ||
		$this->loader->add_action( 'admin_footer',  				 			$ijcgs_settings, 'ijcgs_remove_log' );										# Removing Loge From Database || After 100 
		$this->loader->add_action( 'admin_notices',  							$ijcgs_settings, 'ijcgs_settings_notices');									# Admin notice  For test And Debug 
		
		$ijcgs_update = new IJcgs_Update( $this->get_plugin_name(), $this->get_version(), $googleSheet, $common );											# POST type update
		$this->loader->add_action( 'rest_api_init',  							$ijcgs_update, 'ijcgs_register_rest_route');								# Registering REST END Point, This Hook Will register Two Hooks, Two End point.  -accept -updates
		$this->loader->add_action( 'admin_notices',  							$ijcgs_update, 'ijcgs_update_notices');										# Admin notice  For test And Debug 	
	}
	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 * @since    1.0.0
	 * @access   private
	*/
	private function define_public_hooks() {
		$plugin_public = new IJcgs_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 * @since    1.0.0
	*/
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	*/
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 * @since     1.0.0
	 * @return    IJcgs_Loader   Orchestrates the hooks of the plugin.
	*/
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	*/
	public function get_version() {
		return $this->version;
	}
}
