<?php
/*
=====================================================
 MWS Custom Comments v1.3 - Mehmet Hanoğlu
-----------------------------------------------------
 http://dle.net.tr/ -  Copyright (c) 2015
-----------------------------------------------------
 Mail: mehmethanoglu@dle.net.tr
-----------------------------------------------------
 Lisans : MIT License
=====================================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$comm_conf = array(
	'sel_user_info' => "1",		// Get user info
	'sel_news_info' => "1",		// Get news info
	'sel_extra_info' => "1",	// Get extra news info
	'prev_text_len' => 100,		// Preview text length
);

if ( $comm_conf['sel_news_info'] ) {

	function comm_fulllink( $id, $category, $alt_name, $date, $comm_id = 0 ) {
		global $config;
		$comm_link = ( $comm_id != 0 ) ? "#comment-id-" . $comm_id : "";
		if ( $config['allow_alt_url'] ) {
			if ( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				if ( $category and $config['seo_type'] == 2 ) {
					$full_link = $config['http_home_url'] . get_url( $category ) . "/" . $id . "-" . $alt_name . ".html";
				} else {
					$full_link = $config['http_home_url'] . $id . "-" . $alt_name . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $date ) . $alt_name . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $id;
		}
		return $full_link . $comm_link;
	}

	function comm_title( $count, $title ) {
		global $config;
		if ( $count AND dle_strlen( $title, $config['charset'] ) > $count ) {
			$title = dle_substr( $title, 0, $count, $config['charset'] );
			if ( ( $temp_dmax = dle_strrpos( $title, ' ', $config['charset'] ) ) ) $title = dle_substr( $title, 0, $temp_dmax, $config['charset'] );
		}
		return strip_tags( stripslashes( $title ) );
	}
}


function custom_comments_module( $matches = array() ) {
	global $db, $_TIME, $config, $lang, $user_group, $comm_conf, $member_id;

	if ( ! count( $matches ) ) return "";
	$yes_no_map = array( "yes" => "1", "no" => "0" );

	$param_str = trim( $matches[1] );
	$thisdate = date( "Y-m-d H:i:s", $_TIME );
	$where = array();

	if ( preg_match( "#template=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$comm_tpl = trim( $match[1] );
	} else return "Error: <b>template</b> parameter missing.";

	if ( preg_match( "#approve=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$where[] = "c.approve='" . $yes_no_map[ $match[1] ] . "'";
	}
	if ( preg_match( "#author=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$author = $db->safesql( trim( $match[1] ) );
		if ( $author == "_THIS_" && $_REQUEST['subaction'] == "userinfo" ) {
			$author = $db->safesql( $_REQUEST['user'] );
		} else if ( $author == "_CURRENT_" && isset( $member_id ) ) {
			$author = $db->safesql( $member_id['name'] );
		}
		$where[] = "c.autor='" . $author . "'";
	}
	if ( preg_match( "#users=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$where[] = "c.is_register='" . $yes_no_map[ $match[1] ] . "'";
	}
	if ( preg_match( "#days=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$days = intval( trim( $match[1] ) );
		$where[] = "c.date >= '{$thisdate}' - INTERVAL {$days} DAY AND c.date < '{$thisdate}'";
	} else $days = 0;

	if ( preg_match( "#id=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$temp_array = array();
		$where_id = array();
		$match[1] = explode( ',', trim( $match[1] ) );
		foreach ( $match[1] as $value ) {
			if ( count( explode( '-', $value ) ) == 2 ) {
				$value = explode( '-', $value );
				$where_id[] = "c.id >= '" . intval( $value[0] ) . "' AND c.id <= '" . intval( $value[1] ) . "'";
			} else $temp_array[] = intval($value);
		}
		if ( count( $temp_array ) ) {
			$where_id[] = "c.id IN ('" . implode( "','", $temp_array ) . "')";
		}
		if ( count( $where_id ) ) {
			$custom_id = implode( ' OR ', $where_id );
			$where[] = $custom_id;
		}
	}

	if ( preg_match( "#news=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$temp_array = array();
		$where_id = array();
		$match[1] = explode( ',', trim( $match[1] ) );
		foreach ( $match[1] as $value ) {
			if ( count( explode( '-', $value ) ) == 2 ) {
				$value = explode( '-', $value );
				$where_id[] = "c.post_id >= '" . intval( $value[0] ) . "' AND c.post_id <= '" . intval( $value[1] ) . "'";
			} else $temp_array[] = intval($value);
		}
		if ( count( $temp_array ) ) {
			$where_id[] = "c.post_id IN ('" . implode( "','", $temp_array ) . "')";
		}
		if ( count( $where_id ) ) {
			$custom_id = implode( ' OR ', $where_id );
			$where[] = $custom_id;
		}
	}

	if ( preg_match( "#from=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$comm_from = intval( $match[1] ); $custom_all = $custom_from;
	} else {
		$comm_from = 0; $custom_all = 0;
	}
	if ( preg_match( "#limit=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$comm_limit = intval( $match[1] );
	} else {
		$comm_limit = $config['comm_nummers'];
	}

	if ( preg_match( "#order=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$allowed_order = array ( 'postid' => 'post_id', 'date' => 'date', 'author' => 'autor', 'rand' => 'RAND()' );
		if ( $allowed_order[ $match[1] ] ) $comm_order = $allowed_order[ $match[1] ];
	} else { $comm_order = "date"; }
	if ( preg_match( "#sort=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$allowed_sort = array ( 'asc' => 'ASC', 'desc' => 'DESC' );
		if ( $allowed_sort[ $match[1] ] ) $comm_sort = $allowed_sort[ $match[1] ];
	} else { $comm_sort = "desc"; }

	if ( preg_match( "#cache=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$comm_cache = $yes_no_map[ $match[1] ];
	} else {
		$comm_cache = "0";
	}

	$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
	if ( $allow_list[0] != "all" AND !$user_group[$member_id['user_group']]['allow_short'] ) {
		if ( $config['allow_multi_category'] ) {
			$where[] = "p.category REGEXP '[[:<:]](" . implode( '|', $allow_list ) . ")[[:>:]]'";
		} else {
			$where[] = "p.category IN ('" . implode( "','", $allow_list ) . "')";
		}
	}
	if ( preg_match( "#category=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$temp_array = array();
		$match[1] = explode( ',', $match[1] );
		foreach ( $match[1] as $value ) {
			$_tmp = explode( '-', $value );
			if ( count( $_tmp ) == 2 ) $temp_array[] = get_mass_cats( $value );
			else $temp_array[] = intval( $value );
		}
		$temp_array = implode( ',', $temp_array );
		$custom_category = $db->safesql( trim( str_replace( ',', '|', $temp_array ) ) );
		if ( $config['allow_multi_category'] ) {
			$where[] = "p.category REGEXP '[[:<:]](" . $custom_category . ")[[:>:]]'";
		} else {
			$custom_category = str_replace( "|", "','", $custom_category );
			$where[] = "p.category IN ('" . $custom_category . "')";
		}
	}
	if ( preg_match( "#not-category=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$temp_array = array();
		$match[1] = explode( ',', $match[1] );
		foreach( $match[1] as $value ) {
			$_tmp = explode( '-', $value );
			if ( count( $_tmp ) == 2 ) $temp_array[] = get_mass_cats( $value );
			else $temp_array[] = intval( $value );
		}
		$temp_array = implode(',', $temp_array);
		$custom_category = $db->safesql( trim(str_replace( ',', '|', $temp_array )) );
		if ( $config['allow_multi_category'] ) {
			$where[] = "p.category NOT REGEXP '[[:<:]](" . $custom_category . ")[[:>:]]'";
		} else {
			$custom_category = str_replace( "|", "','", $custom_category );
			$where[] = "p.category NOT IN ('" . $custom_category . "')";
		}
	}

	if ( preg_match( "#not=['\"](.+?)['\"]#i", $param_str, $match ) ) {
		$not_found = $db->safesql( $match[1] );
	} else {
		$not_found = "";
	}

	if ( $comm_conf['sel_user_info'] ) {
		$u_select = ", u.foto, u.user_group, u.comm_num, u.news_num, u.lastdate"; $u_from = " LEFT JOIN " . PREFIX . "_users u ON ( c.user_id = u.user_id )";
	} else {
		$u_select = ""; $u_from = "";
	}
	if ( $comm_conf['sel_news_info'] ) {
		$p_select = ", p.title, p.category, p.alt_name"; $p_from = " LEFT JOIN " . PREFIX . "_post p ON ( c.post_id = p.id )";
	} else {
		$p_select = ""; $p_from = "";
	}
	if ( $comm_conf['sel_extra_info'] ) {
		$e_select = ", e.news_read, e.rating, e.vote_num"; $e_from = " LEFT JOIN " . PREFIX . "_post_extras e ON ( c.post_id = e.news_id )";
	} else {
		$e_select = ""; $e_from = "";
	}

	$_WHERE = ( count( $where ) > 0 ) ? " WHERE " . implode( ' AND ', $where ) : "";

	$comm_yes = false;
	$comm_sql = "SELECT c.*{$u_select}{$p_select}{$e_select} FROM " . PREFIX . "_comments c{$u_from}{$p_from}{$e_from}{$_WHERE} ORDER BY {$comm_order} {$comm_sort} LIMIT {$comm_from},{$comm_limit}";
	$comm_que = $db->query( $comm_sql );

	if ( $comm_cache ) {
		$comm_cacheid = $param_str . $comm_sql;
		$cache_content = dle_cache( "comm_custom", $comm_cacheid, true );
	} else $cache_content = false;
	if ( ! $cache_content ) {

		$tpl = new dle_template();
		$tpl->dir = TEMPLATE_DIR;
		$tpl->load_template( $comm_tpl . '.tpl' );

		$config['http_home_url'] .= ( substr( $config['http_home_url'], -1 ) != "/" ) ? "/" : "";
		while( $comm_row = $db->get_row( $comm_que ) ) {
			$comm_yes = true;

			if ( $config['allow_links'] AND function_exists('replace_links') AND isset( $replace_links['comments'] ) ) $comm_row['text'] = replace_links( $comm_row['text'], $replace_links['comments'] );
			if ( $user_group[$member_id['user_group']]['allow_hide'] ) $comm_row['text'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $comm_row['text']) );
			else $comm_row['text'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#is", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $comm_row['text'] );
			$tpl->set( '{text}', stripslashes( $comm_row['text'] ) );

			if ( date( 'Ymd', $comm_row['date'] ) == date( 'Ymd', $_TIME ) ) {
				$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $comm_row['date'] ) );
			} else if ( date( 'Ymd', $comm_row['date'] ) == date( 'Ymd', ( $_TIME - 86400 ) ) ) {
				$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $comm_row['date'] ) );
			} else {
				$tpl->set( '{date}', $comm_row['date'] );
			}

			if ( $comm_conf['sel_user_info'] ) {
				if ( count( explode( "@", $comm_row['foto'] ) ) == 2 ) {
					$tpl->set( '{author-foto}', 'http://www.gravatar.com/avatar/' . md5( trim( $comm_row['foto'] ) ) . '?s=' . intval( $user_group[$comm_row['user_group']]['max_foto'] ) );
				} else {
					if ( $comm_row['foto'] && $config['version_id'] < "10.5" ) {
						if ( ( file_exists( ROOT_DIR . "/uploads/fotos/" . $comm_row['foto'] ) ) ) {
							$tpl->set( '{author-foto}', $config['http_home_url'] . "uploads/fotos/" . $comm_row['foto'] );
						} else {
							$tpl->set( '{author-foto}', "{THEME}/dleimages/noavatar.png" );
						}
					} else if ( $comm_row['foto'] && $config['version_id'] >= "10.5" ) {
						$tpl->set( '{author-foto}', $comm_row['foto'] );
					}
					else $tpl->set( '{author-foto}', "{THEME}/dleimages/noavatar.png" );
				}
				$tpl->set( "{author-colored}", $user_group[ $comm_row['user_group'] ]['group_prefix'] . $comm_row['autor'] . $user_group[ $comm_row['user_group'] ]['group_suffix'] );
				$tpl->set( "{author-group}", $user_group[ $comm_row['user_group'] ]['group_prefix'] . $user_group[ $comm_row['user_group'] ]['group_name'] . $user_group[ $comm_row['user_group'] ]['group_suffix'] );
				$tpl->set( "{author-group-icon}", $user_group['icon'] );
				$tpl->set( "{author-news}", intval( $comm_row['news_num'] ) );
				$tpl->set( "{author-comm}", intval( $comm_row['comm_num'] ) );
			} else {
				$tpl->set( "", array( "{author-comm}" => "", "{author-news}" => "", "{author-group-icon}" => "", "{author-group}" => "", "{author-foto}" => "" ) );
			}
			if ( $comm_conf['sel_news_info'] ) {
				if ( preg_match( "#\\{news-title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) { $count = intval( $matches[1] ); $tpl->set( $matches[0], comm_title( $count, $comm_row['title'] ) ); }
				else $tpl->set( '{news-title}', strip_tags( stripslashes( $comm_row['title'] ) ) );
				$tpl->set( '{news-link}', comm_fulllink( $comm_row['post_id'], $comm_row['category'], $comm_row['alt_name'], $comm_row['pdate'] ) );
				$tpl->set( '{comm-link}', comm_fulllink( $comm_row['post_id'], $comm_row['category'], $comm_row['alt_name'], $comm_row['pdate'], $comm_row['id'] ) );
				$tpl->set( '{news-cat}', get_categories( $comm_row['category'] ) );
			}
			if ( $comm_conf['sel_extra_info'] ) {
				$tpl->set( '{news-read}', $comm_row['news_read'] );
				if ( $comm_row['vote_num'] != "0" ) {
					$tpl->set( '{news-rating}', round( ( floatval( $comm_row['rating'] ) / floatval( $comm_row['vote_num'] ) ), 1 ) );
				} else {
					$tpl->set( '{news-rating}', "0" );
				}
			}
			$tpl->set( "{text-preview}", dle_substr( strip_tags( stripslashes( $comm_row['text'] ) ), 0, $comm_conf['prev_text_len'], $config['charset'] ) );
			$tpl->set( "{author-id}", $comm_row['user_id'] );
			$tpl->set( "{author}", $comm_row['autor'] );
			$tpl->set( "{approve}", $comm_row['approve'] );
			if ( $comm_row['is_register'] ) {
				$tpl->set( "[registered]", "" );
				$tpl->set( "[/registered]", "" );
				$tpl->set( "{author-url}", ( $config['allow_alt_url'] ) ? $config['http_home_url'] . "user/" . urlencode( $comm_row['autor'] ) : $config['http_home_url'] . "index.php?subaction=userinfo&amp;user=" . urlencode( $comm_row['autor'] ) );
			} else {
				$tpl->set_block( "'\\[registered\\](.*?)\\[/registered\\]'si", "" );
				$tpl->set( "{author-url}", "#" );
			}
			$tpl->set( "{is_register}", $comm_row['is_register'] );
			if ( $comm_row['lastdate'] + 1200 > $_TIME ) {
				$tpl->set( "{status}", "online" );
			} else {
				$tpl->set( "{status}", "offline" );
			}
			$tpl->set( "{email}", $comm_row['email'] );
			$tpl->set( "{news-id}", $comm_row['post_id'] );
			$tpl->set( "{ip}", $comm_row['ip'] );
			$tpl->set( "{id}", $comm_row['id'] );

	    	$tpl->compile( "content" );

			$tpl->result['content'] = preg_replace_callback( "#\{date=(.+?)\}#i", function( $match ) use ( $comm_row ) { return langdate( $match[1], strtotime( $comm_row['date'] ), $servertime = false, $custom = false ); }, $tpl->result['content'] );
		}

		if ( ! $comm_yes ) {
			$tpl->result['content'] = $not_found;
		}

		$tpl->result['content'] = str_replace( "{THEME}", $config['http_home_url'] . "templates/" . $config['skin'], $tpl->result['content'] );

		if ( $comm_cache ) {
			create_cache( "comm_custom", $tpl->result['content'], $comm_cacheid, true );
		}
		return $tpl->result['content'];
	} else return $cache_content;

}

?>
