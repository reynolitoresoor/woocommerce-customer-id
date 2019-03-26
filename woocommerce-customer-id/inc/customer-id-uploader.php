     <?php 
      $customer_id = new Customer_ID_Model();
	  $user_profile = $customer_id->getUserProfile();
	  $profile_user = true;

      if($type == 'account_details'){
      	 $type = "dashboardImage";
      } else if($type == 'checkout'){
      	 $type = "checkoutImage";
      } else if($type == 'orders'){
      	 $type = "orderImage";
      }

      foreach($user_profile as $profile){
	      $user_image = $profile->profile;
	  }

      $image_url = parse_url($user_image);
	  $image_path = $image_url['path'];

	  if(empty($user_image) || !file_exists($_SERVER['DOCUMENT_ROOT'].$image_path)){
	      $profile_user = false;
	      $user_image = plugins_url('assets/images/profile-user.png',__FILE__);
	  } else {
	      $profile_user = true;
	      $user_image = $user_image;
	  }

      if($profile_user == true){
	      $parse_url = parse_url($user_image);
	      $image_path = $parse_url['path'];
	 ?>
	  <div class="large-4">
	    <p><strong>Government Issued ID</strong></p>
	    <div class="slim" id="Cropper" data-size="300,200" data-force-size="300,200" data-push="true" data-service="<?php echo plugins_url('../async.php',__FILE__) ?>" data-did-remove="handleImageRemoval">
	      <img src="<?php echo $image_path; ?>" id="<?php echo $type; ?>">
	        </div>
	    </div>
	    <script type="text/javascript">
	       /*
	       *   Permanently delete an image
	       */
	       function handleImageRemoval(){
	             var image = jQuery('#<?php echo $type; ?>').attr('src');
	             jQuery.ajax({
	               url: '<?php echo plugins_url('../remove-file.php', __FILE__); ?>',
	               type: 'post',
	               data: {path: image},
	               success: function(response){}
	            });
	      }
	    </script>
	  <?php
	  } else {
	      $parse_url = parse_url($user_image);
	      $image_path = $parse_url['path'];
	  ?>
	  <div class="large-4">
	        <p><strong>Upload Government Issued ID</strong></p>
	        <div class="slim" id="Cropper" data-size="300,200" data-force-size="300,200" data-push="true" data-service="<?php echo plugins_url('../async.php',__FILE__) ?>" data-label="Click To Upload ID">
	          <input type="file" name="slim[]">
	        </div>
	  </div>
	  <?php
	  }

	  ?>