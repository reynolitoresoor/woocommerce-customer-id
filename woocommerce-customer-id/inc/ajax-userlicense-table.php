<?php
         $customer_id = new Customer_ID_Model();

	     $search_keyword = sanitize_text_field($_POST['keyword']);
	     $toHTML = '';
	     $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
	     $limit = 10; // number of rows in page
	     $offset = ( $pagenum - 1 ) * $limit;
	     $total = $customer_id->getTotalUserLicense();
	     $num_of_pages = ceil( $total / $limit );
	     
	     if(!empty($search_keyword)){
	        $results = $customer_id->getAllUserLicenseSearchByDisplayName($search_keyword, $offset, $limit);

	        if(empty($results)){
	          $results = $customer_id->getAllUserLicenseSearchByDateUploaded($search_keyword, $offset, $limit);
	        }
	     } else {
	        $results = $customer_id->getAllUserLicenseAcending($offset, $limit);
	     }
	     


	     $page_links = paginate_links( array(
	    'base' => add_query_arg( 'pagenum', '%#%' ),
	    'format' => '',
	    'prev_text' => __( '&laquo;', 'text-domain' ),
	    'next_text' => __( '&raquo;', 'text-domain' ),
	    'total' => $num_of_pages,
	    'current' => $pagenum
	    ) );

	    $toHTML='<table id="main-table" class="responsive-table widefat fixed striped comments" width="100%">';
	        $toHTML.='<thead>';
	           $toHTML.='<tr>';
	              $toHTML.='<td id="cb" class="manage-column column-cb check-column" style="width: 1%;"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>';
	                $toHTML.='<th scope="col" id="id" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=id&amp;order=desc"><span>ID</span><span class="sorting-indicator"></span></a>
	                </th>';
	                $toHTML.='<th scope="col" id="id" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=display_name&amp;order=desc"><span>Display Name</span><span class="sorting-indicator"></span></a>
	                </th>';
	                $toHTML.='<th scope="col" id="author" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=medical_license&amp;order=desc"><span>Medical License</span><span class="sorting-indicator"></span></a>
	                </th>';
	                $toHTML.='<th scope="col" id="author" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=status&amp;order=desc"><span>Status</span><span class="sorting-indicator"></span></a>
	                </th>';
	                $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=date-uploaded&amp;order=desc"><span>Date Uploaded</span><span class="sorting-indicator">
	                </th>'; 
	                $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Update Status</span><span class="sorting-indicator">
	                </th>';     
	            $toHTML.='</tr>';
	        $toHTML.='</thead>';

	        if($results){
	           foreach($results as $result):
	           
	           $date = new DateTime($result->date_updated);
	           $date_uploaded = $date->format('Y-m-d');
	           $status_option = "";

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

	            if($result->status == 0 OR $result->status == 2){
	                $status_color = 'color: red';
	            } else {
	                $status_color = 'color:green';
	            }

	            if($result->status == 0) {
	                $status = "Pending Review";
	            } else if($result->status == 1) {
	                $status = "Approved";
	            } else {
	                $status = 'Declined';
	            }
	           
	           if($result->status == 0) {
	              $status_option = '<option value="0" selected>Pending Review</option>';
	              $status_option.='<option value="1">Approved</option>';
	              $status_option.='<option value="2">Declined</option>';
	           }
	           if($result->status == 1) {
	              $status_option = '<option value="0">Pending Review</option>';
	              $status_option.='<option value="1" selected>Approved</option>';
	              $status_option.='<option value="2">Declined</option>';
	           }
	           if($result->status == 2) {
	              $status_option = '<option value="0" selected>Pending Review</option>';
	              $status_option.='<option value="1">Approved</option>';
	              $status_option.='<option value="2" selected>Declined</option>';
	           }

	                $toHTML.='<tbody id="the-comment-list" data-wp-lists="list:comment">';
	                      $toHTML.='<tr id="comment-1" class="comment even thread-even depth-1 approved">';
	                      $toHTML.='<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-1">Select comment</label>
	                      <input id="cb-select-1" type="checkbox" name="delete_comments[]" value="1">
	                      </th>';
	                  $toHTML.='<td class="response column-response" data-colname="In Response To">'.$result->id.'</td>';
	                  $toHTML.='<td class="response column-response" data-colname="In Response To">'.$result->display_name.'</td>';
	                  $toHTML.='<td class="response column-response" data-colname="In Response To">'.$file_preview.'</td>';
	                  $toHTML.='<td class="response column-response" data-colname="In Response To"><p style="'.$status_color.'">'.$status.'</p></td>';
	                  $toHTML.='<td class="response column-response" data-colname="In Response To">'.$date_uploaded.'</td>';
	                  $toHTML.='<td class="response column-response" data-colname="In Response To">
	                    <select name="status['.$result->ID.']">'.$status_option.'</select>
	                  </td></tr>';
	               $toHTML.='</tbody>';
	           endforeach; 
	        } else {
	               $toHTML.='<tbody id="the-extra-comment-list" data-wp-lists="list:comment" style="display: none;">';
	                        $toHTML.='<tr class="no-items">';
	                           $toHTML.='<td class="colspanchange" colspan="5">No records found.</td>';
	                        $toHTML.='</tr>';
	               $toHTML.='</tbody>';
	        } 
	          $toHTML.='<tfoot>';
	              $toHTML.='<tr>';
	                  $toHTML.='<td id="cb" class="manage-column column-cb check-column" style="width: 1%;"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>';
	                  $toHTML.='<th scope="col" id="id" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=id&amp;order=desc"><span>ID</span><span class="sorting-indicator"></span></a>
	                  </th>';
	                  $toHTML.='<th scope="col" id="id" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=display_name&amp;order=desc"><span>Display Name</span><span class="sorting-indicator"></span></a>
	                  </th>';
	                  $toHTML.='<th scope="col" id="author" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=medical_license&amp;order=desc"><span>Medical License</span><span class="sorting-indicator"></span></a>
	                  </th>';
	                  $toHTML.='<th scope="col" id="author" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=status&amp;order=desc"><span>Status</span><span class="sorting-indicator"></span></a>
	                  </th>';
	                  $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-medical-license&orderby=date-uploaded&amp;order=desc"><span>Date Uploaded</span><span class="sorting-indicator">
	                  </th>'; 
	                  $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Update Status</span><span class="sorting-indicator">
	                  </th>';
	                $toHTML.='</tfoot>';
	            $toHTML.='</table>';
	            if ( $page_links ) {
	            $toHTML.='<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
	            }
	            $toHTML.='<div style="margin-top: 20px;">
	              <input type="submit" name="search_update_medical_license_status" value="Save Changes" class="button button-primary">
	            </div>';
	    echo $toHTML;
?>