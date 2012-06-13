<?php

/**
 * WpHerissonFriends
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class WpHerissonFriends extends BaseWpHerissonFriends
{

 public function getInfo() {
	 $url = $this->url."/info";
		$json_data = herisson_network_download($url);
		if (!is_wp_error($json_data)) {
			$data = json_decode($json_data,1);

			if (sizeof($data)) {
 			$this->name  = $data['sitename'];
 			$this->email = $data['adminEmail'];
#				$this->is_active=1;
   } else { $this->is_active=0; }

  } else {
		 $this->is_active=0;
			echo $data->get_error_message("herisson");
		}
 }

 public function setUrl($url) {
  parent::_set('url',$url);
		$this->reloadPublicKey();
 }

	public function reloadPublicKey() {
	 $url = $this->_get('url');
		if (substr($url, -1) != "/") {	$url .= "/"; }
		$url .= "publickey";
		$content = herisson_network_download($url);
		if (!is_wp_error($content)) {
   $this->_set('public_key',$content);
		} else { 
			echo $data->get_error_message("herisson");
		}
	}


	public function retrieveBookmarks() {
	 
	 $options = get_option('HerissonOptions');
		$my_public_key = $options['publicKey'];
  if (function_exists('curl_init')) {
 		$content = herisson_network_download($this->url."/retrieve",array('key' => $my_public_key));
 		if (!is_wp_error($content)) {
 			$json_data = herisson_decrypt($content,$this->public_key);
 			$bookmarks = json_decode($json_data,1);
    return $bookmarks;
 		} else { 
 			echo $data->get_error_message("herisson");
 		}
  }
	}

 public function generateBookmarksdata() {
  $data_bookmarks = array();
  $bookmarks = Doctrine_Query::create()
   ->from('WpHerissonBookmarks')
   ->where('is_public=1')
   ->execute();
  foreach ($bookmarks as $bookmark) {
   $data_bookmarks[] = $bookmark->toArray();
  }
  $json_data = json_encode($data_bookmarks);
#  print_r($json_data);
  $json_display = herisson_encrypt($json_data,$this->public_key);
  return json_encode($json_display);
 }

 public function askForFriend() {
	 $options = get_option('HerissonOptions');
	 $url = $this->url."/ask";
		$mysite = get_option('siteurl')."/".$options['basePath'];
  $signature = herisson_encrypt_short($mysite);
		$data = array(
		 'url'=> $mysite,
			'signature' => $signature
		);
		$content = herisson_network_download($url,$data);
		echo $content."<br>\n";
		if (!is_wp_error($content)) {
		 if ($content == "1") {
			 $this->b_youwant=1;
			} else {
			 echo sprintf(__("Error while adding friend : %s",HERISSONTD),$url);
			}
		} else {
			echo $content->get_error_message("herisson");
		}
 }

 public function validateFriend() {
	 $options = get_option('HerissonOptions');
	 $url = $this->url."/validate";
		$mysite = get_option('siteurl')."/".$options['basePath'];
  $signature = herisson_encrypt_short($mysite);
		$data = array(
		 'url'=> $mysite,
			'signature' => $signature
		);
		$content = herisson_network_download($url,$data);
		echo $content."<br>\n";
		if (!is_wp_error($content)) {
		 if ($content == "1") {
			 echo __('ok');
			} else {
			 echo sprintf(__("Error while adding friend : %s",HERISSONTD),$url);
			}
		} else {
			echo $content->get_error_message("herisson");
		}
 }

	public function approve() {
	 $this->b_wantsyou=0;
	 $this->is_active=1;
		$this->save();
		$this->validateFriend();
	}

}
