<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       javmah.com
 * @since      1.0.0
 * @package    IJcgs
 * @subpackage IJcgs/admin
 * @author     javmah <jaedmah@gmail.com>
 */
class IJcgs_Settings {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * The events object .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private  $events;
	/**
	 * The events object's eventsAndTitles array .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $version    The current version of this plugin.
	 */
	public $eventsAndTitles = array();	
	/**
	 * Google Service Account  client_id 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */	
	public $client_id;
	/**
	 * Google Service Account  client_secret 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */	
	public $client_secret;
	/**
	 * Google Service Account Credentials  
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $version    The current version of this plugin.
	 */	
	public $credentials = array();
	/**
	 * Google Service Account Credentials client_id 
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $version    The current version of this plugin.
	 */	
	public $googleSheet;

	/**
	 * Common methods used in the all the classes 
	 * @since    3.6.0
	 * @var      object    $version    The current version of this plugin.
	 */	
	public $common;

	/**
	 * Initialize the class and set its properties.
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $eventsObj, $googleSheet, $common ){
		$this->plugin_name 		= $plugin_name;				# Name of the Plugin setting for this Class
		$this->version 			= $version;					# Version of this Plugin setting for this Class
		$this->events 			= $eventsObj;				# Events of This Plugin setting for this Class
		$this->googleSheet 		= $googleSheet;				# Passing $googleSheet object
		$this->common 			= $common;					# Passing common methods object
	}

	# Admin menu init
	public function ijcgs_settings_menu(){
		add_submenu_page('ijcgs', __('Settings','ijcgs' ), __('Settings', 'ijcgs'),'manage_options','ijcgs-settings', array( $this,'ijcgs_settings_view'));
	}

	public function ijcgs_settings_notices(){
		// echo "<pre>";

		
		// echo "</pre>";
	}

	/**
     * AKA URL routers , And Settings And Log page view Page , Related to menu , menu Page.
     * @uses  ijcgs_settings_menu function.
     */
	public function ijcgs_settings_view(){
		# Change the Code  || it should be Not like This Way 
		if ( ! @fsockopen('www.google.com', 80 ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__,"914","ERROR: no internet connection ! from settings view page !");
			echo"<h3> No internet connection. Sorry ! you can't see the settings or log . </h3>";
			return; 
		}
		# URL param 
		$action 		 = ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) ? $_GET['action'] : false ;
		# Getting Google Service account credential
		$credential 	 = get_option( 'ijcgs_google_credential', false );
		$token 			 = get_option( 'ijcgs_google_token', false );
		# Change the log display status
		$logStatusOption = get_option( 'ijcgs_logStatus', false );
		
		# Checking Token Validation starts
		if ( $token ) {
			# Checking the Expiration time || if Expired Do below Code 
			if ( isset( $token['expires_in'] ) && time() > $token['expires_in'] ) {
				# if there is a credential
				if ( $credential ) {
					# Create New Token and Save the Toke to  $new_token var
					$new_token = $this->googleSheet->ijcgs_token( $credential );
					# if token created successfully 
					if ( $new_token[0] ) {
						# Change The Token Info; specially expires_in time; adding current time with expires_in time
						$new_token[1]['expires_in'] = $new_token[1]['expires_in'] + time();
						# coping The Token to Old google_token array
						$token = $new_token[1];
						# Save new token to Options
						update_option( 'ijcgs_google_token', $token );
					} else {
						$this->common->ijcgs_log( get_class($this), __METHOD__,"701", "ERROR: on creating token. " . json_encode( $new_token ) );
					}
				} else {
					$this->common->ijcgs_log( get_class($this), __METHOD__,"701", "ERROR: get_option('ijcgs_google_credential'); is FALSE or Empty" );
				}
			}
		} else {
			$this->common->ijcgs_log( get_class($this), __METHOD__,"701", "ERROR: get_option('ijcgs_google_token'); is FALSE or Empty" );
		}
		# Checking Token validation ends

		# Routing Starts
		# if it is log then User Will Go this True side, to see the log 
		if( $action == 'log' ){
			# For Log Page 
			echo"<div class='wrap'>";
				echo"<h1 class='wp-heading-inline'>  Log Page ";
					if( ! $logStatusOption  OR $logStatusOption == 'enable'  ){
						echo"<span onclick='window.location=\"admin.php?page=ijcgs-settings&action=logStatus\"' ><code>Log status <input type='checkbox' checked=checked ></code></span>&#32;";
					} else {
						echo"<span style='color:red;' onclick='window.location=\"admin.php?page=ijcgs-settings&action=logStatus\"' ><code>Log status <input type='checkbox' ></code></span>&#32; ";
					}

					echo"<code>Last 200 log</code> &#32;&#32; ";
					echo"<code>V". esc_html($this->version) ."</code> &#32;&#32; <code>". esc_html($this->events->ijcgs_integrations()[2]) ." Active Integration</code> &#32;&#32; <code>". esc_html($this->events->ijcgs_integrations()[3]) ." Hold Integration</code>";
				echo"</h1>";
			
				$ijcgs_logs = get_posts( array('post_type' => 'ijcgs_log', 'order' => 'DESC', 'posts_per_page' => -1) );
				$i = 1 ;
				foreach ( $ijcgs_logs as $key => $log ) {
					$post_excerpt = json_decode( $log->post_excerpt  );
					if ( $log->post_title == 200 ) {
						echo"<div class='notice notice-success inline'>";
					} else {
						echo"<div class='notice notice-error inline'>";
					}
					echo"<p><span class='ijcgs-circle'>".esc_html($log->ID);
					echo" .</span>";
					echo "<code>" . esc_html($log->post_title) . "</code>";
					echo "<code>";
					if ( isset( $post_excerpt->file_name, $post_excerpt->function_name ) ){
						echo esc_html($post_excerpt->file_name)  . " | " . esc_html($post_excerpt->function_name);
					}
					echo "</code>";
					echo esc_html( $log->post_content );
					echo" <code>". esc_html($log->post_date)  ."</code>";
					echo"</p>";
					echo"</div>";
					$i++ ;
				}
			echo"</div>";
			
		} elseif ( $action == 'service-account-help' ){
			# for service-account-help slug !
			require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/ijcgs-service-ac-help-display.php';
		} elseif ( $action == 'logStatus' ){
			
			if(! $logStatusOption  OR  $logStatusOption == 'enable' ){
				
				# disabling the log
				update_option( 'ijcgs_logStatus', 'disable' );
				
				# Deleting All logs 
				$ijcgs_logs = get_posts( array( 'post_type' => 'ijcgs_log', 'posts_per_page' => -1 ) );
				foreach ($ijcgs_logs as $key =>  $log ) {
					wp_delete_post($log->ID, true);
				}
				
			} else {
				update_option( 'ijcgs_logStatus', 'enable' );
			}

			# redirecting to the log page again;
			wp_redirect(admin_url('/admin.php?page=ijcgs-settings&action=log'));
		} else {
			# For Settings View , If Not log or Help || Default 
			require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/ijcgs-settings-display.php';
		}
	}

	/**
     * Saving Settings Form Submission, Creating token and other Works.
     * @uses 			   Settings Page 
    */
	public function ijcgs_google_settings(){
		# check set or not
		$credential = ( isset( $_POST['credential'] ) && !empty( $_POST['credential']) ) ? json_decode( stripslashes( $_POST['credential'] ), true ) : false ;
		# Check & Balance 
		if ( $credential  &&  wp_verify_nonce( $_POST['nonce'], 'ijcgs-google-nonce' ) ) {
			# check for vitals
			if ( isset( $credential['private_key'], $credential['client_email'] ) ) {
				# Creating a Token & Saving it ;
				$token = $this->googleSheet->ijcgs_token( $credential );
				# Check and Balance 
				if ( $token[0] ) {
					# Change The Token Info
					$token[1]['expires_in'] = time() + $token[1]['expires_in'];
					# Save Option Table
					update_option( 'ijcgs_google_credential',	$credential );
					update_option( 'ijcgs_google_token', 	  	$token[1] );
					#
				} else {
					#
					$this->common->ijcgs_log( get_class($this), __METHOD__, "702", "ERROR: false credential ! Google said so ;-D ." . json_encode( $token ) );
					wp_redirect( 'admin.php?page=ijcgs-settings&msg=false' );
				}
			} else {
				# if credential vitals are empty or Missing 
				$this->common->ijcgs_log( get_class($this), __METHOD__, "703", "ERROR:  private_key or client_email is Not set !");
				wp_redirect( 'admin.php?page=ijcgs-settings&msg=false' );
			}
		} else {
			# if credential vitals are empty or Missing 
			$this->common->ijcgs_log( get_class($this), __METHOD__, "705", "ERROR: Something missing when you copied the key ! that not JSON.");
			wp_redirect( 'admin.php?page=ijcgs-settings&msg=false' );
		}

		# Delete Credential from option Table
		if ( isset( $_GET['deleteCredential'] ) &&  wp_verify_nonce( $_GET['nonce'], 'ijcgs-google-nonce-delete' ) ) {
			delete_option( "ijcgs_google_token");
			delete_option( "ijcgs_google_credential");
		}

		# redirecting 
		wp_redirect( 'admin.php?page=ijcgs-settings&msg=success' );
	}

	/**
     * Removing Logs From Database after 200 
     * @param array  		No        	Data array.
     * @uses 			    Wp Admin Footer Hook
    */
	public function ijcgs_remove_log( ){
		$ijcgs_logs = get_posts( array( 'post_type' => 'ijcgs_log', 'posts_per_page' => -1 ) );
		if ( count( $ijcgs_logs ) > 200 ){
			foreach ($ijcgs_logs as $key =>  $log ) {
				if (  $key > 200 ){
					wp_delete_post($log->ID, true);
				}
			}
		}
	}

}
