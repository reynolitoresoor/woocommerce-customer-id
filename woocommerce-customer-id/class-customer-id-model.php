<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Customer_ID_Model {
    
    private $user_id;

    function __construct(){
    	$current_user = wp_get_current_user();
	    $this->user_id = $current_user->ID;
    }
    
    /*
    *  Create custom tables
    */
    public static function createTables(){
		global $wpdb;

	    $create_table_userprofile_query = "
	            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}userprofile` (
	              `id` INT unsigned NOT NULL AUTO_INCREMENT,
	              `user_id` INT NULL,
	              `profile` VARCHAR(255) NULL,
	              `date_updated` DATETIME NULL,
	              PRIMARY KEY (id)
	            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	    ";

	    $create_table_userlicense_query = "
	            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}userlicense` (
	              `id` INT unsigned NOT NULL AUTO_INCREMENT,
	              `user_id` INT NULL,
	              `medical_license` VARCHAR(255) NULL,
	              `location` VARCHAR(255) NULL,
	              `status` INT NULL COMMENT '0 = Pending Review, 1 = Approved, 2 = Declined',
	              `date_updated` DATETIME NULL,
	              `type` VARCHAR(50) NULL,
	              PRIMARY KEY (id)
	            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	    ";

	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    dbDelta( $create_table_userprofile_query );
	    dbDelta( $create_table_userlicense_query);
	    

	    $results = $wpdb->get_results("SELECT * FROM $users_table GROUP BY ID");
	    if(COUNT($results) <= 0){
	         foreach($results as $result):
	            $aid=get_user_meta( $result->ID, 'get_avatar', true );
	            $user_id = $result->ID;
	            $date_updated = date('Y-m-d H:i:s');

	            if(empty(wp_get_attachment_image_src( $aid, $size = 'thumbnail', $icon = false)['0'])){
	                $user_profile = $result->profile = plugins_url('assets/images/profile-user.png',__FILE__);
	            } else {
	                $user_profile = wp_get_attachment_image_src( $aid, $size = 'thumbnail', $icon = false)['0'];
	            }

	            $wpdb->insert($userprofile_table,
	            	       array(
	            	       	  'user_id'=>$user_id,
	            	       	  'profile'=>$user_profile,
	            	       	  'date_updated'=> $date_updated
	            	       	),
	            	       array('%s','%s','%s')
	            );

	        endforeach;
	    }

	}

    /*
    *  Update usermeta
    */ 
    public static function updateUserMeta(){
	    update_user_meta('138', 'email_notification', NULL);
	    update_user_meta('138','email_notification_recipient', NULL);
	    update_user_meta('138', 'medical_license_discount', NULL);
	    update_user_meta('138', 'medical_license_status', NULL);
    }
    
    /*
    *  Get the current user id
    */
    public function getUserId(){
    	return $this->user_id;
    }

    /*
    *  Get user profile id
    */
    public function getUserProfile(){
    	global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}userprofile WHERE user_id = %d",$this->user_id));
        
        return $results;
    }

    /*
    *  Get user license
    */
    public function getUserLicense(){
       global $wpdb;

       $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}userlicense WHERE user_id = %d", $this->user_id));

       return $results;
    }

    /*
    *  Insert user license
    */
    public function insertUserLicense($data){
    	global $wpdb;
        $userlicense_table = $wpdb->prefix."userlicense";
        
        foreach($data as $v){
           $results = $wpdb->insert($userlicense_table,
           	                    array(
           	                    	'user_id'=> $v['user_id'],
           	                    	'medical_license' => $v['file_name'],
           	                    	'location'=> $v['location'],
           	                    	'status' => $v['status'],
           	                    	'date_updated'=> $v['date_updated'], 
           	                    	'type' => $v['type'], 
           	                    ),
           	                    array('%s','%s','%s','%s','%s','%s','%s')
           	                 );
        }

        return $results;
    	
    }

    /*
    *  Update user license
    */
    public function updateUserLicense($data){
    	global $wpdb;
        $userlicense_table = $wpdb->prefix."userlicense";
        
        foreach($data as $v){
           $results = $wpdb->query("UPDATE $userlicense_table SET medical_license = '".$v['file_name']."', location = '".$v['location']."', status = '".$v['status']."', date_updated = '".$v['date_updated']."', type = '".$v['type']."' WHERE user_id = '".$v['user_id']."'");
        }

        return $results;
    	
    }

    /*
    *  Get total user profile
    */
    public function getTotalUserProfile(){
    	global $wpdb; 

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.ID ASC" );
    	
    	return $results;
    }

    /*
    *  Get all user profile
    */
    public function getAllUserProfile($offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id AND {$wpdb->prefix}users.user_login LIKE '".$search_keyword."%' ORDER BY {$wpdb->prefix}users.ID ASC LIMIT $offset, $limit" );
    	return $results;
    }


    /*
    *  Get total user profile by id
    */
    public function getTotalUserProfileById($order){
       global $wpdb;

       $results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.ID $order" );

       return $results;
    }

    /*
    *  Get all user profile by id
    */
    public function getAllUserProfileById($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.ID $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user profile by username
    */
    public function getTotalUserProfileByUsername($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.user_login $order" );

    	return $results;
    }

    /*
    *  Get all user profile by username
    */
    public function getAllUserProfileByUsername($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.user_login $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user profile by display name
    */
    public function getTotalUserProfileByDisplayName($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.display_name $order" );

    	return $results;
    }

    /*
    *  Get all user profile by display name
    */
    public function getAllUserProfileByDisplayName($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.display_name $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user profile by date uploaded
    */
    public function getTotalUserProfileByDateUploaded($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.ID $order" );

    	return $results;
    }

    /*
    *  Get all user profile by date uploaded
    */
    public function getAllUserProfileByDateUploaded($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.ID $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user license 
    */
    public function getTotalUserLicense(){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.ID ASC" );

    	return $results;
    }

    /*
    *  Get all user license search by display name
    */
    public function getAllUserLicenseSearchByDisplayName($search_keyword, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT {$wpdb->prefix}userlicense.*, {$wpdb->prefix}users.* FROM {$wpdb->prefix}userlicense,{$wpdb->prefix}users WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id AND {$wpdb->prefix}users.display_name LIKE '".$search_keyword."%' ORDER BY {$wpdb->prefix}users.ID ASC LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get all user license search by date uploaded
    */
    public function getAllUserLicenseSearchByDateUploaded($search_keyword, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT {$wpdb->prefix}userlicense.*, {$wpdb->prefix}users.* FROM {$wpdb->prefix}userlicense,{$wpdb->prefix}users WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id AND {$wpdb->prefix}userlicense.date_updated LIKE '".$search_keyword."%' ORDER BY {$wpdb->prefix}users.ID ASC LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get all user license when search keyword is empty
    */
    public function getAllUserLicenseAscending($offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT {$wpdb->prefix}userlicense.*, {$wpdb->prefix}users.* FROM {$wpdb->prefix}userlicense,{$wpdb->prefix}users WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.ID ASC LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user license by id
    */
    public function getTotaluserLicenseById($order){
       global $wpdb;

       $results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.ID $order" );

       return $results;
    }

    /*
    *  Get all user license by id
    */
    public function getAllUserLicenseById($order, $offset, $limit){
       global $wpdb;

       $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.ID $order LIMIT $offset, $limit" );

       return $results;
    }

    /*
    *  Get total user license by display name
    */
    public function getTotalUserLicenseByDisplayName($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.display_name $order" );

    	return $results;
    }

    /*
    *  Get all user license by display name
    */
    public function getAllUserLicenseByDisplayName($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.display_name $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user license by medical license
    */ 
    public function getTotalUserLicenseByMedicalLicense($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}userlicense.medical_license $order" );

    	return $results;
    }

    /*
    *  Get all user license by medical license
    */
    public function getAllUserLicenseByMedicalLicense($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}userlicense.medical_license $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user license by status
    */
    public function getTotalUserLicenseByStatus($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}userlicense.status $order" );

    	return $results;
    }

    /*
    *  Get all user license by status
    */
    public function getAllUserLicenseByStatus($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}userlicense.status $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get total user license by date uploaded
    */
    public function getTotalUserLicenseByDateUploaded($order){
    	global $wpdb;

    	$results = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.ID $order" );

    	return $results;
    }

    /*
    *  Get all user license by date uploaded
    */
    public function getAllUserLicenseByDateUploaded($order, $offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userprofile WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userprofile.user_id ORDER BY {$wpdb->prefix}users.ID $order LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get all user license
    */
    public function getAllUserLicense($offset, $limit){
    	global $wpdb;

    	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users,{$wpdb->prefix}userlicense WHERE {$wpdb->prefix}users.ID = {$wpdb->prefix}userlicense.user_id ORDER BY {$wpdb->prefix}users.ID ASC LIMIT $offset, $limit" );

    	return $results;
    }

    /*
    *  Get user license by user id
    */
    
    


}
?>