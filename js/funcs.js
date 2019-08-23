jQuery(document).ready(function() {
    lnk_loading(false);
    lnk_load_posts();
    lnk_bind_componentes();
});

function lnk_loading(cargando) {
    if(cargando) {
        jQuery('#cargando').show();
    } else {
        jQuery('#cargando').hide();
    }
}

var lnkPosts = [];

function lnk_load_posts() {
    lnk_loading(true);
    var filtro_tiempo = jQuery('#filtro_tiempo').val();
    var mostrar_revisados = (typeof(jQuery('#chk_mostrar_revisados').attr('checked')) != "undefined");
    var data = {
        'action': 'lnk_multisite_manager_get_posts_action',
        'filtro_tiempo': filtro_tiempo,
        'mostrar_revisados': mostrar_revisados
    }
    console.log(data);
    jQuery.ajax({
        url: ajaxurl,
        data: data,
        dataType:'json',
        success: function(response){
            lnkPosts = response;
            lnk_render_list();
            lnk_loading(false);
        }
    })
}

function lnk_bind_componentes(){
    jQuery('#filtro_tiempo').on('change',function(){
        lnk_load_posts();
    })
    jQuery('#chk_mostrar_revisados').on('change',function(){
        lnk_load_posts();
    })
}

function lnk_render_list() {
    html = "<div class='posts-header'>";
    html+= "<ul>";
    html+= "<li class='col-titulo'>Titulo</li>";
    html+= "<li class='col-site'>Sitio</li>";
    html+= "<li class='col-estado'>Estado</li>";
    html+= "<li class='col-acciones'>Acciones</li>";
    html+= "</ul>";
    html+= "</div>";
    html+= "<div class='posts-body'>";
    html+= lnkPosts.map(function(item){
        html_item = "<ul id='item-"+item.ID+"' class='";
        if(typeof(item.lnk_onhome) != 'undefined' && item.lnk_onhome === '1') {
            html_item+= "onhome ";
        }
        if(typeof(item.lnk_featured) != 'undefined' && item.lnk_featured === '1') {
            html_item+= "featured ";
        }
        html_item+= "' >";
        html_item+= "<li class='col-titulo'>"+item.post_title+"</li>";
        html_item+= "<li class='col-site'>"+item.blog.blog_name+"</li>";
        html_item+= "<li class='col-estado'>";
        
        html_item+= "<label>En Home: </label>"
        if(typeof(item.lnk_onhome) != 'undefined' && item.lnk_onhome === '1') {
            html_item+= "<span>SI</span>";
        } else {
            html_item+= "<span>NO</span>";
        }
        
        html_item+= "<label>Destacada: </label>"
        if(typeof(item.lnk_featured) != 'undefined' && item.lnk_featured === '1') {
            html_item+= "<span>SI</span>";
        } else {
            html_item+= "<span>NO</span>";
        }
        
        html_item+= "</li>";
        html_item+= "<li class='col-acciones'>";
        if(typeof(item.lnk_onhome) != 'undefined' && item.lnk_onhome === '1') {
            html_item+= "<button id='btn_despublicar' onClick='lnk_publicar_home("+item.blog.blog_id+","+item.ID+",0)'><span title='Despublicar Home' class='dashicons dashicons-hidden' ></span></a>";
            if(typeof(item.lnk_featured) != 'undefined' && item.lnk_featured === '1') {
                html_item+= "<button id='btn_destacar' onClick='lnk_destacar("+item.blog.blog_id+","+item.ID+",0)'><span title='Quitar Destacado' class='dashicons dashicons-star-empty' ></span></a>";
            } else {
                html_item+= "<button id='btn_destacar' onClick='lnk_destacar("+item.blog.blog_id+","+item.ID+",1)'><span title='Destacar' class='dashicons dashicons-star-filled' ></span></a>";
            }
        } else {
            html_item+= "<button id='btn_publicar' onClick='lnk_publicar_home("+item.blog.blog_id+","+item.ID+",1)'><span title='Publicar Home' class='dashicons dashicons-visibility'></span></a>";
        }
        if(!(item.lnk_checked == '1')) {
            html_item+= "<button id='marcar_revisado' onClick='lnk_revisar("+item.blog.blog_id+","+item.ID+",0)'><span title='Marcar Revisado' class='dashicons dashicons-yes' ></span></a>";
        }
        html_item+= "</li></ul>";
        return html_item;
    }).join('');
    html+= '</div>';
    jQuery('#posts').html(html);
}

function lnk_publicar_home(blog_id,post_id,publicar) {
    var data = {
        'action': 'lnk_multisite_manager_publicar_home_action',
        'blog_id': blog_id,
        'post_id': post_id,
        'publicar': publicar
    }

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        dataType:'json',
        success: function(response){
            lnk_load_posts();
        }
    })
}

function lnk_destacar(blog_id,post_id,destacar) {
    var data = {
        'action': 'lnk_multisite_manager_destacar_action',
        'blog_id': blog_id,
        'post_id': post_id,
        'destacar': destacar
    }

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        dataType:'json',
        success: function(response){
            lnk_load_posts();
        }
    })
}

function lnk_revisar(blog_id,post_id,publicar) {
    if(confirm("Al realizar esto la publicacion desaparecerá de este listado. ¿Continuar?")) {
        var data = {
            'action': 'lnk_multisite_manager_revisar_action',
            'blog_id': blog_id,
            'post_id': post_id,
        }

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            dataType:'json',
            success: function(response){
                lnk_load_posts();
            }
        })
    }
}