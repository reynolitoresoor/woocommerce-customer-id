<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(CUSTOMER_ID__PLUGIN_DIR . 'class-customer-id-model.php');

class Customer_Id {

	public static function init() {
	    self::init_hooks();
	}

	public static function init_hooks(){
       add_action( 'wp_enqueue_scripts', array( 'Customer_Id', 'load_resources' ) );
       add_action( 'wp_enqueue_scripts', array( 'Customer_Id', 'wp_slim_styles' ) );
       add_action('woocommerce_edit_account_form', array( 'Customer_Id', 'xms_upload_id') );
       add_action('woocommerce_before_checkout_billing_form', array( 'Customer_Id', 'xms_checkout_upload_id') );
       add_action('woocommerce_before_account_orders', array( 'Customer_Id', 'xms_order_page_upload_id') );
       add_filter( 'woocommerce_email_attachments', array( 'Customer_Id', 'xms_creation_woocommerce_attachments', 10, 3) );
       add_action( 'woocommerce_thankyou' , array('Customer_Id', 'xms_id_display') );
       add_action( 'woocommerce_admin_order_data_after_billing_address', array( 'Customer_Id', 'my_custom_checkout_field_display_admin_order_meta', 10, 1) );
	}

	public static function load_resources(){
        wp_register_style( 'customer-id.css', plugin_dir_url( __FILE__ ) . 'assets/css/customer-id.css');
		wp_enqueue_style( 'customer-id.css');

		wp_register_style( 'slim-uploader.css', plugin_dir_url( __FILE__ ) . 'xms-uploader/css/slim.min.css');
		wp_enqueue_style( 'slim-uploader.css');

		wp_register_script( 'slim-uploader.js', plugin_dir_url( __FILE__ ) . 'xms-uploader/js/slim.kickstart.js','', '1.0.0', true);
		wp_enqueue_script( 'slim-uploader.js' );

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
	* Creates slim cropper id uploader on the account details
	*   
	*/
	public static function xms_upload_id(){
	  $type = "account_details";   
	  include 'inc/customer-id-uploader.php';

	}

    /*
	*
	* Creates slim cropper id uploader on the checkout page
	*   
	*/
	public static function xms_checkout_upload_id(){
      $type = "checkout";
	  include 'inc/customer-id-uploader.php';

	}

	/*
	*
	* Creates slim cropper id uploader on the orders page
	*   
	*/
	public static function xms_order_page_upload_id(){
      $type = "orders";
	  include 'inc/customer-id-uploader.php';
	}

	/*
	*
	* Email attachments
	*   
	*/

	public static function xms_creation_woocommerce_attachments($attachments, $email_id, $email_object){
	    
	    $customer_id = new Customer_ID_Model();
	    $results = $customer_id->getUserProfile();
	    
	    foreach($results as $result){
	       $user_image = $result->profile;
	    }

	    if($email_id === 'new_order'){
	        $user_image_file = $user_image;
	        $attachments[] = $user_image_file;
	    }

	    return $attachments;
	}

	/*
	*
	* Add user image on the thank you page.
	*   
	*/
	
	public static function xms_id_display() { 
	   $customer_id = new Customer_ID_Model();
	   $results = $customer_id->getUserProfile();
	   
	   foreach($results as $result){
	    	$user_image = $result->profile;
	   }
	   $user_img = $user_image;
	    
	?>
	    <span style="margin:20px 0 20px 0" class="image mr-half inline-block">
	        <a href="/my-account/edit-account"><img alt="" src="<?php echo $user_img;?>" class="avatar-photo" width="170" height="120"></a>
	    </span>
	<?php 
    }

	/*
	|--------------------------------------------------------------------------
	| Display field value on the order edit page
	|--------------------------------------------------------------------------
	*/
	
	public static function my_custom_checkout_field_display_admin_order_meta($order){
	  
	   $customer_id = new Customer_ID_Model();
	   $results = $customer_id->getUserProfile();

	   $user_id = get_post_meta( $order->id, '_customer_user', true );

	   foreach($results as $result){
	        $user_image = $result->profile;
	   }
	    
	   if (empty($user_image)) {
	        $aid = get_user_meta( $user_id, 'get_avatar', true );
	        $user_img = wp_get_attachment_image_src( $aid, $size = 'small', $icon = false)['0'];
	   } else {
	        $user_img = $user_image;
	   }
	?>
	  <p><strong>Government Issued ID:</strong></p>
	  <span style="margin:20px 0 20px 0" class="image mr-half inline-block">
	        <a href="<?php echo $user_img;?>" data-lightbox="government issued id" data-title="Customer Goverment Issued ID"><img alt="" src="<?php echo $user_img;?>" class="avatar-photo avatar avatar-32 wp-user-avatar wp-user-avatar-32 alignnone photo avatar-default" width="200" height="140"></a>
	    </span>
	<?php 
    }
    
	/*
	*  Medical License endpoint content
	*/
	public static function medical_license_endpoint_content() {
	    if ( ! function_exists( 'wp_handle_upload' ) ) {
	        require_once( ABSPATH . 'wp-admin/includes/file.php' );
	    }

	    $customer_id = new Customer_ID_Model();
	    $user_id = $customer_id->getUserId();
	    $user_license = $customer_id->getUserLicense();
	    $usermeta_id = 138;

	    $file_icon = "";
	    $user_meta = get_user_meta($usermeta_id);

	    $num_rows = COUNT($user_license);
	    $filename = pathinfo($user_license[0]->location);
        $data = array();
        
	    switch($user_license[0]->type){
	       case 'image/jpeg': $file_icon = plugin_dir_url(__FILE__).'assets/images/JPG.png';
	          break;
	       case 'image/jpg': $file_icon = plugin_dir_url(__FILE__).'assets/images/JPG.png';
	          break;
	       case 'image/png': $file_icon = plugin_dir_url(__FILE__).'assets/images/PNG.png';
	          break;
	       case 'application/pdf': $file_icon = plugin_dir_url(__FILE__).'assets/images/PDF.png';
	          break;
	       case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': $file_icon = plugin_dir_url(__FILE__).'assets/images/Doc.png';
	          break;
	       case 'application/vnd.openxmlformats-officedocument.word': $file_icon = plugin_dir_url(__FILE__).'assets/images/Doc.png';
	          break;
	       default:
	          break;
	    }



	    if($_POST){
	        $uid = get_user_by( 'id', $user_id );
	        $name = $uid->user_login;

	        // Get the type of the uploaded file. This is returned as "type/extension"
	        $arr_file_type = wp_check_filetype(basename($_FILES['medical_license']['name']));

	        $file_name = $_FILES['medical_license']['name'];

	         $uploaded_file_type = $arr_file_type['type'];
	         
	        // Set an array containing a list of acceptable formats
	        $allowed_file_types = array('image/jpg','image/jpeg','image/png','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document');

	        // If the uploaded file is the right format
	        if(in_array($uploaded_file_type, $allowed_file_types)) {

	            $uploadedfile = $_FILES['medical_license'];

	            $upload_overrides = array( 'test_form' => false );

	            $date_updated = date('Y-m-d H:i:s');
	            $string_date = date_create($date_updated);
	            $string_date = date_format($string_date, "M j, Y");
	            
	            //Converts bytes to mb
	            $size = number_format($_FILES['medical_license']['size'] / 1048576, 2);

	            //Email defined recipient if enabled
	            $subject = "Headz.ca Medical License Submission";
	            $message .= '<p>';
	            $message .= ucfirst($name).' have submitted Medical License for review.';
	            $message .= '</p>';

	            
	            if($size <= 5 && isset($_FILES['medical_license'])){

	              if(isset($user_license[0]->location)){
	                   $image_url = parse_url($user_license[0]->location);
	                   $path = str_replace('/wordpress/', '', $image_url['path']);
	                   $file_path = ABSPATH.$path;

	                   

	                   if(file_exists($file_path)){
	                      unlink($file_path);
	                      
	                      $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	                      
	                      if ( $movefile && ! isset( $movefile['error'] ) ) {
	                         
                             array_push($data, 
		                     	 array(
		                     	 	 'user_id'		=>	$user_id,
		                     	 	 'file_name'	=>	$file_name,
		                     	 	 'location'		=>	$movefile['url'],
		                     	 	 'status'		=>  0,
		                     	 	 'date_updated' =>	$date_updated,
		                     	 	 'type'			=>	$uploaded_file_type		
		                     	 )
		                     );
	                         $update_results = $customer_id->updateUserLicense($data);
	                         
	                         if($user_meta['email_notification'][0] == 1 && $user_meta['email_notification_recipient'][0] != ''){
	                                  add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
	                                  $send_email = wp_mail( $user_meta['email_notification_recipient'][0], $subject, $message );
	                          } else {
	                                  $admin_email = get_option('admin_email');
	                                  add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
	                                  $send_email = wp_mail( $admin_email, $subject, $message );
	                          }

	                          if($update_results){
	                            echo "<script>window.location.href='?save=success'</script>";
	                          }
	                      }
	                   } else {
	                      $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	                      
	                      if ( $movefile && ! isset( $movefile['error'] ) ) {

	                         array_push($data, 
		                     	 array(
		                     	 	 'user_id'		=>	$user_id,
		                     	 	 'file_name'	=>	$file_name,
		                     	 	 'location'		=>	$movefile['url'],
		                     	 	 'status'		=>  0,
		                     	 	 'date_updated' =>	$date_updated,
		                     	 	 'type'			=>	$uploaded_file_type		
		                     	 )
		                     );

		                     $update_results = $customer_id->updateUserLicense($data);

	                         if($user_meta['email_notification'][0] == 1 && $user_meta['email_notification_recipient'][0] != ''){
	                                  add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
	                                  $send_email = wp_mail( $user_meta['email_notification_recipient'][0], $subject, $message );
	                          } else {
	                                  $admin_email = get_option('admin_email');
	                                  add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
	                                  $send_email = wp_mail( $admin_email, $subject, $message );
	                          }
	                      }

	                      if($update_results){
	                        echo "<script>window.location.href='?save=success'</script>";
	                      } 
	                   }
	              } else {
	                   $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	                    if ( $movefile && ! isset( $movefile['error'] ) ) {
	                         array_push($data, 
		                     	 array(
		                     	 	 'user_id'		=>	$user_id,
		                     	 	 'file_name'	=>	$file_name,
		                     	 	 'location'		=>	$movefile['url'],
		                     	 	 'status'		=>  0,
		                     	 	 'date_updated' =>	$date_updated,
		                     	 	 'type'			=>	$uploaded_file_type		
		                     	 )
		                     );
	                         
	                         $insert_results = $customer_id->insertUserLicense($data);

	                         if($user_meta['email_notification'][0] == 1 && $user_meta['email_notification_recipient'][0] != ''){
	                                  add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
	                                  $send_email = wp_mail( $user_meta['email_notification_recipient'][0], $subject, $message );
	                          } else {
	                                  $admin_email = get_option('admin_email');
	                                  add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
	                                  $send_email = wp_mail( $admin_email, $subject, $message );
	                          }
	                    }

	                    if($insert_results){
	                      echo "<script>window.location.href='?save=success'</script>";
	                    }

	              }

	            }


	        }
	    }


	    ?>
	        <form class="woocommerce-EditAccountForm edit-account" enctype="multipart/form-data" action="" method="post">
	            <hr style="border-width: 1px; border-color: black;" />
	            <p><strong>Medical License</strong></p>
	            <p>
	            <?php if(isset($upload_error)){ ?>
	            <label style="color: red;font-size: 1em;" for="license"><?php _e( $upload_error, 'woocommerce' ); ?> </label>
	            <?php } ?>
	            <?php if($num_rows <= 0){ ?>
	            <label style="color: red;font-size: 1em;" for="license"><?php _e( 'No medical license uploaded', 'woocommerce' ); ?> </label>
	            <?php } ?>
	            <?php if($user_license[0]->status == 0 AND $num_rows == 1){ ?>
	            <div class="wp-menu-image dashicons-before">
	            <label style="color: red;font-size: 1em;" for="license"><?php _e( 'Status: Pending Review and Approval', 'woocommerce' ); ?> </label>
	            <img src="<?php echo $file_icon; ?>" alt="" width="80"><span><?php echo $filename['filename']; ?></span></div>
	            <?php } else if($user_license[0]->status == 1 AND $num_rows == 1) { ?>
	            <div class="wp-menu-image dashicons-before">
	            <label for="license" style="color: green;"><?php _e( 'Status: Approved', 'woocommerce' ); ?> </label>
	            <img src="<?php echo $file_icon; ?>" alt="" width="80"><span><?php echo $filename['filename']; ?></span></div>
	            <?php } else if($user_license[0]->status == 2) { ?>
	            <div class="wp-menu-image dashicons-before">
	            <label for="license" style="color: red;font-size: 1em;"><?php _e( 'Status: Declined', 'woocommerce' ); ?> </label>
	            <img src="<?php echo $file_icon; ?>" alt="" width="80"><span><?php echo $filename['filename']; ?></span></div>
	            <?php } ?>
	            <?php if($user_license[0]->status == ''){ ?>
	            <label style="color: red;" for="license"><?php _e( 'If you have a medical license you will receive a 5% discount on all products! Please, upload your document proving you are a medical license holder. Headz will then review and approve your submission and a 5% discount will automatically be deducted during the check out process. Your medical marijuana license is not your drivers license or passport. It is your official license granted to you to partake in medical marijuana by a doctor or dispensary. Your picture, drivers license nor passport will not be accepted.', 'woocommerce' ); ?> </label>
	            <?php } ?>
	          <div class="upload-btn-wrapper">
	            <button class="woocommerce-Button button" style="margin-bottom: 0px;cursor:pointer">1. CHOOSE FILE</button>
	            <input id="file" type="file" name="medical_license" /><span id="filename"></span><br/>
	            <p>Accepted formats: jpg/jpeg, png, doc and pdf</p>
	          </div> <br/>
	            </p>
	            <p>
	          <input id="upload" type="submit" class="woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e( '2. Upload', 'woocommerce' ); ?>" disabled />
	          <input type="hidden" name="action" value="save_account_details"/>
	        </p>
	        <hr style="border-width: 1px; border-color: black;" />
	        </form>
	        <style type="text/css">
	          .upload-btn-wrapper {
	            position: relative;
	            overflow: hidden;
	            display: inline-block;
	          }

	          .upload-btn-wrapper input[type=file] {
	            font-size: 100px;
	            position: absolute;
	            left: 0;
	            top: 0;
	            opacity: 0;
	          }  
	        </style>
	        <script type="text/javascript">
	          jQuery(document).ready(function(){
	             jQuery('#file').change(function(){
	                 var file = jQuery(this).val().replace(/C:\\fakepath\\/i, '');
	                 if(file){
	                    jQuery('#filename').html(file);
	                    jQuery('#upload').removeAttr('disabled');
	                 }
	             });
	          }); 
	        </script>
	    <?php
	}

}
?>