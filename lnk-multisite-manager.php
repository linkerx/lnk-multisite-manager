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
require_once('inc/face.php');

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
    wp_enqueue_script('lnk_multisite_manager_js', plugins_url('/js/funcs.js', __FILE__ ), array( 'jquery','jquery-ui-datepicker','jquery-ui-dialog'), null, true);
    wp_enqueue_style('lnk_multisite_manager_css', plugins_url('/css/styles.css', __FILE__ ));

    // para datepicker
    wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'jquery-ui' );  
}

function lnk_multisite_manager_page_html(){
    echo "<section id='lnk-multisite-manager'>";
    echo "<h1>Articulos para Revisión</h1>";
    echo lnk_multisite_manager_page_config();
    echo "<div id='posts'></div>";
    echo "<div id='cargando'></div>";
    echo lnk_multisite_modal_cambiar_sitio();
    echo "</section>";
}

function lnk_multisite_modal_cambiar_sitio() {
    $sites = get_sites($sites_args);

    $html = "<div id='modal_cambiar_sitio' style='display:none;'>";
    $html.= "<input id='modal_cambiar_sitio_id_post' type='hidden' value='0' />";
    $html.= "<input id='modal_cambiar_sitio_id_blog' type='hidden' value='0' />";
    $html.= "<select id='select_sitio'>";
    foreach($sites as $site) {
        $html.="<option value='".$site->blog_id."'>".$site->path."</option>";
    }
    $html.= "</select>";
    $html.= "<button onClick='lnk_cambiar_sitio_post()'>Cambiar</button>";
    $html.= "</div>";
    return $html;

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

function lnk_multisite_manager_post_facebook_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    switch_to_blog($blog_id);
    $post = get_post($post_id);
    
    $site = get_site($blog_id);
    $post->blog = array(
        'blog_id' => $site->blog_id,
        'blog_name' => get_bloginfo('name'),
        'blog_url' => $site->path
    );

    $cat = get_the_category($post_id)[0]->slug;

    $post->lnk_url = getenv('FRONTEND_URL').$site->path.$cat."/".$post->post_name;

    restore_current_blog();

    $facebook_count = get_post_meta($post_id,'lnk_facebook_count',true);

    if(lnk_multisite_manager_post_facebook($post)) {
        $facebook_count++;
        switch_to_blog($blog_id);
        update_post_meta($post_id,'lnk_facebook_count',$facebook_count);
        restore_current_blog();
    }

    $post->lnk_facebook_count = $facebook_count;
    
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_post_facebook_action', 'lnk_multisite_manager_post_facebook_action' );

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

function lnk_multisite_manager_cambiar_sitio_post_action($request) {
    $post_id = $_POST['post_id'];
    $blog_id = $_POST['blog_id'];
    $blog_ori_id = $_POST['blog_ori_id'];
    switch_to_blog($blog_ori_id);
    $post = get_post($post_id, ARRAY_A);
    $meta = get_post_meta($post_id);
    $post['ID'] = '';
    switch_to_blog($blog_id);
    $inserted_post_id = wp_insert_post($post);
    foreach($meta as $key=>$value) {
        update_post_meta($inserted_post_id,$key,$value[0]);
    }
    switch_to_blog($blog_ori_id);
    wp_delete_post($post_id,true);
    restore_current_blog();
    echo json_encode($post);
    wp_die();
}
add_action( 'wp_ajax_lnk_multisite_manager_cambiar_sitio_post_action', 'lnk_multisite_manager_cambiar_sitio_post_action' );