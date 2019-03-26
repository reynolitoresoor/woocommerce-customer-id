<?php
         $customer_id = new Customer_ID_Model();

	     $search_keyword = sanitize_text_field($_POST['keyword']);
	     $toHTML = '';
	     $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
	     $limit = 50; // number of rows in page
	     $offset = ( $pagenum - 1 ) * $limit;

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
	    
	    $toHTML='<table id="main-table" class="responsive-table widefat fixed striped comments">';
	        $toHTML.='<thead>';
	           $toHTML.='<tr>';
	              $toHTML.='<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>';
	                    $toHTML.='<th scope="col" id="id" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=desc'.'"><span>ID</span><span class="sorting-indicator"></span></a>
	                    </th>';
	                    $toHTML.='<th scope="col" id="author" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=desc'.'"><span>Thumbnail</span><span class="sorting-indicator"></span></a>
	                    </th>';
	                    $toHTML.='<th scope="col" id="comment" class="manage-column column-comment column-primary sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=username&amp;order=desc'.'"><span>Username</span><span class="sorting-indicator"></th>';
	                    $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=display-name&amp;order=desc'.'"><span>Display Name</span><span class="sorting-indicator">
	                    </th>'; 
	                    $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=date-uploaded&amp;order=asc'.'"><span>Date Uploaded</span><span class="sorting-indicator">
	                    </th>';
	                    $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Edit</span><span class="sorting-indicator">
	                    </th>';    
	            $toHTML.='</tr>';
	        $toHTML.='</thead>';

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

	                $toHTML.='<tbody id="the-comment-list" data-wp-lists="list:comment">';
	                    $toHTML.='<tr id="comment-1" class="comment even thread-even depth-1 approved">';
	                        $toHTML.='<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-1">Select comment</label>
	                        <input id="cb-select-1" type="checkbox" name="delete_comments[]" value="1">
	                        </th>';
	                    $toHTML.='<td>'.$result->ID.'</td>';
	                    $toHTML.='<td class="author column-author" data-colname="Author"><strong><a href="'.$result->profile.'" data-lightbox="government issued id" data-title="'.$result->user_login.'"><img alt="'.$result->user_login.'ID'.'" srcset="'.$result->profile.'" class="avatar avatar-32 wp-user-avatar wp-user-avatar-32 alignnone photo avatar-default" height="40" style="width: 65%;"></a></td>';
	                    $toHTML.='<td class="comment column-comment has-row-actions column-primary" data-colname="Comment">'.$result->user_login.'</td>';
	                    $toHTML.='<td class="response column-response" data-colname="In Response To">'.$result->display_name.'</td>';
	                    $toHTML.='<td class="response column-response" data-colname="In Response To">'.$date_uploaded.'</td>';
	                    $edit_user = 'editAccount("'.$result->ID.'","'.$result->profile.'","'.$result->user_login.'")';
	                    $toHTML.="<td class='response column-response' data-colname='In Response To'><a style='cursor:pointer' onclick='".$edit_user."'><span class='dashicons-before dashicons-edit'></span>Edit</a></td>";
	                  $toHTML.='</tr>';
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
	                $toHTML.='<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>';
	                    $toHTML.='<th scope="col" id="id" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=asc'.'"><span>ID</span><span class="sorting-indicator"></span></a>
	                    </th>';
	                    $toHTML.='<th scope="col" id="author" class="manage-column column-author sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=id&amp;order=asc'.'"><span>Thumbnail</span><span class="sorting-indicator"></span></a>
	                    </th>';
	                    $toHTML.='<th scope="col" id="comment" class="manage-column column-comment column-primary sortable soascrted"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=username&amp;order=asc'.'"><span>Username</span><span class="sorting-indicator">
	                    </th>';
	                    $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=display-name&amp;order=asc'.'"><span>Display Name</span><span class="sorting-indicator">
	                    </th>';
	                    $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a href="'.home_url().'/wp-admin/admin.php?page=customer-id.php&orderby=date-uploaded&amp;order=asc'.'"><span>Date Uploaded</span><span class="sorting-indicator">
	                    </th>';
	                    $toHTML.='<th scope="col" id="response" class="manage-column column-response sortable asc"><a><span>Edit</span><span class="sorting-indicator">
	                    </th>';
	                $toHTML.='</tfoot>';
	            $toHTML.='</table>';
	            if ( $page_links ) {
	            $toHTML.='<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
	            }
	    echo $toHTML;
?>