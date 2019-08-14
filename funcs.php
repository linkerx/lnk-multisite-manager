<?php

/**
 * Lista de posts de todos los sitios
 */
 function lnk_multisite_manager_get_posts(){

   $sites_args = array(
     'public' => 1
   );
   $sites = get_sites($sites_args);
   $allPosts = array();
   if(is_array($sites))
   foreach($sites as $site_key => $site){
    switch_to_blog($site->blog_id);
    $posts_args = array(
        'numberposts' => '-1',
        'meta_query' => array(
          'relation' => 'OR',
          /*array(
           'key' => 'lnk_revision',
           'compare' => 'NOT EXISTS'
          ),*/
          array(
            'key' => 'lnk_revision',
            'compare' => '=',
            'value' => '0'
          )
      )
     );
     $posts = get_posts($posts_args);
     
     /*
     print "<pre>";
     var_dump($site);
     var_dump($posts);
     print "</pre>;";
     */

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
