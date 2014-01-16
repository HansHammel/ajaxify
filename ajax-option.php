<?php
/*
	Plugin name:Ajaxify Wordpress
	Plugin URI: 
	Description:Ajaxify Links of your wordpress theme
	Author: Vishal Parikh
	Author URI:
	Version: 1.0
 */
   function ajx_plugin_menu() {
		
		add_options_page( 'Ajaxify Plugin Options', 'Ajaxify', 'manage_options', 'aj-id', 'ajaxify_options' );
	}
	    add_action('admin_menu', 'ajx_plugin_menu');  

   function ajaxify_options() {
	
	if ( !current_user_can( 'manage_options' ) )  {
		
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
  // Create New option in wordpress option table
	
   //New optoin created if no image selected the old image will be placed	
	$new_option=get_option('axj_settings');
	
	
	 if(!empty($_REQUEST['sub'])):
     
	  $_POST['ajx_reload']= trim(urldecode(stripslashes($_POST['ajx_reload'])));
	  $options['ajx_div_id'] = $_POST['ajx_div'];
      $options['ajx_link_ids'] = $_POST['ajx_link'];
	  $options['ajx_reload_code'] = $_POST['ajx_reload'];
	  
	
	  if($_FILES['file']['name']!=''):
	     $options['ajx_loader'] = substr($_FILES['file']['name'],0,2).'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);	
         $uploaddir = plugin_dir_path( __FILE__ ).'/images/';
         move_uploaded_file($_FILES["file"]["tmp_name"], $uploaddir . substr($_FILES['file']['name'],0,2).'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
		 update_option('axj_settings', $options);
	  else:
	  
	    if(!empty($new_option['ajx_loader'])):
	   	 $options['ajx_loader'] = $new_option['ajx_loader'];	
	  	 update_option('axj_settings', $options);
		endif;
	 
	 endif;
   endif;   
 
  $ajx_get_opt=get_option('axj_settings');	
   add_option( 'axj_settings', $ajx_options );   
     $ajx_options = array (
         
		 'ajx_div_id' => '',
         'ajx_link_ids' => '',
         'ajx_reload_code' => '',
         'ajx_loader' => '',
         
       );
	
    //admin layout for ajaxify opition	
	
      echo '<style> .ajax_layout lable{ display: block;font-weight: bold;min-width: 300px; padding-bottom: 5px; }
	                .ajax_layout  .decription{font-style:italic;}
				    .wrap-div  h4{font-size:21px;font-weight:normal;}
	     </style>';
		 
			echo '<div class="wrap-div">';
			echo '<h4>Ajaxify Option Dashboard.</h4>';
			echo '<hr>';
	?>
			
	        <?php if( isset($_REQUEST['sub']) ):?>
              <div id="message" class="updated"><p><strong><?php _e('Settings saved.') ?></strong></p></div>
            <?php endif; ?>


      
  <div class="ajax_layout">
  
    <form name="ajx_form" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>" enctype="multipart/form-data" > 
         
       <p><lable>Enter Your Main Outer  div id (comman for pages): </lable> <input type="text" name="ajx_div" value="<?php if(!empty($ajx_get_opt['ajx_div_id'])) echo $ajx_get_opt['ajx_div_id'] ;?>" size="20" required="required"> 
       <span class="decription"> Excluding '#'</span>
       </p>
         
       <p><lable>Enter Link Id Or Class: </lable> <input type="text" name="ajx_link" value="<?php if(!empty($ajx_get_opt['ajx_link_ids'])) echo $ajx_get_opt['ajx_link_ids'];?>" size="70"  width="700px" required="required"/>
     
       <span class="decription"> For eg. #menu a, .menu a (Separated by comma)</span></p>
         
     <p><lable>Enter Reload Code: </lable> <textarea name="ajx_reload"  style="width:800px; height:200px;"><?php  if(!empty($ajx_get_opt['ajx_reload_code'])) 
	  echo $ajx_get_opt['ajx_reload_code'];?></textarea><span class="decription">For expample slider,forms where jquery is used</span></p>
         
      <p><lable>Choose Gif Loader: </lable><input type="file"  name="file" <?php if(empty($ajx_get_opt['ajx_loader'])):?> required="required" <?php endif;?>/> 
         
        <?php if(!empty($ajx_get_opt['ajx_loader'])):?> 
         <img src="<?php echo plugins_url().'/Ajaxifier/images/'.$ajx_get_opt['ajx_loader']?>">
         <?php endif;?>
       </p>       
         
      <p class="submit"><input type="submit"  name="sub" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
          
        </form>
  </div>  
<?php } //ajax options ends here


function axj_frontend_scripts() {	
    if(!is_admin()){

		wp_enqueue_script('jquery');
    
	}
}

  add_action('init', 'axj_frontend_scripts');
  add_action('wp_head','ajx_jscript');
  
  function ajx_jscript(){  $ajx_get_opt=get_option('axj_settings');	  ?>
   
   <?php if(!empty($ajx_get_opt['ajx_link_ids'])&& !empty($ajx_get_opt['ajx_div_id'])):?>
    <div class="loader" style="top:70%; left:50%; position:absolute; z-index:9999;display:none;"></div>
 <script>
   function reloadJs(){
	   
	   <?php echo $ajx_get_opt['ajx_reload_code'];?>
    }
	
	
	
  jQuery(document).ready(function(){
  jQuery('<?php echo $ajx_get_opt['ajx_link_ids'];?>').on('click' ,function(e){
  e.preventDefault();
   link_url=jQuery(this).attr('href');
   jQuery.ajax({
	type: "POST",
	url: link_url,
	cache: false,
	      beforeSend: function() { 
		  jQuery('.loader').show();
		  jQuery('.loader').html('<img src="<?php echo plugins_url().'/Ajaxifier/images/'.$ajx_get_opt['ajx_loader']?>">'); },
	   }).done(function( result ) {
	 jQuery('.loader').hide();

  <?php $url=$_SERVER['REQUEST_URI'];
   $using_ie8 = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.') !== FALSE);
   $using_ie9 = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.') !== FALSE);
   
   if(!$using_ie8 && !$using_ie9):?>
     window.history.pushState(null,null, link_url)
  <?php endif; ?>
	 
	 var findhtml = jQuery(result).find('<?php echo '#'.$ajx_get_opt['ajx_div_id'];?>').html();
	 jQuery('<?php echo '#'.$ajx_get_opt['ajx_div_id'];?>').html(findhtml);
     reloadJs();
	});
 });
 });
   </script>
  <?php endif;?>  
    
<?php }?>