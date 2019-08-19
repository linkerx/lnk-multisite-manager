<?php

/**
 * Plugin Name: LNK-MULTISITE-MANAGER
 * Plugin URI: https://github.com/linkerx/lnk-multisite-manager
 * Description: Gestion de articulos multisite
 * Version: 0.1
 * Author: Diego Martinez Diaz
 * Author URI: https://github.com/linkerx
 * License: GPLv3
 */

require_once('inc/funcs.php');

function lnk_multisite_manager_page() {
    $page = add_menu_page(
        'Gestor de Publicacion',
        'Publicación',
        'manage_options',
        'lnk_multisite_manager',
        'lnk_multisite_manager_page_html',
        '',
        50
    );
    add_action('load-'.$page,'lnk_multisite_manager_include_assets');
}
add_action('admin_menu', 'lnk_multisite_manager_page');

function lnk_multisite_manager_include_assets() {
    wp_enqueue_script('lnk_multisite_manager_js', plugins_url('/js/funcs.js', __FILE__ ), array( 'jquery' ), null, true);
    wp_enqueue_style('lnk_multisite_manager_css', plugins_url('/css/styles.css', __FILE__ ));
}
add_action('wp_enqueue_scripts','ava_test_init');

function lnk_multisite_manager_page_html(){
    echo "<section id='lnk-multisite-manager'>";
    echo "<h1>Articulos para Revisión</h1>";
    echo lnk_multisite_manager_page_config();
    echo "<div id='posts'></div>";
    echo "<div id='cargando'></div>";
    echo "</section>";
}

/* RENDER */
function lnk_multisite_manager_page_config() {
    $config = "<ul id='config'>";
    $config.= "<li><label>Postear en redes al publicar</label><input type='checkbox' name='chk-postear-redes' id='chk-postear-redes'></li>";
    $config.= "</ul>";
    return $config;
}

/* AJAX */

/**
 * GET POSTS 
 */
function lnk_multisite_manager_get_posts_action(){
    $posts = lnk_multisite_manager_get_posts();
    echo json_encode($posts);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_get_posts_action', 'lnk_multisite_manager_get_posts_action' );

/**
 * PUBLICAR HOME
 */

function lnk_multisite_manager_publicar_home_action($request){
    $post_id = $_POST['post_id'];
    $publicar = ($_POST['publicar'] == '1');
    $post = get_post($post_id);

    if($publicar) {
        update_post_meta($post_id,'lnk_onhome',1);
    } else {
        update_post_meta($post_id,'lnk_onhome',0);
    }
    $post->onhome = get_post_meta($post_id,'lnk_onhome',true);
    echo json_encode($post);
    
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_publicar_home_action', 'lnk_multisite_manager_publicar_home_action' );
