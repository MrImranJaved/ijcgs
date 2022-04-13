<?php
/**
 * Fired during plugin deactivation.
 * This class defines all code necessary to run during the plugin's deactivation.
 * @since      1.0.0
 * @package    IJcgs
 * @subpackage IJcgs/includes
 * @author     javmah <jaedmah@gmail.com>
*/

# This  Google System Will RUN on GOOGLE SERVICE ACCOUNT SO PREVIOUS SYSTEM OF OAUTH2 WILL STOP FROM NOW ON .
# Composer Auto Loads
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';
use \Firebase\JWT\JWT;

class IJcgs_Google_Sheet {

	/**
	 * The ID of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name     The ID of this plugin.
	*/
	private $plugin_name;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version   The version of this plugin.
	*/
	private $version;

	/**
	 * The version of this plugin.
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $events    The version of this plugin.
	*/
	private $events;

	/**
	 * Private_key_id of  Google Service Account Credentials  
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $private_key_id   Private_key_id of  Google Service Account Credentials  
	 */				
	public $private_key_id;	

	/**
	 * Private_key Google Service Account Credentials  
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $private_key    Private_key Google Service Account Credentials  .
	 */		
	public $private_key;	

	/**
	 * Google Service Account Credentials  client_email aka service account email
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $client_email    Google Service Account Credentials  client_email aka service account email
	*/			
	public $client_email;

	/**
	 * Google Service Account Credentials client id 
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $client_id    Google Service Account Credentials client id 
	*/	
	public $client_id;
	
	/**
	 * Common methods used in the all the classes 
	 * @since    3.6.0
	 * @var      object    $version    The current version of this plugin.
	*/	
	public $common;


	public function __construct( $plugin_name, $version, $common ) {
		# setting Plugin name 
		$this->plugin_name 	= $plugin_name;
		# setting version 
		$this->version 		= $version;
		# Common function
		$this->common 		= $common;
		# getting Gkeys from Saved Options ;
		$gkey 				= get_option('ijcgs_google_credential');
		# Assigned the Class Variables Value ;
		if ( $gkey AND isset( $gkey['private_key_id'], $gkey['private_key'], $gkey['client_email'], $gkey['client_id'] ) ) {
			# setting values from saved meta Data
			$this->private_key_id 	= $gkey['private_key_id'];
			$this->private_key 		= $gkey['private_key'];
			$this->client_email 	= $gkey['client_email'];
			$this->client_id 		= $gkey['client_id'];
		}
	}

	/**
     * This is a Testing method for "IJcgs_Google_Sheet" class, call it to see Functionality      	
     */
	public function ijcgs_google_test(){
		# Log 
		$this->common->ijcgs_log( get_class($this), __METHOD__, "200", "SUCCESS: This is testing; From GOOGLE Class. Check the log.");
	}


	/**
     * Creating google API tokens & Getting tokens from Google                      		
     * @param string|array  $credential       Google Service account token.
     * @note Some error On This Function || When There is No Internat it Show error. 
     * @uses 
    */
	public function ijcgs_token(  $credential = [] ){
		# Check is Token array or not
		if ( ! is_array( $credential )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "300", "ERROR: credential is Not Array. from ijcgs_token function ! " . $credential );
			return array( FALSE, "ERROR: credential is Not Array !" );
		}
		# Check  client_email is set or not 
		if ( ! isset( $credential['client_email']) ) {
			$this->common->ijcgs_log( get_class( $this ),__METHOD__,"301", "ERROR: client_email not set. from  ijcgs_token function !");
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => 'ERROR: client_email not set. from  ijcgs_token function !') );
		}
		#  check client_email is empty or not
		if ( empty( $credential['client_email'] )  ){
			$this->common->ijcgs_log( get_class( $this ), __METHOD__, "302", "ERROR: client_email is Empty. from ijcgs_token function !");
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: client_email is Empty. from ijcgs_token function !") );
		}
		# Check private_key is set or not
		if ( ! isset( $credential['private_key'] ) ) {
			$this->common->ijcgs_log( get_class( $this ),__METHOD__,"303", "ERROR: private_key not set. from  ijcgs_token function !");
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: private_key not set. from  ijcgs_token function !") );
		}
		# Check private_key is Empty or not
		if ( empty( $credential['private_key'] ) ){
			$this->common->ijcgs_log( get_class( $this ), __METHOD__, "304", "ERROR: private_key is Empty. from ijcgs_token function !");
			return array( FALSE, array('ERROR:'=> 420 , 'Message' => "ERROR: private_key is Empty. from ijcgs_token function !") );
		}

		# Creating payload
		$payload = array(
		    "iss" 	=>  $credential['client_email'],
		    "scope"	=> 'https://www.googleapis.com/auth/drive',
		    "aud" 	=> 'https://oauth2.googleapis.com/token',
		    "exp"	=>	time()+3600,
		    "iat" 	=> 	time(),
		);

		$jwt  = JWT::encode( $payload, $credential['private_key'], 'RS256' );

		$args = array(
		    'headers' => array(),
		    'body'    => array(
	            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
	            'assertion'  => $jwt,
	        )
		);
		
		# Token url Remote request 
		$returns  =  wp_remote_post('https://oauth2.googleapis.com/token', $args );
		# Check & Balance 
		if ( is_wp_error( $returns ) OR ! is_array( $returns ) OR ! isset( $returns['body'] )  ) {
			# Inserting error log 
			$this->common->ijcgs_log( get_class($this),__METHOD__,"305","ERROR:  on token Creation." . json_encode( $returns, TRUE ) );
			return array( FALSE, "ERROR :  on token Creation." . json_encode( $returns, TRUE )  );
		} else {
			# inserting SUCCESS log
			$this->common->ijcgs_log( get_class($this),__METHOD__,"200","SUCCESS: Successfully token created.");
			return array( TRUE, json_decode( $returns['body'], TRUE ) );
		}
	}
	
	/**
     * Token Validation Checking , GET request to google server to know detail information on google token.                        		
     * @param array  $token       Google Service account token.
     * @note Some ERROR On This Function || When There is No Internat it Show ERROR. 
     * @uses 
     */
	public function ijcgs_token_validation_checker(  $token = [] ){
		# Check is Token array or not
		if ( ! is_array( $token )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "306", "ERROR: Token is Not Array! " . $token );
			return array( FALSE, "ERROR: Token is Not Array !" );
		}
		# Check access_token elements is set or not;
		if ( ! isset( $token['access_token'] ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "307", "ERROR: access_token elements is_not_set in the token Array ! " . json_encode( $token ) );
			return array( FALSE, "ERROR: access_token elements are not set on the token Array!" );
		}
		# Check access_token element is empty or not 
		if ( empty( $token['access_token'] ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "308", "ERROR: access_token elements is empty on the token Array ! " . json_encode( $token ) );
			return array( FALSE, "ERROR: access_token elements is empty on the token Array!" );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$request = wp_remote_get( "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $token['access_token'] );
		# is_wp_error()
		if (  is_wp_error( $request ) OR ! isset( $request['response']['code'])  OR $request['response']['code'] != 200) {
			$this->common->ijcgs_log( get_class($this),__METHOD__, "309", "ERROR: Token Validation Checked, Invalid token [x]. Response is : " . json_encode( $request ) );
			return  array( FALSE,  json_encode( $request ) );
		} else {
			$this->common->ijcgs_log( get_class($this), __METHOD__, "200", "SUCCESS: Token Validation Checked, Valid Token [ok].");
			return  array( TRUE, $request['body'] );
		}
	}
	
    /**
     * Fetching the user spreadsheets , That had shared With Service Account Email.                      		
     * @param array  $token       Google Service account token.
     * @note This Function Should Need To Check , Is it a fawo function or use full . 
     * @uses 
     */
	public function ijcgs_spreadsheets( $token = [] ){
		# Check is Token array or not
		if ( ! is_array( $token ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "310", "ERROR: Token is Not Array. from ijcgs_spreadsheets func !" );
			return array( FALSE, "ERROR : Token is Not Array. from ijcgs_spreadsheets func !"  );
		}
		# Check access_token elements is set or not 
		if ( ! isset( $token['access_token'] )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "311", "ERROR: access_token elements are not set on the token Array! from ijcgs_spreadsheets func !" );
			return array( FALSE, "ERROR : access_token elements are not set on the token Array! from ijcgs_spreadsheets func !"  );
		}
		# Check access_token element is empty or not 
		if ( empty( $token['access_token'] )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "311", "ERROR: access_token elements is empty on the token Array! from ijcgs_spreadsheets func !" );
			return array( FALSE, "ERROR : access_token elements is empty on the token Array! from ijcgs_spreadsheets func !"  );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$returns = wp_remote_get( "https://www.googleapis.com/drive/v3/files?access_token=" . $token['access_token'] );

		# Check and Balance the $returns
		if ( is_wp_error( $returns ) OR ! isset( $returns['response']['code'] ) OR $returns['response']['code'] != 200){
			$this->common->ijcgs_log( get_class($this),__METHOD__,"312","ERROR: spreadsheets returns failed. Response is : " . json_encode( $returns ) );
			return array( FALSE, json_encode( $returns )  );
		}

		$spreadsheets 	= array();
		$body 			= json_decode( $returns['body'], TRUE );
		$files 			= $body['files'];
		# Looping the CSV files 
		foreach( $files  as $file ){
			if ( $file['mimeType'] == "application/vnd.google-apps.spreadsheet" ) {
				$spreadsheets[ $file['id'] ] = $file['name'];
			}
		}

		return array( TRUE, $spreadsheets );
	}

	/**
     * List of Google worksheets of a given spreadsheet.                       		
     * @param string  		$spreadsheet_id     Google Spreadsheet ID.
     * @param array  		$token        		Google Service account token.
     * @note This Function Should Need To Check , Is it a fawo function or use full . 
     * @uses 
     */
	public function ijcgs_worksheets( $spreadsheet_id = '',  $token = [] ){
		# Check spreadsheet_id is empty or not
		if ( ! is_string( $spreadsheet_id ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "313", "ERROR: spreadsheet_id is not string. from ijcgs_worksheets func !" );
			return array( FALSE, "ERROR: spreadsheet_id is not string. from ijcgs_worksheets func !" );
		}
		# Check spreadsheet_id is empty or not
		if ( empty( $spreadsheet_id ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "314", "ERROR: spreadsheet_id id is empty! from ijcgs_worksheets func !" );
			return array( FALSE, "ERROR: spreadsheet_id id is empty! from ijcgs_worksheets func !" );
		}
		# Check is Token array or not
		if ( ! is_array( $token ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "315", "ERROR: Token is Not Array. from ijcgs_worksheets func !" );
			return array( FALSE, "ERROR: Token is Not Array. from ijcgs_worksheets func !"  );
		}
		# Check access_token elements is set or not 
		if ( ! isset( $token['access_token'] )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "316", "ERROR: access_token elements are not set on the token Array! from ijcgs_worksheets func !" );
			return array( FALSE, "ERROR: access_token elements are not set on the token Array! from ijcgs_worksheets func !" );
		}
		# Check access_token element is empty or not 
		if ( empty( $token['access_token'] )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "317", "ERROR: access_token elements is empty on the token Array! from ijcgs_worksheets func !" );
			return array( FALSE, "ERROR: access_token elements is empty on the token Array! from ijcgs_worksheets func !"  );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$returns = wp_remote_get( "https://sheets.googleapis.com/v4/spreadsheets/" . $spreadsheet_id . "/?access_token=" . $token['access_token'] );
		
		# Response Status Check 
		if ( is_wp_error( $returns ) OR ! isset( $returns['response']['code'] ) OR $returns['response']['code'] != 200){
			$this->common->ijcgs_log( get_class( $this ), __METHOD__, "318", "ERROR:  on getting spreadsheets. " . json_encode( $returns ) );
			return array( FALSE, json_encode( $returns ) );
		}

		# Empty Holder;
		$sheets = array();
		# Body JSON TO ARRAY;
		$body 	= json_decode( $returns['body'], TRUE);
		#
		foreach ( $body['sheets'] as $value) {
			$sheets[ $value['properties']['sheetId'] ] = $value['properties']['title'];
		}
		# returns the worksheets array; 
		return array( TRUE, $sheets );
	}
	
    
	/**
     * Google spreadsheets and worksheets combine for a relational data structure . 
     * SpreadsheetsID:{"1st worksheetsName":"1st worksheetsKey" ,"2nd worksheetsName":"2nd worksheetsKey"}                             		
     * @param array  $token        		Google Service account token.
     * @uses 		 class method's to get the data 
    */
	public function ijcgs_spreadsheetsAndWorksheets( $token = [] ){
		# Check is Token array or not
		if ( ! is_array( $token ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "319", "ERROR: Token is Not Array. from ijcgs_spreadsheetsAndWorksheets func !" );
			return array( FALSE, "ERROR : Token is Not Array. from ijcgs_spreadsheetsAndWorksheets func !"  );
		}
		# Check access_token elements is set or not 
		if ( ! isset( $token['access_token'] )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "320", "ERROR: access_token elements are not set on the token Array! from ijcgs_spreadsheetsAndWorksheets func !" );
			return array( FALSE, "ERROR : access_token elements are not set on the token Array! from ijcgs_spreadsheetsAndWorksheets func !"  );
		}
		# Check access_token element is empty or not 
		if ( empty( $token['access_token'] )  ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "321", "ERROR: access_token elements is empty on the token Array! from ijcgs_spreadsheetsAndWorksheets func !" );
			return array( FALSE, "ERROR : access_token elements is empty on the token Array! from ijcgs_spreadsheetsAndWorksheets func !"  );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$returns = wp_remote_get( "https://www.googleapis.com/drive/v3/files?access_token=" . $token['access_token'] );
		
		# Response Status Check 
		if ( is_wp_error( $returns )  OR  ! isset( $returns['response']['code'] )  OR $returns['response']['code'] != 200 ){
			$this->common->ijcgs_log( get_class( $this ), __METHOD__, "322", "ERROR:  on getting shared spreadsheets. " . json_encode( $returns ) );
			return array( FALSE, json_encode( $returns ) );
		}

		# Func variables and array's;
		$body 					= json_decode( $returns['body'], TRUE );
		$files 					= $body['files'];
		$spreadsheets 			= array();
		$spreadsheetsWorksheet  = array();
		
		# Sorting Spreadsheet Only ;
		foreach ( $files  as $file ){
			if ( $file['mimeType'] == "application/vnd.google-apps.spreadsheet" ) {
				$spreadsheets[ $file['id'] ] = $file['name'];
			}
		}

		# Getting worksheets of those spreadsheets
		foreach ( $spreadsheets as $spreadsheetsKey => $spreadsheetsName) {
			# Creating URL 
			$worksheetsReturns = wp_remote_get( "https://sheets.googleapis.com/v4/spreadsheets/" . $spreadsheetsKey."/?access_token=" . $token['access_token'] );
			# There Maybe an ERROR || Object as array ;
			if ( ! is_wp_error( $worksheetsReturns )  &&  isset( $worksheetsReturns['response']['code'] ) && $worksheetsReturns['response']['code'] == 200 ){
				# JSON to PHP Array;
				$worksheetsResponseBody = json_decode($worksheetsReturns['body'] , TRUE);
				# Temporary worksheets Holder;
				$sheets = array();
				# Looping spreadsheets;
				foreach ( $worksheetsResponseBody['sheets'] as $value) {
					$sheets[ $value['properties']['sheetId'] ] = $value['properties']['title'];
				}
				# Populating $spreadsheetsWorksheet Array For Output ;
				$spreadsheetsWorksheet[ $spreadsheetsKey ] = array( $spreadsheetsName, $sheets );
			} else {
				$this->common->ijcgs_log( get_class( $this ),__METHOD__,"323",  json_encode( $worksheetsReturns ) );
				return array( FALSE,  json_encode( $worksheetsReturns ) );
			}
		}
		# Returns || Remember It's an array so Git tha value on that Way ;
		return array( TRUE, $spreadsheetsWorksheet );
	}

	/**
     * Read Google worksheet 1st row for relation purpose .
     * @param string		$worksheet_name    	Google spreadsheet ID.
     * @param string        $spreadsheets_id    Google worksheet ID.                            		
     * @param array  		$token        		Google Service account token.
     * @uses 
    */
	public function ijcgs_columnTitle( $worksheet_name = '',  $spreadsheets_id = '', $token = [] ){
		# check worksheet_name is empty or not  
		if ( empty( $worksheet_name ) ){
			return array( FALSE, "ERROR: worksheet_name is Empty. from  ijcgs_columnTitle func" );
		}
		# Check spreadsheets_id is empty or not
		if ( empty( $spreadsheets_id ) ){
			return array( FALSE, "ERROR: spreadsheets_id is Empty. from  ijcgs_columnTitle func" );
		}
		# Check is Token array or not
		if ( ! is_array( $token ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "324", "ERROR: Token is Not Array. from ijcgs_columnTitle func !" );
			return array( FALSE, "ERROR: Token is Not Array. from ijcgs_columnTitle func !"  );
		}
		# Check access_token elements is set or not 
		if ( ! isset( $token['access_token'] ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "325", "ERROR: access_token elements are not set on the token Array! from ijcgs_columnTitle func !" );
			return array( FALSE, "ERROR: access_token elements are not set on the token Array! from ijcgs_columnTitle func !"  );
		}
		# Check access_token element is empty or not 
		if ( empty( $token['access_token'] ) ){
			$this->common->ijcgs_log( get_class($this),__METHOD__, "326", "ERROR: access_token elements is empty on the token Array! from ijcgs_columnTitle func !" );
			return array( FALSE, "ERROR: access_token elements is empty on the token Array! from ijcgs_columnTitle func !"  );
		}
		# If passed parameter is Array and Not String  || Creating Query URL
		$request = wp_remote_get( 'https://sheets.googleapis.com/v4/spreadsheets/'. $spreadsheets_id . '/values/'. $worksheet_name . '!A1:YZ1?access_token='. $token['access_token'] );
		
		# If Not response code is not 200 then return ERROR with ERROR code 
		if ( is_wp_error( $request )  OR  ! isset( $request['response']['code']) OR $request['response']['code'] != 200 ){
			$this->common->ijcgs_log( get_class( $this ), __METHOD__, "327", "ERROR: on getting worksheet column title || worksheet name '".$worksheet_name."', Response is : " . json_encode( $request ) );
			return array( FALSE, json_encode( $request) );
		}

		# Converting json body into PHP array 
		$responseBody = json_decode( $request['body'], TRUE );
		# If There are no column title or First ROW is Empty Then Send a Arry with key without value 
		if ( !isset( $responseBody['values'][0] ) ) {
        	return array( TRUE, array( "A"=>"","B"=>"","C"=>"","D"=>"","E"=>"","F"=>"","G"=>"","H"=>"","I"=>"","J"=>"","K"=>"","L"=>"","M"=>"","N"=>"","O"=>"","P"=>"","Q"=>"","R"=>"","S"=>"","T"=>"","U"=>"","V"=>"","W"=>"","X"=>"","Y"=>"","Z"=>"" ) );
		}

		# this code is after 3.5.0, This will solve ERROR: invalid JSON string. Please delete this integration & create new one.
		# removing Single quotes &#39;
		$responseBody['values'][0] = str_replace( '\'', '&#39;',  $responseBody['values'][0]);
		# removing Double quotes  &#34;
		$responseBody['values'][0] = str_replace( '"',  '&#34;',  $responseBody['values'][0]);

		# Below Are vary Funny Code Just See IT .
		# What A Marka Mara Code ( funny Code as you know it is not int , so how come ) || LOL  || garbage code ...........! Like PHP , Its a BUG of PHP or So called Spacial features. -javmah , Dhaka , Bangladesh . 
		$key_array = array();
		for ( $i = "A"; $i < 'ZZ' ; $i++ ) {
			array_push( $key_array, $i );
		}

		# Combining arrays for return 
		$columnKeyTitle  = array_combine( array_slice( $key_array, 0,  count( $responseBody['values'][0] ) ), $responseBody['values'][0] );
		
		return array( TRUE, $columnKeyTitle );
	}

	/**
     * Insert data into Google  spreadsheets 
     *
     * @param string		$spreadsheetID    	Google Spreadsheet ID.
     * @param string        $worksheetsID     	Google worksheet ID.
     *         				If the algorithm used is asymmetric, this is the private key
     * @param array  		$dataArray        	Data array.
     * @uses 				Custom Hooks , like ijcgs_kattas
     */
	public  function ijcgs_append_row( $spreadsheetID = '', $worksheetsID = '', $dataArray = '' ){
		# error_log( print_r("writing error log to log file ", true) );
		# Check & Balance if spreadsheetID is Empty
		if ( empty( $spreadsheetID ) ){
			$this->common->ijcgs_log( get_class( $this ),__METHOD__,"328", 'ERROR: spreadsheetID is Empty. from  ijcgs_append_row func' );
			return array( FALSE, "ERROR: spreadsheetID is Empty.");  							//	Should be in ERROR log
		}
		# Check & Balance if worksheetsID is Empty *** carefully if use empty() it will show error on first Sheet
		if ( is_null( $worksheetsID ) ){
			$this->common->ijcgs_log( get_class( $this ),__METHOD__,"329", 'ERROR: worksheetsID is Empty. worksheetsID is : '. $worksheetsID  );
			return array( FALSE, "ERROR: worksheetsID is Empty.");								//	Should be in ERROR log
		}
		# Check & Balance if Data Array is Empty
		if ( empty( $dataArray ) ){
			$this->common->ijcgs_log( get_class( $this ),__METHOD__,"330", 'ERROR: dataArray is Empty.' );
			return array( FALSE, "ERROR: dataArray is Empty.");									//	Should be in ERROR log
		}
		# Check & Balance if Data Array is not an array or a Object
		if ( ! is_array( $dataArray ) ){
			$this->common->ijcgs_log( get_class( $this ),__METHOD__,"331", 'ERROR: dataArray should be Array.' );
			return array( FALSE, "ERROR: dataArray should be Array.");
		}

		# Checking Token Validation starts
		$credential = get_option( 'ijcgs_google_credential' );
		$token 		= get_option( 'ijcgs_google_token' );
		# Checking & validation 
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
						# coping The new_token to Old token array
						$token = $new_token;
						# Save new token to Options
						update_option( 'ijcgs_google_token', $token[1] );
					} else {
						$this->events->ijcgs_google_log( get_class($this), __METHOD__,"332", "ERROR : on creating token. " . json_encode( $new_token ) );
					}
				} else {
					$this->events->ijcgs_google_log( get_class($this), __METHOD__,"333", "ERROR: get_option('ijcgs_google_credential'); is FALSE or Empty" );
				}
			}
		} else {
			$this->events->ijcgs_google_log( get_class($this), __METHOD__,"334", "ERROR: get_option('ijcgs_google_token'); is FALSE or Empty" );
		}
		# Checking Token validation ends
		
		# getting worksheet Name || because google sheet API need a worksheet name NOT worksheet Id so ;
		$worksheets = $this->ijcgs_worksheets( $spreadsheetID, $token ) ;
		# Check & Balance 
		if ( $worksheets[0] AND isset( $worksheets[1][$worksheetsID] ) ){
			$worksheetName = $worksheets[1][$worksheetsID] ;
		} else {
			$this->common->ijcgs_log( get_class($this),__METHOD__, "335", "ERROR : " .  json_encode( $worksheets ) );
			return array( FALSE,  "ERROR: " . json_encode( $worksheets ) );
		}
		# Request link;
		$url  = "https://sheets.googleapis.com/v4/spreadsheets/" . $spreadsheetID . "/values/" . $worksheetName . "!A:A:append?valueInputOption=USER_ENTERED";
		# Argument Array
		$args = Array(
		    'headers' => Array(
		    	'Authorization'=>'Bearer ' .  $token['access_token'],
		    	'Content-Type'=> 'application/json',
		    ),
		    'body' => '{"range":"' . $worksheetName . '!A:A", "majorDimension":"ROWS", "values":['.json_encode( array_values($dataArray) ).']}'
		);

		# insatiate the Request 
		$return  =  wp_remote_post( $url, $args );

		# After Work Response ;
		if ( ! is_wp_error( $return  ) ){
			# Inserting success into log
			$this->common->ijcgs_log( get_class( $this),__METHOD__, "200", "SUCCESS: " . json_encode( $return ));
			return array( TRUE, json_encode( array_values($dataArray) ) );
		} else {
			# Inserting ERROR into log
			$this->common->ijcgs_log( get_class( $this),__METHOD__,"336", "ERROR: from ijcgs_append_row func. " . json_encode( $return ) );
			return array( FALSE,  "ERROR: from ijcgs_append_row func. " . json_encode( $return ) );
		}
	}
}
