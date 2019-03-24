<?php
/**
* nanogp2 add-on for nanogallery2 for displaying Google Photos images/albums
* http://nanogallery2.nanostudio.org
*
* PHP 5.2+
* @version    2.0.4
* @author     Christophe Brisbois - http://www.brisbois.fr/
* @copyright  Copyright 2019
* @license    GPLv3
* @link       https://github.com/nanostudio-org/nanogp2
* @Support    https://github.com/nanostudio-org/nanogp2/issues
*
*
* https://developers.google.com/photos/library/guides/overview
*/

  $callback='';
  include('admin/config.php');
  include('admin/tools.php');

  set_globals();
  
  $request=$_GET;
  // echo implode($request);

	$albums = array();	
	$medias = array();
  $report = '';
	
  $user_id = $request['nguserid'];
  unset($request['nguserid']);
  
  $album_id = '';
  if( isset($_GET['ngalbumid']) ) {
    $album_id = $request['ngalbumid'];
    unset($request['ngalbumid']);
  }
  if( $callback != '' ) {
    unset($request['callback']);
  }
  if( isset($_GET['_']) ) {
    unset($request['_']);
  }

  $content_kind = $request['kind'];

  // option for generating a report of the user's Google Photo content
  // to avoid publishing confidential data, the report is only viewable with restricted access (in the user folder)	
  $generate_report = false;
  $report = '';
  if( isset($_GET['report']) ) {
    $generate_report = true;
    $content_kind = 'album';
    file_put_contents( 'admin/users/' . $user_id . '/google_photos_data.txt', 'Data for user ' . $user_id . "\r\n");
    file_put_contents( 'admin/users/' . $user_id . '/google_photos_data.txt', "\r\n", FILE_APPEND);

    // $report = "Data for user " . $user_id . "\r\n\r\n";
    unset($request['report']);
		$content_kind = 'album';
  }

  
  if( !function_exists('curl_version') ) {
    response_json( array('nano_status' => 'error', 'nano_message' => 'Please install/enable CURL on your web server.' ) );
    exit;
  }

  $atoken = file_get_contents('admin/users/' . $user_id . '/token_a.txt');
  if( $atoken === false || $atoken == '' ) {
    response_json( array('nano_status' => 'error', 'nano_message' => 'Missing access token. Please grant authorization.' ) );
    exit;
  }
  
  
  // ##### retrieve the list of albums
  if( $content_kind == 'album' ) {
		$url = 'https://photoslibrary.googleapis.com/v1/albums';

		$r = '';
		do {
			// loop until no next page token
			$r = send_gprequest( $url, 'album', $r );
			if( $r === 'token_expired') {
				// error -> get a new access token
				get_new_access_token();
				// send request again, with the new access token
				$r=send_gprequest( $url, 'album', '' );
			}
		} while( $r != '' );

    if( !$generate_report ) {
      // send data back to browser
      response_json( array_merge(array('nano_status' => 'ok', 'nano_message' => ''), $albums) );
    }
    else {
      // write to report file in the user folder
      // file_put_contents( 'admin/users/' . $user_id . '/google_photos_data.txt', $report);
			file_put_contents( 'admin/users/' . $user_id . '/google_photos_data.txt', "\r\n### end of report ###\r\n", FILE_APPEND);
      response_json( array('nano_status' => 'ok', 'nano_message' => 'The report for user ' . $user_id . ' has been generated on the server.' ) );
    }
	}
  
  // ##### retrieve the content of one album -> list of medias
  if( $content_kind == 'photo' ) {
		$url = 'https://photoslibrary.googleapis.com/v1/mediaItems:search';
		
		$r = '';
		do {
			// loop until no next page token
			$r = send_gprequest( $url, 'photo', $r );
			if( $r === 'token_expired') {
				// error -> get a new access token
				get_new_access_token();
				// send request again, with the new access token
				$r=send_gprequest( $url, 'photo', '' );
			}
		} while( $r != '' );

		response_json( array_merge(array('nano_status' => 'ok', 'nano_message' => ''), $medias) );
  }
  
  
  // ##### send the request to GOOGLE PHOTOS
  function send_gprequest( $url, $content_kind, $nextPageToken ) {
    global $callback, $atoken, $request, $albums, $album_id, $medias, $generate_report, $report, $user_id;	

		$request = array();
    $request['access_token'] = $atoken;
		if( $content_kind == 'album' ) {
			$request['pageSize'] = 50;
			if( $nextPageToken != '' ) {
				$request['pageToken'] = $nextPageToken;
			}
		}

    $ch = curl_init();
    $url = $url . '?' . http_build_query($request, '', '&');
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = 'Content-Type: application/json';
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array("GData-Version: 3"));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
		if( $content_kind == 'photo' ) {
			// send json parameters
			$data = array('albumId' => $album_id, 'pageSize' => '100', 'pageToken' =>  $nextPageToken );  
			$data_string = json_encode($data); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
		}
    $response = curl_exec($ch);
    $msg = curl_errno($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
		
    if( $response == 'Token revoked' ) {
      response_json( array('nano_status' => 'error', 'nano_message' => 'Token revoked - ' . $url ) );
      exit;
    }

    if( $response == 'No album found.' ) {
      response_json( array('nano_status' => 'error', 'nano_message' => 'No album found - ' . $url ) );
      exit;
    }
 
    if( $info['http_code'] === 403 or $info['http_code'] === 401 ) {
      return 'token_expired';
    }

    
    if( $info['http_code'] === 200 ) {
			$received = json_decode($response, true);

			if( $content_kind == 'photo' ) {
        // ALBUM CONTENT
				foreach ($received['mediaItems'] as $k => $v) {
					// append single media to array
					$medias[] = $v;
				}
			}
			else {
        // LIST OF ALBUMS
				foreach ($received['albums'] as $k => $v) {
					// filter albums (see configuration)
          
					global $albums_filter;
          $value = $v['title'];
					$filter = false;
          foreach( $albums_filter as $one_filter ) {
            if (stripos($value, $one_filter) !== false) {
              $filter = true;
            }
          }
          if( $generate_report ) {
            // append data to report
            $f = '';
            if( $filter ) {
              $f = ' [match filter] ';
            }
            $sh = '';
            if( property_exists((object) $v, 'shareInfo') ) {
              $sh = ' - shared: ' . $v[shareInfo] . ' ';
            }
            // $report .= $v['title'] . " - " . $v[id] . " - number of medias: [" . $v[mediaItemsCount] . ']' . $sh . $f . "\r\n";
            $re = $v['title'] . " - " . $v[id] . " - number of medias: " . $v[mediaItemsCount] . ' ' . $sh . $f . "\r\n";
						file_put_contents( 'admin/users/' . $user_id . '/google_photos_data.txt', $re, FILE_APPEND);
          }
          else {
            // append single album to array
            if( !$filter ) {
              if( property_exists((object) $v, 'mediaItemsCount') && property_exists((object) $v, 'coverPhotoBaseUrl')) {
                $albums[] = $v;
              }
              // $albums[] = clone $v;
            }
					}
				}
			}

			return $received['nextPageToken'];

    }
    else {
      response_json( array('nano_status' => 'error', 'nano_message' => 'curl error' . $info['http_code'] . ' - ' . $msg . ' - ' . $url ) );
      exit;
    }
  
  }
  

?>
