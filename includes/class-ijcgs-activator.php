<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    IJcgs
 * @subpackage IJcgs/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class IJcgs_Activator {

	/**
	 * Installed and reinstall date;
	 *
	 * this is important for tracking  and time base notification;
	 * 
	 * @since    1.0.0
	 */
	public static function activate() {
		# Stop Duala Installation or aka Error Handler 
		$active_plugins = get_option( 'active_plugins');
		
		if ( in_array('ijcgs/ijcgs.php' , $active_plugins )) {
			die('<h3>Please uninstall & remove the Free version of this plugin before installing the Professional version ! </h3>');
		}

		# Setting the Instal time 
		$installed = get_option("ijcgs_installed");

		if ( ! $installed ){
			update_option("ijcgs_installed", time());				# first time installed date;
		}else{
			update_option("ijcgs_re_installed", time());			# last time installed date;
		}
	}
}

