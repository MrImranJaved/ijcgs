<?php

/**
 * This is new remote update class, This call will update wordPress From google Sheet.
 * This call  has Dependence in googleSheet class for Token Generation 
 * @since      3.6.0
 * @package    IJcgs
 * @subpackage IJcgs/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class IJcgs_Update
{
    /**
     * Events Children titles .
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
     * Common methods used in the all the classes 
     * @since    3.6.0
     * @var      object    $version    The current version of this plugin.
     */
    public  $common ;
    /**
     * Class Constrictors. Setting the class Variables
     * @since    1.0.0
     * @access   Public
     * @var      array    $eventsAndTitles    Events list.
     */
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
        # Events
        $this->common = $common;
    }
    
    /**
     * This is a Admin notification function 
     * This Will use for test and Debug 
     * @since    	1.0.0
     * @return 	   	array 	Integrations details.
     */
    public function ijcgs_update_notices()
    {
        echo  "<pre>" ;
        echo  "</pre>" ;
    }
    
    /**
     * REST API end Point creator, This Function Will create two rest end point one for data acceptance and another for Update data
     * Request will be POST
     * Payload will be <token> and Sheet JSON data 
     * END POINT WILL BE : http://localhost/office/wp-json/ijcgs/update/ 
     * @since    	3.6.0
     * @return 	   	array 	Integrations details.
     */
    public function ijcgs_register_rest_route()
    {
        # For receiving data and saving that to option table
        register_rest_route( 'ijcgs', '/accept', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'ijcgs_callBackFuncAccept' ),
            'permission_callback' => '__return_true',
        ) );
        # For updating data site data
        register_rest_route( 'ijcgs', '/update', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'ijcgs_callBackFuncUpdate' ),
            'permission_callback' => '__return_true',
        ) );
    }
    
    /**
     * This is the callback function of register_rest_route() Function
     * This Function get the Request data & handel the Request and return the response 
     * @param       $Request data 
     * @since    	3.6.0
     * @return 	   	array 	Integrations details.
     */
    public function ijcgs_callBackFuncAccept( $data )
    {
        # Check & Balance; Check to see data <token> is empty or not
        
        if ( !isset( $data['token'] ) or empty($data['token']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "300",
                "ERROR: update error from Google Sheet. token is empty."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: update error from Google Sheet. token is empty.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # converting data from base64 string
        $jsonString = @base64_decode( $data['token'] );
        # encoding JSON string to PHP array
        $updateInfo = json_decode( $jsonString, TRUE );
        # User information validation;  $updateInfo array and isset( ) check for ID, UID, email
        
        if ( !is_array( $updateInfo ) or !isset( $updateInfo['ID'], $updateInfo['UID'], $updateInfo['email'] ) ) {
            echo  "DANGER: update error from Google Sheet. Not array or ID, UID, email, URL is not set !" ;
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "301",
                "ERROR: update error from Google Sheet. Not array or ID, UID, email, URL is not set !"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: update error from Google Sheet. token is empty.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # integration Id
        
        if ( empty($updateInfo['ID']) or !is_numeric( $updateInfo['ID'] ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "302",
                "ERROR: update error from Google Sheet. integration ID is empty."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: update error from Google Sheet. integration ID is empty.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Getting the ID
        $integrationID = sanitize_text_field( $updateInfo['ID'] );
        # getting user data
        $userData = get_userdata( sanitize_text_field( $updateInfo['UID'] ) );
        # User ID check see user
        
        if ( !is_array( $userData ) and $updateInfo['UID'] != $userData->data->ID ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "303",
                "ERROR: update error from Google Sheet. user id is not correct or no user."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: update error from Google Sheet. user id is not correct or no user.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Check User Role If User role is not administrator or editor STOP. send a 400 response
        $user_roles = $userData->roles;
        
        if ( !in_array( 'administrator', $user_roles, true ) and !in_array( 'editor', $user_roles, true ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "304",
                "ERROR: sorry user didn't have permission to do this task. User role is not administrator or editor"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: sorry user didn't have permission to do this task. User role is not OK",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Email Check
        
        if ( empty($updateInfo['email']) or $updateInfo['email'] != $userData->data->user_email ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "304",
                "ERROR: update error from Google Sheet. user email address is not correct."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: update error from Google Sheet. user email address is not correct.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # getting the remote Update Status.
        $remoteUpdateStatus = get_post_meta( $integrationID, "remoteUpdateStatus", TRUE );
        # remote Update Status check
        
        if ( $remoteUpdateStatus ) {
            # Keeping Log
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "200",
                "SUCCESS: remote update from google sheet is initiated. Integration ID : " . $integrationID . " User email : " . $updateInfo['email']
            );
        } else {
            # Keeping Log
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "305",
                "ERROR:  Integration ID: " . $integrationID . " remote update status to DISABLED! Request for Update is received."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR:  Integration ID: " . $integrationID . " remote update status to DISABLED! Request for Update is received.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        // Testing
        // delete_option( 'ijcgs_update_integrationID' );
        // delete_option( 'ijcgs_update_cache' );
        // exit;
        # *** Important note.
        # getting integration ID if There is a ID then Its JUST UPDATE,  No need to download the  Data from Google sheet Just RUN THE UPDATER FUNCTION
        # If new $integration_id is not same as saved one then it will also get the data from the remote Google Sheet Too;
        # Global database instance
        global  $wpdb ;
        #Product List Empty Array
        $updatePostList = array();
        # Getting the integration
        $Integration = get_post( $integrationID );
        # Post Content
        $post_content = json_decode( $Integration->post_content, TRUE );
        # valid JSON check
        
        if ( !isset( $post_content[0], $post_content[1] ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "306",
                "ERROR: Saved Relation array is Not Set or JSON parse ERROR or Wrong Post ID of ijcgsintegration !"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: Saved Relation array is Not Set or JSON parse ERROR or Wrong Post ID of ijcgsintegration !",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Empty Check Empty or not
        
        if ( empty($post_content[0]) and empty($post_content[1]) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "307",
                "ERROR: Saved Relation array is EMPTY !"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: Saved Relation array is EMPTY !",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Converting The Content to array
        $post_excerpt = ( !empty($Integration->post_content) ? json_decode( $Integration->post_excerpt, TRUE ) : array() );
        # Empty check, if empty then return the ERROR message
        
        if ( !isset( $post_excerpt['Worksheet'] ) or empty($post_excerpt['Worksheet']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "308",
                "ERROR: Worksheet Name or Worksheet is empty!"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: Worksheet Name or Worksheet is empty!",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Empty check, if empty then return the ERROR message
        
        if ( !isset( $post_excerpt['SpreadsheetID'] ) or empty($post_excerpt['SpreadsheetID']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "309",
                "ERROR: Worksheet Name or SpreadsheetID is empty!"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: Worksheet Name or SpreadsheetID is empty!",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Integration Platform check
        
        if ( !isset( $post_excerpt['DataSourceID'] ) or empty($post_excerpt['DataSourceID']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "310",
                "ERROR: DataSourceID is Empty! its means integration Platform is not present."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: DataSourceID is Empty! its means integration Platform is not present.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # New Code is HERE
        # id DataSourceID(integration platform) is not not POST TYPE.
        
        if ( !in_array( $post_excerpt['DataSourceID'], array_keys( $this->ijcgs_postTypeDetails()[2] ) ) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "311",
                "ERROR: SORRY not this time : This integration platform is not supported !"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: SORRY not this time : This integration platform is not supported !",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # BLOCKING Professional Version STARTS
        $lock = TRUE;
        # Check and Balance for Free and professional version
        
        if ( in_array( $post_excerpt['DataSourceID'], array(
            'wordpress_newPost',
            'wordpress_editPost',
            'wordpress_deletePost',
            'wordpress_page'
        ) ) ) {
            #  including the View File;
            $lock = FALSE;
        } else {
        }
        
        # Open for professional version
        
        if ( $lock ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "312",
                "We are very sorry for your unexpected experience. All default WordPress Posts and Pages remote updates are FREE.<br> WooCommerce and Custom post types are in the Professional version. Thank you for using the Plugin."
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: We are very sorry for your unexpected experience. All default WordPress Posts and Pages remote updates are FREE. WooCommerce and Custom post types are in the Professional version. Thank you for using the Plugin.!",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # BLOCKING Professional Version ENDS
        # getting post content
        $post_content = ( !empty($Integration->post_content) ? json_decode( $Integration->post_content, TRUE ) : array() );
        
        if ( !isset( $post_content[1] ) or empty($post_content[1]) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "312",
                "ERROR: Relation is Empty!"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: Relation is Empty!",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Processing the relation
        $relations = array_flip( array_filter( array_values( $post_content[1] ) ) );
        $spreadsheets_id = $post_excerpt['SpreadsheetID'];
        $worksheet_name = $post_excerpt['Worksheet'];
        # Check & balance,
        
        if ( !isset( $data['sheetData'] ) or empty($data['sheetData']) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "318",
                "ERROR: sheetData is not array or sheetData is Empty!"
            );
            echo  "ERROR: sheetData is not array or sheetData is Empty!" ;
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: update error from Google Sheet. token is empty.",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Json encoding the response Data
        $dataArray = ( (isset( $data['sheetData'] ) and !empty($data['sheetData'])) ? $data['sheetData'] : array() );
        # Check and Balance >> is array and not empty
        
        if ( !is_array( $dataArray ) or empty($dataArray) ) {
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "318",
                "ERROR: dataArray is not array or dataArray is Empty!"
            );
            # http Response
            $response_data = array(
                'status'  => TRUE,
                'message' => "ERROR: dataArray is not array or dataArray is Empty!",
                'code'    => "400",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 400 );
            return $response;
        }
        
        # Looping the Spreadsheet data that got From Google Sheet
        foreach ( $dataArray as $key => $rowData ) {
            # match the value with the Relation
            $updateData = $this->relationToValue( $rowData, $relations );
            # Block if POST ID is not present
            # Check POST id Or Product ID is Present or not # if not present skip
            # Now Update the Product to Store The Things
            $updateDataForInput = array();
            # Now Update to the Product Meta
            $updateMetaDataForInput = array();
            # If this is no post or Product ID
            
            if ( isset( $updateData[1]['postID'] ) or isset( $updateData[1]['productID'] ) ) {
                # if Platform is Any Post type || default or CPT post type
                
                if ( in_array( $post_excerpt['DataSourceID'], array( 'wc-new_product', 'wc-edit_product' ) ) ) {
                    # getting WooCommerce Product With ID
                    $product = wc_get_product( $updateData[1]['productID'] );
                    # Do the ACTION if PRODUCT is a simple or external Product # there are different kinds of product like Variable product
                    
                    if ( $product and in_array( $product->get_type(), array( 'simple', 'external' ) ) ) {
                        # For Product ID
                        if ( isset( $updateData[1]['productID'] ) and !empty($updateData[1]['productID']) ) {
                            $updateDataForInput['ID'] = sanitize_text_field( $updateData[1]['productID'] );
                        }
                        # Assigning one-to-one relations
                        # post_date relation,
                        if ( isset( $updateData[1]['post_date'] ) and !empty($updateData[1]['post_date']) ) {
                            $updateDataForInput['post_date'] = sanitize_text_field( $updateData[1]['post_date'] );
                        }
                        # Modified Date
                        # Need to add this
                        # Product Description relation || Content
                        if ( isset( $updateData[1]['description'] ) and !empty($updateData[1]['description']) ) {
                            $updateDataForInput['post_content'] = wp_kses_post( $updateData[1]['description'] );
                        }
                        # Product post_title relation || Title
                        if ( isset( $updateData[1]['name'] ) and !empty($updateData[1]['name']) ) {
                            $updateDataForInput['post_title'] = sanitize_text_field( $updateData[1]['name'] );
                        }
                        # Product post_excerpt relation || Short description
                        if ( isset( $updateData[1]['short_description'] ) and !empty($updateData[1]['short_description']) ) {
                            $updateDataForInput['post_excerpt'] = wp_kses_post( $updateData[1]['short_description'] );
                        }
                        # Product post_status relation || post status
                        if ( isset( $updateData[1]['post_status'] ) and !empty($updateData[1]['post_status']) ) {
                            $updateDataForInput['post_status'] = sanitize_text_field( $updateData[1]['post_status'] );
                        }
                        # Product post_status relation  || comment status
                        if ( isset( $updateData[1]['comment_status'] ) and !empty($updateData[1]['comment_status']) ) {
                            $updateDataForInput['comment_status'] = sanitize_text_field( $updateData[1]['comment_status'] );
                        }
                        # Product menu_order relation || Menu order
                        if ( isset( $updateData[1]['menu_order'] ) and !empty($updateData[1]['menu_order']) ) {
                            $updateDataForInput['menu_order'] = sanitize_text_field( $updateData[1]['menu_order'] );
                        }
                        # New Code For Meta Data
                        
                        if ( isset( $updateDataForInput['ID'] ) and is_numeric( $updateDataForInput['ID'] ) and !empty($updateDataForInput['ID']) ) {
                            # getting Product Meta
                            $product_meta = $wpdb->get_results( "SELECT  meta_key  FROM " . $wpdb->prefix . "postmeta WHERE post_id = " . $updateDataForInput['ID'], ARRAY_A );
                            # Looping the Product Meta Data;
                            foreach ( $product_meta as $value ) {
                                # SKU
                                if ( $value['meta_key'] == '_sku' and isset( $updateData[1]['sku'] ) ) {
                                    $updateMetaDataForInput['_sku'] = sanitize_text_field( $updateData[1]['sku'] );
                                }
                                # Price
                                if ( $value['meta_key'] == '_price' and isset( $updateData[1]['price'] ) ) {
                                    $updateMetaDataForInput['_price'] = sanitize_text_field( $updateData[1]['price'] );
                                }
                                # Regular Price
                                if ( $value['meta_key'] == '_regular_price' and isset( $updateData[1]['regular_price'] ) ) {
                                    $updateMetaDataForInput['_regular_price'] = sanitize_text_field( $updateData[1]['regular_price'] );
                                }
                                # Sale Price
                                if ( $value['meta_key'] == '_sale_price' and isset( $updateData[1]['sale_price'] ) ) {
                                    $updateMetaDataForInput['_sale_price'] = sanitize_text_field( $updateData[1]['sale_price'] );
                                }
                                # Sales price date from
                                if ( $value['meta_key'] == '_sale_price_dates_from' and isset( $updateData[1]['date_on_sale_from'] ) ) {
                                    $updateMetaDataForInput['_sale_price_dates_from'] = sanitize_text_field( $updateData[1]['date_on_sale_from'] );
                                }
                                # Sales price date to
                                if ( $value['meta_key'] == '_sale_price_dates_to' and isset( $updateData[1]['date_on_sale_to'] ) ) {
                                    $updateMetaDataForInput['_sale_price_dates_to'] = $updateData[1]['date_on_sale_to'];
                                }
                                # tax status
                                if ( $value['meta_key'] == '_tax_status' and isset( $updateData[1]['tax_status'] ) ) {
                                    $updateMetaDataForInput['_tax_status'] = sanitize_text_field( $updateData[1]['tax_status'] );
                                }
                                # tax class || tax class
                                if ( $value['meta_key'] == '_tax_class' and isset( $updateData[1]['tax_class'] ) ) {
                                    $updateMetaDataForInput['_tax_class'] = sanitize_text_field( $updateData[1]['tax_class'] );
                                }
                                # manage stock || manage stock
                                if ( $value['meta_key'] == '_manage_stock' and isset( $updateData[1]['manage_stock'] ) ) {
                                    $updateMetaDataForInput['_manage_stock'] = sanitize_text_field( $updateData[1]['manage_stock'] );
                                }
                                # backorders sell
                                if ( $value['meta_key'] == '_backorders' and isset( $updateData[1]['backorders'] ) ) {
                                    $updateMetaDataForInput['_backorders'] = sanitize_text_field( $updateData[1]['backorders'] );
                                }
                                # weight
                                if ( $value['meta_key'] == '_weight' and isset( $updateData[1]['weight'] ) ) {
                                    $updateMetaDataForInput['_weight'] = sanitize_text_field( $updateData[1]['weight'] );
                                }
                                # length
                                if ( $value['meta_key'] == '_length' and isset( $updateData[1]['length'] ) ) {
                                    $updateMetaDataForInput['_length'] = sanitize_text_field( $updateData[1]['length'] );
                                }
                                # width
                                if ( $value['meta_key'] == '_width' and isset( $updateData[1]['width'] ) ) {
                                    $updateMetaDataForInput['_width'] = sanitize_text_field( $updateData[1]['width'] );
                                }
                                # height
                                if ( $value['meta_key'] == '_height' and isset( $updateData[1]['height'] ) ) {
                                    $updateMetaDataForInput['_height'] = sanitize_text_field( $updateData[1]['height'] );
                                }
                                # For Unknown and Unrelated Meta Value
                                if ( !array_key_exists( $value['meta_key'], $updateMetaDataForInput ) and isset( $updateData[1][$value['meta_key']] ) ) {
                                    $updateMetaDataForInput[$value['meta_key']] = wp_kses_post( $updateData[1][$value['meta_key']] );
                                }
                                #---------------------------------------------------------------------------------------------------------------------------------
                                # WooCommerce known Meta Data Validation :: this will validate _price, _regular_price, _sale_price, _weight, _length, _width, _height
                                #---------------------------------------------------------------------------------------------------------------------------------
                                # _price
                                
                                if ( isset( $updateMetaDataForInput['_price'] ) and !is_numeric( $updateMetaDataForInput['_price'] ) ) {
                                    unset( $updateMetaDataForInput['_price'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "319",
                                        "ERROR: WooCommerce product update error, product _price is not number."
                                    );
                                }
                                
                                # _regular_price
                                
                                if ( isset( $updateMetaDataForInput['_regular_price'] ) and !is_numeric( $updateMetaDataForInput['_regular_price'] ) ) {
                                    unset( $updateMetaDataForInput['_regular_price'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "320",
                                        "ERROR: WooCommerce product update error, product _regular_price is not number."
                                    );
                                }
                                
                                # _sale_price
                                
                                if ( isset( $updateMetaDataForInput['_sale_price'] ) and !is_numeric( $updateMetaDataForInput['_sale_price'] ) ) {
                                    unset( $updateMetaDataForInput['_sale_price'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "321",
                                        "ERROR: WooCommerce product update error, product _sale_price is not number."
                                    );
                                }
                                
                                # _weight
                                
                                if ( isset( $updateMetaDataForInput['_weight'] ) and !is_numeric( $updateMetaDataForInput['_weight'] ) ) {
                                    unset( $updateMetaDataForInput['_weight'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "322",
                                        "ERROR: WooCommerce product update error, product _weight is not number."
                                    );
                                }
                                
                                # _length
                                
                                if ( isset( $updateMetaDataForInput['_length'] ) and !is_numeric( $updateMetaDataForInput['_length'] ) ) {
                                    unset( $updateMetaDataForInput['_length'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "323",
                                        "ERROR: WooCommerce product update error, product _length is not number."
                                    );
                                }
                                
                                # _width
                                
                                if ( isset( $updateMetaDataForInput['_width'] ) and !is_numeric( $updateMetaDataForInput['_width'] ) ) {
                                    unset( $updateMetaDataForInput['_width'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "324",
                                        "ERROR: WooCommerce product update error, product _width is not number."
                                    );
                                }
                                
                                # _height
                                
                                if ( isset( $updateMetaDataForInput['_height'] ) and !is_numeric( $updateMetaDataForInput['_height'] ) ) {
                                    unset( $updateMetaDataForInput['_height'] );
                                    $this->common->ijcgs_log(
                                        get_class( $this ),
                                        __METHOD__,
                                        "325",
                                        "ERROR: WooCommerce product update error, product _height is not number."
                                    );
                                }
                                
                                # Known WooCommerce meta Validation ends
                            }
                        }
                    
                    } else {
                        # Logging error, Product type is not simple or external.
                        $this->common->ijcgs_log(
                            get_class( $this ),
                            __METHOD__,
                            "326",
                            "ERROR: WooCommerce product update error, product type is not simple or external."
                        );
                    }
                
                } else {
                    # if Platform is any kind of POST type
                    # For Post ID
                    if ( isset( $updateData[1]['postID'] ) and !empty($updateData[1]['postID']) ) {
                        $updateDataForInput['ID'] = sanitize_text_field( $updateData[1]['postID'] );
                    }
                    # Assigning one-to-one relations
                    # post_date relation,
                    if ( isset( $updateData[1]['post_date'] ) and !empty($updateData[1]['post_date']) ) {
                        $updateDataForInput['post_date'] = sanitize_text_field( $updateData[1]['post_date'] );
                    }
                    # Modified Date
                    # Need to add this
                    # Product Description relation || Content
                    if ( isset( $updateData[1]['post_content'] ) and !empty($updateData[1]['post_content']) ) {
                        $updateDataForInput['post_content'] = wp_kses_post( $updateData[1]['post_content'] );
                    }
                    # Product post_title relation || Title
                    if ( isset( $updateData[1]['post_title'] ) and !empty($updateData[1]['post_title']) ) {
                        $updateDataForInput['post_title'] = sanitize_text_field( $updateData[1]['post_title'] );
                    }
                    # Product post_excerpt relation || Short description
                    if ( isset( $updateData[1]['post_excerpt'] ) and !empty($updateData[1]['post_excerpt']) ) {
                        $updateDataForInput['post_excerpt'] = wp_kses_post( $updateData[1]['post_excerpt'] );
                    }
                    # Product post_status relation || post status
                    if ( isset( $updateData[1]['post_status'] ) and !empty($updateData[1]['post_status']) ) {
                        $updateDataForInput['post_status'] = sanitize_text_field( $updateData[1]['post_status'] );
                    }
                    # Product post_status relation  || comment status
                    if ( isset( $updateData[1]['comment_status'] ) and !empty($updateData[1]['comment_status']) ) {
                        $updateDataForInput['comment_status'] = sanitize_text_field( $updateData[1]['comment_status'] );
                    }
                    # Product post_type relation || Post type
                    if ( isset( $updateData[1]['post_type'] ) and !empty($updateData[1]['post_type']) ) {
                        $updateDataForInput['post_type'] = sanitize_text_field( $updateData[1]['post_type'] );
                    }
                    # Product menu_order relation || Menu order
                    if ( isset( $updateData[1]['menu_order'] ) and !empty($updateData[1]['menu_order']) ) {
                        $updateDataForInput['menu_order'] = sanitize_text_field( $updateData[1]['menu_order'] );
                    }
                    # For Post META
                    
                    if ( is_numeric( $updateDataForInput['ID'] ) and !empty($updateDataForInput['ID']) ) {
                        # getting Product Meta
                        $product_meta = $wpdb->get_results( "SELECT  meta_key  FROM " . $wpdb->prefix . "postmeta WHERE post_id = " . $updateDataForInput['ID'], ARRAY_A );
                        # Looping the Product Meta Data;
                        foreach ( $product_meta as $value ) {
                            # For Unknown and Unrelated Meta Value
                            # New Code Starts
                            if ( !array_key_exists( $value['meta_key'], $updateMetaDataForInput ) and isset( $updateData[1][$value['meta_key']] ) ) {
                                $updateMetaDataForInput[$value['meta_key']] = wp_kses_post( $updateData[1][$value['meta_key']] );
                            }
                        }
                    }
                
                }
                
                # Adding Every Row Data to Main POST ARRAY
                if ( isset( $updateDataForInput['ID'] ) and is_numeric( $updateDataForInput['ID'] ) ) {
                    $updatePostList[$updateDataForInput['ID']] = array(
                        "postData" => $updateDataForInput,
                        "metaData" => $updateMetaDataForInput,
                    );
                }
            }
            
            # Separate Post Default Data And Meta Data
        }
        # Setting Update List on the Site Option cache *** important without saving it will n
        update_option( 'ijcgs_update_cache', $updatePostList );
        update_option( 'ijcgs_update_integrationID', $Integration->ID );
        # For Testing!
        // print_r( json_encode($updatePostList) );
        # After Update to the Array Unset The Variable For Memory management
        unset( $updateDataForInput );
        unset( $updateMetaDataForInput );
        unset( $updatePostList );
        # Keeping data accept log
        $this->common->ijcgs_log(
            get_class( $this ),
            __METHOD__,
            "200",
            "SUCCESS: getting data from remote Google Sheet. Integration ID : " . $Integration->ID
        );
        # setting the WP REST response.
        $response_data = array(
            'status'  => TRUE,
            'message' => 'SUCCESS: getting data from remote Google Sheet.',
            'code'    => "200",
        );
        $response = new WP_REST_Response( $response_data );
        $response->set_status( 201 );
        return $response;
    }
    
    /**
     * This is the callback function of register_rest_route() Function
     * This Function get the Request data & handel the Request and return the response 
     * This is the updater function it will update Post Types to database.
     * After getting data from the site Option DB Table It Will Update the post and WooCommerce  product .
     * It will Update 13 Product at a time, A frontend Ajax Function will run this Function After every 20 second 
     * @param       $Request data
     * @since    	3.6.0
     * @return 	   	array 	Integrations details.
     */
    public function ijcgs_callBackFuncUpdate()
    {
        # getting integration ID. If integration is Active make it Pending First;
        $integration_id = sanitize_text_field( get_option( 'ijcgs_update_integrationID' ) );
        # if ID Present proceed on.
        
        if ( !empty($integration_id) ) {
            // echo"debug: A1";   // testing & debug
            # getting the Integration.
            $post = get_post( $integration_id );
            # if Post is Publish, Stop the Post by making it Pending.
            
            if ( $post->post_status == 'publish' ) {
                $update_post = array(
                    'ID'          => $integration_id,
                    'post_status' => 'pending',
                );
                wp_update_post( $update_post, true );
            }
        
        } else {
            // echo"debug: A2";  // testing & debug
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "327",
                "ERROR: Not Implemented, No integration ID on the SITE Option DB table."
            );
            $response_data = array(
                'status'  => TRUE,
                'message' => 'ERROR: Not Implemented, No integration ID on the SITE Option DB table.',
                'code'    => "501",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 501 );
            return $response;
        }
        
        # Get The POST List array From the Site Option DB.
        $postList = get_option( 'ijcgs_update_cache' );
        # Loop the Option Saved Product list.
        $i = 0;
        # if postList array is not empty.
        
        if ( !empty($postList) ) {
            # Post update loop
            foreach ( $postList as $key => $dataArray ) {
                # increment the Counter;
                $i++;
                # Updating Post meta
                $r = @wp_update_post( $dataArray['postData'], true );
                # ERROR Checking
                
                if ( is_wp_error( $r ) ) {
                    // echo"debug: A3"; // testing & debug
                    # Keeping the Log
                    $this->common->ijcgs_log(
                        get_class( $this ),
                        __METHOD__,
                        "328",
                        "ERROR: post is not updated. " . $r->get_error_message()
                    );
                    # Preparing response
                    $response_data = array(
                        'status'  => TRUE,
                        'message' => "ERROR: wp_update_post() is returning error, post is not updated. " . $r->get_error_message(),
                        'code'    => "501",
                    );
                    $response = new WP_REST_Response( $response_data );
                    $response->set_status( 501 );
                    return $response;
                } else {
                    // echo"debug: A4";  // testing & debug
                    # Updating Product Meta
                    if ( !empty($r) ) {
                        foreach ( $dataArray['metaData'] as $meta_key => $meta_value ) {
                            update_post_meta( $key, $meta_key, $meta_value );
                        }
                    }
                }
                
                # unset the inserted array item.
                unset( $postList[$key] );
                # Break the Loop after 13
                if ( $i == 13 ) {
                    break;
                }
            }
            # Update Product list cache with remaining list item
            update_option( 'ijcgs_update_cache', $postList );
            # Preparing response
            $response_data = array(
                'status'  => TRUE,
                'message' => "SUCCESS: Post is updating ...! remaining " . count( $postList ),
                'code'    => "201",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 201 );
            return $response;
        } else {
            # Noting to update || post cash is empty || Update is Don || if array is empty this part will Happen.
            // echo"debug: A5";
            # getting  integration ID
            $post = get_post( $integration_id );
            # if integration is  Pending Publish the integration
            
            if ( $post->post_status == 'pending' ) {
                $update_post = array(
                    'ID'          => $integration_id,
                    'post_status' => 'publish',
                );
                wp_update_post( $update_post, true );
            }
            
            # Delete Product cache Too || no garbage
            delete_option( 'ijcgs_update_cache' );
            # Delete ijcgs_update_integrationID
            delete_option( 'ijcgs_update_integrationID' );
            # Keeping update log
            $this->common->ijcgs_log(
                get_class( $this ),
                __METHOD__,
                "200",
                "SUCCESS: Post update successfully. Integration ID : " . $integration_id
            );
            # response
            $response_data = array(
                'status'  => TRUE,
                'message' => "SUCCESS: Done ...!",
                'code'    => "202",
            );
            $response = new WP_REST_Response( $response_data );
            $response->set_status( 202 );
            return $response;
        }
    
    }
    
    /**
     * This Function will create a relation between data and the Integration key || Its a Helper Function 
     * @since     3.5.0
     * @return    array   	it will not return  array of relation
     */
    public function relationToValue( $data = array(), $relations = array() )
    {
        # data array empty check.
        if ( empty($data) ) {
            return array( FALSE, 'ERROR: Data array is Empty ! ' . json_encode( $data, TRUE ) );
        }
        # relations array empty check.
        if ( empty($relations) ) {
            return array( FALSE, 'ERROR: Relation array is Empty !' );
        }
        # Empty Array Holder.
        $rtnArr = array();
        # Looping starts.
        foreach ( $relations as $key => $value ) {
            if ( isset( $data[$value] ) ) {
                $rtnArr[str_replace( array( '{{', '}}' ), '', $key )] = ( $data[$value] == '--' ? '' : trim( $data[$value] ) );
            }
        }
        # This is The return.
        
        if ( !empty($rtnArr) ) {
            return array( TRUE, $rtnArr );
        } else {
            return array( FALSE, "ERROR: Empty array!" );
        }
    
    }
    
    # New Code Starts
    /**
     * This Function will All Post types except some;
     * @since      3.6.0
     * @return     array   First one is CPS and Second one is CPT's Field source.
     */
    public function ijcgs_postTypeDetails()
    {
        # Getting The Global wp_post_types array.
        global  $wp_post_types ;
        # Check And Balance.
        
        if ( isset( $wp_post_types ) && !empty($wp_post_types) ) {
            # CPT holder empty array declared.
            $postType = array();
            # List of items for removing.
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
                # if Post type is Not Default.
                if ( isset( $PostValue->_builtin ) and !$PostValue->_builtin ) {
                    # Look is it on remove list, if not insert
                    if ( !in_array( $postKey, $removeArray ) ) {
                        # Pre populate $postType array
                        
                        if ( isset( $PostValue->label ) and !empty($PostValue->label) ) {
                            $postType[$postKey] = $PostValue->label . " (" . $postKey . ")";
                        } else {
                            $postType[$postKey] = $postKey;
                        }
                    
                    }
                }
            }
            # Empty Holder Array for CPT events
            $postTypeEvents = array(
                'wordpress_newPost'    => 'Wordpress New Post',
                'wordpress_editPost'   => 'Wordpress Edit Post',
                'wordpress_deletePost' => 'Wordpress Delete Post',
                'wordpress_page'       => 'Wordpress Page',
                'wc-new_product'       => 'WooCommerce New Product',
                'wc-edit_product'      => 'WooCommerce Update Product',
                "wc-new_order"         => "WooCommerce New Checkout Page Order",
                "wc-pending"           => "WooCommerce Order Pending payment",
                "wc-processing"        => "WooCommerce Order Processing",
                "wc-on-hold"           => "WooCommerce Order On-hold",
                "wc-completed"         => "WooCommerce Order Completed",
                "wc-cancelled"         => "WooCommerce Order Cancelled",
                "wc-refunded"          => "WooCommerce Order Refunded",
                "wc-failed"            => "WooCommerce Order Failed",
            );
            # Creating events
            
            if ( !empty($postType) ) {
                # Looping for Creating Extra Events Like Update and Delete
                foreach ( $postType as $key => $value ) {
                    $postTypeEvents['cpt_new_' . $key] = 'CPT New ' . $value;
                    $postTypeEvents['cpt_update_' . $key] = 'CPT Update ' . $value;
                    $postTypeEvents['cpt_delete_' . $key] = 'CPT Delete ' . $value;
                }
                # Adding default POST AND PAGE the front of $postType Array
                $postType = array(
                    "wordpress_Post" => "Wordpress Post",
                    "wordpress_page" => "Wordpress Page",
                ) + $postType;
                # Everything seems ok, Now send the CPT events and Related Data source;
                return array( TRUE, $postType, $postTypeEvents );
            } else {
                return array( FALSE, "ERROR: postType Array is Empty." );
            }
        
        } else {
            return array( FALSE, "ERROR: wp_post_types global array is not exists or Empty." );
        }
    
    }

}
#