<?php 
add_action('publish_post', 'xyz_link_publish');
add_action('publish_page', 'xyz_link_publish');


$xyz_smap_include_customposttypes=get_option('xyz_smap_include_customposttypes');
$carr=explode(',', $xyz_smap_include_customposttypes);
foreach ($carr  as $cstyps ) {
	add_action('publish_'.$cstyps, 'xyz_link_publish');

}


function xyz_smap_string_limit($string, $limit) {
	
	$space=" ";$appendstr=" ...";
	if(mb_strlen($string) <= $limit) return $string;
	if(mb_strlen($appendstr) >= $limit) return '';
	$string = mb_substr($string, 0, $limit-mb_strlen($appendstr));
	$rpos = mb_strripos($string, $space);
	if ($rpos===false) 
		return $string.$appendstr;
   else 
	 	return mb_substr($string, 0, $rpos).$appendstr;
}

function xyz_smap_getimage($post_ID,$description_org)
{
	$attachmenturl="";
	$post_thumbnail_id = get_post_thumbnail_id( $post_ID );
	if($post_thumbnail_id!="")
	{
		$attachmenturl=wp_get_attachment_url($post_thumbnail_id);
		$attachmentimage=wp_get_attachment_image_src( $post_thumbnail_id, full );
		
	}
	else {
		$first_img = '';
		preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $description_org, $matches);
		$attachmenturl = $matches[1][0];
		
	
	}
	return $attachmenturl;
}
function xyz_link_publish($post_ID) {


	global $current_user;
	get_currentuserinfo();
	$af=get_option('xyz_smap_af');
	$id=$current_user->ID;


	$tappid=get_option('xyz_smap_twconsumer_id');
	$tappsecret=get_option('xyz_smap_twconsumer_secret');
	$twid=get_option('xyz_smap_tw_id');
	$taccess_token=get_option('xyz_smap_current_twappln_token');
	$taccess_token_secret=get_option('xyz_smap_twaccestok_secret');
	$messagetopost=get_option('xyz_smap_twmessage');
	if(isset($_POST['xyz_smap_twmessage']))
		$messagetopost=$_POST['xyz_smap_twmessage'];
	$appid=get_option('xyz_smap_application_id');
	$post_permissin=get_option('xyz_smap_post_permission');
	if(isset($_POST['xyz_smap_post_permission']))
		$post_permissin=$_POST['xyz_smap_post_permission'];

	$post_twitter_permission=get_option('xyz_smap_twpost_permission');
	if(isset($_POST['xyz_smap_twpost_permission']))
		$post_twitter_permission=$_POST['xyz_smap_twpost_permission'];

	$post_twitter_image_permission=get_option('xyz_smap_twpost_image_permission');
	if(isset($_POST['xyz_smap_twpost_image_permission']))
		$post_twitter_image_permission=$_POST['xyz_smap_twpost_image_permission'];

	$appsecret=get_option('xyz_smap_application_secret');
	$useracces_token=get_option('xyz_smap_fb_token');


	$message=get_option('xyz_smap_message');
	if(isset($_POST['xyz_smap_message']))
		$message=$_POST['xyz_smap_message'];
	$fbid=get_option('xyz_smap_fb_id');


	$postpp= get_post($post_ID);

	$posting_method=get_option('xyz_smap_po_method');
	if(isset($_POST['xyz_smap_po_method']))
		$posting_method=$_POST['xyz_smap_po_method'];

	if ($postpp->post_status == 'publish')
	{
		$posttype=$postpp->post_type;
			
		if ($posttype=="page")
		{

			$xyz_smap_include_pages=get_option('xyz_smap_include_pages');
			if($xyz_smap_include_pages==0)
				return;
		}
			
		if($posttype=="post")
		{
			$xyz_smap_include_categories=get_option('xyz_smap_include_categories');
			if($xyz_smap_include_categories!="All")
			{
				$carr1=explode(',', $xyz_smap_include_categories);
					
				$defaults = array('fields' => 'ids');
				$carr2=wp_get_post_categories( $post_ID, $defaults );
				$retflag=1;
				foreach ($carr2 as $key=>$catg_ids)
				{
					if(in_array($catg_ids, $carr1))
						$retflag=0;
				}
					
					
				if($retflag==1)
					return;
			}
		}

		$link = get_permalink($postpp->ID);



		$content = $postpp->post_content;apply_filters('the_content', $content);

		$excerpt = $postpp->post_excerpt;apply_filters('the_excerpt', $excerpt);
		if($excerpt=="")
		{
			if($content!="")
			{
				$excerpt=implode(' ', array_slice(explode(' ', $content), 0, 50));
			}
		}
		$description = $content;
		$description_org=$description;
		$attachmenturl=xyz_smap_getimage($post_ID, $description_org);
		if($attachmenturl!="")
			$image_found=1;
		else
			$image_found=0;
		

		$name = html_entity_decode(get_the_title($postpp->ID), ENT_QUOTES, get_bloginfo('charset'));
		$caption = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset'));
		apply_filters('the_title', $name);

		$name=strip_tags($name);
		$name=strip_shortcodes($name);

		$description=strip_tags($description);
		$description=strip_shortcodes($description);


		if($useracces_token!="" && $appsecret!="" && $appid!="" && $post_permissin==1)
		{

			$user_page_id=get_option('xyz_smap_fb_numericid');

			$xyz_smap_pages_ids=get_option('xyz_smap_pages_ids');

			$xyz_smap_pages_ids1=explode(",",$xyz_smap_pages_ids);


			foreach ($xyz_smap_pages_ids1 as $key=>$value)
			{
				if($value!=-1)
				{
					$value1=explode("-",$value);
					$acces_token=$value1[1];$page_id=$value1[0];
				}
				else
				{
					$acces_token=$useracces_token;$page_id=$user_page_id;
				}

					
				$fb=new Facebook();
				$message1=str_replace('{POST_TITLE}', $name, $message);
				$message2=str_replace('{BLOG_TITLE}', $caption,$message1);
				$message3=str_replace('{PERMALINK}', $link, $message2);
				$message4=str_replace('{POST_EXCERPT}', $excerpt, $message3);
				$message5=str_replace('{POST_CONTENT}', $description, $message4);

               $disp_type="feed";
				if($posting_method==1) //attach
				{
					$attachment = array('message' => $message5,
							'access_token' => $acces_token,
							'link' => $link,
							'name' => $name,
							'caption' => $caption,
							'description' => $description,
							'actions' => array(array('name' => $name,
									'link' => $link))

					);
				}
				else if($posting_method==2)  //share link
				{
					$attachment = array('message' => $message5,
							'access_token' => $acces_token,
							'link' => $link,
							'name' => $name,
							'caption' => $caption,
							'description' => $description


					);
				}
				else if($posting_method==3) //simple text message
				{
					//$message6=xyz_smap_string_limit($message5, 900);
					//$description_li=xyz_smap_string_limit($description, 900);
						
					$attachment = array('message' => $message5,
							'access_token' => $acces_token				
					
					);
					
				}
				else if($posting_method==4 || $posting_method==5) //text message with image 4 - app album, 5-timeline
				{
					//$message6=xyz_smap_string_limit($message5, 900);
					//$description_li=xyz_smap_string_limit($description, 900);
					if($attachmenturl!="")
					{
						
						if($posting_method==5)
						{
							
							$albums = $fb->api("/$page_id/albums", "get", array('access_token'  => $acces_token));
							
							foreach ($albums["data"] as $album) {
								if ($album["type"] == "wall") {
									$timeline_album = $album; break;
								}
							}
							if (isset($timeline_album) && isset($timeline_album["id"])) $page_id = $timeline_album["id"];
						}
						
						
						$disp_type="photos";
						$attachment = array('message' => $message5,
								'access_token' => $acces_token,
								'url' => $attachmenturl	
						
						);
					}
					else
					{
						$attachment = array('message' => $message5,
								'access_token' => $acces_token
						
						);
					}
					
				}
				$result = $fb->api('/'.$page_id.'/'.$disp_type.'/', 'post', $attachment);

			}

			//If the post is not published, print error details

			// 					$content = wp_remote_get($urltopost);
			// 				print_r($content);
			// 				die;


		}


		if($taccess_token!="" && $taccess_token_secret!="" && $tappid!="" && $tappsecret!="" && $post_twitter_permission==1)
		{
				
			////image up start///

			
			if($post_twitter_image_permission==1)
			{

				$img=array();
				if($attachmenturl!="")
					$img = wp_remote_get($attachmenturl);
					

				if (isset($img['body'])&& trim($img['body'])!='')
					$img = $img['body'];
				else
					$image_found = 0;
					
			}
			///Twitter upload image end/////
				

			preg_match_all("/{(.+?)}/i",$messagetopost,$matches);
			$matches1=$matches[1];$substring="";$islink=0;$issubstr=0;
			$len=118;
			if($image_found==1)
				$len=$len-24;

			foreach ($matches1 as $key=>$val)
			{
				$val="{".$val."}";
				if($val=="{POST_TITLE}")
				{$replace=$name;}
				if($val=="{POST_CONTENT}")
				{$replace=$description;}
				if($val=="{PERMALINK}")
				{
					$replace="{PERMALINK}";$islink=1;
				}
				if($val=="{POST_EXCERPT}")
				{$replace=$excerpt;}
				if($val=="{BLOG_TITLE}")
					$replace=$caption;



				$append=mb_substr($messagetopost, 0,mb_strpos($messagetopost, $val));

				if(mb_strlen($append)<($len-mb_strlen($substring)))
				{
					$substring.=$append;
				}
				else if($issubstr==0)
				{
					$avl=$len-mb_strlen($substring)-4;
					if($avl>0)
						$substring.=mb_substr($append, 0,$avl)."...";
						
					$issubstr=1;

				}



				if($replace=="{PERMALINK}")
				{
					$chkstr=mb_substr($substring,0,-1);
					if($chkstr!=" ")
					{$substring.=" ".$replace;$len=$len+12;}
					else
					{$substring.=$replace;$len=$len+11;}
				}
				else
				{
						
					if(mb_strlen($replace)<($len-mb_strlen($substring)))
					{
						$substring.=$replace;
					}
					else if($issubstr==0)
					{
							
						$avl=$len-mb_strlen($substring)-4;
						if($avl>0)
							$substring.=mb_substr($replace, 0,$avl)."...";
							
						$issubstr=1;

					}


				}
				$messagetopost=mb_substr($messagetopost, mb_strpos($messagetopost, $val)+strlen($val));
					
			}

			if($islink==1)
				$substring=str_replace('{PERMALINK}', $link, $substring);
			//echo $substring;die;
				
				
			$twobj=new TwitterOAuth($tappid, $tappsecret,$taccess_token,$taccess_token_secret);
				
			if($image_found==1)
			{
				$resultfrtw = $twobj -> post('http://upload.twitter.com/1/statuses/update_with_media.json', array( 'media[]' => $img, 'status' => $substring), true, true);
			}
			else
				$resultfrtw=$twobj->post('statuses/update', array('status' => $substring));
			//print_r($resultfrtw);
			//die;
		}
	}
}

?>