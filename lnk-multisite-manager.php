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
    wp_enqueue_script('lnk_multisite_manager_js', plugins_url('/js/funcs.js', __FILE__ ), array( 'jquery','jquery-ui-datepicker' ), null, true);
    wp_enqueue_style('lnk_multisite_manager_css', plugins_url('/css/styles.css', __FILE__ ));

    // para datepicker
    wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'jquery-ui' );  
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
    $config.= "<li><label>Mostrar Revisados</label><input type='checkbox' name='chk_mostrar_revisados' id='chk_mostrar_revisados'></li>";
    $config.= "<li><label>Filtrar por tiempo</label>";
    $config.= "<select name='filtro_tiempo' id='filtro_tiempo'>";
    $config.= "<option value='1 week ago'>Última Semana</option>";
    $config.= "<option value='1 month ago'>Último Mes</option>";
    $config.= "<option value='1 year ago'>Último Año</option>";
    $config.= "<option value='siempre'>Siempre</option>";
    $config.= "</select></li>";
    $config.= "<li><label>Postear en redes al publicar</label><input type='checkbox' name='chk_postear_redes' id='chk_postear_redes'></li>";
    $config.= "</ul>";
    return $config;
}

/* AJAX */

/**
 * GET POSTS 
 */
function lnk_multisite_manager_get_posts_action(){
    $filtro = array();
    $filtro['tiempo'] = $_GET['filtro_tiempo'];
    $filtro['revisados'] = $_GET['mostrar_revisados'];
    
    $posts = lnk_multisite_manager_get_posts($filtro);
    echo json_encode($posts);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_get_posts_action', 'lnk_multisite_manager_get_posts_action' );

/**
 * PUBLICAR HOME
 */

function lnk_multisite_manager_publicar_home_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    $publicar = ($_POST['publicar'] == '1');
    switch_to_blog($blog_id);
    $post = get_post($post_id);
    if($publicar) {
        update_post_meta($post_id,'lnk_onhome',1);
    } else {
        update_post_meta($post_id,'lnk_onhome',0);
        update_post_meta($post_id,'lnk_featured',0);
    }
    $post->lnk_onhome = get_post_meta($post_id,'lnk_onhome',true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_publicar_home_action', 'lnk_multisite_manager_publicar_home_action' );

function lnk_multisite_manager_destacar_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    $destacar = ($_POST['destacar'] == '1');
    switch_to_blog($blog_id);
    $post = get_post($post_id);
    if($destacar) {
        update_post_meta($post_id,'lnk_featured',1);
        update_post_meta($post_id,'lnk_featured_mode',1);
    } else {
        update_post_meta($post_id,'lnk_featured',0);
    }
    $post->lnk_onhome = get_post_meta($post_id,'lnk_onhome',true);
    $post->lnk_featured = get_post_meta($post_id,'lnk_featured',true);
    $post->lnk_featured_mode = get_post_meta($post_id,'lnk_featured_mode',true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_destacar_action', 'lnk_multisite_manager_destacar_action' );

function lnk_multisite_manager_destacar_mode_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    $mode = $_POST['mode'];

    switch_to_blog($blog_id);
    $post = get_post($post_id);
    if($mode == 1) {
        update_post_meta($post_id,'lnk_featured_mode',2);
    } else if($mode == 2){
        update_post_meta($post_id,'lnk_featured_mode',1);
    }
    $post->lnk_onhome = get_post_meta($post_id,'lnk_onhome',true);
    $post->lnk_featured = get_post_meta($post_id,'lnk_featured',true);
    $post->lnk_featured_mode = get_post_meta($post_id,'lnk_featured_mode',true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_destacar_mode_action', 'lnk_multisite_manager_destacar_mode_action' );

function lnk_multisite_manager_revisar_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    switch_to_blog($blog_id);
    update_post_meta($post_id,'lnk_checked',1);
    $post = get_post($post_id);
    $post->lnk_checked = get_post_meta($post_id,'lnk_checked',true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_revisar_action', 'lnk_multisite_manager_revisar_action' );

function lnk_multisite_manager_agendar_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    $agendar = ($_POST['agendar'] == '1');
    switch_to_blog($blog_id);
    $post = get_post($post_id);
    if($agendar) {
        update_post_meta($post_id,'lnk_onagenda',1);
        update_post_meta($post_id,'lnk_agenda',date('Y-m-d'));
    } else {
        update_post_meta($post_id,'lnk_onagenda',0);
        update_post_meta($post_id,'lnk_agenda','');
    }
    $post->lnk_onagenda = get_post_meta($post_id,'lnk_onagenda',true);
    $post->lnk_agenda = get_post_meta($post_id,'lnk_agenda',true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_agendar_action', 'lnk_multisite_manager_agendar_action' );

function lnk_multisite_manager_change_agenda_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    $change_agenda = $_POST['fecha'];
    print $change_agenda;
    switch_to_blog($blog_id);
    $post = get_post($post_id);
    update_post_meta($post_id,'lnk_agenda',$change_agenda);
    $post->lnk_onagenda = get_post_meta($post_id,'lnk_onagenda',true);
    $post->lnk_agenda = get_post_meta($post_id,'lnk_agenda',true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_change_agenda_action', 'lnk_multisite_manager_change_agenda_action' );