<?php

/**
 * Define the internationalization functionality.
 * Loads and defines the internationalization files for this plugin
 * @since      1.0.0
 * @package    IJcgs
 * @subpackage IJcgs/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class IJcgs_Admin
{
    /**
     * Events Children titles .
     *
     * @since    1.0.0
     * @access   Public
     * @var      array    $eventsAndTitles    Events list.
     */
    private  $plugin_name ;
    /**
     * Events Children titles .
     * @since    1.0.0
     * @access   Public
     * @var      array    $eventsAndTitles    Events list.
     */
    private  $version ;
    /**
     * Events Children titles.
     * @since    1.0.0
     * @access   Public
     * @var      array    $eventsAndTitles    Events list.
     */
    public  $googleSheet ;
    /**
     * The current Date.
     * @since    1.0.0
     * @access   Public
     * @var      string    $Date    The current version of the plugin.
     */
    public  $Date = "" ;
    /**
     * The current Time.
     * @since    1.0.0
     * @access   Public
     * @var      string    $Time   The current Time.
     */
    public  $Time = "" ;
    /**
     * Events list.
     * @since    1.0.0
     * @access   Public
     * @var      array    $events    Events list.
     */
    public  $events = array() ;
    /**
     * Events Children titles.
     * @since    1.0.0
     * @access   Public
     * @var      array    $eventsAndTitles    Events list.
     */
    public  $eventsAndTitles = array() ;
    # Event Key and Event Title
    /**
     * WooCommerce Order Statuses.
     * @since    1.0.0
     * @access   Public
     * @var      array    $active_plugins     List of active plugins .
     */
    public  $wooCommerceOrderStatuses = array() ;
    /**
     * List of active plugins.
     * @since    1.0.0
     * @access   Public
     * @var      array    $active_plugins     List of active plugins .
     */
    public  $active_plugins = array() ;
    /**
     * Common methods used in the all the classes 
     * @since    3.6.0
     * @var      object    $version    The current version of this plugin.
     */
    public  $common ;
    # Class Constrictors
    public function __construct(
        $plugin_name,
        $version,
        $googleSheet,
        $common
    )
    {
        # Plugin Name
        $this->plugin_name = $plugin_name;
        # WPGSI version
        $this->version = $version;
        # Events
        $this->googleSheet = $googleSheet;
        # Common Methods
        $this->common = $common;
    }
    
    # Register the stylesheets for the admin area.
    public function ijcgs_enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/ijcgs-admin.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    # Register the JavaScript for the admin area.
    public function ijcgs_enqueue_scripts()
    {
        # ============================= 3.4.0 starts =================================
        # Limit The Code only For WPGSI Page So that It will Not slow the Process
        
        if ( get_current_screen()->id == 'toplevel_page_ijcgs' ) {
            # +++++++++++++++++++++++++++++++ Below code should Fix ++++++++++++++++++++++++++++++++++++++++++++
            # There are come Default function for This, So Why Custom  Thing
            # Set date
            # Current Date
            $date_format = get_option( 'date_format' );
            $this->Date = ( $date_format ? current_time( $date_format ) : current_time( 'd/m/Y' ) );
            # Current Time
            $time_format = get_option( 'time_format' );
            $this->Time = ( $date_format ? current_time( $time_format ) : current_time( 'g:i a' ) );
            # Active Plugins, Checking Active And Inactive Plugin
            $this->active_plugins = get_option( 'active_plugins' );
            # ++++++++++++++++++++++++++++++ below Code also Should Change as you see Custom Order Status will not Display +++++++++++++++++++
            # WooCommerce order Statuses
            
            if ( function_exists( "wc_get_order_statuses" ) ) {
                $woo_order_statuses = wc_get_order_statuses();
                # for Woocommerce New orders;
                $this->wooCommerceOrderStatuses['wc-new_order'] = 'WooCommerce New Checkout Page Order';
                # For Default Status
                foreach ( $woo_order_statuses as $key => $value ) {
                    $this->wooCommerceOrderStatuses[$key] = 'WooCommerce ' . $value;
                }
            } else {
                # If Function didn't exist do it
                $this->wooCommerceOrderStatuses = array(
                    "wc-new_order"  => "WooCommerce New Checkout Page Order",
                    "wc-pending"    => "WooCommerce Order Pending payment",
                    "wc-processing" => "WooCommerce Order Processing",
                    "wc-on-hold"    => "WooCommerce Order On-hold",
                    "wc-completed"  => "WooCommerce Order Completed",
                    "wc-cancelled"  => "WooCommerce Order Cancelled",
                    "wc-refunded"   => "WooCommerce Order Refunded",
                    "wc-failed"     => "WooCommerce Order Failed",
                );
            }
            
            # User Starts
            # wordpress user events
            $wordpressUserEvents = array(
                "wordpress_newUser"           => 'Wordpress New User',
                "wordpress_UserProfileUpdate" => 'Wordpress User Profile Update',
                "wordpress_deleteUser"        => 'Wordpress Delete User',
                "wordpress_userLogin"         => 'Wordpress User Login',
                "wordpress_userLogout"        => 'Wordpress User Logout',
            );
            # Inserting User Events to All Events
            $this->events += $wordpressUserEvents;
            # New Code for User
            foreach ( $wordpressUserEvents as $key => $value ) {
                # This is For Free User
                $this->eventsAndTitles[$key] = array(
                    "userID"               => "User ID",
                    "userName"             => "User Name",
                    "firstName"            => "User First Name",
                    "lastName"             => "User Last Name",
                    "nickname"             => "User Nickname",
                    "displayName"          => "User Display Name",
                    "eventName"            => "Event Name",
                    "description"          => "User Description",
                    "userEmail"            => "User Email",
                    "userRegistrationDate" => "User Registration Date",
                    "userRole"             => "User Role",
                    "site_time"            => "Site Time",
                    "site_date"            => "Site Date",
                );
                
                if ( $key == 'wordpress_userLogin' ) {
                    $this->eventsAndTitles[$key]["userLogin"] = "Logged in ";
                    $this->eventsAndTitles[$key]["userLoginTime"] = "Logged in Time";
                    $this->eventsAndTitles[$key]["userLoginDate"] = "Logged in Date";
                }
                
                
                if ( $key == 'wordpress_userLogout' ) {
                    $this->eventsAndTitles[$key]["userLogout"] = "User Logout";
                    $this->eventsAndTitles[$key]["userLogoutTime"] = "Logout Time";
                    $this->eventsAndTitles[$key]["userLogoutDate"] = "Logout Date";
                }
            
            }
            # Post Event array
            $wordpressPostEvents = array(
                'wordpress_newPost'    => 'Wordpress New Post',
                'wordpress_editPost'   => 'Wordpress Edit Post',
                'wordpress_deletePost' => 'Wordpress Delete Post',
                'wordpress_page'       => 'Wordpress Page',
            );
            # Inserting WP Post Events to All Events
            $this->events += $wordpressPostEvents;
            # post loop
            foreach ( $wordpressPostEvents as $key => $value ) {
                # setting wordpress_page profile update events
                if ( $key != 'wordpress_page' ) {
                    # This is For Free User
                    $this->eventsAndTitles[$key] = array(
                        "postID"              => "Post ID",
                        "post_authorID"       => "Post Author ID",
                        "authorUserName"      => "Post Author User name",
                        "authorDisplayName"   => "Post Author Display Name",
                        "authorEmail"         => "Post Author Email",
                        "authorRole"          => "Post Author Role",
                        "post_title"          => "Post Title",
                        "post_date"           => "Post Date",
                        "post_date_gmt"       => "Post Date GMT",
                        "site_time"           => "Site Time",
                        "site_date"           => "Site Date",
                        "post_date_year"      => "Post on Year",
                        "post_date_month"     => "Post on Month",
                        "post_date_date"      => "Post on Date",
                        "post_date_time"      => "Post on Time",
                        "post_content"        => "Post Content",
                        "post_excerpt"        => "Post Excerpt",
                        "post_status"         => "Post Status",
                        "eventName"           => "Event Name",
                        "comment_status"      => "Comment Status",
                        "ping_status"         => "Ping Status",
                        "post_password"       => "Post Password",
                        "post_name"           => "Post Name",
                        "to_ping"             => "To Ping",
                        "pinged"              => "Pinged",
                        "post_modified"       => "Post Modified Date",
                        "post_modified_gmt"   => "Post Modified GMT",
                        "post_modified_year"  => "Post modified Year",
                        "post_modified_month" => "Post modified Month",
                        "post_modified_date"  => "Post modified Date",
                        "post_modified_time"  => "Post modified Time",
                        "post_parent"         => "Post Parent",
                        "guid"                => "Guid",
                        "menu_order"          => "Menu Order",
                        "post_type"           => "Post Type",
                        "post_mime_type"      => "Post Mime Type",
                        "comment_count"       => "Comment Count",
                        "filter"              => "Filter",
                    );
                }
                if ( $key == 'wordpress_page' ) {
                    $this->eventsAndTitles[$key] = array(
                        "postID"              => "Page ID",
                        "post_authorID"       => "Page Author ID",
                        "authorUserName"      => "Page Author User name",
                        "authorDisplayName"   => "Page Author Display Name",
                        "authorEmail"         => "Page Author Email",
                        "authorRole"          => "Page Author Role",
                        "post_title"          => "Page Title",
                        "post_date"           => "Page Date",
                        "post_date_gmt"       => "Page Date GMT",
                        "site_time"           => "Site Time",
                        "site_date"           => "Site Date",
                        "post_date_year"      => "Page on Year",
                        "post_date_month"     => "Page on Month",
                        "post_date_date"      => "Page on Date",
                        "post_date_time"      => "Page on Time",
                        "post_content"        => "Page Content",
                        "post_excerpt"        => "Page Excerpt",
                        "post_status"         => "Page Status",
                        "eventName"           => "Event Name",
                        "comment_status"      => "Comment Status",
                        "ping_status"         => "Ping Status",
                        "post_password"       => "Page Password",
                        "post_name"           => "Page Name",
                        "to_ping"             => "To Ping",
                        "pinged"              => "Pinged",
                        "post_modified"       => "Page Modified",
                        "post_modified_gmt"   => "Page Modified GMT",
                        "post_modified_year"  => "Page modified Year",
                        "post_modified_month" => "Page modified Month",
                        "post_modified_date"  => "Page modified Date",
                        "post_modified_time"  => "Page modified Time",
                        "post_parent"         => "Page Parent",
                        "guid"                => "Guid",
                        "menu_order"          => "Menu Order",
                        "post_type"           => "Page Type",
                        "post_mime_type"      => "Page Mime Type",
                        "comment_count"       => "Comment Count",
                        "filter"              => "Filter",
                    );
                }
            }
            # Loop Ends
            # Comment Starts
            $wordpressCommentEvents = array(
                'wordpress_comment'      => 'Wordpress Comment',
                'wordpress_edit_comment' => 'Wordpress Edit Comment',
            );
            # Inserting comment Events to All Events
            $this->events += $wordpressCommentEvents;
            # setting wordpress comments events
            foreach ( $wordpressCommentEvents as $key => $value ) {
                # For Free User
                $this->eventsAndTitles[$key] = array(
                    "comment_ID"           => "Comment ID",
                    "comment_post_ID"      => "Comment Post ID",
                    "comment_author"       => "Comment Author",
                    "comment_author_email" => "Comment Author Email",
                    "comment_author_url"   => "Comment Author Url",
                    "comment_content"      => "Comment Content",
                    "comment_type"         => "Comment Type",
                    "user_ID"              => "Comment User ID",
                    "comment_author_IP"    => "Comment Author IP",
                    "comment_agent"        => "Comment Agent",
                    "comment_date"         => "Comment Date",
                    "comment_date_gmt"     => "Comment Date GMT",
                    "filtered"             => "Filtered",
                    "comment_approved"     => "Comment Approved",
                    "site_time"            => "Site Time",
                    "site_date"            => "Site Date",
                );
            }
            # Woocommerce
            
            if ( in_array( 'woocommerce/woocommerce.php', $this->active_plugins ) ) {
                # Woo product  Starts
                # WooCommerce Product Event Array
                $wooCommerceProductEvents = array(
                    'wc-new_product'    => 'WooCommerce New Product',
                    'wc-edit_product'   => 'WooCommerce Update Product',
                    'wc-delete_product' => 'WooCommerce Delete Product',
                );
                # Inserting WooCommerce product Events to All Events
                $this->events += $wooCommerceProductEvents;
                # WooCommerce Products
                foreach ( $wooCommerceProductEvents as $key => $value ) {
                    # Default fields
                    $this->eventsAndTitles[$key] = array(
                        "productID"          => "Product ID",
                        "type"               => "Type",
                        "name"               => "Name",
                        "slug"               => "Slug",
                        "date_created"       => "Date created",
                        "date_modified"      => "Date modified",
                        "weight"             => "Weight",
                        "length"             => "Length",
                        "width"              => "Width",
                        "height"             => "Height",
                        "attributes"         => "Attributes",
                        "default_attributes" => "Default attributes",
                        "category_ids"       => "Category ids",
                        "tag_ids"            => "Tag ids",
                        "image_id"           => "Image id",
                        "gallery_image_ids"  => "Gallery image ids",
                        "site_time"          => "Site Time",
                        "site_date"          => "Site Date",
                    );
                }
                # Inserting WooCommerce Order Events to All Events
                $this->events += $this->wooCommerceOrderStatuses;
                # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                #(1) Product Meta s
                #(2) Product Info
                #(3) Product Details
                #(4) Empty Product Place Holder
                # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                # WooCommerce Orders
                foreach ( $this->wooCommerceOrderStatuses as $key => $value ) {
                    # Default fields
                    $this->eventsAndTitles[$key] = array(
                        "orderID"             => "Order ID",
                        "billing_first_name"  => "Billing first name",
                        "billing_last_name"   => "Billing last name",
                        "billing_company"     => "Billing company",
                        "billing_address_1"   => "Billing address 1",
                        "billing_address_2"   => "Billing address 2",
                        "billing_city"        => "Billing city",
                        "billing_state"       => "Billing state",
                        "billing_postcode"    => "Billing postcode",
                        "shipping_first_name" => "Shipping first name",
                        "shipping_last_name"  => "Shipping last name",
                        "shipping_company"    => "Shipping company",
                        "shipping_address_1"  => "Shipping address 1",
                        "shipping_address_2"  => "Shipping address 2",
                        "shipping_city"       => "Shipping city",
                        "shipping_state"      => "Shipping state",
                        "shipping_postcode"   => "Shipping postcode",
                        "site_time"           => "Site Time",
                        "site_date"           => "Site Date",
                        "status"              => "Status",
                        "eventName"           => "Event name",
                    );
                }
            }
            
            # Below are Contact forms
            # Contact Form 7
            $cf7 = $this->cf7_forms_and_fields();
            
            if ( $cf7[0] ) {
                foreach ( $cf7[1] as $form_id => $form_name ) {
                    $this->events[$form_id] = $form_name;
                }
                foreach ( $cf7[2] as $form_id => $fields_array ) {
                    $this->eventsAndTitles[$form_id] = $fields_array;
                }
            }
            
            # For Ninja Form
            $ninja = $this->ninja_forms_and_fields();
            
            if ( $ninja[0] ) {
                foreach ( $ninja[1] as $form_id => $form_name ) {
                    $this->events[$form_id] = $form_name;
                }
                foreach ( $ninja[2] as $form_id => $fields_array ) {
                    $this->eventsAndTitles[$form_id] = $fields_array;
                }
            }
            
            # formidable form
            $formidable = $this->formidable_forms_and_fields();
            
            if ( $formidable[0] ) {
                foreach ( $formidable[1] as $form_id => $form_name ) {
                    $this->events[$form_id] = $form_name;
                }
                foreach ( $formidable[2] as $form_id => $fields_array ) {
                    $this->eventsAndTitles[$form_id] = $fields_array;
                }
            }
            
            # wpforms-lite/wpforms.php
            $wpforms = $this->wpforms_forms_and_fields();
            
            if ( $wpforms[0] ) {
                foreach ( $wpforms[1] as $form_id => $form_name ) {
                    $this->events[$form_id] = $form_name;
                }
                foreach ( $wpforms[2] as $form_id => $fields_array ) {
                    $this->eventsAndTitles[$form_id] = $fields_array;
                }
            }
            
            # weforms/weforms.php
            $weforms = $this->weforms_forms_and_fields();
            
            if ( $weforms[0] ) {
                foreach ( $weforms[1] as $form_id => $form_name ) {
                    $this->events[$form_id] = $form_name;
                }
                foreach ( $weforms[2] as $form_id => $fields_array ) {
                    $this->eventsAndTitles[$form_id] = $fields_array;
                }
            }
            
            # gravity forms/gravity forms.php
            $gravityForms = $this->gravity_forms_and_fields();
            
            if ( $gravityForms[0] ) {
                foreach ( $gravityForms[1] as $form_id => $form_name ) {
                    $this->events[$form_id] = $form_name;
                }
                foreach ( $gravityForms[2] as $form_id => $fields_array ) {
                    $this->eventsAndTitles[$form_id] = $fields_array;
                }
            }
        
        }
        
        # toplevel_page_ijcgs ends Here
        # ============================= 3.4.0 ends ==================================
        # Passing the Data To WPGSI Page
        
        if ( get_current_screen()->id == 'toplevel_page_ijcgs' ) {
            wp_register_script(
                'vue',
                plugin_dir_url( __FILE__ ) . 'js/vue.js',
                '',
                FALSE,
                FALSE
            );
            wp_enqueue_script(
                'ijcgs-admin',
                plugin_dir_url( __FILE__ ) . 'js/ijcgs-admin.js',
                array( 'vue' ),
                '0.1',
                TRUE
            );
            
            if ( isset( $_GET["action"], $_GET["id"] ) ) {
                # getting the integration
                $Integration = $this->ijcgs_getIntegration( sanitize_text_field( $_GET["id"] ) );
                # if There is a integration
                if ( $Integration[0] ) {
                    $frontEnd = array(
                        "ajaxUrl"               => admin_url( 'admin-ajax.php' ),
                        "CurrentPage"           => 'edit',
                        "DataSourceTitles"      => json_encode( $this->events ),
                        "DataSourceFields"      => json_encode( $this->eventsAndTitles ),
                        "IntegrationTitle"      => ( isset( $Integration[1]["IntegrationTitle"] ) ? $Integration[1]["IntegrationTitle"] : '' ),
                        "DataSource"            => ( isset( $Integration[1]["DataSource"] ) ? $Integration[1]["DataSource"] : '' ),
                        "DataSourceID"          => ( isset( $Integration[1]["DataSourceID"] ) ? $Integration[1]["DataSourceID"] : '' ),
                        "Worksheet"             => ( isset( $Integration[1]["Worksheet"] ) ? $Integration[1]["Worksheet"] : '' ),
                        "WorksheetID"           => ( isset( $Integration[1]["WorksheetID"] ) ? $Integration[1]["WorksheetID"] : '' ),
                        "Spreadsheet"           => ( isset( $Integration[1]["Spreadsheet"] ) ? $Integration[1]["Spreadsheet"] : '' ),
                        "SpreadsheetID"         => ( isset( $Integration[1]["SpreadsheetID"] ) ? $Integration[1]["SpreadsheetID"] : '' ),
                        "WorksheetColumnsTitle" => ( isset( $Integration[1]["WorksheetColumnsTitle"] ) ? $Integration[1]["WorksheetColumnsTitle"] : '' ),
                        "Relations"             => ( isset( $Integration[1]["Relations"] ) ? $Integration[1]["Relations"] : '' ),
                        "GoogleSpreadsheets"    => json_encode( $this->ijcgs_GoogleSpreadsheets()[1] ),
                        'nonce'                 => wp_create_nonce( 'ijcgsProNonce' ),
                    );
                }
            } else {
                $frontEnd = array(
                    "ajaxUrl"            => admin_url( 'admin-ajax.php' ),
                    "CurrentPage"        => 'new',
                    "DataSourceTitles"   => json_encode( $this->events ),
                    "DataSourceFields"   => json_encode( $this->eventsAndTitles ),
                    "GoogleSpreadsheets" => json_encode( $this->ijcgs_GoogleSpreadsheets()[1] ),
                    'nonce'              => wp_create_nonce( 'ijcgsProNonce' ),
                );
            }
            
            # Localizing js data to the script
            
            if ( isset( $frontEnd ) && !empty($frontEnd) ) {
                wp_localize_script( 'ijcgs-admin', 'frontEnd', $frontEnd );
            } else {
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "500",
                    "ERROR: frontEnd array is empty ! wp_localize_script has no data to Pass."
                );
            }
        
        }
    
    }
    
    /**
     * Admin menu init
     * @since    	1.0.0
     * @return 	   	array    Integrations details  .
     */
    public function ijcgs_admin_menu()
    {
        add_menu_page(
            __( 'Spreadsheet Integrations', 'ijcgs' ),
            __( 'Spreadsheet Integrations', 'ijcgs' ),
            'manage_options',
            'ijcgs',
            array( $this, 'ijcgs_requestDispatcher' ),
            'dashicons-media-spreadsheet'
        );
    }
    
    /**
     * URL routers for main landing Page 
     * @since    	1.0.0
     * @return 	   	array 	Integrations details.
     */
    public function ijcgs_requestDispatcher()
    {
        $action = ( isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list' );
        $id = ( isset( $_GET['id'] ) ? intval( sanitize_text_field( $_GET['id'] ) ) : 0 );
        # routing to the Pages
        switch ( $action ) {
            case 'new':
                $this->ijcgs_new_integration();
                break;
            case 'edit':
                ( $id ? $this->ijcgs_edit_integration( $id ) : $this->ijcgs_new_integration() );
                break;
            case 'status':
                ( $id ? $this->ijcgs_connection_status( $id ) : $this->ijcgs_connections() );
                break;
            case 'remoteUpdateStatus':
                ( $id ? $this->ijcgs_remoteUpdate_status( $id ) : $this->ijcgs_connections() );
                break;
            case 'remoteUpdate':
                ( $id ? $this->ijcgs_remoteUpdate( $id ) : $this->ijcgs_connections() );
                break;
            case 'delete':
                ( $id ? $this->ijcgs_delete_connection( $id ) : $this->ijcgs_connections() );
                break;
            case 'columnTitle':
                ( $id ? $this->ijcgs_columnTitle( $id ) : $this->ijcgs_connections() );
                break;
            default:
                $this->ijcgs_connections();
                break;
        }
    }
    
    # comments;
    public function ijcgs_admin_notices()
    {
        // echo "<pre>";
        // echo "</pre>";
    }
    
    /**
     * Third party plugin :
     * Checkout Field Editor ( Checkout Manager ) for WooCommerce
     * BETA testing;
     * @since    2.0.0
     */
    public function ijcgs_woo_checkout_field_editor_pro_fields()
    {
        $active_plugins = get_option( 'active_plugins' );
        $woo_checkout_field_editor_pro = array();
        
        if ( in_array( 'woo-checkout-field-editor-pro/checkout-form-designer.php', $active_plugins ) ) {
            $a = get_option( "wc_fields_billing" );
            $b = get_option( "wc_fields_shipping" );
            $c = get_option( "wc_fields_additional" );
            if ( $a ) {
                foreach ( $a as $key => $field ) {
                    
                    if ( isset( $field['custom'] ) && $field['custom'] == 1 ) {
                        $woo_checkout_field_editor_pro[$key]['type'] = $field['type'];
                        $woo_checkout_field_editor_pro[$key]['name'] = $field['name'];
                        $woo_checkout_field_editor_pro[$key]['label'] = $field['label'];
                    }
                
                }
            }
            if ( $b ) {
                foreach ( $b as $key => $field ) {
                    
                    if ( isset( $field['custom'] ) && $field['custom'] == 1 ) {
                        $woo_checkout_field_editor_pro[$key]['type'] = $field['type'];
                        $woo_checkout_field_editor_pro[$key]['name'] = $field['name'];
                        $woo_checkout_field_editor_pro[$key]['label'] = $field['label'];
                    }
                
                }
            }
            if ( $c ) {
                foreach ( $c as $key => $field ) {
                    
                    if ( isset( $field['custom'] ) && $field['custom'] == 1 ) {
                        $woo_checkout_field_editor_pro[$key]['type'] = $field['type'];
                        $woo_checkout_field_editor_pro[$key]['name'] = $field['name'];
                        $woo_checkout_field_editor_pro[$key]['label'] = $field['label'];
                    }
                
                }
            }
        } else {
            return array( FALSE, "ERROR : Checkout Field Editor aka Checkout Manager for WooCommerce is not INSTALLED." );
        }
        
        
        if ( empty($woo_checkout_field_editor_pro) ) {
            return array( FALSE, "ERROR : Checkout Field Editor aka Checkout Manager for WooCommerce is EMPTY no Custom Field." );
        } else {
            return array( TRUE, $woo_checkout_field_editor_pro );
        }
    
    }
    
    /**
     * Main Landing Page . List of Integrations
     * @since    	1.0.0
     * @return 	   	
     */
    public function ijcgs_connections()
    {
        # Adding List table
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ijcgs-list-table.php';
        $credential = get_option( 'ijcgs_google_credential', FALSE );
        # Creating view Page layout
        echo  "<div class='wrap'>" ;
        # if credentials is empty; Show this message to create credential.
        
        if ( !$credential ) {
            echo  "<div class='notice notice-warning inline'>" ;
            echo  "<p> Please integrate Google APIs & Service Account before creating new connection. Get <code><b><a href=" . admin_url( 'admin.php?page=ijcgs-settings&action=service-account-help' ) . " style='text-decoration: none;'> step-by-step</a></b></code> help. This plugin will not work without Google APIs & Service Account. </p>" ;
            echo  "</div>" ;
        }
        
        echo  "<h1 class='wp-heading-inline'> Integrations </h1>" ;
        echo  "<a href=" . admin_url( 'admin.php?page=ijcgs&action=new' ) . " class='page-title-action'>Add New Integration</a>" ;
        # Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions
        echo  "<form id='newIntegration' method='get'>" ;
        # For plugins, we also need to ensure that the form posts back to our current page
        echo  "<input type='hidden' name='page' value='" . esc_attr( $_REQUEST['page'] ) . "' />" ;
        echo  "<input type='hidden' name='ijcgs_nonce' value='" . wp_create_nonce( 'ijcgs_nonce_bulk_action' ) . "' />" ;
        # Now we can render the completed list table
        $ijcgs_table = new IJcgs_List_Table( $this->eventsAndTitles );
        $ijcgs_table->prepare_items();
        $ijcgs_table->display();
        echo  "</form>" ;
        echo  "</div>" ;
        # Caching the integrations
        $integrations = $this->ijcgs_getIntegrations();
        if ( $integrations[0] ) {
            # setting or updating the transient;
            set_transient( 'ijcgs_integrations', $integrations[1] );
        }
    }
    
    /**
     * ijcgs Add new Connections  view page 
     * @since    	1.0.0
     * @return 	   	array 		Integrations details.
     */
    public function ijcgs_new_integration()
    {
        
        if ( @fsockopen( 'www.google.com', 80 ) ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/ijcgs-new-integration-display.php';
        } else {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "501",
                "ERROR: No internet connection."
            );
            echo  "<h3> No internet connection. Sorry ! you can't create a integrations now.</h3>" ;
            return array( FALSE, "ERROR: No internet connection." );
        }
    
    }
    
    /**
     * Edit a Connection view page  
     * @since    	1.0.0
     * @return 	   	array 		Integrations details  .
     */
    public function ijcgs_edit_integration( $id = '' )
    {
        
        if ( @fsockopen( 'www.google.com', 80 ) ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/ijcgs-edit-integration-display.php';
        } else {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "502",
                "ERROR: No internet connection."
            );
            echo  "<h3> No internet connection. Sorry ! you can't edit a integrations now. </h3>" ;
            return array( FALSE, "ERROR: No internet connection." );
        }
    
    }
    
    /**
     * Getting Google Spreadsheets 
     * @since    	1.0.0
     * @return 	   	array    Integrations details.
     */
    public function ijcgs_GoogleSpreadsheets()
    {
        # Internet Connection Testing .
        
        if ( !@fsockopen( 'www.google.com', 80 ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "503",
                "ERROR: No internet connection !"
            );
            return array( FALSE, "ERROR: No internet connection !" );
        }
        
        # Token task Starts
        $credential = get_option( 'ijcgs_google_credential', FALSE );
        $google_token = get_option( 'ijcgs_google_token', FALSE );
        # Checking Token Validation
        if ( $google_token && time() > $google_token['expires_in'] ) {
            # if Credentials & Not empty
            
            if ( $credential ) {
                $new_token = $this->googleSheet->ijcgs_token( $credential );
                # Check & Balance
                
                if ( $new_token[0] ) {
                    # Change The Token Info
                    $new_token[1]['expires_in'] = time() + $new_token[1]['expires_in'];
                    # coping The Token
                    $google_token = $new_token[1];
                    # Save in Options
                    update_option( 'ijcgs_google_token', $new_token[1] );
                } else {
                    echo  "<b> ERROR : false credential ! Google said so ;-D  </b> " ;
                    $this->common->ijcgs_log(
                        get_class( $this ),
                        __METHOD__,
                        "503",
                        "ERROR: from  ijcgs_GoogleSpreadsheets func. " . json_encode( $new_token )
                    );
                }
            
            }
        
        }
        # Token Task Ends
        
        if ( $google_token ) {
            # getting spreadsheets and Worksheets
            $r = $this->googleSheet->ijcgs_spreadsheetsAndWorksheets( $google_token );
            
            if ( isset( $r[0] ) && $r[0] ) {
                return $r;
            } else {
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "504",
                    "ERROR: from ijcgs_spreadsheetsAndWorksheets func. " . json_encode( $r )
                );
                return array( FALSE, array() );
            }
        
        } else {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "505",
                "ERROR: google_token is False. " . json_encode( $google_token )
            );
            return array( FALSE, array() );
        }
    
    }
    
    /**
     * Change connection status;
     * @since    	1.0.0
     * @return 	   	array 		Integrations details  .
     */
    public function ijcgs_connection_status( $id = '' )
    {
        # check the Post type status
        
        if ( get_post( $id )->post_status == 'publish' ) {
            $custom_post = array(
                'ID'          => $id,
                'post_status' => 'pending',
            );
        } else {
            $custom_post = array(
                'ID'          => $id,
                'post_status' => 'publish',
            );
        }
        
        # Keeping Log
        $this->common->ijcgs_log(
            get_class( $this ),
            __METHOD__,
            "200",
            "SUCCESS: ID " . $id . " Integration status  change to ." . get_post( $id )->post_status
        );
        # redirect
        ( wp_update_post( $custom_post ) ? wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=success_from_status_change' ) ) : wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=fail' ) ) );
    }
    
    /**
     * Change remote Update Status;
     * @since    	3.6.0
     * @return 	   	array 		Integrations details  .
     */
    public function ijcgs_remoteUpdate_status( $id = '' )
    {
        # Getting Integrations
        $Integrations = get_post( $id );
        # Check and Balance
        
        if ( $Integrations ) {
            # getting Integration status
            $remoteUpdateStatus = get_post_meta( $id, "remoteUpdateStatus", TRUE );
            
            if ( $remoteUpdateStatus ) {
                # Setting Integration status FALSE
                update_post_meta( $id, "remoteUpdateStatus", FALSE );
                # Keeping Log
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS:  ID " . $id . " remote update status to DISABLED"
                );
                # redirect
                wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=disabled' ) );
            } else {
                # Setting Integration status TRUE
                update_post_meta( $id, "remoteUpdateStatus", TRUE );
                # Keeping Log
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS:  ID " . $id . " remote update status to ENABLED"
                );
                # redirect
                wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=enable' ) );
            }
        
        } else {
            # if Integration ID is not present redirect the user to Integration list page
            wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=fail' ) );
            # Keeping Log
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "420",
                "ERROR: No Integration on  this ID " . $id
            );
        }
    
    }
    
    /**
     * Help and Code for remote update. 
     * This Function Will generate Google App script code For Remote Update.
     * This Function also Show step-by-step help to stepup google App script 
     * @since    	3.6.0
     * @return 	   	array 		Integrations details.
     */
    public function ijcgs_remoteUpdate( $id = '' )
    {
        # Getting Integrations
        $Integrations = get_post( $id );
        # Check and Balance
        
        if ( $Integrations ) {
            # Remote Update Help.
            # Check to see  wp_get_current_user() is exist or not;
            
            if ( !function_exists( 'wp_get_current_user' ) ) {
                echo  "ERROR: wp_get_current_user() is not exist." ;
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "420",
                    "ERROR: wp_get_current_user() is not exist."
                );
            }
            
            # getting Current user Details.
            $current_user = wp_get_current_user();
            # Check and Balance.
            
            if ( !isset( $current_user->data->ID, $current_user->data->user_email ) or empty($current_user->data->user_email) ) {
                echo  "ERROR: user ID or user Email is not set or empty." ;
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "420",
                    "ERROR: user ID or User Email is not set or empty."
                );
            }
            
            # Setting array value.
            $userBase64TokenArr = array();
            # Integration ID
            $userBase64TokenArr['ID'] = $id;
            # User ID
            $userBase64TokenArr['UID'] = $current_user->data->ID;
            # User Email
            $userBase64TokenArr['email'] = $current_user->data->user_email;
            # Creating token;
            $userToken = base64_encode( json_encode( $userBase64TokenArr ) );
            # Check and Balance.
            
            if ( !empty($userToken) ) {
                $sheetData = @json_decode( $Integrations->post_excerpt, TRUE );
                $integrationsTitle = esc_html( $Integrations->post_title );
                $Worksheet = esc_html( $sheetData['Worksheet'] );
                $Spreadsheet = esc_html( $sheetData['Spreadsheet'] );
                $WorksheetID = esc_html( $sheetData['WorksheetID'] );
                $SpreadsheetID = esc_html( $sheetData['SpreadsheetID'] );
                $DataSourceID = esc_html( $sheetData['DataSourceID'] );
                $lock = TRUE;
                # Check and Balance for Free and professional version
                
                if ( in_array( $DataSourceID, array(
                    'wordpress_newPost',
                    'wordpress_editPost',
                    'wordpress_deletePost',
                    'wordpress_page'
                ) ) ) {
                    #  including the View File;
                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/ijcgs-remoteUpdate.php';
                } else {
                }
                
                # weaning message
                if ( $lock and !in_array( $DataSourceID, array(
                    'wordpress_newPost',
                    'wordpress_editPost',
                    'wordpress_deletePost',
                    'wordpress_page'
                ) ) ) {
                    echo  "<br><b><i>We are very sorry. All default WordPress Posts and Pages remote updates are FREE.<br> WooCommerce and Custom post types are in the Professional version. Hope you understand our situation. Thank you for using the Plugin. </i></b>" ;
                }
            } else {
                echo  "ERROR: json_encode or base64_encode error." ;
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "420",
                    "ERROR: json_encode or base64_encode error."
                );
            }
        
        } else {
            wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=fail' ) );
            # Keeping Log.
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "420",
                "ERROR: No Integration on  this ID " . $id
            );
        }
    
    }
    
    /**
     * Delete the Connection;
     * @since    	1.0.0
     * @return 	   	array 		Integrations details  .
     */
    public function ijcgs_delete_connection( $id = '' )
    {
        # insert log
        $this->common->ijcgs_log(
            get_class( $this ),
            __METHOD__,
            "200",
            "SUCCESS: Integration Deleted Successfully. ID " . $id
        );
        # Redirect
        ( wp_delete_post( $id ) ? wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=success' ) ) : wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=fail' ) ) );
        # Reset And Caching the integrations
        $integrations = $this->ijcgs_getIntegrations();
        if ( $integrations[0] ) {
            # setting or updating the transient;
            set_transient( 'ijcgs_integrations', $integrations[1] );
        }
    }
    
    /**
     * Creating Column titles;
     * @since    	1.0.0
     * @return 	   	array 		Integrations details  .
     */
    public function ijcgs_columnTitle( $id = null )
    {
        # get the post with Post ID
        $post = get_post( $id );
        # Check & balance if there is a Post
        
        if ( $post ) {
            # Converting to PHP array from JSON
            $post_content = json_decode( $post->post_content, TRUE );
            $post_excerpt = json_decode( $post->post_excerpt );
            # Replacing Sheet ABC With Event Titles;
            $newArray = array();
            if ( isset( $this->eventsAndTitles[$post_excerpt->DataSourceID] ) ) {
                foreach ( $this->eventsAndTitles[$post_excerpt->DataSourceID] as $key => $value ) {
                    $newArray["{{" . $key . "}}"] = $value;
                }
            }
            $FinalArray = array();
            foreach ( $post_content[1] as $key => $value ) {
                $FinalArray[$key] = strip_tags( strtr( $value, $newArray ) );
            }
            $returns = $this->googleSheet->ijcgs_append_row( $post_excerpt->SpreadsheetID, $post_excerpt->WorksheetID, $FinalArray );
            # Redirect The User With message
            
            if ( $returns[0] ) {
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "200",
                    "SUCCESS: Google spreadsheet column title created, " . json_encode( $returns )
                );
                wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=success' ) );
            } else {
                $this->common->ijcgs_log(
                    get_class( $this ),
                    __METHOD__,
                    "506",
                    "ERROR: Google spreadsheet column title didn't created " . json_encode( array(
                    "ret"           => $returns,
                    "SpreadsheetID" => $post_excerpt->SpreadsheetID,
                    "WorksheetID"   => $post_excerpt->WorksheetID,
                    "FinalArray"    => $FinalArray,
                ) )
                );
                wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=failed' ) );
            }
        
        }
    
    }
    
    /**
     * Save getIntegration Data to Database , New getIntegration and Edit getIntegration use This Function;
     * @since    	1.0.0
     * @return 	   	array 		Integrations details.
     */
    public function ijcgs_save_integration()
    {
        # Setting ERROR status
        $errorStatus = TRUE;
        //
        // It Should be removed From $_POST Array ***
        // unset( $_POST['SpreadsheetAndWorksheet'] );
        //
        # Check and Balance
        
        if ( !isset( $_POST['IntegrationTitle'] ) or empty($_POST['IntegrationTitle']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "507",
                "ERROR: IntegrationTitle is Empty. "
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_IntegrationTitle' ) );
        }
        
        
        if ( !isset( $_POST['DataSource'] ) or empty($_POST['DataSource']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: DataSource name is Empty."
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_DataSource' ) );
        }
        
        
        if ( !isset( $_POST['DataSourceID'] ) or empty($_POST['DataSourceID']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: DataSourceID is Empty."
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_DataSourceID' ) );
        }
        
        
        if ( empty($_POST['Worksheet']) or is_null( $_POST['WorksheetID'] ) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: Worksheet or WorksheetID is Empty. "
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_Worksheet_worksheetID' ) );
        }
        
        
        if ( empty($_POST['Spreadsheet']) or empty($_POST['Spreadsheet']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: Spreadsheet is Empty."
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_Spreadsheet' ) );
        }
        
        
        if ( !isset( $_POST['SpreadsheetID'] ) or empty($_POST['SpreadsheetID']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: SpreadsheetID is Empty. "
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_SpreadsheetID' ) );
        }
        
        
        if ( $_POST['status'] == "edit_Integration" and empty($_POST['ID']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: edit_Integration ID is Empty. "
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=empty_edit_id' ) );
        }
        
        
        if ( empty($_POST['Relation']) or empty($_POST['Relation']) ) {
            $errorStatus = FALSE;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: Relations is Empty."
            );
            wp_redirect( admin_url( '/admin.php?page=ijcgs&action=new&rms=fail_empty_Relation' ) );
        }
        
        # sanitize_text_field
        $ColumnTitle = array_map( 'sanitize_text_field', $_POST['ColumnTitle'] );
        $Relation = array_map( 'sanitize_text_field', $_POST['Relation'] );
        # Save new integration
        
        if ( $_POST['status'] == "new_Integration" and $errorStatus ) {
            # Preparing Post array for DB insert
            $customPost = array(
                'ID'           => '',
                'post_content' => json_encode( array( $ColumnTitle, $Relation ) ),
                'post_title'   => sanitize_text_field( $_POST['IntegrationTitle'] ),
                'post_status'  => 'publish',
                'post_excerpt' => json_encode( array(
                "DataSource"    => sanitize_text_field( $_POST['DataSource'] ),
                "DataSourceID"  => sanitize_text_field( $_POST['DataSourceID'] ),
                "Worksheet"     => sanitize_text_field( $_POST['Worksheet'] ),
                "WorksheetID"   => sanitize_text_field( $_POST['WorksheetID'] ),
                "Spreadsheet"   => sanitize_text_field( $_POST['Spreadsheet'] ),
                "SpreadsheetID" => sanitize_text_field( $_POST['SpreadsheetID'] ),
            ) ),
                'post_name'    => '',
                'post_type'    => 'ijcgsIntegration',
                'menu_order'   => '',
                'post_parent'  => '',
            );
            # Inserting New integration custom Post type
            $post_id = wp_insert_post( $customPost );
            //  Insert the post into the database
        }
        
        # Save edited Integration
        
        if ( $_POST['status'] == "edit_Integration" and !empty($_POST['ID']) and $errorStatus ) {
            # Preparing Post array for status Change
            $customPost = array(
                'ID'           => sanitize_text_field( $_POST['ID'] ),
                'post_content' => json_encode( array( $ColumnTitle, $Relation ) ),
                'post_title'   => sanitize_text_field( $_POST['IntegrationTitle'] ),
                'post_status'  => 'publish',
                'post_excerpt' => json_encode( array(
                "DataSource"    => sanitize_text_field( $_POST['DataSource'] ),
                "DataSourceID"  => sanitize_text_field( $_POST['DataSourceID'] ),
                "Worksheet"     => sanitize_text_field( $_POST['Worksheet'] ),
                "WorksheetID"   => sanitize_text_field( $_POST['WorksheetID'] ),
                "Spreadsheet"   => sanitize_text_field( $_POST['Spreadsheet'] ),
                "SpreadsheetID" => sanitize_text_field( $_POST['SpreadsheetID'] ),
            ) ),
                'post_name'    => '',
                'post_type'    => 'ijcgsIntegration',
                'menu_order'   => '',
                'post_parent'  => '',
            );
            # Updating Custom Post Type
            $post_id = wp_update_post( $customPost );
            // Insert the post into the database
        }
        
        # if There is a Post Id , That Means Post is success fully saved
        
        if ( $post_id and $errorStatus ) {
            # inserting on log
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "200",
                "SUCCESS: Integration saved. " . json_encode( $customPost )
            );
            # Caching integrations to wp set_transient
            $integrations = $this->ijcgs_getIntegrations();
            if ( $integrations[0] ) {
                # setting or updating the Options
                set_transient( 'ijcgs_integrations', $integrations[1] );
            }
            # Redirecting
            wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=success' ) );
            // Redirect User With SUCCESS Note is not With ERROR Note
        } else {
            # Inserting on log
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "507",
                "ERROR: Integration didn't saved. Integration insert fail. " . json_encode( $customPost )
            );
            # redirecting
            wp_redirect( admin_url( '/admin.php?page=ijcgs&rms=fail_insert' ) );
            // Redirect User With SUCCESS Note is not With ERROR Note
        }
    
    }
    
    /**
     * Get getIntegration Data from Database  by there id
     * @since    	1.0.0
     * @param     	int    		Integration id      .
     * @return 	   	array 		Integrations details  .
     */
    public function ijcgs_getIntegration( $IntegrationID = '' )
    {
        # Check IntegrationID is empty or not
        
        if ( empty($IntegrationID) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "508",
                "ERROR: IntegrationID id is Empty."
            );
            // Check Data is Any returns or Not
            return array( FALSE, "ERROR: IntegrationID id is Empty." );
        }
        
        # Check IntegrationID is numeric or not
        
        if ( !is_numeric( $IntegrationID ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "509",
                "ERROR: IntegrationID id is not numeric."
            );
            // Check Data is Any returns or Not
            return array( FALSE, "ERROR: IntegrationID id is not numeric." );
        }
        
        # getting the integration
        $post_data = get_post( $IntegrationID );
        // Check There is a Data in the Database !
        
        if ( empty($post_data) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "510",
                "ERROR: Nothing in the Database on this ID or Empty Data or ID is Wrong !"
            );
            // Check Data is Any returns or Not
            return array( FALSE, "Nothing in the Database on this ID or Empty Data or ID is Wrong !" );
        }
        
        $data = json_decode( $post_data->post_excerpt, TRUE );
        // Getting Data from WP server
        $return_array = array();
        $return_array['IntegrationTitle'] = sanitize_text_field( $post_data->post_title );
        $return_array['DataSource'] = sanitize_text_field( $data['DataSource'] );
        $return_array['DataSourceID'] = sanitize_text_field( $data['DataSourceID'] );
        $return_array['Worksheet'] = sanitize_text_field( $data['Worksheet'] );
        $return_array['WorksheetID'] = sanitize_text_field( $data['WorksheetID'] );
        $return_array['Spreadsheet'] = sanitize_text_field( $data['Spreadsheet'] );
        $return_array['SpreadsheetID'] = sanitize_text_field( $data['SpreadsheetID'] );
        $post_content = json_decode( $post_data->post_content, TRUE );
        $return_array['WorksheetColumnsTitle'] = $post_content[0];
        $return_array['Relations'] = $post_content[1];
        $return_array['Status'] = $post_data->post_status;
        return array( TRUE, $return_array );
    }
    
    /**
     * AJAX events  function for New integration and edit integration , This will supply worksheet column titles 
     * @since    	1.0.0
     * @param     	string    	$SpreadsheetID       The name of this plugin.
     * @param      	string    	$Worksheet    The version of this plugin.
     * @return 	   	string 		This will return json string ,of column titles .
     */
    public function ijcgs_WorksheetColumnsTitle()
    {
        # Testing security nonce Set and Valid test
        
        if ( !isset( $_POST['nonce'] ) or !wp_verify_nonce( $_POST['nonce'], 'ijcgsProNonce' ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "511",
                "ERROR : invalid nonce."
            );
            json_encode( array(
                "status"  => FALSE,
                "message" => "ERROR: invalid nonce.",
            ), TRUE );
            exit;
        }
        
        # Checking  Worksheet is set or not
        
        if ( !isset( $_POST['Worksheet'] ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "512",
                "ERROR : Worksheet is not set."
            );
            json_encode( array(
                "status"  => FALSE,
                "message" => "ERROR: Worksheet is not set.",
            ), TRUE );
            exit;
        }
        
        # Checking  SpreadsheetID is set or not
        
        if ( !isset( $_POST['SpreadsheetID'] ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "513",
                "ERROR : SpreadsheetID is not set."
            );
            json_encode( array(
                "status"  => FALSE,
                "message" => "ERROR: SpreadsheetID is not set.",
            ), TRUE );
            exit;
        }
        
        # Checking  Worksheet is empty or not
        
        if ( empty($_POST['Worksheet']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "514",
                "ERROR : Worksheet is empty !"
            );
            json_encode( array(
                "status"  => FALSE,
                "message" => "ERROR: Worksheet is empty !",
            ), TRUE );
        }
        
        # Checking  SpreadsheetID is empty or not
        
        if ( empty($_POST['SpreadsheetID']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "515",
                "ERROR : SpreadsheetID is empty !"
            );
            json_encode( array(
                "status"  => FALSE,
                "message" => "ERROR: SpreadsheetID is empty !",
            ), TRUE );
        }
        
        $WorksheetName = strip_tags( $_POST['Worksheet'] );
        $SpreadsheetID = sanitize_text_field( $_POST['SpreadsheetID'] );
        $google_token = get_option( 'ijcgs_google_token', FALSE );
        $columnTitle = $this->googleSheet->ijcgs_columnTitle( $WorksheetName, $SpreadsheetID, $google_token );
        # Printing, not returning
        echo  json_encode( $columnTitle ) ;
        exit;
    }
    
    /**
     * Using custom hook sending data to Google spreadsheet 
     * @since    	1.0.0
     * @param     	string    	$plugin_name       The name of this plugin.
     * @param      	string    	$version    The version of this plugin.
     * @return 	   	array 		$columns Array of all the list table columns.
     */
    public function ijcgs_SendToGS(
        $Evt_DataSource,
        $Evt_DataSourceID,
        $data_array,
        $id
    )
    {
        # Don't do anything if there is No internet , As you know it is a Integration Plugin.
        # This Code Should Be Change | Change Code in WooTrello
        
        if ( !@fsockopen( 'www.google.com', 80 ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "516",
                "ERROR: No internet connection."
            );
            return array( FALSE, "ERROR: No internet connection." );
        }
        
        # Token task Starts , Very important . Now token will validate in every event so, nothing will miss on token failure .
        $credential = get_option( 'ijcgs_google_credential', FALSE );
        $google_token = get_option( 'ijcgs_google_token', FALSE );
        # Checking Token Validation
        if ( $google_token && time() > $google_token['expires_in'] ) {
            # if there is a credential
            
            if ( $credential ) {
                # creating new Token
                $new_token = $this->googleSheet->ijcgs_token( $credential );
                # if token is True
                
                if ( $new_token[0] ) {
                    # Change The Token Info
                    $new_token[1]['expires_in'] = time() + $new_token[1]['expires_in'];
                    # coping The Token
                    $google_token = $new_token[1];
                    # Save in Options
                    update_option( 'ijcgs_google_token', $new_token[1] );
                } else {
                    $this->common->ijcgs_log(
                        get_class( $this ),
                        __METHOD__,
                        "517",
                        "ERROR: from  ijcgs_SendToGS func. " . json_encode( $credential )
                    );
                }
            
            }
        
        }
        # Token Task Ends
        $integrations = get_posts( array(
            'post_type'      => 'ijcgsIntegration',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ) );
        # Looping the integrations
        foreach ( $integrations as $integration ) {
            #
            $post_content = json_decode( $integration->post_content, TRUE );
            $post_excerpt = json_decode( $integration->post_excerpt, TRUE );
            #
            $DataSource = $post_excerpt["DataSource"];
            $DataSourceID = $post_excerpt["DataSourceID"];
            $Worksheet = $post_excerpt["Worksheet"];
            $WorksheetID = $post_excerpt["WorksheetID"];
            $Spreadsheet = $post_excerpt["Spreadsheet"];
            $SpreadsheetID = $post_excerpt["SpreadsheetID"];
            $ColumnsTitle = $post_content[0];
            $relation = $post_content[1];
            # Pre-process
            $ArrayKeyAndValue = array();
            foreach ( $data_array as $relationKey => $relationValue ) {
                $ArrayKeyAndValue["{{" . $relationKey . "}}"] = $relationValue;
            }
            # Check the value change depends on type
            $dataWithRelationKey = array();
            foreach ( $relation as $key => $value ) {
                
                if ( is_array( $value ) ) {
                    $dataWithRelationKey[$key] = implode( ", ", $value );
                } else {
                    $dataWithRelationKey[$key] = strtr( $value, $ArrayKeyAndValue );
                }
            
            }
            # Sending Request;
            
            if ( $Evt_DataSourceID == $DataSourceID ) {
                # getting last time this Integrator Occurred TimeStamp, So that i Can Prevent Dual Submission
                # Integration_id , ijcgs_lastFired, New Code After 3.5.0
                $ijcgs_lastFired = (int) get_post_meta( $integration->ID, 'ijcgs_lastFired', TRUE );
                # dualSubmission Prevention
                # lastFired is set and value is Not grater then 301 seconds
                
                if ( $ijcgs_lastFired and time() - $ijcgs_lastFired < 33 ) {
                    $this->common->ijcgs_log(
                        get_class( $this ),
                        __METHOD__,
                        "518",
                        "ERROR: Dual submission Prevented of Integration : <b> " . $integration->ID . " </b> " . json_encode( $dataWithRelationKey )
                    );
                } else {
                    # Send the request
                    $ret = $this->googleSheet->ijcgs_append_row( $SpreadsheetID, $WorksheetID, $dataWithRelationKey );
                    # Check ERROR or SUCCESS
                    
                    if ( $ret[0] ) {
                        $this->common->ijcgs_log(
                            get_class( $this ),
                            __METHOD__,
                            "200",
                            "SUCCESS: okay, on the event . " . json_encode( $ret )
                        );
                        # New Code after 3.5.0
                        # New Code for preventing Dual Submission || saving last Fired time
                        update_post_meta( $integration->ID, 'ijcgs_lastFired', time() );
                    } else {
                        $this->common->ijcgs_log(
                            get_class( $this ),
                            __METHOD__,
                            "519",
                            "ERROR: on sending data . " . json_encode( array(
                            "SpreadsheetID"       => $SpreadsheetID,
                            "WorksheetID"         => $WorksheetID,
                            "dataWithRelationKey" => $dataWithRelationKey,
                            "Google_response"     => $ret,
                        ) )
                        );
                    }
                
                }
            
            }
        
        }
    }
    
    /**
     * This Function will return [wordPress Pages] Meta keys.
     * @since      3.3.0
     * @return     array    This array has two vale First one is Bool and Second one is meta key array.
     */
    public function ijcgs_pages_metaKeys()
    {
        # Global Db object
        global  $wpdb ;
        # Query
        $query = "SELECT DISTINCT({$wpdb->postmeta}.meta_key) \r\n\t\t\t\t\tFROM {$wpdb->posts} \r\n\t\t\t\t\tLEFT JOIN {$wpdb->postmeta} \r\n\t\t\t\t\tON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id \r\n\t\t\t\t\tWHERE {$wpdb->posts}.post_type = 'page' \r\n\t\t\t\t\tAND {$wpdb->postmeta}.meta_key != '' ";
        # execute Query
        $meta_keys = $wpdb->get_col( $query );
        # return Depend on the Query result
        
        if ( empty($meta_keys) ) {
            return array( FALSE, 'Error: Empty! No Meta key exist of the Post type page.' );
        } else {
            return array( TRUE, $meta_keys );
        }
    
    }
    
    /**
     * This Function will return [wordPress Posts] Meta keys.
     * @since      3.3.0
     * @return     array    This array has two vale First one is Bool and Second one is meta key array.
     */
    public function ijcgs_posts_metaKeys()
    {
        # Global Db object
        global  $wpdb ;
        # Query
        $query = "SELECT DISTINCT({$wpdb->postmeta}.meta_key) \r\n\t\t\t\t  \tFROM {$wpdb->posts} \r\n\t\t\t\t\tLEFT JOIN {$wpdb->postmeta} \r\n\t\t\t\t\tON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id \r\n\t\t\t\t\tWHERE {$wpdb->posts}.post_type = 'post' \r\n\t\t\t\t\tAND {$wpdb->postmeta}.meta_key != '' ";
        # execute Query
        $meta_keys = $wpdb->get_col( $query );
        # return Depend on the Query result
        
        if ( empty($meta_keys) ) {
            return array( FALSE, 'ERROR: Empty! No Meta key exist of the Post.' );
        } else {
            return array( TRUE, $meta_keys );
        }
    
    }
    
    /**
     * This Function will return [wordPress Users] Meta keys.
     * @since      3.3.0
     * @return     array    This array has two vale First one is Bool and Second one is meta key array.
     */
    public function ijcgs_users_metaKeys()
    {
        # Global Db object
        global  $wpdb ;
        # Query
        $query = "SELECT DISTINCT( {$wpdb->usermeta}.meta_key ) FROM {$wpdb->usermeta} ";
        # execute Query
        $meta_keys = $wpdb->get_col( $query );
        # return Depend on the Query result
        
        if ( empty($meta_keys) ) {
            return array( FALSE, 'ERROR: Empty! No Meta key exist of users.' );
        } else {
            return array( TRUE, $meta_keys );
        }
    
    }
    
    /**
     * This Function will return [wordPress Users] Meta keys.
     * @since      3.3.0
     * @return     array    This array has two vale First one is Bool and Second one is meta key array.
     */
    public function ijcgs_comments_metaKeys()
    {
        # Global Db object
        global  $wpdb ;
        # Query
        $query = "SELECT DISTINCT( {$wpdb->commentmeta}.meta_key ) FROM {$wpdb->commentmeta} ";
        # execute Query
        $meta_keys = $wpdb->get_col( $query );
        # return Depend on the Query result
        
        if ( empty($meta_keys) ) {
            return array( FALSE, 'ERROR: Empty! No Meta key exist on comment meta.' );
        } else {
            return array( TRUE, $meta_keys );
        }
    
    }
    
    /**
     * This Function will return [WooCommerce Order] Meta keys.
     * @since      3.3.0
     * @return     array    This array has two vale First one is Bool and Second one is meta key array.
     */
    public function ijcgs_wooCommerce_order_metaKeys()
    {
        # Global Db object
        global  $wpdb ;
        # Query
        $query = "SELECT DISTINCT({$wpdb->postmeta}.meta_key) \r\n\t\t\t\t\tFROM {$wpdb->posts} \r\n\t\t\t\t\tLEFT JOIN {$wpdb->postmeta} \r\n\t\t\t\t\tON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id \r\n\t\t\t\t\tWHERE {$wpdb->posts}.post_type = 'shop_order' \r\n\t\t\t\t\tAND {$wpdb->postmeta}.meta_key != '' ";
        # execute Query
        $meta_keys = $wpdb->get_col( $query );
        # return Depend on the Query result
        
        if ( empty($meta_keys) ) {
            return array( FALSE, 'ERROR: Empty! No Meta key exist of the post type WooCommerce Order.' );
        } else {
            return array( TRUE, $meta_keys );
        }
    
    }
    
    /**
     * This Function will return [WooCommerce product] Meta keys.
     * @since      3.3.0
     * @return     array    This array has two vale First one is Bool and Second one is meta key array.
     */
    public function ijcgs_wooCommerce_product_metaKeys()
    {
        # Global Db object
        global  $wpdb ;
        # Query
        $query = "SELECT DISTINCT({$wpdb->postmeta}.meta_key) \r\n\t\t\t\t\tFROM {$wpdb->posts} \r\n\t\t\t\t\tLEFT JOIN {$wpdb->postmeta} \r\n\t\t\t\t\tON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id \r\n\t\t\t\t\tWHERE {$wpdb->posts}.post_type = 'product' \r\n\t\t\t\t\tAND {$wpdb->postmeta}.meta_key != '' ";
        # execute Query
        $meta_keys = $wpdb->get_col( $query );
        # return Depend on the Query result
        
        if ( empty($meta_keys) ) {
            return array( FALSE, 'ERROR: Empty! No Meta key exist of the Post type WooCommerce Product.' );
        } else {
            return array( TRUE, $meta_keys );
        }
    
    }
    
    /**
     *  Contact form 7,  form  fields 
     *  @since    3.1.0
     */
    public function cf7_forms_and_fields()
    {
        # is there CF7
        if ( !in_array( 'contact-form-7/wp-contact-form-7.php', $this->active_plugins ) or !$this->ijcgs_dbTableExists( 'posts' ) ) {
            return array( FALSE, "ERROR:  Contact form 7 is Not installed or DB Table is Not Exist  " );
        }
        $cf7forms = array();
        $fieldsArray = array();
        global  $wpdb ;
        $cf7Forms = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->posts}.post_type = 'wpcf7_contact_form' AND {$wpdb->postmeta}.meta_key = '_form'" );
        # Looping the Forms
        foreach ( $cf7Forms as $form ) {
            # Inserting Fields 																			# Loop the Custom Post ;
            $cf7forms["cf7_" . $form->ID] = "Cf7 - " . $form->post_title;
            # Getting Fields Meta
            $formFieldsMeta = get_post_meta( $form->ID, '_form', true );
            # Replacing Quoted string
            $formFieldsMeta = preg_replace( '/"((?:""|[^"])*)"/', "", $formFieldsMeta );
            # Removing : txt
            $formFieldsMeta = preg_replace( '/\\w+:\\w+/', "", $formFieldsMeta );
            # Removing submit
            $formFieldsMeta = preg_replace( '/\\bsubmit\\b/', "", $formFieldsMeta );
            # if txt is Not empty
            
            if ( !empty($formFieldsMeta) ) {
                # Getting Only [] txt
                $bracketTxt = array();
                # Separating bracketed txt and inserting theme to  $bracketTxt array
                preg_match_all( '/\\[(.*?)\\]/', $formFieldsMeta, $bracketTxt );
                # Check is set & not empty
                if ( isset( $bracketTxt[1] ) && !empty($bracketTxt[1]) ) {
                    # Field Loop
                    foreach ( $bracketTxt[1] as $txt ) {
                        # Divide the TXT after every space
                        $tmpArr = explode( ' ', $txt );
                        # taking Only the second Element of every array || first one is Field type || Second One is Field key
                        $singleItem = array_slice( $tmpArr, 1, 1 );
                        # Remove Submit Empty Array || important i am removing submit
                        if ( isset( $singleItem[0] ) && !empty($singleItem[0]) ) {
                            $fieldsArray["cf7_" . $form->ID][$singleItem[0]] = $singleItem[0];
                        }
                    }
                }
            }
        
        }
        return array( TRUE, $cf7forms, $fieldsArray );
    }
    
    /**
     *  Ninja  form  fields 
     *  @param     int     $user_id     username
     *  @param     int     $old_user_data     username
     *  @since     1.0.0
     */
    public function ninja_forms_and_fields()
    {
        if ( !in_array( 'ninja-forms/ninja-forms.php', $this->active_plugins ) or !$this->ijcgs_dbTableExists( 'nf3_forms' ) ) {
            return array( FALSE, "ERROR:  Ninja form 7 is Not Installed " );
        }
        global  $wpdb ;
        $FormArray = array();
        # Empty Array for Value Holder
        $fieldsArray = array();
        $ninjaForms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nf3_forms", ARRAY_A );
        foreach ( $ninjaForms as $form ) {
            $FormArray["ninja_" . $form["id"]] = "Ninja - " . $form["title"];
            $ninjaFields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nf3_fields where parent_id = '" . $form["id"] . "'", ARRAY_A );
            foreach ( $ninjaFields as $field ) {
                $field_list = array( "textbox", "textarea", "number" );
                if ( in_array( $field["type"], $field_list ) ) {
                    $fieldsArray["ninja_" . $form["id"]][$field["key"]] = $field["label"];
                }
            }
        }
        return array( TRUE, $FormArray, $fieldsArray );
    }
    
    /**
     *  formidable form  fields 
     *  @since    1.0.0
     */
    public function formidable_forms_and_fields()
    {
        if ( !in_array( 'formidable/formidable.php', $this->active_plugins ) or !$this->ijcgs_dbTableExists( 'frm_forms' ) ) {
            return array( FALSE, "ERROR: formidable form  is Not Installed OR DB table is Not Exist" );
        }
        global  $wpdb ;
        $FormArray = array();
        # Empty Array for Value Holder
        $fieldsArray = array();
        # Empty Array for Holder
        $frmForms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}frm_forms" );
        # Getting  Forms Database
        foreach ( $frmForms as $form ) {
            $FormArray["frm_" . $form->id] = "Formidable - " . $form->name;
            # Inserting ARRAY title
            # Getting Meta Fields || maybe i don't Know ;-D
            $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}frm_fields WHERE form_id= " . $form->id . " ORDER BY field_order" );
            # Getting  Data from Database
            foreach ( $fields as $field ) {
                # Default fields
                $field_list = array( "text", "textarea", "number" );
                if ( in_array( $field->type, $field_list ) ) {
                    $fieldsArray["frm_" . $form->id][$field->id] = $field->name;
                }
            }
        }
        return array( TRUE, $FormArray, $fieldsArray );
        # Inserting Data to the Main [$eventsAndTitles ] Array
    }
    
    /**
     *  wpforms fields 
     *  @since    1.0.0
     */
    public function wpforms_forms_and_fields()
    {
        if ( !count( array_intersect( $this->active_plugins, array( 'wpforms-lite/wpforms.php', 'wpforms/wpforms.php' ) ) ) or !$this->ijcgs_dbTableExists( 'posts' ) ) {
            return array( FALSE, "ERROR:  wp form is Not Installed OR DB Table is Not Exist  " );
        }
        $FormArray = array();
        $fieldsArray = array();
        # Getting Data from Database
        global  $wpdb ;
        $wpforms = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'wpforms'  " );
        foreach ( $wpforms as $wpform ) {
            $FormArray["wpforms_" . $wpform->ID] = "WPforms - " . $wpform->post_title;
            $post_content = json_decode( $wpform->post_content );
            foreach ( $post_content->fields as $field ) {
                # Default fields
                $field_list = array( "name", "text", "textarea" );
                if ( in_array( $field->type, $field_list ) ) {
                    $fieldsArray["wpforms_" . $wpform->ID][$field->id] = $field->label;
                }
            }
        }
        return array( TRUE, $FormArray, $fieldsArray );
    }
    
    # FIXME:
    # do it after Upload || last off all forms
    /**
     *  WE forms fields 
     *  @since    1.0.0
     */
    public function weforms_forms_and_fields()
    {
        if ( !in_array( 'weforms/weforms.php', $this->active_plugins ) or !$this->ijcgs_dbTableExists( 'posts' ) ) {
            return array( FALSE, "ERROR:  weForms  is Not Active  OR DB is not exist" );
        }
        $FormArray = array();
        $fieldsArray = array();
        $fieldTypeArray = array();
        global  $wpdb ;
        $weforms = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'wpuf_contact_form'  " );
        $weFields = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'wpuf_input'  " );
        foreach ( $weforms as $weform ) {
            $FormArray["we_" . $weform->ID] = 'weForms - ' . $weform->post_title;
        }
        foreach ( $weFields as $Field ) {
            foreach ( $FormArray as $weformID => $weformTitle ) {
                
                if ( $weformID == "we_" . $Field->post_parent ) {
                    $content_arr = unserialize( $Field->post_content );
                    $fieldsArray[$weformID][$content_arr['name']] = $content_arr['label'];
                    $fieldTypeArray[$weformID][$content_arr['name']] = $content_arr['template'];
                }
            
            }
        }
        return array(
            TRUE,
            $FormArray,
            $fieldsArray,
            $fieldTypeArray
        );
    }
    
    /**
     * 	Under Construction 
     *  gravity forms fields 
     *  @since    1.0.0
     */
    public function gravity_forms_and_fields()
    {
        if ( !in_array( 'gravityforms/gravityforms.php', $this->active_plugins ) ) {
            return array( FALSE, "ERROR:  gravity forms  is Not Active  OR DB is not exist" );
        }
        if ( !class_exists( 'GFAPI' ) ) {
            return array( FALSE, "ERROR:  gravityForms class GFAPI is not exist" );
        }
        $gravityForms = GFAPI::get_forms();
        #check and Test
        
        if ( !empty($gravityForms) ) {
            # Empty array holder Declared
            $FormArray = array();
            # Empty Array for Value Holder
            $fieldsArray = array();
            $fieldTypeArray = array();
            # New Code Loop
            foreach ( $gravityForms as $form ) {
                $FormArray["gravity_" . $form["id"]] = "Gravity - " . $form["title"];
                # Form Fields || Check fields are set or Not
                if ( isset( $form['fields'] ) and is_array( $form['fields'] ) ) {
                    foreach ( $form['fields'] as $field ) {
                        
                        if ( empty($field['inputs']) ) {
                            # if there is no subfields
                            $fieldsArray["gravity_" . $form["id"]][$field["id"]] = $field["label"];
                            $fieldTypeArray["gravity_" . $form["id"]][$field["id"]] = $field["type"];
                        } else {
                            # Looping Subfields
                            foreach ( $field["inputs"] as $subField ) {
                                $fieldsArray["gravity_" . $form["id"]][$subField["id"]] = $field["label"] . ' (' . $subField["label"] . ')';
                                $fieldTypeArray["gravity_" . $form["id"]][$subField["id"]] = $field["type"];
                            }
                        }
                    
                    }
                }
            }
        } else {
            return array( FALSE, "ERROR:  gravityForms form object is empty." );
        }
        
        return array(
            TRUE,
            $FormArray,
            $fieldsArray,
            $fieldTypeArray
        );
    }
    
    /**
     * forminator forms fields 
     * @since      3.6.0
     * @return     array   First one is CPS and Second one is CPT's Field source.
     */
    public function forminator_forms_and_fields()
    {
        if ( !in_array( 'forminator/forminator.php', $this->active_plugins ) ) {
            return array( FALSE, "ERROR: forminator form  is Not Installed OR no integration Exist" );
        }
        $FormArray = array();
        # Empty Array for Value Holder
        $fieldsArray = array();
        # Empty Array for Holder
        # Getting Forminator Fields
        $forms = Forminator_API::get_forms();
        # Check And Balance
        if ( !empty($forms) ) {
            # Looping the Forms
            foreach ( $forms as $form ) {
                # inserting Forms
                $FormArray["forminator_" . $form->id] = "forminator - " . $form->name;
                # Getting Fields
                $fields = get_post_meta( $form->id, 'forminator_form_meta' );
                # Check & balance
                
                if ( isset( $fields[0]['fields'] ) and !empty($fields[0]['fields']) ) {
                    # Looping the Fields
                    foreach ( $fields[0]['fields'] as $field ) {
                        if ( isset( $field['id'], $field['field_label'] ) ) {
                            $fieldsArray["forminator_" . $form->id][$field['id']] = $field['field_label'];
                        }
                    }
                    # Date And Time
                    $fieldsArray["forminator_" . $form->id]['ijcgs_submitted_time'] = "ijcgs Form submitted  time";
                    $fieldsArray["forminator_" . $form->id]['ijcgs_submitted_date'] = "ijcgs Form submitted date";
                }
            
            }
        }
        return array( TRUE, $FormArray, $fieldsArray );
    }
    
    /**
     * fluent forms fields 
     * @since      3.6.0
     * @return     array   First one is CPS and Second one is CPT's Field source.
     */
    public function fluent_forms_and_fields()
    {
        if ( !in_array( 'fluentform/fluentform.php', get_option( 'active_plugins' ) ) ) {
            return array( FALSE, "ERROR: fluentform form  is Not Installed OR no integration Exist" );
        }
        $FormArray = array();
        $fieldsArray = array();
        $fluentForms = fluentFormApi( 'forms' )->forms( array(
            'sort_by' => 'DESC',
        ), TRUE );
        # Check and Balance
        if ( isset( $fluentForms['data'] ) and !empty($fluentForms['data']) ) {
            foreach ( $fluentForms['data'] as $form ) {
                
                if ( isset( $form->id, $form->title, $form->form_fields ) ) {
                    $FormArray["fluent_" . $form->id] = $form->title;
                    # getting Fields
                    $fields = fluentFormApi( 'forms' )->form( $formId = $form->id )->fields();
                    # Check and Balance
                    if ( !empty($fields) and isset( $fields['fields'] ) ) {
                        foreach ( $fields['fields'] as $field ) {
                            if ( isset( $field['index'], $field['attributes']['name'] ) ) {
                                $fieldsArray["fluent_" . $form->id][$field['attributes']['name']] = ( isset( $field['attributes']['placeholder'] ) ? $field['attributes']['placeholder'] : $field['attributes']['name'] );
                            }
                        }
                    }
                }
                
                # Date And Time
                $fieldsArray["fluent_" . $form->id]['ijcgs_submitted_time'] = "ijcgs Form submitted  time";
                $fieldsArray["fluent_" . $form->id]['ijcgs_submitted_date'] = "ijcgs Form submitted date";
            }
        }
        return array( TRUE, $FormArray, $fieldsArray );
    }
    
    /**
     * This Function will All Custom Post types 
     * @since      3.3.0
     * @return     array   First one is CPS and Second one is CPT's Field source.
     */
    public function ijcgs_allCptEvents()
    {
        # Getting The Global wp_post_types array
        global  $wp_post_types ;
        # Check And Balance
        
        if ( isset( $wp_post_types ) && !empty($wp_post_types) ) {
            # CPT holder empty array declared
            $cpts = array();
            # List of items for removing
            $removeArray = array(
                "wpforms",
                "acf-field-group",
                "acf-field",
                "product",
                "product_variation",
                "shop_order",
                "shop_order_refund"
            );
            # Looping the Post types
            foreach ( $wp_post_types as $postKey => $PostValue ) {
                # if Post type is Not Default
                if ( isset( $PostValue->_builtin ) and !$PostValue->_builtin ) {
                    # Look is it on remove list, if not insert
                    if ( !in_array( $postKey, $removeArray ) ) {
                        # Pre populate $cpts array
                        
                        if ( isset( $PostValue->label ) and !empty($PostValue->label) ) {
                            $cpts[$postKey] = $PostValue->label . " (" . $postKey . ")";
                        } else {
                            $cpts[$postKey] = $postKey;
                        }
                    
                    }
                }
            }
            # Empty Holder Array for CPT events
            $cptEvents = array();
            # Creating events
            
            if ( !empty($cpts) ) {
                # Looping for Creating Extra Events Like Update and Delete
                foreach ( $cpts as $key => $value ) {
                    $cptEvents['cpt_new_' . $key] = 'CPT New ' . $value;
                    $cptEvents['cpt_update_' . $key] = 'CPT Update ' . $value;
                    $cptEvents['cpt_delete_' . $key] = 'CPT Delete ' . $value;
                }
                # Now setting default Event data Source Fields; Those events data source  are common in all WordPress Post type
                $eventDataFields = array(
                    "postID"            => "ID",
                    "post_authorID"     => "post author_ID",
                    "authorUserName"    => "author User Name",
                    "authorDisplayName" => "author Display Name",
                    "authorEmail"       => "author Email",
                    "authorRole"        => "author Role",
                    "post_title"        => "post title",
                    "post_date"         => "post date",
                    "post_date_gmt"     => "post date gmt",
                    "site_time"         => "Site Time",
                    "site_date"         => "Site Date",
                    "post_content"      => "post content",
                    "post_excerpt"      => "post excerpt",
                    "post_status"       => "post status",
                    "comment_status"    => "comment status",
                    "ping_status"       => "ping status",
                    "post_password"     => "post password",
                    "post_name"         => "post name",
                    "to_ping"           => "to ping",
                    "pinged"            => "pinged",
                    "post_modified"     => "post modified date",
                    "post_modified_gmt" => "post modified date GMT",
                    "post_parent"       => "post parent",
                    "guid"              => "guid",
                    "menu_order"        => "menu order",
                    "post_type"         => "post type",
                    "post_mime_type"    => "post mime type",
                    "comment_count"     => "comment count",
                    "filter"            => "filter",
                );
                # Global Db object
                global  $wpdb ;
                # Query for getting Meta keys
                $query = "SELECT DISTINCT({$wpdb->postmeta}.meta_key) \r\n\t\t\t\t\t\t\tFROM {$wpdb->posts} \r\n\t\t\t\t\t\t\tLEFT JOIN {$wpdb->postmeta} \r\n\t\t\t\t\t\t\tON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id \r\n\t\t\t\t\t\t\tWHERE {$wpdb->posts}.post_type != 'post' \r\n\t\t\t\t\t\t\tAND {$wpdb->posts}.post_type   != 'page' \r\n\t\t\t\t\t\t\tAND {$wpdb->posts}.post_type   != 'product' \r\n\t\t\t\t\t\t\tAND {$wpdb->posts}.post_type   != 'shop_order' \r\n\t\t\t\t\t\t\tAND {$wpdb->posts}.post_type   != 'shop_order_refund' \r\n\t\t\t\t\t\t\tAND {$wpdb->posts}.post_type   != 'product_variation' \r\n\t\t\t\t\t\t\tAND {$wpdb->posts}.post_type \t != 'wpforms' \r\n\t\t\t\t\t\t\tAND {$wpdb->postmeta}.meta_key != '' ";
                # execute Query for getting the Post meta key it will use for event data source
                $meta_keys = $wpdb->get_col( $query );
                # Inserting Meta keys to Main $eventDataFields Array;
                
                if ( !empty($meta_keys) and is_array( $meta_keys ) ) {
                    foreach ( $meta_keys as $value ) {
                        if ( !isset( $eventDataFields[$value] ) ) {
                            $eventDataFields[$value] = "CPT Meta " . $value;
                        }
                    }
                } else {
                    # insert to the log but don't return
                    # ERROR:  Meta keys  are empty;
                }
                
                # Everything seems ok, Now send the CPT events and Related Data source;
                return array(
                    TRUE,
                    $cpts,
                    $cptEvents,
                    $eventDataFields,
                    $meta_keys
                );
            } else {
                return array( FALSE, "ERROR: cpts Array is Empty." );
            }
        
        } else {
            return array( FALSE, "ERROR: wp_post_types global array is not exists or Empty." );
        }
    
    }
    
    /**
     * This is a Helper function to check Table is Exist or Not 
     * If DB table Exist it will return True if Not it will return False
     * @since      3.2.0
     * @param      string    $data_source    Which platform call this function s
     */
    public function ijcgs_dbTableExists( $tableName = null )
    {
        if ( empty($tableName) ) {
            return FALSE;
        }
        global  $wpdb ;
        $r = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . $tableName . "'" );
        
        if ( $r ) {
            return TRUE;
        } else {
            return FALSE;
        }
    
    }
    
    /**
     * This Function Will return all the Save integrations from database 
     * @since      3.4.0
     * @return     array   	 This Function Will return an array 
     */
    public function ijcgs_getIntegrations()
    {
        # Setting Empty Array
        $integrationsArray = array();
        # Getting All Posts
        $listOfConnections = get_posts( array(
            'post_type'      => 'ijcgsIntegration',
            'post_status'    => array( 'publish', 'pending' ),
            'posts_per_page' => -1,
        ) );
        # integration loop starts
        foreach ( $listOfConnections as $key => $value ) {
            # Compiled to JSON String
            $post_excerpt = json_decode( $value->post_excerpt, TRUE );
            # if JSON Compiled successfully
            
            if ( is_array( $post_excerpt ) and !empty($post_excerpt) ) {
                $integrationsArray[$key]["IntegrationID"] = $value->ID;
                $integrationsArray[$key]["DataSource"] = $post_excerpt["DataSource"];
                $integrationsArray[$key]["DataSourceID"] = $post_excerpt["DataSourceID"];
                $integrationsArray[$key]["Worksheet"] = $post_excerpt["Worksheet"];
                $integrationsArray[$key]["WorksheetID"] = $post_excerpt["WorksheetID"];
                $integrationsArray[$key]["Spreadsheet"] = $post_excerpt["Spreadsheet"];
                $integrationsArray[$key]["SpreadsheetID"] = $post_excerpt["SpreadsheetID"];
                $integrationsArray[$key]["Status"] = $value->post_status;
            } else {
                # Display ERROR, Because Data is corrected or Empty
            }
        
        }
        # integration loop Ends
        # return  array with First Value as Bool and second one is integrationsArray array
        
        if ( count( $integrationsArray ) ) {
            return array( TRUE, $integrationsArray );
        } else {
            return array( FALSE, $integrationsArray );
        }
    
    }
    
    /**
     * This Function will create a relation between data and the Integration key || Its a Helper Function 
     * @since     3.5.0
     * @return    array   	it will not return  array of relation
     */
    public function relationToValue( $data = array(), $relations = array() )
    {
        # data array empty check,
        if ( empty($data) ) {
            return array( FALSE, 'Data array is Empty! ' . json_encode( $data, TRUE ) );
        }
        # relations array empty check
        if ( empty($relations) ) {
            return array( FALSE, 'Relation array is Empty!' );
        }
        # Empty Array Holder
        $rtnArr = array();
        # Looping starts
        foreach ( $relations as $key => $value ) {
            if ( isset( $data[$value] ) ) {
                $rtnArr[str_replace( array( '{{', '}}' ), '', $key )] = ( $data[$value] == '--' ? '' : trim( $data[$value] ) );
            }
        }
        # This is The return
        
        if ( !empty($rtnArr) ) {
            return array( TRUE, $rtnArr );
        } else {
            return array( FALSE, "Empty array!" );
        }
    
    }

}