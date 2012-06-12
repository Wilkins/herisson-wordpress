<?php

/**
 * WpHerissonBookmarks
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class WpHerissonBookmarks extends BaseWpHerissonBookmarks
{

 /** Properties **/
 public function setUrl($url) {
	 echo "Set Url<br>";
  parent::_set('url',$url);
	 echo "Set content<br>";
 	$this->getContentFromUrl();
	 echo "Set capture<br>";
 	$this->captureFromUrl();
	 echo "Set title<br>";
		$this->getTitleFromContent();
	 echo "Set favicon url<br>";
		$this->getFaviconUrlFromContent();
	 echo "Set favicon image<br>";
		$this->getFaviconImageFromUrl();
	 echo "Set hash<br>";
  $this->_set('hash',md5($url));
 }

	public function getTitleFromContent() {
		if (!$this->content || $this->title) { return false; }
		preg_match("#<title>([^<]*)</title>#",$this->content,$match);
		$this->title = $match[1];
	}

	public function getFaviconUrlFromContent() {
		if (!$this->content && $this->favicon_url) { return false; }
		preg_match_all('#<link[^>]*href="([^"]*)"#',$this->content,$match);
		foreach ($match[0] as $i=>$m) {
		 if (preg_match("#(favicon|shortcut)#",$m)) {
			 $favicon_url = $match[1][$i];
				# Absolute path
				if (preg_match("#^/#",$favicon_url)) {
	 			$parsed_url = parse_url($this->url);
				 $favicon_url = $parsed_url['scheme'].'://'.$parsed_url['host'].$favicon_url;
				} else if (preg_match("#https?://#",$favicon_url)) {
				 # Full path
				} else {
				 # Relative path
				 $favicon_url = dirname($this->url)."/".$favicon_url;
				}
				$this->_set('favicon_url',$favicon_url);
			}
		}
	}

	public function getFaviconImageFromUrl() {
		if (!$this->content && $this->favicon_image) { return false; }
		$content = herisson_download($this->favicon_url);
		$base64 = base64_encode($content);
		$this->_set('favicon_image',$base64);
	}

	public function getContentFromUrl() {
		if (!$this->content) {
		 $content = herisson_download($this->url);
 		if (!is_wp_error($content)) {
    $this->_set('content',$content);
 		} else { 
 			echo $data->get_error_message("herisson");
 		}
  }
	}

 /** Export **/
	public function toArray() {
	 return array(
		 "title" => $this->title,
		 "url" => $this->url,
		 "description" => $this->description,
			"tags" => $this->getTagsList(),
		);
	}

	public function toJSON() {
	 return json_encode($this->toArray());
	}
 
 /** Tags **/
	public function getTagsList() {
	 $tags = $this->getTags();
		$list = array();
		foreach ($tags as $tag) {
		 $list[] = $tag;
		}
		return $list;
	}

	public function getTags() {
	 return Doctrine_Query::create()
		  ->from('WpHerissonTags')
		  ->where("bookmark_id=".$this->id)
		  ->orderby("name")
		  ->execute();
	}

	public function delTags() {
	 Doctrine_Query::create()
	  ->delete()
	  ->from('WpHerissonTags')
	  ->where("bookmark_id=".$this->id)
	  ->execute();
	}
 
 /** Capture **/
	public function getThumbUrl() {
	 return get_option('siteurl')."/wp-content/plugins/herisson/screenshots/".$this->id."_small.png";
	}

	public function getImageUrl() {
	 return get_option('siteurl')."/wp-content/plugins/herisson/screenshots/".$this->id.".png";
	}

	public function getThumb() {
	 return HERISSON_SCREENSHOTS_DIR.$this->id."_small.png";
	}

	public function getImage() {
	 return HERISSON_SCREENSHOTS_DIR.$this->id.".png";
	}

	public function captureFromUrl() {
	 if (!$this->id) { return false; }
	 # ./wkhtmltoimage-amd64 --disable-javascript --quality 50 http://www.wilkins.fr/ /home/web/www.wilkins.fr/google.png
		$url = $this->url;
		$image = HERISSON_SCREENSHOTS_DIR.$this->id.".png";
		$thumb = HERISSON_SCREENSHOTS_DIR.$this->id."_small.png";
		$wkhtmltoimage = HERISSON_BASE_DIR."wkhtmltoimage-amd64";
		$convert = "/usr/bin/convert";
		$options_nojs = " --disable-javascript ";
		$options_quality50 = " --quality 50 ";
		if (!file_exists($image) || filesize($image) == 0) {
# 		echo "$wkhtmltoimage $options_quality50 \"$url\" $image";
 		exec("$wkhtmltoimage $options_quality50 \"$url\" $image",$output);
		 echo implode("\n",$output);
		}

		if (!file_exists($image) || filesize($image) == 0) {
# 		echo "$wkhtmltoimage $options_nojs $options_quality50 \"$url\" $image";
 		exec("$wkhtmltoimage $options_nojs $options_quality50 \"$url\" $image",$output);
		 echo implode("\n",$output);
		}

		if (!file_exists($thumb) || filesize($thumb) == 0) {
 		exec("$convert -resize 200x \"$image\" \"$thumb\"",$output);
		 echo implode("\n",$output);
		}

	}

}
