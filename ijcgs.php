<?php

/**
 * @link              
 * @since             1.0.0
 * @package           IJcgs
 *
 * @wordpress-plugin
 * Plugin Name:       Spreadsheet Integration – Connect WordPress, WooCommerce & Most popular Form Plugins With  Google Sheets 
 * Plugin URI:        https://wordpress.org/plugins/ijcgs
 * Description:       Spreadsheet Integration, Connects WordPress events and most popular plugin with  Google Sheets via API. 
 * Version:           3.6.1
 * Author:            javmah
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ijcgs
 * Domain Path:       /languages
 */
# If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
# freemius Starts

if ( function_exists( 'ijcgs_fs' ) ) {
    ijcgs_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'ijcgs_fs' ) ) {
        // ... Freemius integration snippet ...
        // Create a helper function for easy SDK access.
        function ijcgs_fs()
        {
            global  $ijcgs_fs ;
            
            if ( !isset( $ijcgs_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
                $ijcgs_fs = fs_dynamic_init( array(
                    'id'             => '5870',
                    'slug'           => 'ijcgs',
                    'premium_slug'   => 'ijcgs-professional',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_e966b3152512a4564903a23c4be4f',
                    'is_premium'     => false,
                    'premium_suffix' => 'professional',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'menu'           => array(
                    'slug'       => 'ijcgs',
                    'first-path' => 'admin.php?page=ijcgs-settings',
                    'support'    => false,
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $ijcgs_fs;
        }
        
        // Init Freemius.
        ijcgs_fs();
        // Signal that SDK was initiated.
        do_action( 'ijcgs_fs_loaded' );
    }
    
    // ... Your plugin's main file logic ...
    /**
     * test purpose s
     */
    add_action( 'activated_plugin', 'save_error_ijcgs' );
    function save_error_ijcgs()
    {
        file_put_contents( dirname( __FILE__ ) . '/error_activation.txt', ob_get_contents() );
    }
    
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'WPGSI_VERSION', '3.6.1' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-ijcgs-activator.php
     */
    function activate_ijcgs()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ijcgs-activator.php';
        IJcgs_Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-ijcgs-deactivator.php
     */
    function deactivate_ijcgs()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ijcgs-deactivator.php';
        IJcgs_Deactivator::deactivate();
    }
    
    # Activation & Deactivation Hooks init
    register_activation_hook( __FILE__, 'activate_ijcgs' );
    register_deactivation_hook( __FILE__, 'deactivate_ijcgs' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-ijcgs.php';
    /**
     * Begins execution of the plugin.
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     * @since    1.0.0
     */
    function run_ijcgs()
    {
        $plugin = new IJcgs();
        $plugin->run();
    }
    
    # 786
    run_ijcgs();
}

# 23 June 2021
# ------------------------------------------------------------.
# Hello, Friend How are you doing? i am doing fine.
# I know  Golang, Python, PHP, Javascript, HTML & CSS. I hade lot of experience in web technology's
# I am from Dhaka, Bangladesh.
# You can contact me with this email : jaedmah@gmail.com
# Thank you & Kindest regards -jav