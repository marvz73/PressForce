<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://alphasys.com.au
 * @since      1.0.0
 *
 * @package    Press_Force_Plugin
 * @subpackage Press_Force_Plugin/admin
 */


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Press_Force_Plugin
 * @subpackage Press_Force_Plugin/admin
 * @author     Marvin Ayaay <marvin@alphasys.com.au>
 */
class Press_Force_Plugin_Admin {

	public $nextRecordsUrl;
	public $plugin_cache_path;

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		global $status, $page;

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_cache_path = plugin_dir_path( dirname( __FILE__ ) ) . 'temp/';


	}


	

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Press_Force_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Press_Force_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/press-force-plugin-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Press_Force_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Press_Force_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/press-force-plugin-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function admin_menu_display() {
	   	add_menu_page ( 
		    'Dashboard', 
		    'Press Force', 
		    'administrator', 
		    'press-force', 
		    array($this, 'dashboard_display'), 
		    'dashicons-tickets',
		    1 
	    );
    	$contact_page_hook = add_submenu_page ( 
		    'press-force', 
		    'Contacts Page', 
		    'Contacts', 
		    'administrator', 
		    'press-force-contact',
		    array($this, 'contacts_display')
	    );
   		add_submenu_page (
		    'press-force', 
		    'Settings Page', 
		    'Settings', 
		    'administrator', 
		    'press-force-settings',
		    array($this, 'settings_display')
		
	    );
	    add_action('load-'.$contact_page_hook, array($this, 'contact_help_screen'));
		add_action('load-'.$contact_page_hook, array($this, 'contact_screen_option'));
	}

	function contact_help_screen () 
	{
	    $screen = get_current_screen();
	    $screen->add_help_tab( 
	    	array(
	        	'id'	=> 'contact_search_tab',
	        	'title'	=> __('Contact Search'),
	        	'content'	=> '<p>' . __( '1. Descriptive content that will show in My Help Tab-body goes here.' ) . '</p>',
	    	)
	    );
	}

	function contact_screen_option()
	{
		$screen = get_current_screen(); //Initiate the $screen variable.
		add_filter('screen_layout_columns', array($this, 'display_screen_option')); //Add our custom HTML to the screen options panel.
		$screen->add_option('',''); //Register our new screen options tab.
	}

	function display_screen_option()
	{ 
		$options = get_option('salesforce_settings');
		$salesforce = new SalesforceAPI(
			$options['url'],
			'32.0',
			$options['client_id'], 
			$options['client_secret'],
			$options['redirect_uri']
		);
		$salesforce->getAccessToken($options['token']);
		$contacts_field = $salesforce->getsObject('sobjects/Contact/describe/');
		require_once('partials/press-force-contact-screenoption-display.php');
	}








	public function dashboard_display()
	{
		global $title;

		require_once('partials/press-force-plugin-admin-display.php');
	}

	public function contacts_display()
	{

		if( !get_option('salesforce_settings') ) 
		{
			die('Salesforce settings not found!');
		}

		global $title;
		$contact_list_table = new Contact_List_Table();

		$data = array();
		$recordCount = 1;
		$current_pagination_number = 1;
		$total_pagination_number = 0;

		$salesforceCacheContacts = 'salesforce_contacts';
	 	$db = new JsonDB($this->plugin_cache_path);

		$options = get_option('salesforce_settings');

		$salesforce = new SalesforceAPI(
			$options['url'],
			'32.0',
			$options['client_id'], 
			$options['client_secret'],
			$options['redirect_uri']
		);

		$current_pagination_number = $contact_list_table->get_pagenum();

		if(get_transient('s') == '' && $current_pagination_number == 1)
		{
			if(file_exists($this->plugin_cache_path . $salesforceCacheContacts))
			{
				$db->createTable($salesforceCacheContacts);
			}
			else
			{
				$db->deleteAll($salesforceCacheContacts);	
			}

			$salesforce->getAccessToken($options['token']);


			$contacts = $salesforce->searchSOQL('SELECT Id, FirstName, LastName, Email FROM Contact');
			

			foreach($contacts->records as $key=>$item)
			{
				$contact_info = array(
					'id' 			=> $recordCount++,
					'salesforce_id' => $item->Id,
					'email'			=> $item->Email,
					'firstName' 	=> $item->FirstName,
					'lastName' 		=> $item->LastName
				);

				$data[] = $contact_info;

				$db->insert($salesforceCacheContacts, $contact_info, true);
			}

		 	// update_option( 'salesforce_contacts', $data);

		 	set_transient( 'salesforce_nextrecords', $contacts->nextRecordsUrl, (60 * 60 * 24) );

		}
		else
		{
			$salesforce_contacts = $db->selectAll($salesforceCacheContacts, true);
			if(is_array($salesforce_contacts))
			{
				foreach($salesforce_contacts as $key=>$item)
				{

					$data[] = array(
						'id' 			=> $item['id'],
						'salesforce_id' => $item['salesforce_id'],
						'email'			=> $item['email'],
						'firstName' 	=> $item['firstName'],
						'lastName' 		=> $item['lastName']
					);
				}
			}
		}

		$total_pagination_number = (count($data) / 10);

		if($total_pagination_number == $current_pagination_number && get_transient('salesforce_nextrecords') != '')
		{

			$salesforce->getAccessToken($options['token']);
			$nextRecords = $salesforce->loadMore(get_transient('salesforce_nextrecords'), false);

			foreach($nextRecords->records as $key=>$item)
			{
				$contact_info = array(
					'id' 			=> $recordCount++,
					'salesforce_id' => $item->Id,
					'email'			=> $item->Email,
					'firstName' 	=> $item->FirstName,
					'lastName' 		=> $item->LastName
				);

				$data[] = $contact_info;

				$db->insert($salesforceCacheContacts, $contact_info, true);
			}

			set_transient( 'salesforce_nextrecords', $nextRecords->nextRecordsUrl, (60 * 60 * 24) );

		}
		
		$total_pagination_number = (count($data) / 10);

        if(isset($_GET['s']) && $_GET['s'] != '')
        {
			$search_keyword = preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['s']);
			$matches = array();
			set_transient('s', $search_keyword);

			$pattern = "/".$search_keyword."/i";  
			
			foreach($data as $key=>$value){
			    foreach($value as $key2=>$value2){
			        if(preg_match($pattern, $value2)){
			            // $matches[$key]=$value;
			            $matches[]=$value;

			            // $data[] = $value;
			            break;
			        }
			    }
			}

			$contact_list_table->table_collection = $matches;

        }
        else
        {
        	delete_transient('s');
        	$contact_list_table->table_collection = $data;
        }

        $contact_list_table->prepare_items();
        
		require_once('partials/press-force-plugin-contacts-display.php');
	}

	public function settings_display()
	{

		global $title;

		if(isset($_GET['code']) && $_GET['code'] != '')
		{

			$options = get_option('salesforce_settings');

			if( !get_option('salesforce_settings') ) 
			{
				die('Salesforce settings not found!');
			}

			$salesforce = new SalesforceAPI(
				$options['url'],
				'32.0',
				$options['client_id'], 
				$options['client_secret'],
				$options['redirect_uri']
			);

			$response = $salesforce->refresh_token($_GET['code']);

			if(isset($response['error']) && $response['error'])
			{
				wp_redirect( add_query_arg( array('page' => 'press-force-settings&error=1'), 'admin.php' ));

				die();
			}
			else
			{
				$options['token'] = $response['refresh_token'];

				$options['timestamp'] = time();

				update_option('salesforce_settings', $options);

				wp_redirect( add_query_arg( array('page' => 'press-force-settings&success=1'), 'admin.php' ));

				die();
			}
		}

	 	add_action( 'admin_post_add_foobar', 'prefix_admin_add_foobar' );
	 
		require_once('partials/press-force-plugin-settings-display.php');
	}


	public function prefix_admin_settings()
	{

		update_option('salesforce_settings', array(
				'client_id' 		=> $_POST['client_id'],
				'client_secret'		=> $_POST['client_secret'],
				'redirect_uri'		=> $_POST['redirect_uri'],
				'login_uri'			=> $_POST['login_uri'],
				'url'				=> $_POST['url'],
				'token'				=> '',
				'timestamp'			=> ''
			)
		);	 	
		
		$options = get_option('salesforce_settings');

		$auth_url = $options['login_uri'] . "/services/oauth2/authorize?response_type=code&client_id=" . $options['client_id'] . "&redirect_uri=" . urlencode($options['redirect_uri']);

		// wp_redirect( add_query_arg( array('page' => 'press-force-settings'), 'admin.php' ));

		wp_redirect($auth_url);

	}


}













/**
 * Create a new table class that will extend the WP_List_Table
 */

// $data[] = array(
//     'id'          => 1,
//     'title'       => 'The Shawshank Redemption',
//     'description' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
//     'year'        => '1994',
//     'director'    => 'Frank Darabont',
//     'rating'      => '9.3'
// );

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Contact_List_Table extends WP_List_Table
{

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public $table_collection;

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_collection;

        $perPage = 10;

        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
 
        $this->set_pagination_args( 
        	array(
	            'total_items' => $totalItems,
	            'per_page'    => $perPage
        	) 
    	);
 
      	$this->process_bulk_action();

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
 
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $data;

    }

	public function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'contacts',  			//Let's simply repurpose the table's singular label ("video")
            /*$2%s*/ $item['salesforce_id']             //The value of the checkbox should be the record's id
        );
    }

    public function get_bulk_actions() {
        return array(
                'sync' => __( 'Sync'),
                'update' => __( 'Update'),
        );
    }

	public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'sync':
                // wp_die( 'Delete something' );
            	$pressforce = new Press_Force_Plugin_Admin('press-force-plugin', '1.0.0');

            	$jsondb = new JsonDB($pressforce->plugin_cache_path);

				if(isset($_POST['contacts']) && is_array($_POST['contacts']))
				{
					foreach($_POST['contacts'] as $contact)
					{
						$record = $jsondb->select('salesforce_contacts', 'salesforce_id', $contact);
							
						if(count($record) > 0){
				
							$contact_info = $record[0];

						  	if(!email_exists( $contact_info['email'] )) {

								$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
								
								$userdata = array(
								    'user_login'  		=>  $contact_info['email'],
								    'user_email'  		=>  $contact_info['email'],
								    'nickname'	  		=>  $contact_info['firstName'] . ' ' . $contact_info['lastName'],
								    'first_name'  		=>  $contact_info['firstName'],
								    'last_name'   		=>  $contact_info['lastName'],
								    'user_pass'   		=>  $random_password  // When creating an user, `user_pass` is expected.
								);

								$user_id = wp_insert_user( $userdata );

								update_user_meta($user_id, 'user_salesforce_id',$contact_info['salesforce_id']);

								if ( ! is_wp_error( $user_id ) ) 
								{
									echo '<div id="message" class="updated notice is-dismissible"><p>Contact email <b>' . $contact_info['email'] . '</b> successfully sync.</p></div>';
								}
								else
								{
									echo '<div id="error" class="error  is-dismissible"><p>Contact email <b>' . $contact_info['email'] . '</b> already exist!</p></div>';
								}
							}
							else
							{
								echo '<div id="error" class="error  is-dismissible"><p>Contact email <b>' . $contact_info['email'] . '</b> already exist!</p></div>';
							}
						}
					}
				}
				unset($action);
                break;
 			case 'update':
            	$pressforce = new Press_Force_Plugin_Admin('press-force-plugin', '1.0.0');

            	$jsondb = new JsonDB($pressforce->plugin_cache_path);

				if(isset($_POST['contacts']) && is_array($_POST['contacts']))
				{
					foreach($_POST['contacts'] as $contact)
					{
						$record = $jsondb->select('salesforce_contacts', 'salesforce_id', $contact);
							
						if(count($record) > 0){
				
							$contact_info = $record[0];
							
							$user_id = email_exists( $contact_info['email'] );

							if( email_exists( $contact_info['email'] )) 
							{
								$userdata = array(
									'ID'				=>  $user_id,
								    'user_login'  		=>  $contact_info['email'],
								    'user_email'  		=>  $contact_info['email'],
								    'nickname'	  		=>  $contact_info['firstName'] . ' ' . $contact_info['lastName'],
								    'first_name'  		=>  $contact_info['firstName'],
								    'last_name'   		=>  $contact_info['lastName'],
								);

								$user_id = wp_update_user($userdata);

								if ( ! is_wp_error( $user_id ) ) 
								{
									echo '<div id="message" class="updated notice is-dismissible"><p>Contact email <b>' . $contact_info['email'] . '</b> successfully updated.</p></div>';
								}
								else
								{
									echo '<div id="error" class="error  notice is-dismissible"><p>Contact email <b>' . $contact_info['email'] . '</b> update failed!</p></div>';
								}
							}
							else
							{
								echo '<div id="error" class="error  notice is-dismissible"><p>Contact email <b>' . $contact_info['email'] . '</b> does not exist!</p></div>';
							}

						}
					}
				}
 				break;
            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }

	public function column_name($item) {
	    $actions = array(
	        'edit' => sprintf('<a href="?page=%s&action=%s& hotel=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
	        'delete' => sprintf('<a href="?page=%s&action=%s&hotel=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
	    );
	    return sprintf('%1$s %2$s', $item['Name'], $this->row_actions($actions) );
	}

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
    	 	'cb'    => '<input type="checkbox" />', // this is all you need for the bulk-action checkbox
            'salesforce_id'     => 'Salesforce ID',
            'email'     => 'Email',
            'firstName'       	=> 'First Name',
            'lastName' 			=> 'Last Name'
        );
 
        return $columns;
    }

	/**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();

        return $data;
    }
 
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
 
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }
 

 	public function search_box( $text, $input_id ){
 		?>
 		<form method="get" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<p class="search-box" style="">
				<label class="screen-reader-text" for="search_id-search-input">
				search:</label> 
				<input id="search_id-search-input" type="text" name="s" value="<?php echo (get_transient('s') != '') ? get_transient('s') : '' ?>" /> 
				<input id="search-submit" class="button" type="submit" name="" value="search" />
			 	<input type="hidden" name="page" value="press-force-contact" />
			</p>
		</form>
 		<?php
 	}
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'email':
            case 'firstName':
            case 'lastName':
            case 'salesforce_id':
                return $item[ $column_name ];
 
            default:
                return print_r( $item, true ) ;
            	// $item[ $column_name ]
        }
    }
 
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';
 
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
 
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
 
 
        $result = strnatcmp( $a[$orderby], $b[$orderby] );
 
        if($order === 'asc')
        {
            return $result;
        }
 
        return -$result;
    }
}