<?php

/**
 * Lista de posts de todos los sitios
 */
function lnk_multisite_manager_get_posts($filtro){

  //var_dump($filtro);
  //die;

  $sites_args = array(
    'public' => 1
  );

  $posts_args = array();

  $posts_args['numberposts'] = '-1';
  
  $posts_args['meta_query'] = array(
    'relation' => 'OR'
  );

  if($filtro['revisados'] == 'false') {
    $posts_args['meta_query'] = array(
      'relation' => 'OR',
      array(
        'key' => 'lnk_checked',
        'compare' => 'NOT EXISTS'
      ),
      array(
        'key' => 'lnk_checked',
        'compare' => '=',
        'value' => '0'
      )
    );
  }

  if($filtro['tiempo'] !== 'siempre') {
      $posts_args['date_query'] = array(array('after' => $filtro['tiempo']));
  }

   $sites = get_sites($sites_args);
   $allPosts = array();
   if(is_array($sites))
   foreach($sites as $site_key => $site){
    switch_to_blog($site->blog_id);

     $posts = get_posts($posts_args);

     foreach($posts as $post_key => $post){

       $posts[$post_key]->blog = array(
         'blog_id' => $site->blog_id,
         'blog_name' => get_bloginfo('name'),
         'blog_url' => $site->path
       );

       $terms = wp_get_post_categories($post->ID);
       if(is_array($terms)){
         $posts[$post_key]->the_term = get_term($terms[0])->slug;
       }
       $posts[$post_key]->thumbnail = get_the_post_thumbnail_url($post->ID);
       $posts[$post_key]->lnk_checked = get_post_meta($post->ID,'lnk_checked',true);
       $posts[$post_key]->lnk_onhome = get_post_meta($post->ID,'lnk_onhome',true);
       $posts[$post_key]->lnk_featured = get_post_meta($post->ID,'lnk_featured',true);
     }

     $allPosts = array_merge($allPosts,$posts);
     restore_current_blog();
   }
   usort($allPosts,'lnk_multisite_manager_compare_by_date');
   return $allPosts;
 }

 /**
  * Compara 2 objetos WP_Post para ordenar decrecientemente
  */
 function lnk_multisite_manager_compare_by_date($post1, $post2){
   if($post1->post_date == $post2->post_date) {
     return 0;
   } else if ($post1->post_date > $post2->post_date) {
     return -1;
   } else {
     return 1;
   }
 }
