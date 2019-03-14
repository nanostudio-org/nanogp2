<?php
/**
* nanogp2 - installation info
*
*/

  echo 'nanogp2 - installation check' . PHP_EOL . '<br/>';
  echo '----------------------------' . PHP_EOL . '<br/>';
  echo '' . PHP_EOL . '<br/>';
  echo 'PHP version: ' . phpversion() . PHP_EOL . '<br/>';
  echo 'Free disk space: ' . disk_free_space('.') . PHP_EOL . '<br/>';

  const API_BASE_PATH =       'https://www.googleapis.com';
  const OAUTH2_TOKEN_URI =    'https://www.googleapis.com/oauth2/v4/token';
  const OAUTH2_AUTH_URL =     'https://accounts.google.com/o/oauth2/auth';
  const OAUTH2_REVOKE_URI =   'https://accounts.google.com/o/oauth2/revoke';
  // Google OAUTH 2.0 API: https://developers.google.com/identity/protocols/OpenIDConnect
  $user_id = '';
  $atoken = '';
  $rtoken = '';
  $callback = '';
  
  include('admin/config.php');
  include('admin/tools.php');

  // check CURL availability
  echo '- CURL: ' . PHP_EOL . '<br/>';
  if( !function_exists('curl_version') ) {
    echo 'Error: not installed/enabled --> please install/enable CURL.' . PHP_EOL . '<br/>';
  }
  else {
    echo 'Ok.' . PHP_EOL . '<br/>';
  }
  
  // check write permissions
  echo '- Write permissions: ' . PHP_EOL . '<br/>';
  if( !is_writable('admin/users') ) {
    echo 'Error: no write permissions on folder admin/users.' . PHP_EOL . '<br/>';
  }
  else {
    echo 'Ok.' . PHP_EOL . '<br/>';
  }
  
  $prot='http://';
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
    $prot='https://';
  }
  set_globals();

  echo '- URL current page: ' . $prot . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . PHP_EOL . '<br/>';


  $params = array(
    "response_type" =>  "code",
    "client_id" =>      $cfg_client_id,
    "redirect_uri" =>   $prot . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"],
    // "redirect_uri" =>   "https://nano.gallery/nanogp2/authorize.php",
    "access_type" =>    "offline",
    "scope" =>          "https://www.googleapis.com/auth/photoslibrary.readonly profile email"
  );

  $request_to = OAUTH2_AUTH_URL . '?' . http_build_query($params, '', '&');
  echo '- URL request: ' . $request_to . PHP_EOL . '<br/>';
  
  
  
  // display nanogallery2 settings
  if( $cfg_max_accounts == 1 ) {
    foreach( glob( 'admin/users/*', GLOB_ONLYDIR ) as $folder) 
    {
      $atoken=file_get_contents( $folder . '/token_a.txt');
      $rtoken=file_get_contents( $folder . '/token_r.txt');
      $user_id = basename($folder);
      if( $atoken !== false && $atoken != '' && $rtoken !== false && $rtoken != '' && $user_id != '' ) {
        display_settings();
        exit;
      }
    }

  }
  
  
  
  // ##########
  // Display connection info for nanogallery2 
  function display_settings() {
    global $user_id, $prot;
    
    echo '- Settings for nanogallery2:'. PHP_EOL . '<br/>';
    echo "  kind : 'google2'," . PHP_EOL . '<br/>';
    echo "  userID : '" . $user_id . "'," . PHP_EOL . '<br/>';
    
    
    
    $u= $prot . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
    $ul = explode('/', $u);
    array_pop($ul);
    // array_pop($ul);
    $u= implode('/', $ul) . '/nanogp.php';    
    echo "  google2URL : '" . $u . "'" . PHP_EOL . "<br/>";
  }
  

?>
