<?php
/**
 * This is a Common utility Methods class.
 * All those Methods are used in many classes
 * @link       javmah.com
 * @since      3.6.0
 * @package    IJcgs
 * @subpackage IJcgs/admin
 * @author     javmah <jaedmah@gmail.com>
 */
class IJcgs_Common {
	/**
	 * The ID of this plugin.
	 * @since    3.6.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    3.6.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	private $version;
	/**
	 * The common object.
	 * @since    3.6.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
	public function __construct( $plugin_name, $version){
		$this->plugin_name 		= $plugin_name;				# Name of the Plugin setting for this Class
		$this->version 			= $version;					# Version of this Plugin setting for this Class
	}

	/**
	 * LOG ! For Good , This the log Method 
	 * @since      3.6.0
	 * @param      string    $file_name       	File Name . Use  [ get_class($this) ]
	 * @param      string    $function_name     Function name.	 [  __METHOD__  ]
	 * @param      string    $status_code       The name of this plugin.
	 * @param      string    $status_message    The version of this plugin.
	*/
	public function ijcgs_log($file_name = '', $function_name = '', $status_code = '', $status_message = ''){
		# Log status
		$logStatusOption = get_option( 'ijcgs_logStatus', false );
		# check log status 
		if(  $logStatusOption  AND  $logStatusOption == 'disable' ){
			return  array( FALSE, "ERROR: Log is disable." ); 
		} 
		# Check and Balance 
		if ( empty( $status_code ) or empty( $status_message ) ){
			return  array( FALSE, "ERROR: status_code OR status_message is Empty");
		}
		# Post Excerpt 
		$post_excerpt  = json_encode( array( "file_name" => esc_sql($file_name), "function_name" => esc_sql($function_name) ) );
		# Inserting into the DB
		global $wpdb;
		$sql 	 = "INSERT INTO {$wpdb->prefix}posts (post_content, post_title, post_excerpt, post_type) VALUES ( '" . esc_sql($status_message) . "','" . esc_sql($status_code) . "','" . esc_sql($post_excerpt) . "', 'ijcgs_log' )";
		$results = $wpdb->get_results( $sql );
		
		return  array( TRUE, "SUCCESS: Successfully inserted to the Log" ); 
	}

	/**
     * Testing Common Class; this Method is a variadic Method so it can get all kind of data;
     * @param array  		Data  or Data array optional.
     * @param string  		Data  or Data array optional.
     * @param int  			Data  or Data array optional.
     * @uses 			    Wp Admin Footer Hook
    */
	public function ijcgs_common_test( ...$data ){
		?>
			<div class="notice notice-success is-dismissible">
				<?php
					if( ! empty($data) ){
						echo"<pre>";
						print_r($data);
						echo"</pre>";
					}else{	
						echo"<br>Common test function successfully called.<br><br>";
					}
				?>
			</div>
    	<?php
	}

}
