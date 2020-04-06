<?php

require_once __DIR__.'/../lib/Facebook/autoload.php';


function lnk_multisite_manager_post_facebook($post) {

  $facebook_app_id = get_option('curza_subsitio_conf_facebook_app_id','xxx');
  $facebook_app_secret = get_option('curza_subsitio_conf_facebook_app_secret','xxx');
  $facebook_token = get_option('curza_subsitio_conf_facebook_token','xxx');
  $facebook_page_id = get_option('curza_subsitio_conf_facebook_page_id','xxx');

  $fb = new \Facebook\Facebook([
    'app_id'  => $facebook_app_id,
    'app_secret' => $facebook_app_secret,
    'default_graph_version' => 'v2.10'
  ]);

  $data = [
    'message' => $post->post_title,
    'link' => $post->lnk_url
  ];

  try {
    $response = $fb->post('/'.$facebook_page_id.'/feed', $data, $facebook_token);
  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    return false;
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    return false;
  }
  return true;
}