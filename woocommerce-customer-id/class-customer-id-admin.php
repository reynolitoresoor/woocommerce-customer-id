<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(CUSTOMER_ID__PLUGIN_DIR . 'class-customer-id-model.php');

class Customer_Id_Admin {

	public static function init() {
	    self::init_hooks();
	}

	public static function init_hooks(){
        add_action('admin_enqueue_scripts', array( 'Customer_Id_Admin', 'wp_slim_styles' ) );
        add_action('admin_head', array( 'Customer_Id_Admin', 'custom_admin_styles' ) );
        add_action('admin_enqueue_scripts', array( 'Customer_Id_Admin', 'load_resources' ) );
        add_action('wp_ajax_data_fetch' , array( 'Customer_Id_Admin', 'data_fetch') );
	    add_action('wp_ajax_nopriv_data_fetch', array( 'Customer_Id_Admin', 'data_fetch') );
	    add_action( 'admin_menu', array( 'Customer_Id_Admin', 'customer_admin_menu') );
	    add_action( 'admin_menu', array( 'Customer_Id_Admin', 'customer_medical_license_admin_menu') );
	    add_action( 'admin_enqueue_scripts', array('Customer_Id_Admin', 'ajax_update_status') );
	    add_action('wp_ajax_update_status' , array('Customer_Id_Admin', 'update_status') );
	    add_action('wp_ajax_nopriv_update_status', array('Customer_Id_Admin','update_status') );
	    add_action( 'admin_enqueue_scripts', array( 'Customer_Id_Admin', 'ajax_update_discount_status') );
	    add_action('wp_ajax_update_discount_status' , array( 'Customer_Id_Admin', 'update_discount_status'));
	    add_action('wp_ajax_nopriv_update_discount_status', array( 'Customer_Id_Admin', 'update_discount_status'));
	    add_action( 'admin_enqueue_scripts', array( 'Customer_Id_Admin', 'ajax_update_email_notification') );
	    add_action('woocommerce_cart_calculate_fees' , array( 'Customer_Id_Admin', 'add_custom_fees') );
	    add_action( 'admin_enqueue_scripts', array('Customer_Id_Admin', 'ajax_fetch_license') );
	    add_action('wp_ajax_data_fetch_license' , array('Customer_Id_Admin', 'data_fetch_license'));
	    add_action('wp_ajax_nopriv_data_fetch_license', array('Customer_Id_Admin', 'data_fetch_license') );
      
	}

	public static function load_resources(){
		wp_register_style( 'slim.css', plugin_dir_url( __FILE__ ) . 'xms-uploader/css/slim.min.css');
		wp_enqueue_style( 'slim.css');

		wp_register_style( 'lightbox.css',  plugin_dir_url( __FILE__ ) . 'assets/lightbox/css/lightbox.min.css');
		wp_enqueue_style( 'lightbox.css');

		wp_register_script( 'lightbox.js',  plugin_dir_url( __FILE__ ) . 'assets/lightbox/js/lightbox.js','', '1.0.0', false);
        wp_enqueue_script( 'lightbox.js');

		wp_register_script( 'slim.js', plugin_dir_url( __FILE__ ) . 'xms-uploader/js/slim.kickstart.js','', '1.0.0', true);

		wp_enqueue_script( 'slim.js');
	}

	/*
	*
	* XMS file uploader styles
	*   
	*/
	public static function wp_slim_styles(){
	    echo '<style> 
	     .slim-btn-edit {
	        background-image: url("'.plugin_dir_url(__FILE__).'assets/images/edit.png'.'");
	     }   
	    </style>';
	}

	/*
	*
	* XMS file uploader styles
	*   
	*/
	public static function custom_admin_styles(){
	    wp_register_style( 'customer-id.css', plugin_dir_url( __FILE__ ) . 'assets/css/customer-id.css');
		wp_enqueue_style( 'customer-id.css');
	}

	/*
	*
	* Ajax function
	*   
	*/

	public static function data_fetch(){
        include 'inc/ajax-customer-id-table.php';
	    die();
	}

    /*
	*
	* Creates customer id page on the admin panel
	*   
	*/
	public static function customer_admin_menu() {
	    add_menu_page( 'Customer ID', 'Customer ID', 'manage_options', 'customer-id.php', array( 'Customer_Id_Admin', 'customer_id_admin_page'), 'dashicons-id', 6  );
	}

	public static function customer_id_admin_page(){
    $customer_id = new Customer_ID_Model();    
    /*
    *  For pagination
    */    
    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $limit = 50; // number of rows in page
    $offset = ( $pagenum - 1 ) * $limit;

    if(isset($_GET['orderby']) && isset($_GET['order'])){
        $order = sanitize_text_field($_GET['order']);
        switch($_GET['orderby']){
            case 'id': 
                 $total = $customer_id->getTotalUserProfileById($order);

                 $num_of_pages = ceil( $total / $limit );

                 $results = $customer_id->getAllUserProfileById($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'username':
                 $total = $customer_id->getTotalUserProfileByUsername($order);

                 $num_of_pages = ceil( $total / $limit );

                 $results = $customer_id->getAllUserProfileByUsername($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'display-name':
                 $total = $customer_id->getTotalUserProfileByDisplayName($order);

                 $num_of_pages = ceil( $total / $limit );
                 
                 $results = $customer_id->getAllUserProfileByDisplayName($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'date-uploaded':
                 $total = $customer_id->getTotalUserProfileByDateUploaded($order);

                 $num_of_pages = ceil( $total / $limit );

                 $results = $customer_id->getAllUserProfileByDateUploaded($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            default: //Default
            break;

        }
    } else {
    
         $total = $customer_id->getTotalUserProfile();

         $num_of_pages = ceil( $total / $limit );
         
         $results = $customer_id->getAllUserProfile($offset, $limit);
         
         $page_links = paginate_links( array(
        'base' => add_query_arg( 'pagenum', '%#%' ),
        'format' => '',
        'prev_text' => __( '&laquo;', 'text-domain' ),
        'next_text' => __( '&raquo;', 'text-domain' ),
        'total' => $num_of_pages,
        'current' => $pagenum
        ) );

        
        /*
        * If modal form submitted update user ID
        */
        if(!empty($_POST)){
            require_once('admin-slim.php');
            $images = Slim::getImages('slim');
            $image = $images[0];
            $name = $image['output']['name'];
            $data = $image['output']['data'];
            $user_id = sanitize_text_field($_POST['user_id']);
            $username = sanitize_text_field($_POST['username']);
            if(isset($user_id) AND isset($username)){
              $file = Slim::saveFile($user_id,$username,$data, $name, plugin_dir_path(__FILE__).'uploads/images');
              echo "<script>window.location.reload()</script>";
            }
        }
    }
    ?>
    <div class="wrap">
        <h2>Customer ID's | Customer (<?php echo $customers[0]->Customers;?>)</h2>
        <div style="text-align: right; padding: 5px 0px 5px 0px">
            <input type="text" name="keyword" id="keyword" onkeyup="fetch()" placeholder="Search here..." style="padding: 10px;"></input>
        </div>
        <div id="search-result"></div>
        <div id="main-container">
            <table class="responsive-table widefat fixed striped comments">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                <th scope="col" id="id" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=desc"><span>ID</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="author" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=desc"><span>Thumbnail</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="comment" class="manage-column column-comment column-primary sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=username&amp;order=desc"><span>Username</span><span class="sorting-indicator"></th>
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=display-name&amp;order=desc"><span>Display Name</span><span class="sorting-indicator">
                </th>
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=date-uploaded&amp;order=desc"><span>Date Uploaded</span><span class="sorting-indicator">
                </th> 
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Edit</span><span class="sorting-indicator">
                </th>   
            </tr>
            </thead>
            <?php 

                if($results){
                     foreach($results as $result):
                     $aid = get_user_meta( $result->ID, 'get_avatar', true );
                     $date_uploaded = get_the_date('',$aid);
                     if(!$date_uploaded){
                         $date = new DateTime($result->date_updated);
                         $new_date = $date->format('Y-m-d');
                         $final_date = date("M j, Y", strtotime($new_date));
                         $date_uploaded = $final_date;
                     }
            ?>
            <tbody id="the-comment-list" data-wp-lists="list:comment">
                <tr id="comment-1" class="comment even thread-even depth-1 approved">
                    <th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-1">Select comment</label>
                    <input id="cb-select-1" type="checkbox" name="delete_comments[]" value="1">
                    </th>
                <td><?php echo $result->ID;?></td>
                <td class="author column-author" data-colname="Author"><strong><a href="<?php echo $result->profile;?>" data-lightbox="government issued id" data-title="<?php echo $result->user_login; ?>"><img alt="<?php echo $result->user_login.' ID';?>" src="<?php echo $result->profile;?>" class="avatar avatar-32 wp-user-avatar wp-user-avatar-32 alignnone photo avatar-default" height="40" style="width: 65%;"></a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><?php echo $result->user_login?></td><td class="response column-response" data-colname="In Response To"><?php echo $result->display_name;?></td><td class="response column-response" data-colname="In Response To"><?php echo $date_uploaded; ?></td><td class="response column-response" data-colname="In Response To"><a style="cursor: pointer" onclick="editAccount('<?php echo $result->ID;?>','<?php echo $result->profile;?>','<?php echo $result->user_login;?>')"><span class="dashicons-before dashicons-edit"></span>Edit</a></td></tr>
            </tbody>
            <?php endforeach; } else {?>
            <tbody id="the-comment-list" data-wp-lists="list:comment">
                <tr class="no-items"><td class="colspanchange" colspan="5">No records found.</td></tr> </tbody>
            <?php } ?>

            <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                <th scope="col" id="id" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=asc"><span>ID</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="author" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=asc"><span>Thumbnail</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="comment" class="manage-column column-comment column-primary sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=username&amp;order=asc"><span>Username</span><span class="sorting-indicator"></th>
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=display-name&amp;order=asc"><span>Display Name</span><span class="sorting-indicator">
                </th>
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-id.php&orderby=date-uploaded&amp;order=desc"><span>Date Uploaded</span><span class="sorting-indicator">
                </th>  
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Edit</span><span class="sorting-indicator">
                </th>  
            </tfoot>

        </table>
  
        <?php
        if ( $page_links ) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
        ?>
        </div>
    </div>
    
    <!-- The Modal -->
    <div id="updateUserModal" class="modal">
      <!-- Modal content -->
      <div class="modal-content">
        <div class="modal-header">
          <span class="close">&times;</span>
          <h2 style="color: white">Edit Account ID</h2>
        </div>
        <div class="modal-body">
              <form action="" method="post" enctype="multipart/form-data" accept-charset="utf-8">
                  <input hidden type="text" name="user_id" value="" id="user_id">
                  <input hidden type="text" name="username" value="" id="username">
                  <div class="slim" id="Cropper" data-size="600,400" data-force-size="600,400">
                      <input type="file" name="slim[]">
                  </div>
                  <div class="modal-footer" style="text-align: right">
                      <button id="cancel" class="button button-secondary" type="button">Cancel</button>
                      <input type="submit" value="Update" class="button button-primary">
                  </div>
              </form>
        </div>
        
      </div>

    </div>
    <script type="text/javascript">
          jQuery('.close').click(function($){
             jQuery('#updateUserModal').css('display','none');
          });

          jQuery('#cancel').click(function($){
             jQuery('#updateUserModal').css('display','none');
          });

          function editAccount(id,img,username){
             var cropper = new Slim(document.getElementById('Cropper'));
                cropper.forceSize = { width:600, height:400 };
                cropper.ratio = "3:2";
                cropper.load(img);
             jQuery('#username').val(username);
             jQuery('#user_id').val(id);
             jQuery('#updateUserModal').css('display','block');
          }

          lightbox.option({
          'maxWidth': 600,
          'maxHeight': 400,
          'positionFromTop': 100,
        });

    </script>
    <?php
	}

	/*
	*
	* Search feature on the Customer ID page
	*   
	*/
	public static function ajax_fetch_license() {
	?>
	<script type="text/javascript">
	function fetch_license(){
	    jQuery.ajax({
	        url: '<?php echo admin_url('admin-ajax.php'); ?>',
	        type: 'post',
	        data: { action: 'data_fetch_license', keyword: jQuery('#keyword').val() },
	        success: function(data) {
	            if(data){
	                jQuery('#search-result').html( data );
	                jQuery('#main-form').hide();
	            } else {
	                jQuery('#search-result').hide();
	                jQuery('#main-form').css('display','visible');
	            }
	            
	        }
	    });

	}
	</script>

	<?php
	}

	/*
	*
	* Ajax function
	*   
	*/
	public static function data_fetch_license(){
	    include 'inc/ajax-userlicense-table.php';
	    die();
	}

	/*
	*
	* Creates customer medical license page on the admin panel
	*   
	*/
	
	public static function customer_medical_license_admin_menu() {
	    add_menu_page( 'Medical License', 'Medical Licenses', 'manage_options', 'customer-medical-license', array( 'Customer_Id_Admin', 'customer_medical_license_admin_page' ), 'dashicons-id', 7  );
	    add_submenu_page('customer-medical-license', 'Medical License Settings', 'Settings', 'manage_options', 'medical-license-settings', array( 'Customer_Id_Admin', 'medical_license_settings' ), 7);
	}

	public static function customer_medical_license_admin_page(){
    $customer_id = new Customer_ID_Model();
    global $wpdb;
    /*
    *  For pagination
    */    
    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $limit = 10; // number of rows in page
    $offset = ( $pagenum - 1 ) * $limit;

    if(isset($_GET['orderby']) && isset($_GET['order'])){
        $order = sanitize_text_field($_GET['order']);
        switch($_GET['orderby']){
            case 'id': 
                 $total = $customer_id->getTotalUserLicenseById($order);

                 $num_of_pages = ceil( $total / $limit );
                 
                 $results = $customer_id->getAllUserLicenseById($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'display_name': 
                 $total = $customer_id->getTotalUserLicenseByDisplayName($order);

                 $num_of_pages = ceil( $total / $limit );
                
                 $results = $customer_id->getAllUserLicenseByDisplayName($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'medical_license':
                $total = $customer_id->getTotalUserLicenseByMedicalLicense($order);

                 $num_of_pages = ceil( $total / $limit );
                
                 $results = $customer_id->getAllUserLicenseByMedicalLicense($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'status':
                 $total = $customer_id->getTotalUserLicenseByStatus($order);

                 $num_of_pages = ceil( $total / $limit );

                 $results = $customer_id->getAllUserLicenseByStatus($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            case 'date-uploaded':
                 $total = $customer_id->getTotalUserLicenseByDateUploaded($order);

                 $num_of_pages = ceil( $total / $limit );
                 
                 $results = $customer_id->getAllUserLicenseByDateUploaded($order, $offset, $limit);

                 $page_links = paginate_links( array(
                'base' => add_query_arg( 'pagenum', '%#%' ),
                'format' => '',
                'prev_text' => __( '&laquo;', 'text-domain' ),
                'next_text' => __( '&raquo;', 'text-domain' ),
                'total' => $num_of_pages,
                'current' => $pagenum
                ) );
            break;
            default: //Default
            break;

        }
    } else {
    
         $total = $customer_id->getTotalUserLicense();
         $num_of_pages = ceil( $total / $limit );
         $results = $customer_id->getAllUserLicense($offset, $limit);
         $page_links = paginate_links( array(
        'base' => add_query_arg( 'pagenum', '%#%' ),
        'format' => '',
        'prev_text' => __( '&laquo;', 'text-domain' ),
        'next_text' => __( '&raquo;', 'text-domain' ),
        'total' => $num_of_pages,
        'current' => $pagenum
        ) );

         /*
        * If modal form submitted update user ID
        */
        if(!empty($_POST)){
            $userlicense_table = $wpdb->prefix.'userlicense';
            
            if($_POST['update']){
            $user_id = sanitize_text_field($_POST['user_id']);
            $status = sanitize_text_field($_POST['status']);

            if(isset($user_id) AND isset($status)){
              
              
              $result = $wpdb->update(
                $userlicense_table, 
                array('status' => $status),
                array('user_id' => $user_id),
                array('%s'),
                array('%s')
                    );
              
              if($result){
                 echo "<script>window.location.reload()</script>";
              }
              
            }

          }


          if($_POST['update_medical_license_status']){
              $status = $_POST['status'];
              foreach($status as $key=>$status_value){
                 foreach($results as $result){
                     if($result->ID == $key){
                        $customer_id = sanitize_text_field($result->ID);
                        $status_value = sanitize_text_field($status_value);

                        $update_status = $wpdb->update(
                        $userlicense_table, 
                        array('status' => $status_value),
                        array('user_id' => $customer_id),
                        array('%s'),
                        array('%s')
                            );
                        
                     } else {
                        $update_status = "Save success";
                     }
                 }
              }
              wp_redirect("?page=customer-medical-license&save=success");
                exit;
          }

          if($_POST['search_update_medical_license_status']){
              $status = $_POST['status'];
              foreach($status as $key=>$status_value){
                 foreach($results as $result){
                     if($result->ID == $key){
                        $customer_id = sanitize_text_field($result->ID);
                        $status_value = sanitize_text_field($status_value);

                        $update_status = $wpdb->update(
                        $userlicense_table, 
                        array('status' => $status_value),
                        array('user_id' => $customer_id),
                        array('%s'),
                        array('%s')
                            );
                        
                     } else {
                        $update_status = "Save success";
                     }
                 }
              }
              wp_redirect("?page=customer-medical-license&save=success");
                exit;
          }

        }

        
    }

    ?>
    <div class="wrap">
        <h2>Customer Medical License</h2>
        <div style="text-align: right; padding: 5px 0px 5px 0px">
            <input type="text" name="keyword" id="keyword" onkeyup="fetch_license()" placeholder="Search here..." style="padding: 10px;"></input>
        </div>
        <form method="post" action="" id="search-form">
          <div id="search-result"></div> 
        </form>
        <form method="post" action="" id="main-form">
        <div id="main-container">
            <?php if(isset($_GET['save']) && $_GET['save'] == 'success'){ ?>
            <div>
              <h1 style="color: green">Status successfully updated!</h1>
            </div>
            <?php } ?>
            <table class="responsive-table widefat fixed striped comments" width="100%">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column" style="width: 1%;"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                <th scope="col" id="id" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=id&amp;order=desc"><span>ID</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="id" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=display_name&amp;order=desc"><span>Display Name</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="author" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=medical_license&amp;order=desc"><span>Medical License</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="author" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=status&amp;order=desc"><span>Status</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=date-uploaded&amp;order=desc"><span>Date Uploaded</span><span class="sorting-indicator">
                </th> 
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Update Status</span><span class="sorting-indicator">
                </th> 
            </tr>
            </thead>
            <?php 

                if($results){
                     foreach($results as $result):
                     
                     $date = new DateTime($result->date_updated);
                     $date_uploaded = $date->format('Y-m-d');

                     switch($result->type){
                         case 'image/jpeg': $file_preview = '<a href="'.$result->location.'" data-lightbox="Medical License" data-title="'.$result->medical_license.'"><img alt="'.$result->medical_license.'" srcset="'.$result->location.'" class="avatar avatar-32 wp-user-avatar wp-user-avatar-32 alignnone photo avatar-default" height="40" style="width: 65%;"></a>';
                            break;
                         case 'image/jpg': $file_preview = '<a href="'.$result->location.'" data-lightbox="Medical License" data-title="'.$result->medical_license.'"><img alt="'.$result->medical_license.'" srcset="'.$result->location.'" class="avatar avatar-32 wp-user-avatar wp-user-avatar-32 alignnone photo avatar-default" height="40" style="width: 65%;"></a>';
                            break;
                         case 'image/png': $file_preview = '<a href="'.$result->location.'" data-lightbox="Medical License" data-title="'.$result->medical_license.'"><img alt="'.$result->medical_license.'" srcset="'.$result->location.'" class="avatar avatar-32 wp-user-avatar wp-user-avatar-32 alignnone photo avatar-default" height="40" style="width: 65%;"></a>';
                            break;
                         case 'application/pdf': $file_preview = '<a href="'.$result->location.'" target="_blank">'.$result->medical_license.'</a>';
                            break;
                         case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': $file_preview = '<a href="'.$result->location.'">'.$result->medical_license.'</a>';
                            break;
                         case 'application/vnd.openxmlformats-officedocument.word': $file_preview = '<a href="'.$result->location.'">'.$result->medical_license.'</a>';
                            break;
                         default:
                            break;
                      }
            ?>
             <tbody id="the-comment-list" data-wp-lists="list:comment">
                <tr id="comment-1" class="comment even thread-even depth-1 approved">
                    <th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-1">Select comment</label>
                    <input id="cb-select-1" type="checkbox" name="delete_comments[]" value="1">
                    </th>
                <td class="response column-response" data-colname="In Response To"><?php echo $result->id;?></td>
                <td class="response column-response" data-colname="In Response To"><?php echo $result->display_name;?></td>
                <td class="response column-response" data-colname="In Response To"><?php echo $file_preview; ?></td>
                <td class="response column-response" data-colname="In Response To"><p style="<?php if($result->status == 0 OR $result->status == 2){echo 'color: red';}else{echo 'color:green';}  ?>"><?php if($result->status == 0){ echo "Pending Review";}else if($result->status == 1){echo "Approved";}else{echo 'Declined';} ?></p></td>
                <td class="response column-response" data-colname="In Response To"><?php echo $date_uploaded; ?></td>
                <td class="response column-response" data-colname="In Response To">
                  <select name="status[<?php echo $result->ID; ?>]">
                     <option value="0" <?php if($result->status == 0){echo 'selected';} ?>>Pending Review</option>
                     <option value="1" <?php if($result->status == 1){echo 'selected';} ?>>Approved</option>
                     <option value="2" <?php if($result->status == 2){echo 'selected';} ?>>Declined</option>
                  </select>
                </td></tr>
            </tbody>
            <?php endforeach; } else {?>
            <tbody id="the-comment-list" data-wp-lists="list:comment">
                <tr class="no-items"><td class="colspanchange" colspan="5">No records found.</td></tr> </tbody>
            <?php } ?>

            <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                <th scope="col" id="id" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=id&amp;order=asc"><span>ID</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="id" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=display_name&amp;order=asc"><span>Display Name</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="comment" class="manage-column column-comment column-primary sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=medical_license&amp;order=asc"><span>Medical License</span><span class="sorting-indicator"></th>
                <th scope="col" id="author" class="manage-column column-author sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=status&amp;order=desc"><span>Status</span><span class="sorting-indicator"></span></a>
                </th>
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a href="<?php echo home_url();?>/wp-admin/admin.php?page=customer-medical-license&orderby=date-uploaded&amp;order=desc"><span>Date Uploaded</span><span class="sorting-indicator">
                </th>  
                <th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Update Status</span><span class="sorting-indicator">
                </th>  
            </tfoot>

        </table>
  
        <?php
        if ( $page_links ) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
        ?>
        </div>
        <div style="margin-top: 20px;">
          <input type="submit" name="update_medical_license_status" value="Save Changes" class="button button-primary">
        </div>
      </form>
    </div>
    
    <!-- The Modal -->
    <div id="updateUserMedicalLicenseModal" class="modal">
      <!-- Modal content -->
      <div class="modal-content">
        <div class="modal-header">
          <span class="close">&times;</span>
          <h2 style="color: white">Edit Customer Medical License</h2>
        </div>
        <div class="modal-body">
              <form action="" method="post" enctype="multipart/form-data" accept-charset="utf-8">
                  <br/>
                  <input hidden type="text" name="user_id" value="" id="user_id">
                  <label>Status</label>
                  <input type="text" name="status" value="" id="status">
                  <div class="modal-footer" style="text-align: right">
                      <button id="cancel" class="button button-secondary" type="button">Cancel</button>
                      <input type="submit" value="Update" class="button button-primary">
                  </div>
              </form>
        </div>
        
      </div>

    </div>
    <script type="text/javascript">
          jQuery('.close').click(function($){
             jQuery('#updateUserMedicalLicenseModal').css('display','none');
          });

          jQuery('#cancel').click(function($){
             jQuery('#updateUserMedicalLicenseModal').css('display','none');
          });

          function editAccount(id,status){
             jQuery('#user_id').val(id);
             jQuery('#status').val(status);
             jQuery('#updateUserMedicalLicenseModal').css('display','block');
          }

    </script>
    <?php
}

   /*
	*
	* Update status on medical license
	*   
	*/
	
	public static function ajax_update_status() {
	?>
	<script type="text/javascript">
	function updateStatus(status){
	    var status = status;
	    jQuery.ajax({
	        url: '<?php echo admin_url('admin-ajax.php'); ?>',
	        type: 'post',
	        data: { action: 'update_status', status: status },
	        success: function(data) {
	            if(data == 1){
	               jQuery('.success-message').show();
	            } else {
	               jQuery('.error-message').show();
	            }
	        }
	    });
	}
	</script>

	<?php
	}

	/*
	*
	* Ajax function to update medical license status
	*   
	*/
	
	public static function update_status(){
	     $customer_id = new Customer_ID_Model();
	     $user_id = $customer_id->getUserId();
	     $status = sanitize_text_field($_POST['status']);
	    
	     $userlicense_table = $wpdb->prefix.'userlicense';
	     $result = $wpdb->update(
	                  $userlicense_table, 
	                  array('status' => $status),
	                  array('user_id' => $user_id),
	                  array('%s'),
	                  array('%s')
	                      );
	     echo $result;
	     die();
	}

	/*
	*
	*  Medical License settings   
	*
	*/
	function medical_license_settings(){
	     $customer_id = new Customer_ID_Model();
	     $user_id = $customer_id->getUserId();
	     $usermeta_id = 138;

	     $status = sanitize_text_field($_POST['status']);
	     $user_meta = get_user_meta($usermeta_id);
	     $medical_license_status = $user_meta['medical_license_status'][0];
	     $medical_license_discount = $user_meta['medical_license_discount'][0];
	     $email_notification = $user_meta['email_notification'][0];
	     $email_notification_recipient = $user_meta['email_notification_recipient'][0];

	     if($_POST){
	          $email_notification = sanitize_text_field($_POST['email_notification']);
	          $email_notification_recipient = sanitize_text_field($_POST['email_notification_recipient']);
	          $medical_license_status = sanitize_text_field($_POST['medical_license_status']);
	          $medical_license_discount = sanitize_text_field($_POST['medical_license_discount']);

	          /*
	          *  User id to udpate the user meta
	          */
	         
	          update_user_meta($usermeta_id, 'email_notification',$email_notification);
	          update_user_meta($usermeta_id,'email_notification_recipient', $email_notification_recipient);

	          update_user_meta($usermeta_id, 'medical_license_discount', $medical_license_discount);
	          update_user_meta($usermeta_id, 'medical_license_status', $medical_license_status);
	     }
	?>
	   <div class="wrap">
	      <div class="row">
	         <div class="large-12 col">
	              <h2>Customer Medical License</h2>
	              <div class="wrap woocommerce">
	                <?php if($invalid_email){ ?>
	                <p style="font-size: 1.5em;color:red;">Invalid Email</p>
	                <?php } ?>
	                <p id="discount_error_message" style="font-size: 1.5em;color:red;display: none;">Please define discount!</p>
	                  <form method="post" action="">
	                      <table class="form-table">
	                        <tbody>
	                          <tr valign="top">
	                                  <th scope="row" class="titledesc">
	                                     <label for="woocommerce_new_order_enabled">Enable/Disable </label>
	                                  </th>
	                                  <td class="forminp">
	                                      <fieldset>
	                                          <legend class="screen-reader-text"><span>Enable/Disable</span></legend>
	                                          <label id="enable-discount-label" for="enable-discount" style="font-size: 1em;">
	                                          <input type="checkbox" id="enable-discount" name="medical_license_status" value="<?php if($medical_license_status){echo '1';}else{echo 0;} ?>" onchange="enableDiscount(this.value)" <?php if($medical_license_status){echo "checked";}else{} ?>> Enable WooCommerce Discount </label><br>
	                                     </fieldset>
	                                  </td>
	                          </tr>
	                          <tr valign="top">
	                                  <th scope="row" class="titledesc">
	                                    <label for="medical-license-discount">Discount<span class="woocommerce-help-tip"></span></label>
	                                  </th>
	                                  <td class="forminp">
	                                    <fieldset>
	                                      <input type="text" id="medical-license-discount" name="medical_license_discount" value="<?php echo $medical_license_discount; ?>" <?php if(!$medical_license_status){echo "disabled";} ?>>%
	                                              </fieldset>
	                                  </td>
	                          </tr>
	                           <tr valign="top">
	                                  <th scope="row" class="titledesc">
	                                     <label for="enable-email-notification">Enable/Disable </label>
	                                  </th>
	                                  <td class="forminp">
	                                      <fieldset>
	                                          <legend class="screen-reader-text"><span>Enable/Disable Email Notification</span></legend>
	                                          <label id="enable-email-notification" for="enable-email-notification" style="font-size: 1em;">
	                                          <input name="email_notification" type="checkbox" id="enable-email-notification" value="<?php if($email_notification){echo 1;}else{echo 0;} ?>" onchange="enableEmailNotification(this.value)" name="email_notification" <?php if($email_notification
	                                            ){echo "checked";}else{} ?>> Enable/Disable Email Notification </label><br>
	                                     </fieldset>
	                                  </td>
	                          </tr>
	                          <tr valign="top">
	                                  <th scope="row" class="titledesc">
	                                    <label for="recipient">Recipient<span class="woocommerce-help-tip"></span></label>
	                                  </th>
	                                  <td class="forminp">
	                                    <fieldset>
	                                      <input name="email_notification_recipient" type="text" id="recipient" value="<?php echo $email_notification_recipient; ?>" placeholder="example@gmail.com" <?php if(!$email_notification){echo "disabled";} ?>>
	                                              </fieldset>
	                                  </td>
	                          </tr>
	                        </tbody>
	                     </table>
	                        <p class="submit">
	                            <button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
	                          <input type="hidden" id="_wpnonce" name="_wpnonce" value="f63dd91b76"></p>
	                </form>
	              </div>
	         </div>
	      </div>
	   </div>
	   
	<?php
	  
	}

	/*
	*
	* Update status medical license discount
	*   
	*/
	
	public static function ajax_update_discount_status() {
	?>
	<script type="text/javascript">
	function enableDiscount(status){
	    var status = status;
	    var medical_license_discount = jQuery('#medical-license-discount').val();
	    if(status ==  1){
	       jQuery('#enable-discount').val(0);
	       jQuery('#medical-license-discount').attr('disabled','true');
	    } else {
	       jQuery('#enable-discount').val(1);
	       jQuery('#medical-license-discount').removeAttr('disabled');
	    }

	}
	</script>

	<?php
	}

	/*
	*
	* Ajax function to update medical license discount status
	*   
	*/
	
	public static function update_discount_status(){
	     $customer_id = new Customer_ID_Model();
	     $status = sanitize_text_field($_POST['status']);
	     $discount = sanitize_text_field($_POST['medical_license_discount']);
	     $usermeta_id = 138;
	     $user_meta = get_user_meta($usermeta_id);
	     
	     $medical_license_discount = $user_meta['medical_license_discount'][0];

	     if($status == 1){
	        $status = 0;
	     } else {
	        $status = 1;
	     }

	     if(!$medical_license_discount){
	        update_user_meta($usermeta_id, 'medical_license_discount', $discount);
	        update_user_meta($usermeta_id, 'medical_license_status', $status);
	     } 
	     die();
	}

	/*
	*
	* Update email notification
	*   
	*/
	
	public static function ajax_update_email_notification() {
	?>
	<script type="text/javascript">
	function enableEmailNotification(status){
	   if(status == 1){
	      jQuery('input#enable-email-notification').val(0);
	      jQuery('#recipient').attr('disabled','true');
	   } else {
	    jQuery('input#enable-email-notification').val(1);
	    jQuery('#recipient').removeAttr('disabled');
	   }
	}
	</script>

	<?php
	}
	

	/**
	 * Add custom fee if medical license has been approved by admin
	 * @param WC_Cart $cart
	 */
	function add_custom_fees( WC_Cart $cart ){
        $customer_id = new Customer_ID_Model();
        $user_id = $customer_id->getUserId();
        $usermeta_id = 138;
	    $user_meta = get_user_meta($usermeta_id);

	    $medical_license_discount = $user_meta['medical_license_discount'][0];
	    $medical_license_status = $user_meta['medical_license_status'][0];
	    
	    $user_license = $customer_id->getUserLicense();
	    
	    if($user_license[0]->status == 1){
	      // Calculate the amount to reduce
	        if($medical_license_status){
	              $discount = $medical_license_discount / 100;
	              $discount = $cart->subtotal * $discount;
	              $cart->add_fee( 'A '.$medical_license_discount.'% Medical License discount has been added.', -$discount);
	        }
	    }
	 
	}

	
} 
