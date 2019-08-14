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

require_once('funcs.php');

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
}
add_action('wp_enqueue_scripts','ava_test_init');

function lnk_multisite_manager_page_html(){
    echo "<section id='lnk-multisite-manager'>";
    echo "<h1>Articulos para Revisión</h1>";
    echo "<div id='posts'></div>";
    echo "</section>";
}

/* AJAX */

function lnk_multisite_manager_get_posts_action(){
    $posts = lnk_multisite_manager_get_posts();
    return json_encode($posts);
}
add_action( 'wp_ajax_lnk_multisite_manager_get_posts_action', 'lnk_multisite_manager_get_posts_action' );
