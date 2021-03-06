<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 12/9/2010, 22:27
 */

if( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );

$page_title = $lang_module['file_addfile'];

$groups_list = nv_groups_list();
$array_who = array( $lang_global['who_view0'], $lang_global['who_view1'], $lang_global['who_view2'] );
if( ! empty( $groups_list ) )
{
	$array_who[] = $lang_global['who_view3'];
}

$array = array();
$is_error = false;
$error = '';

if( $nv_Request->isset_request( 'submit', 'post' ) )
{
	$array['catid'] = $nv_Request->get_int( 'catid', 'post', 0 );
	$array['title'] = $nv_Request->get_title( 'title', 'post', '', 1 );
	$array['description'] = $nv_Request->get_editor( 'description', '', NV_ALLOWED_HTML_TAGS );
	$array['introtext'] = $nv_Request->get_textarea( 'introtext', '', NV_ALLOWED_HTML_TAGS );
	$array['author_name'] = $nv_Request->get_title( 'author_name', 'post', '', 1 );
	$array['author_email'] = $nv_Request->get_title( 'author_email', 'post', '' );
	$array['author_url'] = $nv_Request->get_title( 'author_url', 'post', '' );
	$array['fileupload'] = $nv_Request->get_typed_array( 'fileupload', 'post', 'string' );
	$array['linkdirect'] = $nv_Request->get_typed_array( 'linkdirect', 'post', 'string' );
	$array['version'] = $nv_Request->get_title( 'version', 'post', '', 1 );
	$array['fileimage'] = $nv_Request->get_title( 'fileimage', 'post', '' );
	$array['copyright'] = $nv_Request->get_title( 'copyright', 'post', '', 1 );
	$array['comment_allow'] = $nv_Request->get_int( 'comment_allow', 'post', 0 );
	$array['who_comment'] = $nv_Request->get_int( 'who_comment', 'post', 0 );
	$array['groups_comment'] = $nv_Request->get_typed_array( 'groups_comment', 'post', 'int' );
	$array['is_del_report'] = $nv_Request->get_int( 'is_del_report', 'post', 0 );

	$array['who_view'] = $nv_Request->get_int( 'who_view', 'post', 0 );
	$array['groups_view'] = $nv_Request->get_typed_array( 'groups_view', 'post', 'int' );
	$array['who_download'] = $nv_Request->get_int( 'who_download', 'post', 0 );
	$array['groups_download'] = $nv_Request->get_typed_array( 'groups_download', 'post', 'int' );

	if( ! empty( $array['author_url'] ) )
	{
		if( ! preg_match( '#^(http|https|ftp|gopher)\:\/\/#', $array['author_url'] ) )
		{
			$array['author_url'] = 'http://' . $array['author_url'];
		}
	}

	$array['filesize'] = 0;
	if( ! empty( $array['fileupload'] ) )
	{
		$fileupload = $array['fileupload'];
		$array['fileupload'] = array();
		$array['filesize'] = 0;
		foreach( $fileupload as $file )
		{
			if( ! empty( $file ) )
			{
				$file2 = substr( $file, strlen( NV_BASE_SITEURL ) );
				if( file_exists( NV_ROOTDIR . '/' . $file2 ) and ( $filesize = filesize( NV_ROOTDIR . '/' . $file2 ) ) != 0 )
				{
					$array['fileupload'][] = substr( $file, strlen( NV_BASE_SITEURL . NV_UPLOADS_DIR ) );
					$array['filesize'] += $filesize;
				}
			}
		}
	}
	else
	{
		$array['fileupload'] = array();
	}

	// Sort image
	if( ! empty( $array['fileimage'] ) )
	{
		if( ! preg_match( '#^(http|https|ftp|gopher)\:\/\/#', $array['fileimage'] ) )
		{
			$array['fileimage'] = substr( $array['fileimage'], strlen( NV_BASE_SITEURL . NV_UPLOADS_DIR ) );
		}
	}

	if( ! empty( $array['linkdirect'] ) )
	{
		$linkdirect = $array['linkdirect'];
		$array['linkdirect'] = array();
		foreach( $linkdirect as $links )
		{
			$linkdirect2 = array();
			if( ! empty( $links ) )
			{
				$links = nv_nl2br( $links, '<br />' );
				$links = explode( '<br />', $links );
				$links = array_map( 'trim', $links );
				$links = array_unique( $links );

				foreach( $links as $link )
				{
					if( ! preg_match( '#^(http|https|ftp|gopher)\:\/\/#', $link ) )
					{
						$link = 'http://' . $link;
					}
					if( nv_is_url( $link ) )
					{
						$linkdirect2[] = $link;
					}
				}
			}

			if( ! empty( $linkdirect2 ) )
			{
				$array['linkdirect'][] = implode( "\n", $linkdirect2 );
			}
		}
	}
	else
	{
		$array['linkdirect'] = array();
	}
	if( ! empty( $array['linkdirect'] ) )
	{
		$array['linkdirect'] = array_unique( $array['linkdirect'] );
	}

	if( ! empty( $array['linkdirect'] ) and empty( $array['fileupload'] ) )
	{
		$array['filesize'] = $nv_Request->get_int( 'filesize', 'post', 0 );
	}

	$alias = change_alias( $array['title'] );

	$stmt = $db->prepare( 'SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE alias= :alias' );
	$stmt->bindParam( ':alias', $alias, PDO::PARAM_STR );
	$stmt->execute();
	$is_exists = $stmt->fetchColumn();

	if( ! $is_exists )
	{
		$stmt = $db->prepare( 'SELECT COUNT(*) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_tmp WHERE title= :title');
		$stmt->bindParam( ':title', $array['title'], PDO::PARAM_STR );
		$stmt->execute();
		$is_exists = $stmt->fetchColumn();
	}

	if( empty( $array['title'] ) )
	{
		$is_error = true;
		$error = $lang_module['file_error_title'];
	}
	elseif( $is_exists )
	{
		$is_error = true;
		$error = $lang_module['file_title_exists'];
	}
	elseif( ! empty( $array['author_email'] ) and ( $check_valid_email = nv_check_valid_email( $array['author_email'] ) ) != '' )
	{
		$is_error = true;
		$error = $check_valid_email;
	}
	elseif( ! empty( $array['author_url'] ) and ! nv_is_url( $array['author_url'] ) )
	{
		$is_error = true;
		$error = $lang_module['file_error_author_url'];
	}
	elseif( empty( $array['fileupload'] ) and empty( $array['linkdirect'] ) )
	{
		$is_error = true;
		$error = $lang_module['file_error_fileupload'];
	}
	else
	{
		$array['introtext'] = ! empty( $array['introtext'] ) ? nv_nl2br( $array['introtext'], '<br />' ) : '';
		$array['description'] = ! empty( $array['description'] ) ? nv_editor_nl2br( $array['description'] ) : $array['introtext'];
		$array['fileupload'] = ( ! empty( $array['fileupload'] ) ) ? implode( '[NV]', $array['fileupload'] ) : '';
		if( ( ! empty( $array['linkdirect'] ) ) )
		{
			$array['linkdirect'] = array_map( 'nv_nl2br', $array['linkdirect'] );
			$array['linkdirect'] = implode( '[NV]', $array['linkdirect'] );
		}
		else
		{
			$array['linkdirect'] = '';
		}

		if( ! in_array( $array['who_comment'], array_keys( $array_who ) ) )
		{
			$array['who_comment'] = 0;
		}
		if( ! in_array( $array['who_view'], array_keys( $array_who ) ) )
		{
			$array['who_view'] = 0;
		}
		if( ! in_array( $array['who_download'], array_keys( $array_who ) ) )
		{
			$array['who_download'] = 0;
		}

		$array['groups_comment'] = ( ! empty( $array['groups_comment'] ) ) ? implode( ',', $array['groups_comment'] ) : '';
		$array['groups_view'] = ( ! empty( $array['groups_view'] ) ) ? implode( ',', $array['groups_view'] ) : '';
		$array['groups_download'] = ( ! empty( $array['groups_download'] ) ) ? implode( ',', $array['groups_download'] ) : '';

		$sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $module_data . " (catid, title, alias, description, introtext, uploadtime, updatetime, user_id, user_name, author_name, author_email, author_url, fileupload, linkdirect, version, filesize, fileimage, status, copyright, view_hits, download_hits, comment_allow, who_comment, groups_comment, who_view, groups_view, who_download, groups_download, comment_hits, rating_detail) VALUES (
			 " . $array['catid'] . ",
			 :title,
			 :alias ,
			 :description ,
			 :introtext ,
			 " . NV_CURRENTTIME . ",
			 " . NV_CURRENTTIME . ",
			 " . $admin_info['admin_id'] . ",
			 :username,
			 :author_name ,
			 :author_email ,
			 :author_url ,
			 :fileupload ,
			 :linkdirect ,
			 :version ,
			 " . $array['filesize'] . ",
			 :fileimage ,
			 1,
			 :copyright ,
			 0, 0,
			 " . $array['comment_allow'] . ",
			 " . $array['who_comment'] . ",
			 :groups_comment ,
			 " . $array['who_view'] . ",
			 :groups_view ,
			 " . $array['who_download'] . ",
			 :groups_download ,
			 0, '')";
		
		$data_insert = array();
		$data_insert['title'] = $array['title'];
		$data_insert['alias'] = $alias;
		$data_insert['description'] = $array['description'];
		$data_insert['introtext'] = $array['introtext'];
		$data_insert['username'] = $admin_info['username'];
		$data_insert['author_name'] = $array['author_name'];
		$data_insert['author_email'] = $array['author_email'];
		$data_insert['author_url'] = $array['author_url'];
		$data_insert['fileupload'] = $array['fileupload'];
		$data_insert['linkdirect'] = $array['linkdirect'];
		$data_insert['version'] = $array['version'];
		$data_insert['fileimage'] = $array['fileimage'];
		$data_insert['copyright'] = $array['copyright'];
		$data_insert['groups_comment'] = $array['groups_comment'];
		$data_insert['groups_view'] = $array['groups_view'];
		$data_insert['groups_download'] = $array['groups_download'];
		
		if( ! $db->insert_id( $sql, 'id', $data_insert ) )
		{
			$is_error = true;
			$error = $lang_module['file_error2'];
		}
		else
		{
			nv_insert_logs( NV_LANG_DATA, $module_name, $lang_module['file_addfile'], $array['title'], $admin_info['userid'] );
			Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name );
			exit();
		}
		$array['fileupload'] = ( ! empty( $array['fileupload'] ) ) ? explode( '[NV]', $array['fileupload'] ) : array();
	}
}
else
{
	$array['catid'] = 0;
	$array['title'] = $array['description'] = $array['introtext'] = $array['author_name'] = $array['author_email'] = $array['author_url'] = $array['version'] = $array['fileimage'] = '';
	$array['fileupload'] = $array['linkdirect'] = $array['groups_comment'] = $array['groups_view'] = $array['groups_download'] = array();
	$array['filesize'] = $array['who_comment'] = $array['who_view'] = $array['who_download'] = 0;
	$array['comment_allow'] = 1;
	$array['is_del_report'] = 1;

	switch ( $module_config[$module_name]['setcomm'] )
	{
		case 0:
			$array['comment_allow'] = 0;
			break;

		case 2:
			$array['who_comment'] = 1;// Thanh vien  
			break;
	}
}

if( ! empty( $array['description'] ) ) $array['description'] = nv_htmlspecialchars( $array['description'] );
if( ! empty( $array['introtext'] ) ) $array['introtext'] = nv_htmlspecialchars( $array['introtext'] );

$array['fileupload_num'] = sizeof( $array['fileupload'] );
$array['linkdirect_num'] = sizeof( $array['linkdirect'] );

// Build fileimage
if( ! empty( $array['fileimage'] ) )
{
	if( ! preg_match( '#^(http|https|ftp|gopher)\:\/\/#', $array['fileimage'] ) )
	{
		$array['fileimage'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . $array['fileimage'];
	}
}

//Rebuild fileupload
if( ! empty( $array['fileupload'] ) )
{
	$fileupload = $array['fileupload'];
	$array['fileupload'] = array();
	foreach( $fileupload as $tmp )
	{
		if( ! preg_match( '#^(http|https|ftp|gopher)\:\/\/#', $tmp ) )
		{
			$tmp = NV_BASE_SITEURL . NV_UPLOADS_DIR . $tmp;
		}
		$array['fileupload'][] = $tmp;
	}
}

if( ! sizeof( $array['linkdirect'] ) ) array_push( $array['linkdirect'], '' );
if( ! sizeof( $array['fileupload'] ) ) array_push( $array['fileupload'], '' );

$listcats = nv_listcats( $array['catid'] );
if( empty( $listcats ) )
{
	Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=cat&add=1' );
	exit();
}

$array['comment_allow'] = $array['comment_allow'] ? ' checked="checked"' : '';
$array['is_del_report'] = $array['is_del_report'] ? ' checked="checked"' : '';

$who_comment = $array['who_comment'];
$array['who_comment'] = array();
foreach( $array_who as $key => $who )
{
	$array['who_comment'][] = array(
		'key' => $key,
		'title' => $who,
		'selected' => $key == $who_comment ? ' selected="selected"' : ''
	);
}

$groups_comment = $array['groups_comment'];
$array['groups_comment'] = array();
if( ! empty( $groups_list ) )
{
	foreach( $groups_list as $key => $title )
	{
		$array['groups_comment'][] = array(
			'key' => $key,
			'title' => $title,
			'checked' => in_array( $key, $groups_comment ) ? ' checked="checked"' : ''
		);
	}
}

$who_view = $array['who_view'];
$array['who_view'] = array();
foreach( $array_who as $key => $who )
{
	$array['who_view'][] = array(
		'key' => $key,
		'title' => $who,
		'selected' => $key == $who_view ? ' selected="selected"' : ''
	);
}

$groups_view = $array['groups_view'];
$array['groups_view'] = array();
if( ! empty( $groups_list ) )
{
	foreach( $groups_list as $key => $title )
	{
		$array['groups_view'][] = array(
			'key' => $key,
			'title' => $title,
			'checked' => in_array( $key, $groups_view ) ? ' checked="checked"' : ''
		);
	}
}

$who_download = $array['who_download'];
$array['who_download'] = array();
foreach( $array_who as $key => $who )
{
	$array['who_download'][] = array(
		'key' => $key,
		'title' => $who,
		'selected' => $key == $who_download ? ' selected="selected"' : ''
	);
}

$groups_download = $array['groups_download'];
$array['groups_download'] = array();
if( ! empty( $groups_list ) )
{
	foreach( $groups_list as $key => $title )
	{
		$array['groups_download'][] = array(
			'key' => $key,
			'title' => $title,
			'checked' => in_array( $key, $groups_download ) ? ' checked="checked"' : ''
		);
	}
}
if( defined( 'NV_EDITOR' ) )
{
	require_once NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php';
}

if( defined( 'NV_EDITOR' ) and nv_function_exists( 'nv_aleditor' ) )
{
	$array['description'] = nv_aleditor( 'description', '100%', '300px', $array['description'] );
}
else
{
	$array['description'] = '<textarea style="width:100%; height:300px" name="description" id="description">' . $array['description'] . '</textarea>';
}

$sql = "SELECT config_value FROM " . NV_PREFIXLANG . "_" . $module_data . "_config WHERE config_name='upload_dir'";
$result = $db->query( $sql );
$upload_dir = $result->fetchColumn();

if( ! $array['filesize'] ) $array['filesize'] = '';

$xtpl = new XTemplate( 'content.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file );

$xtpl->assign( 'FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=add' );

$xtpl->assign( 'LANG', $lang_module );
$xtpl->assign( 'DATA', $array );
$xtpl->assign( 'NV_BASE_ADMINURL', NV_BASE_ADMINURL );
$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );
$xtpl->assign( 'IMG_DIR', NV_UPLOADS_DIR . '/' . $module_name . '/images' );
$xtpl->assign( 'FILES_DIR', NV_UPLOADS_DIR . '/' . $module_name . '/' . $upload_dir );

if( ! empty( $error ) )
{
	$xtpl->assign( 'ERROR', $error );
	$xtpl->parse( 'main.error' );
}

foreach( $listcats as $cat )
{
	$xtpl->assign( 'LISTCATS', $cat );
	$xtpl->parse( 'main.catid' );
}

$a = 0;
foreach( $array['fileupload'] as $file )
{
	$xtpl->assign( 'FILEUPLOAD', array( 'value' => $file, 'key' => $a ) );
	$xtpl->parse( 'main.fileupload' );
	++$a;
}

$a = 0;
foreach( $array['linkdirect'] as $link )
{
	$xtpl->assign( 'LINKDIRECT', array( 'value' => $link, 'key' => $a ) );
	$xtpl->parse( 'main.linkdirect' );
	++$a;
}

foreach( $array['who_comment'] as $who )
{
	$xtpl->assign( 'WHO_COMMENT', $who );
	$xtpl->parse( 'main.who_comment' );
}

if( ! empty( $array['groups_comment'] ) )
{
	foreach( $array['groups_comment'] as $group )
	{
		$xtpl->assign( 'GROUPS_COMMENT', $group );
		$xtpl->parse( 'main.group_empty.groups_comment' );
	}
	$xtpl->parse( 'main.group_empty' );
}

foreach( $array['who_view'] as $who )
{
	$xtpl->assign( 'WHO_VIEW', $who );
	$xtpl->parse( 'main.who_view' );
}

if( ! empty( $array['groups_view'] ) )
{
	foreach( $array['groups_view'] as $group )
	{
		$xtpl->assign( 'GROUPS_VIEW', $group );
		$xtpl->parse( 'main.group_empty_view.groups_view' );
	}
	$xtpl->parse( 'main.group_empty_view' );
}

foreach( $array['who_download'] as $who )
{
	$xtpl->assign( 'WHO_DOWNLOAD', $who );
	$xtpl->parse( 'main.who_download' );
}

if( ! empty( $array['groups_download'] ) )
{
	foreach( $array['groups_download'] as $group )
	{
		$xtpl->assign( 'GROUPS_DOWNLOAD', $group );
		$xtpl->parse( 'main.group_empty_download.groups_download' );
	}
	$xtpl->parse( 'main.group_empty_download' );
}

$xtpl->parse( 'main' );
$contents = $xtpl->text( 'main' );

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';
exit();

?>