<?php
/**
 * The admin interface for managing and editing bookmarks.
 * @package herisson
 */

function herisson_bookmark_actions() {

 $action = param('action');
 switch ($action) {
	 case 'add': herisson_bookmark_add();
		break;
	 case 'edit': herisson_bookmark_edit();
		break;
	 case 'view': herisson_bookmark_view();
		break;
	 case 'submitedit': herisson_bookmark_submitedit();
		break;
		case 'list': herisson_bookmark_list();
		break;
		case 'delete': herisson_bookmark_delete();
		break;
		case 'download': herisson_bookmark_download();
		break;
		case 'tagcloud': herisson_bookmark_tagcloud();
		break;
  default: herisson_bookmark_list();
	}

}


function herisson_bookmark_list() {
 
 if (get('tag')) {
 	$bookmarks = herisson_bookmark_get_tag(get('tag'));
	} else {
 	$bookmarks = herisson_bookmark_all();
	}
 echo '
	<div class="wrap">
				<h2>' . __("All bookmarks", HERISSON_TD).'<a href="'.get_option('siteurl').'/wp-admin/admin.php?page=herisson_bookmarks&action=add&id=0" class="add-new-h2">'.__('Add',HERISSON_TD).'</a></h2>
				';
 if (sizeof($bookmarks)) {
  ?>
 <table class="widefat post " cellspacing="0">
 <tr>
  <th></th>
  <th><?=__('Title',HERISSON_TD)?></th>
  <th><?=__('URL',HERISSON_TD)?></th>
  <th><?=__('Tags',HERISSON_TD)?></th>
  <th><?=__('Action',HERISSON_TD)?></th>
 </tr>
 <?
  foreach ($bookmarks as $bookmark) {
 ?> 
 <tr>
  <td style="width: 30px; vertical-align:baseline"><? if ($bookmark->favicon_image) { ?><img src="data:image/png;base64,<?=$bookmark->favicon_image?>" /><? } ?></td>
  <td><b><a href="<?=get_option('siteurl')?>/wp-admin/admin.php?page=herisson_bookmarks&action=edit&id=<?=$bookmark->id?>"><? echo $bookmark->title ? $bookmark->title : "Unamed-".$bookmark->$id; ?></a></b></td>
  <td><a href="<? echo $bookmark->url; ?>"><? echo strlen($bookmark->url) > 80 ? substr($bookmark->url,0,80)."&hellip;" : $bookmark->url; ?></a></td>
  <td><? foreach ($bookmark->getTagsArray() as $tag) { ?><a href="<?=get_option('siteurl')?>/wp-admin/admin.php?page=herisson_bookmarks&tag=<?=$tag?>"><?=$tag?></a>, &nbsp; <? } ?></td>
  <td>
		 <a href="<?=get_option('siteurl')?>/wp-admin/admin.php?page=herisson_bookmarks&action=delete&id=<?=$bookmark->id?>" onclick="if (confirm('<?=__('Are you sure ? ',HERISSON_TD)?>')) { return true; } return false;"><?=__('Delete',HERISSON_TD)?></a>
		</td>
 </tr>
 <?
 
 	}
		?>
		</table>
	 <? echo __(sizeof($bookmarks)." bookmarks.",HERISSON_TD); ?>
		</div>
		<?
 } else {
	 echo __("No bookmark",HERISSON_TD);
 }

}


function herisson_bookmark_add() {
 herisson_bookmark_edit(0);
}

function herisson_bookmark_edit($id=0) {


	$options = get_option('HerissonOptions');
	$dateTimeFormat = 'Y-m-d H:i:s';

   if ($id == 0) {
 			$id = intval(param('id'));
			}
			if ($id == 0) {
			 $existing = new WpHerissonBookmarks();
				$tags = array();
			} else {
    $existing = herisson_bookmark_get($id);
				$tags = $existing->getTagsArray();
			}

            echo '
			<div class="wrap">
				<h2>' . __("Edit Bookmark", HERISSON_TD) . '</h2>

				<form method="post" action="' . get_option('siteurl') . '/wp-admin/admin.php?page=herisson_bookmarks">
			';


 if ( function_exists('wp_nonce_field') ) wp_nonce_field('bookmark-edit');
 if ( function_exists('wp_referer_field') ) wp_referer_field();
    echo herisson_messages();


            echo '
				<h3>' . __("Bookmark", HERISSON_TD) . ' ' . $existing->id . ':<cite> &laquo;&nbsp;' . $existing->title . '&nbsp;&raquo;</cite></h3>

				<table class="form-table" cellspacing="2" cellpadding="5">

				<input type="hidden" name="action" value="submitedit" />
				<input type="hidden" name="page" value="herisson_bookmarks" />
				<input type="hidden" name="id" value="' . $existing->id . '" />

				<tbody>
				';
				

			// Title.
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="title-0">' . __("Title", HERISSON_TD) . ':</label>
					</th>
					<td>
						<input type="text" class="main" id="title-0" name="title" value="' . $existing->title . '" />
					</td>
					<td rowspan="5" style="text-align: center; vertical-align: top">
						<!--
					 <br/>
						<b><a href="/wp-admin/admin.php?page=herisson_bookmarks&action=view&id='.$existing->id.'&nomenu=1" target="_blank">'.__('View archive',HERISSON_TD).'</a></b><br/><br/>
					 <br/>
						-->
						<b><a href="/wp-admin/admin.php?page=herisson_bookmarks&action=download&id='.$existing->id.'"><img src="'.HERISSON_PLUGIN_URL.'/images/ico-download.png"/><br/>'.__('Download',HERISSON_TD).'</a></b><br/><br/>
						<b><a href="'.$existing->getDirUrl().'" target="_blank">'.__('View archive',HERISSON_TD).'</a></b><br/><br/>
					'.($existing->id && file_exists($existing->getImage()) && filesize($existing->getImage()) ? '
						<b>'.__('Capture',HERISSON_TD).'</b><br/>
					 <a href="'.$existing->getImageUrl().'"><img alt="Capture" src="'.$existing->getThumbUrl().'" style="border:0.5px solid black"/></a>
     ' : '').'
					</td>
				</tr>
				';

			// URL
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="url-0">' . __("URL", HERISSON_TD) . ':</label>
					</th>
					<td>
						<input type="text" size="80" class="main" id="url-0" name="url" value="' . $existing->url . '" readonly="readonly" />
						<br/><small><a href="'.$existing->url.'" style="text-decoration:none">Visit '.$existing->url.'</a></small>
					</td>
				</tr>
				';

			// Description
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
					<label for="description-0">' . __("Description", HERISSON_TD) . ':</label>
					</th>
					<td>
					<textarea class="main" id="description-0" name="description">'. $existing->description.'</textarea>
					</td>
				</tr>
				';
/*
			// Image URL.
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="image-0">' . __("Book Image URL", HERISSON_TD) . ':</label>
					</th>
					<td>
						<input type="text" class="main" id="image-0" name="image" value="' . htmlentities($existing->image) . '" />
					</td>
				</tr>

				';
*/
			// Statut.
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="url-0">' . __("Favicon", HERISSON_TD) . ':</label>
					</th>
					<td>
      '.($existing->favicon_image ? '<img src="data:image/png;base64,'.$existing->favicon_image.'"/>' : '').'
						<input type="text" size="80" class="main" id="url-0" name="url" value="' . $existing->favicon_url . '" readonly="readonly" />
					</td>
				</tr>
				';
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="content-0">' . __("Content", HERISSON_TD) . ':</label>
					</th>
					<td>
      '.($existing->content ? '<span class="herisson-success">' . __("Yes", HERISSON_TD) . '</span>' : '<span class="herisson-errors">' . __("No", HERISSON_TD) . '</span>').'
					</td>
				</tr>';

            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="type-0">' . __("Type", HERISSON_TD) . ':</label>
					</th>
					<td>
      '.$existing->content_type.'
					</td>
				</tr>';

            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="size-0">' . __("Archive size", HERISSON_TD) . ':</label>
					</th>
					<td>
      '.format_size($existing->dirsize).'
					</td>
				</tr>';

			// Visibility.
            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="visibility-0">' . __("Visibility", HERISSON_TD) . ':</label>
					</th>
					<td>
						<select name="is_public" id="visibility-0">
							';

						echo '
									<option value="0"'.(!$existing->is_public ? ' selected="selected"' : '').'>' . __("Private", HERISSON_TD) . '</option>
									<option value="1"'.($existing->is_public ? ' selected="selected"' : '').'>' . __("Public", HERISSON_TD) . '</option>
								';

				echo '
						</select>
						<br><small>' . __("<code>Public Visibility</code> enables a bookmark to appear publicly within the herisson page.", HERISSON_TD) . '</small>
						<br><small>' . __("<code>Private Visibility</code> restricts the visibility of a book to within the administrative interface.", HERISSON_TD) . '</small>
					</td>
				</tr>';

            echo '
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="visibility-0">' . __("Tags", HERISSON_TD) . ':</label>
					</th>
					<td>
					';
				echo "
<script src=\"/wp-content/plugins/herisson/js/herisson.dev.js\" type=\"text/javascript\"></script>
<script>
 jQuery(document).ready(function($) {
  $('#tagsdiv-post_tag, #categorydiv').children('h3, .handlediv').click(function(){
   $(this).siblings('.inside').toggle();
  });
 });
</script>";

 ?>
			 </td>
				</tr>
    </tbody>
    </table>
   <div id="tagsdiv-post_tag" class="postbox">
    <div class="handlediv" title="<?php esc_attr_e( 'Click to toggle' ); ?>"><br /></div>
    <h3><span><?php _e('Tags'); ?></span></h3>
    <div class="inside">
     <div class="tagsdiv" id="post_tag">
      <div class="jaxtag">
       <label class="screen-reader-text" for="newtag"><?php _e('Tags'); ?></label>
       <input type="hidden" name="tags" class="the-tags" id="tags" value="<? foreach ($tags as $tag) { echo $tag; echo ","; } ?>" />
       <div class="ajaxtag">
        <input type="text" name="newtags" class="newtag form-input-tip" size="16" autocomplete="off" value="" />
        <input type="button" class="button tagadd" value="<?php esc_attr_e('Add'); ?>" tabindex="3" />
       </div>
      </div>
      <div class="tagchecklist" id="tagchecklist">
						</div>
     </div>
     <p class="tagcloud-link"><a href="#titlediv" class="tagcloud-link" id="link-post_tag"><?php _e('Choose from the most used tags'); ?></a></p>
    </div>
   </div>

				<?

   echo "
    </tbody>
    </table>";


echo '


    <p class="submit">
     <input class="button" type="submit" value="' . __("Save", HERISSON_TD) . '" />
    </p>

    </form>

   </div>
    ';

}

function herisson_bookmark_submitedit() {

  $id = intval(post('id'));

		$bookmark = herisson_bookmark_get($id);
		$bookmark->title = post('title');
		$bookmark->url = post('url');
		$bookmark->description = post('description');
		$bookmark->is_public = post('is_public');
		$bookmark->save();
 	$bookmark->maintenance();
 	$bookmark->captureFromUrl();

  $tags = explode(',',post('tags'));
		$bookmark->setTags($tags);

	 herisson_bookmark_edit($bookmark->id);

}

function herisson_bookmark_view() {
 $id = intval(get('id'));
 if (!$id) {
  echo __("Error : Missing id\n",HERISSON_TD);
		exit;
	}
 $bookmark = herisson_bookmark_get($id);
	if ($bookmark && $bookmark->content) {
 	echo $bookmark->content;
	} else {
  echo sprintf(__("Error : Missing content for bookmark %s\n",HERISSON_TD),$bookmark->id);
	}
	exit;
}

function herisson_bookmark_delete() {
 $id = intval(param('id'));
	if ($id>0) {
  $bookmark = herisson_bookmark_get($id);
 	$bookmark->delete();
	}
	herisson_bookmark_list();
}


function herisson_bookmark_download() {
 $id = intval(param('id'));
	if ($id>0) {
  $bookmark = herisson_bookmark_get($id);
  $bookmark->maintenance();
 	$bookmark->captureFromUrl();
 	herisson_bookmark_edit($bookmark->id);
	}
}

function herisson_bookmark_tagcloud() {

# select count(*) as c ,name from wp_herisson_tags group by name order by name;
 $tags = Doctrine_Query::create()
	 ->select('count(*) as c, name')
		->from('WpHerissonTags')
		->groupby('name')
		->orderby('name')
		->execute();
	$string="";
	foreach ($tags as $tag) {
	 $string.='<a href="#" class="tag-link-'.$tag->id.'" title="3 sujets" style="font-size: '.( 10+$tag->c*2).'pt">'.$tag->name.'</a>&nbsp;';
	}
	echo $string;
	exit;
}

