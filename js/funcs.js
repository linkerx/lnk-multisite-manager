jQuery(document).ready(function() {
    lnk_loading(false);
    lnk_load_posts();
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
    var data = {
        'action': 'lnk_multisite_manager_get_posts_action'
    }

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
        if(typeof(item.onhome) != 'undefined' && item.onhome === '1') {
            html_item+= "onhome";
        }
        html_item+= "' >";
        html_item+= "<li class='col-titulo'>"+item.post_title+"</li>";
        html_item+= "<li class='col-site'>"+item.blog.blog_name+"</li>";
        html_item+= "<li class='col-estado'>";
        html_item+= "<label>En Home: </label>"
        if(typeof(item.onhome) != 'undefined' && item.onhome === '1') {
            html_item+= "<span>SI</span>";
        } else {
            html_item+= "<span>NO</span>";
        }
        html_item+= "</li>";
        html_item+= "<li class='col-acciones'>";
        if(typeof(item.onhome) != 'undefined' && item.onhome === '1') {
            html_item+= "<button onClick='lnk_publicar_home("+item.ID+",0)'>despublicar</a>";
        } else {
            html_item+= "<button onClick='lnk_publicar_home("+item.ID+",1)'>publicar</a>";
        }
        html_item+= "<button onClick='lnk_revisar("+item.ID+",0)'>revisar</a>";
        html_item+= "</li></ul>";
        return html_item;
    }).join('');
    html+= '</div>';
    jQuery('#posts').html(html);
}

function lnk_publicar_home(post_id,publicar) {
    var data = {
        'action': 'lnk_multisite_manager_publicar_home_action',
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

function lnk_revisar(post_id,publicar) {
    if(confirm("Al realizar esto la publicacion desaparecerá de este listado. ¿Continuar?")) {
        var data = {
            'action': 'lnk_multisite_manager_revisar_action',
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