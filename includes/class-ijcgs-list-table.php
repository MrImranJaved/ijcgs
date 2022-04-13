<?php
/**
 * Define the internationalization functionality.
 * Loads and defines the internationalization files for this plugin
 *
 * @since      1.0.0
 * @package    IJcgs
 * @subpackage IJcgs/includes
 * @author     javmah <jaedmah@gmail.com>
 */

if(!class_exists('WP_List_Table')) require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

// Plugin class.
class IJcgs_List_Table extends WP_List_Table {

    public $eventsAndTitles ;

  /**
   * Construct function
   * Set default settings.
   */
    function __construct( $eventsAndTitles ) {
        global $status, $page;
        $this->eventsAndTitles = $eventsAndTitles;
        //Set parent defaults
        parent::__construct(array(
            'ajax'     => FALSE,
            'singular' => 'user',
            'plural'   => 'users',
        ));
    }
    
  /**
   * Renders the columns.
   * @since 1.0.0
   */
    public function column_default( $item, $column_name ) {
        $post_excerpt = unserialize( $item->post_excerpt );
        $post_content = '';

        switch ($column_name) {
            case 'id':
                $value = $item->ID;
                break;
            case 'IntegrationTitle':
                $value = $item->post_title;
                break;
            case 'DataSource': 
                $value = $post_excerpt->Data_source ;
                break;
            case 'worksheetName':
                $value = $post_excerpt->Worksheet ;
                break;
            case 'WorksheetID':
                $value = $post_excerpt->Worksheet ;
                break;
            case 'spreadsheetName':
                $value = $post_excerpt->Spreadsheet ; 
                break;
            case 'SpreadsheetID':
                $value = '';
                break;
            case 'remoteTitles':
                $value = $post_excerpt->Worksheet ;
                break;
            case 'relations':
                $value = $post_excerpt->Worksheet ;
                break;
            case 'status':
                $value =  $item->post_status;
                break;
            default:
                $value = '--';
        }
    }

    /**
     * Retrieve the table columns.
     * @since 1.0.0
     * @return array $columns Array of all the list table columns.
    */
    public function get_columns() {
        $columns = array(
            'cb'                 => '<input type="checkbox" />',
            'IntegrationTitle'   => esc_html__( 'Title', 'ijcgs' ),
            'DataSource'         => esc_html__( 'Data Source', 'ijcgs' ),
            'Worksheet'          => esc_html__( 'Worksheet', 'ijcgs' ),
            'Spreadsheet'        => esc_html__( 'Spreadsheet', 'ijcgs' ),
            'Relations'          => esc_html__( 'ID : Column Title ⯈ Relations', 'ijcgs' ),
            'status'             => esc_html__( 'Status', 'ijcgs' )
        );
        return $columns;
    }

    # Render the checkbox column.
    public function column_cb( $item ) {
        return '<input type="checkbox" name="id[]" value="' . absint( $item->ID ) . '" />';
    }

    public function column_DataSource( $item ) {
        
        $post_excerpt = json_decode( $item->post_excerpt, true );
        
        if ( isset( $post_excerpt['DataSource'] ) ) {
            return esc_attr( $post_excerpt['DataSource'] );
        } else {
            _e( "Not Set !" , "ijcgs" );
        }
    }

    public function column_Worksheet( $item ) {
        
        $post_excerpt = json_decode( $item->post_excerpt, true );
       
        if ( isset($post_excerpt['Worksheet'] , $post_excerpt['WorksheetID'] ) ) {
            return  esc_attr( $post_excerpt['Worksheet'] ) . "<br><br><i>"  . esc_attr( $post_excerpt['WorksheetID'] ) ."</i>";
        } else {
            _e( "Not Set !" , "ijcgs" );
        }
    }

    public function column_Spreadsheet( $item ) {
        
        $post_excerpt = json_decode( $item->post_excerpt, true );
        
        if( isset( $post_excerpt['Spreadsheet'], $post_excerpt['SpreadsheetID'] ) ) {
            return esc_attr( $post_excerpt['Spreadsheet'] ) .  "<br><br><i>"  . esc_attr( $post_excerpt['SpreadsheetID'] ) ."</i>";
        } else {
            _e( "Not Set !" , "ijcgs" );
        }
    }

    # Working Here || Need To Change || Remove Empty Value array_filter()
    # Relations Output from DB
    public function column_Relations( $item ) {
        $string         = "";
        $DataSource     = json_decode( $item->post_excerpt, true )['DataSourceID'] ;
        $ColumnTitles   = json_decode( $item->post_content, true )[0];
        # ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        # Keep the Error in the Log 
        # Checking is Custom post_content data is Valid JSON AND didn't edited if edited and not valid return an empty array;
        if ( json_decode( $item->post_content, true ) ){
            $Relations  = array_filter( json_decode( $item->post_content, true )[1] );
        } else {
            $string     = "<b>Error: invalid JSON string. Please delete this integration & create new one.</b>";
            $Relations  = array();
        }

        $data           = array();
        $eventsAndTitlesBracket = array();

        # Change The key to Bracketed 
        if ( isset( $this->eventsAndTitles[$DataSource] ) ){
            foreach ( $this->eventsAndTitles[$DataSource] as $key => $value ) {
                $eventsAndTitlesBracket[ "{{". $key ."}}" ] = "<code><b>" . esc_attr( $value ) ."</b></code>" ;
            }
        }

        # replace the placeholder ;
        $countRelations = count($Relations); 
        $i = 0 ;
        foreach ( $Relations as $key => $value ) {
            $i++ ;
            if ( $i == $countRelations ) {
                $string .= $key . " : " . esc_attr( $ColumnTitles[ $key] ) . " ⯈ " . strtr( $value, $eventsAndTitlesBracket) ;
            } else {  
                $string .= $key . " : " . esc_attr( $ColumnTitles[ $key] ) . " ⯈ " . strtr( $value, $eventsAndTitlesBracket) . "<br>" ;
            }
        }

        return  $string;
    }

    # .........................................................................
    # Need some Update to this Place 
    # .........................................................................
    public function column_status( $item ) {
        # Integration status 
        if ( $item->post_status == 'publish' ) {
            $actions = "<br><span title='Enable or Disable the Integrations'  onclick='window.location=\"admin.php?page=ijcgs&action=status&id=" . esc_html($item->ID) . "\"'  class='a_activation_checkbox'  ><a class='a_activation_checkbox' href='?page=ijcgs&action=edit&id=".$item->ID."'>  <input type='checkbox' name='status' checked=checked > </a></span>" ;
        } else {
            $actions = "<br><span title='Enable or Disable the Integrations' onclick='window.location=\"admin.php?page=ijcgs&action=status&id=" . esc_html($item->ID) . " \"'  class='a_activation_checkbox'  ><a class='a_activation_checkbox' href='?page=ijcgs&action=edit&id=".$item->ID."'>  <input type='checkbox' name='status' > </a></span>" ;
        }

        # Creating Sheet Column Title 
        $actions .= "<br><br><span title=Test Fire ! Please check your Google Spreadsheet for effects' onclick='window.location=\"admin.php?page=ijcgs&action=columnTitle&id=" . esc_html($item->ID) . " \"'  class='a_remoteUpdate_checkbox'> <span class='dashicons dashicons-controls-repeat'></span> </span>";

        # getting Data source ID 
        $DataSourceID  = json_decode( $item->post_excerpt, true )['DataSourceID'] ;
       
        # if Post type then show the Download button 
        if( in_array( $DataSourceID ,  array_keys( $this->ijcgs_postTypeDetails()[2] ) ) ) {
            # getting the remote Update Status 
            $remoteUpdateStatus = get_post_meta(  $item->ID , "remoteUpdateStatus", TRUE);
            
            # Enable and Disable Remote Sheet  remoteUpdate
            if ( $remoteUpdateStatus ) {
                $actions .= "<br><br><span title='Enable or Disable Update from the Google Sheet'  onclick='window.location=\"admin.php?page=ijcgs&action=remoteUpdateStatus&id=" . esc_html($item->ID) . "\"'  class='a_remoteUpdate_checkbox'> <input type='checkbox' name='remoteUpdate' checked=checked > </span>";
            } else {
                $actions .= "<br><br><span title='Enable or Disable Update from the Google Sheet' onclick='window.location=\"admin.php?page=ijcgs&action=remoteUpdateStatus&id=" . esc_html($item->ID) . " \"'  class='a_remoteUpdate_checkbox'> <input type='checkbox' name='remoteUpdate' > </span>";
            }
            
            # Remote Update help;
            $actions .= "<br><br><span title='Update from remote Google Sheet Help & code for this Integration.' onclick='window.location=\"admin.php?page=ijcgs&action=remoteUpdate&id=" . esc_html($item->ID) . " \"'  class='a_remoteUpdate_checkbox'> <span class='dashicons dashicons-database-import'></span> </span>";
        } 

        # return the icons and text
        return   $actions ;
    }

    # Render the form name column with action links.
    public function column_IntegrationTitle( $item ) {
        $name = ! empty( $item->post_title ) ? $item->post_title : '--';
        $name = sprintf( '<span><strong>%s</strong></span>', esc_html__( $name ) );
        # Build all of the row action links.
        $row_actions = array();
        # Edit.
        $row_actions['edit'] = sprintf(
            '<a href="%s" title="%s">%s</a>',
            add_query_arg(
                array(
                    'action' => 'edit',
                    'id'     => $item->ID,
                ),
                admin_url( 'admin.php?page=ijcgs' )
            ),
            esc_html__( 'Edit This Relation', 'ijcgs' ),
            esc_html__( 'Edit', 'ijcgs' )
        );

        # Delete.
        $row_actions['delete'] = sprintf(
            '<a href="%s" class="relation-delete" title="%s">%s</a>',
            wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => 'delete',
                        'id'     => $item->ID,
                    ),
                    admin_url( 'admin.php?page=ijcgs' )
                ),
                'ijcgs_delete_relation_nonce'
            ),
            esc_html__( 'Delete this relation', 'ijcgs' ),
            esc_html__( 'Delete', 'ijcgs' )
        );

        # Build the row action links and return the value.
        return $name . $this->row_actions( apply_filters( 'fts_relation_row_actions', $row_actions, $item ) );
    }

    # Define bulk actions available for our table listing.
    public function get_bulk_actions() {
        $actions = array(
            'delete' => esc_html__( 'Delete', 'ijcgs' ),
        );
        return $actions;
    }

    # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # This Function Should be Remove || Use ijcgs_delete_connection function in ijcgs admin class
    # Process the bulk actions.
    public function process_bulk_actions() {
        # getting the ids
        $ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
        # security and ID Check 
        if ( $this->current_action() == 'delete' && wp_verify_nonce( $_GET['ijcgs_nonce'], 'ijcgs_nonce_bulk_action' ) && ! empty( $ids )  ) {
            # Loop the Ids
            foreach ( $ids as $id ) {
                wp_delete_post( $id );
            }

            # Caching the integrations 
            $integrations =  $this->ijcgs_getIntegrations();
            if ( $integrations[0] ){
                # setting or updating the transient;
                set_transient( 'ijcgs_integrations', $integrations[1] );
            }
        }
    }

    # Message to be displayed when there are no relations.
    public function no_items() {
        printf(
            wp_kses(
                __( 'Whoops, you haven\'t created a relation yet. Want to <a href="%s">give it a go</a>?', 'ijcgs' ),
                array(
                    'a' => array(
                        'href' => array(),
                    ),
                )
            ),
            admin_url( 'admin.php?page=ijcgs&action=new' )
        );
    }

    # Sortable settings.
    public function get_sortable_columns() {
        return array(
            'IntegrationTitle'       => array('IntegrationTitle', TRUE),
            'data_source'            => array('data_source', TRUE),
            'spreadsheetsAndProvider'=> array('spreadsheetsAndProvider', TRUE),
        );
    }

    # Fetching Data from Database 
    public function fetch_table_data() {
        return get_posts( array( 
            'post_type'     =>'ijcgsIntegration',
            'post_status'   => 'any',
            'posts_per_page'=> -1 ,
        )); 
    }

    # Query, filter data, handle sorting, pagination, and any other data-manipulation required prior to rendering
    public function prepare_items() {
        # Process bulk actions if found.
        $this->process_bulk_actions();
        # Defining Values
        $per_page              = 20;
        $count                 = $this->count();
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $table_data            = $this->fetch_table_data();
        $this->items           = $table_data;
        $this->admin_header();

        $this->set_pagination_args(
            array(
                'total_items' => $count,
                'per_page'    => $per_page,
                'total_pages' => ceil( $count / $per_page ),
            )
        );
    }

    # Count Items for Pagination 
    public function count() {
        $ijcgs_posts = get_posts( array( 
            'post_type'     => 'ijcgsIntegration',
            'post_status'   => 'any',
            'posts_per_page'=> -1,
        )); 
        return count($ijcgs_posts);
    }

    /**
	 * This Function Will return all the Save integrations from database 
	 * @since      3.4.0
	 * @return     array   	 This Function Will return an array 
	*/
	public function ijcgs_getIntegrations( ) {
		# Setting Empty Array
		$integrationsArray 		= array();
		# Getting All Posts
		$listOfConnections   	= get_posts( array(
			'post_type'   	 	=> 'ijcgsIntegration',
			'post_status' 		=> array('publish', 'pending'),
			'posts_per_page' 	=> -1
		));

		# integration loop starts
		foreach ( $listOfConnections as $key => $value ) {
			# Compiled to JSON String 
			$post_excerpt = json_decode( $value->post_excerpt, TRUE );
			# if JSON Compiled successfully 
			if ( is_array( $post_excerpt ) AND !empty( $post_excerpt ) ) {
				$integrationsArray[$key]["IntegrationID"] 	= $value->ID;
				$integrationsArray[$key]["DataSource"] 		= $post_excerpt["DataSource"];
				$integrationsArray[$key]["DataSourceID"] 	= $post_excerpt["DataSourceID"];
				$integrationsArray[$key]["Worksheet"] 		= $post_excerpt["Worksheet"];
				$integrationsArray[$key]["WorksheetID"] 	= $post_excerpt["WorksheetID"];
				$integrationsArray[$key]["Spreadsheet"] 	= $post_excerpt["Spreadsheet"];
				$integrationsArray[$key]["SpreadsheetID"] 	= $post_excerpt["SpreadsheetID"];
				$integrationsArray[$key]["Status"] 			= $value->post_status;
			} else {
				# Display Error, Because Data is corrected or Empty 
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

    # Check this Function! may be useless 
    public function admin_header() {
        $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
        # if another page redirect user;
        if ( 'ijcgs' != $page ){
            return;
        }
        
        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 10%; }';
        echo '.wp-list-table .column-IntegrationTitle { width: 10%; }';
        echo '.wp-list-table .column-DataSource { width: 15%; }';
        echo '.wp-list-table .column-Worksheet { width: 15%; }';
        echo '.wp-list-table .column-Spreadsheet { width: 20%; }';
        echo '.wp-list-table .column-Relations { width: 25%; }';
        echo '.wp-list-table .column-status { width: 5%; }';
        echo '</style>';
    }

    # New Code Starts 
    /**
	 * This Function will All Post types except some;
	 * @since      3.6.0
	 * @return     array   First one is CPS and Second one is CPT's Field source.
	*/
	public function ijcgs_postTypeDetails(){
		# Getting The Global wp_post_types array.
		global $wp_post_types;
		# Check And Balance.
		if(isset($wp_post_types) && !empty($wp_post_types)){
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
			foreach($wp_post_types as $postKey => $PostValue){
				# if Post type is Not Default.
				if(isset($PostValue->_builtin)  AND ! $PostValue->_builtin){
					# Look is it on remove list, if not insert 
					if(! in_array($postKey, $removeArray)){
						# Pre populate $postType array 
						if(isset($PostValue->label) AND ! empty($PostValue->label)){
							$postType[$postKey]  =  $PostValue->label ." (".  $postKey. ")";
						}else{
							$postType[$postKey]  =  $postKey;
						}
					}
				}
			}

			# Empty Holder Array for CPT events 
			$postTypeEvents = array(
				'wordpress_newPost'		=> 'Wordpress New Post',
				'wordpress_editPost'	=> 'Wordpress Edit Post',
				'wordpress_deletePost'	=> 'Wordpress Delete Post',
				'wordpress_page'		=> 'Wordpress Page',
				# ------------------ Block those if WooCommerce is :: error safe  --------------------
				'wc-new_product'		=> 'WooCommerce New Product',
				'wc-edit_product'		=> 'WooCommerce Update Product',
				# ------------------ Block those if WooCommerce is :: error safe  --------------------
                "wc-new_order"	        => "WooCommerce New Checkout Page Order",
                "wc-pending"	        => "WooCommerce Order Pending payment",
                "wc-processing"	        => "WooCommerce Order Processing",
                "wc-on-hold"	        => "WooCommerce Order On-hold",
                "wc-completed"	        => "WooCommerce Order Completed",
                "wc-cancelled"	        => "WooCommerce Order Cancelled",
                "wc-refunded"	        => "WooCommerce Order Refunded",
                "wc-failed"		        => "WooCommerce Order Failed",
			);

			# Creating events 
			if(!empty($postType)){
				# Looping for Creating Extra Events Like Update and Delete 
				foreach($postType as $key => $value){
					$postTypeEvents['cpt_new_'.$key] 	=  'CPT New '.$value;
					$postTypeEvents['cpt_update_'.$key] =  'CPT Update '.$value;
					$postTypeEvents['cpt_delete_'.$key] =  'CPT Delete '.$value;
				}

				# Adding default POST AND PAGE the front of $postType Array 
				$postType = array("wordpress_Post"=>"Wordpress Post", "wordpress_page"=>"Wordpress Page") + $postType;
				
				# Everything seems ok, Now send the CPT events and Related Data source;
				return array(TRUE, $postType, $postTypeEvents );
			}else{
				return array(FALSE, "ERROR: postType Array is Empty.");
			}
		}else{
			return array(FALSE, "ERROR: wp_post_types global array is not exists or Empty.");
		}
	}


}
