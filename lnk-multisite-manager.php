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

function delegaciones_options_page() {
    add_menu_page(
        'Gestor de Publicacion',
        'Gestor de Publicación',
        'manage_options',
        'lnk_multisite_manager_options',
        'lnk_multisite_manager_options_page_html',
        '',
        50
    );
}
add_action('admin_menu', 'lnk_multisite_manager_options_page');

function lnk_multisite_manager_page_html(){
    echo '<h1>Articulos para Revisión</h1>';



}

