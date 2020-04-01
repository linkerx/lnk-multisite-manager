jQuery(document).ready(function() {
    lnk_loading(false);
    lnk_load_posts();
    lnk_bind_componentes();
    lnk_create_modals();
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
    //console.log(data);
    jQuery.ajax({
        url: ajaxurl,
        data: data,
        dataType:'json',
        success: function(response){
            lnkPosts = response;
            lnk_render_list();
            lnk_loading(false);
            jQuery(".datepicker").datepicker({
                dateFormat : "yy-mm-dd"
            });
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
    jQuery("#posts").on("change",".datepicker",function(elem){
        lnk_change_agenda(
            jQuery(elem.target).attr('data-blog'),
            jQuery(elem.target).attr('data-post'),
            jQuery(elem.target).val()
        );
    });
}

function lnk_create_modals(){
    jQuery("#modal_cambiar_sitio").dialog({ 
        title: "Cambiar sitio",
        autoOpen: false,
        modal: true, 
    });

    jQuery("#modal_compartir").dialog({ 
        title: "Compartir publicación en otros sitios",
        autoOpen: false,
        modal: true, 
        width: 650
    });
}

function lnk_open_dialog_cambiar_sitio(id_post, id_blog){
    jQuery("#modal_cambiar_sitio #modal_cambiar_sitio_id_post").val(id_post);
    jQuery("#modal_cambiar_sitio #modal_cambiar_sitio_id_blog").val(id_blog);
    jQuery("#modal_cambiar_sitio select").val(id_blog);
    jQuery("#modal_cambiar_sitio").dialog("open");
}

function lnk_close_cambiar_modal(){
    jQuery("#modal_cambiar_sitio").dialog("close");
}


function lnk_open_dialog_compartir(id_post, id_blog){
    var post = lnkPosts.find(function(item){
        return item.ID === id_post; 
    });
    jQuery("#modal_compartir input[type=checkbox]").attr("disabled", false);
    jQuery("#modal_compartir input[type=checkbox]").attr("checked", false);
    jQuery("#modal_compartir #modal_compartir_id_post").val(id_post);
    jQuery("#modal_compartir #modal_compartir_id_blog").val(id_blog);
    jQuery("#modal_compartir #lnk_compartir_li_"+id_blog+" input[type=checkbox]").attr("disabled", true);
    if(post.lnk_compartido !== null) {
        post.lnk_compartido.forEach( function(sitio) {
            if(sitio.val === 'true') {
                jQuery("#modal_compartir #lnk_compartir_li_"+sitio.id+" input[type=checkbox]").attr("checked", true);
            }
        });
    }
    
    jQuery("#modal_compartir").dialog("open");
}

function lnk_close_compartir_modal(){
    jQuery("#modal_compartir").dialog("close");
}


function lnk_render_list_header(){
    html = "<div class='posts-header'>";
    html+= "<ul>";
    html+= "<li class='col-id'>#</li>";
    html+= "<li class='col-fecha'>Fecha</li>";
    html+= "<li class='col-titulo'>Titulo</li>";
    html+= "<li class='col-site'>Sitio de Origen</li>";
    html+= "<li class='col-estado'>Visibilidad Home</li>";
    html+= "<li class='col-acciones'>Acciones</li>";
    html+= "</ul>";
    html+= "</div>";
    return html;
}

function lnk_render_item_titulo(item) {
    site = item.blog.blog_url;
    url = site+"wp-admin/post.php?post="+item.ID+"&action=edit";

    html = "<li class='col-titulo'>";
    html+= "<a target='_blank' rel='noopener noreferrer' href='"+url+"' >";
    html+= item.post_title;
    html+= "</a></li>";
    return html;
}

function lnk_render_item_sitio(item){
    html = "<li class='col-site'>";
    html+= item.blog.blog_name;

    html+= "<button id='trigger_modal_cambiar_sitio' onClick='lnk_open_dialog_cambiar_sitio("+item.ID+","+item.blog.blog_id+")'><span title='Cambiar' class='dashicons dashicons-update-alt'></span></button>";
    html+= "<button id='trigger_modal_cambiar_sitio' onClick='lnk_open_dialog_compartir("+item.ID+","+item.blog.blog_id+","+item.lnk_compartir+")'><span title='Visible' class='dashicons dashicons-share'></span></button>";
    //    html+= "<button id='cambiar_sitio' onClick='lnk_cambiarSitio("+item.blog.blog_id+","+item.ID+",0)'><span title='Marcar Revisado' class='dashicons dashicons-yes' ></span></button>";
    html+= "</li>";
    return html;
}

function lnk_render_item_estado(item) {
    html = "<li class='col-estado'>";
    if(typeof(item.lnk_onhome) != 'undefined' && item.lnk_onhome === '1') {
        html+= "<span title='Visible' class='dashicons dashicons-visibility'></span>";
    } else {
        html+= "<span title='Oculto' class='dashicons dashicons-hidden'></span>";
    }
        
    if(typeof(item.lnk_featured) != 'undefined' && item.lnk_featured === '1') {
        html+= "<span title='Destacada' class='dashicons dashicons-star-filled'></span>";
    } else {
        html+= "<span title='No destacada' class='dashicons dashicons-star-empty'></span>";
    }
        
    html+= "</li>";
    return html;
}

function lnk_render_list() {
    html = lnk_render_list_header();
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
        html_item+= "<li class='col-id'>"+item.ID+"</li>";
        html_item+= "<li class='col-fecha'>"+item.post_date+"</li>";
        html_item+= lnk_render_item_titulo(item);
        html_item+= lnk_render_item_sitio(item);
        html_item+= lnk_render_item_estado(item);
        
        html_item+= "<li class='col-acciones'>";
        
        /* EN HOME */
        if(typeof(item.lnk_onhome) != 'undefined' && item.lnk_onhome === '1') {
            html_item+= "<button id='btn_despublicar' onClick='lnk_publicar_home("+item.blog.blog_id+","+item.ID+",0)'><span title='Despublicar Home' class='dashicons dashicons-hidden' ></span></button>";
            if(typeof(item.lnk_featured) != 'undefined' && item.lnk_featured === '1') {
                html_item+= "<button id='btn_destacar' onClick='lnk_destacar("+item.blog.blog_id+","+item.ID+",0)'><span title='Quitar Destacado' class='dashicons dashicons-star-empty' ></span></button>";
                html_item+= "<button id='btn_destacar_mode' onClick='lnk_destacar_mode("+item.blog.blog_id+","+item.ID+","+item.lnk_featured_mode+")'>"+item.lnk_featured_mode+"</button>";
           } else {
                html_item+= "<button id='btn_destacar' onClick='lnk_destacar("+item.blog.blog_id+","+item.ID+",1)'><span title='Destacar' class='dashicons dashicons-star-filled' ></span></button>";
            }
        } else {
            html_item+= "<button id='btn_publicar' onClick='lnk_publicar_home("+item.blog.blog_id+","+item.ID+",1)'><span title='Publicar Home' class='dashicons dashicons-visibility'></span></button>";
        }

        /* FACEBOOK */
            html_item+= "<button id='btn_post_facebook' onClick='lnk_post_facebook("+item.blog.blog_id+","+item.ID+")'>";
            html_item+= "<span title='Postear en Facebook' class='dashicons dashicons-facebook' ></span>";
            html_item+= "</button>";
        
        /* EN AGENDA */
        if(typeof(item.lnk_onagenda) != 'undefined' && item.lnk_onagenda === '1') {
            html_item+= "<button id='btn_desagendar' onClick='lnk_agendar("+item.blog.blog_id+","+item.ID+",0)'><span title='Desagendar' class='dashicons dashicons-calendar'></span></button>";
            html_item+= "<input type='text' id='input_agendar_"+item.ID+"' data-post='"+item.ID+"' data-blog='"+item.blog.blog_id+"' class='datepicker' value='"+item.lnk_agenda+"' />";
        } else {
            html_item+= "<button id='btn_agendar' onClick='lnk_agendar("+item.blog.blog_id+","+item.ID+",1)'><span title='Agendar' class='dashicons dashicons-calendar'></span></button>";
        }

        /* REVISADO */
        if(!(item.lnk_checked == '1')) {
            html_item+= "<button id='marcar_revisado' onClick='lnk_revisar("+item.blog.blog_id+","+item.ID+",0)'><span title='Marcar Revisado' class='dashicons dashicons-yes' ></span></button>";
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

function lnk_destacar_mode(blog_id,post_id,mode) {
    var data = {
        'action': 'lnk_multisite_manager_destacar_mode_action',
        'blog_id': blog_id,
        'post_id': post_id,
        'mode': mode
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

function lnk_post_facebook(blog_id,post_id) {
    var data = {
        'action': 'lnk_multisite_manager_post_facebook_action',
        'blog_id': blog_id,
        'post_id': post_id
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

function lnk_agendar(blog_id,post_id,agendar) {
    var data = {
        'action': 'lnk_multisite_manager_agendar_action',
        'blog_id': blog_id,
        'post_id': post_id,
        'agendar': agendar
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

function lnk_change_agenda(blog_id,post_id,fecha) {
    var data = {
        'action': 'lnk_multisite_manager_change_agenda_action',
        'blog_id': blog_id,
        'post_id': post_id,
        'fecha': fecha
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

function lnk_cambiar_sitio_post() {

    var data = {
        'action': 'lnk_multisite_manager_cambiar_sitio_post_action',
        'blog_id': jQuery('#modal_cambiar_sitio select').val(),
        'blog_ori_id': jQuery("#modal_cambiar_sitio #modal_cambiar_sitio_id_blog").val(),
        'post_id': jQuery("#modal_cambiar_sitio #modal_cambiar_sitio_id_post").val()
    }

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        dataType:'json',
        success: function(response){
            lnk_close_cambiar_modal();
            lnk_load_posts();
        }
    })
}

function lnk_compartir() {
    
    var checks = [];
    jQuery.each(jQuery("#modal_compartir input[type=checkbox]"), function(){
        checks.push({ id: jQuery(this).val() , val: jQuery(this).is(":checked") });
    });
    
    var data = {
        'action': 'lnk_multisite_manager_compartir_post_action',
        'blogs_compartir': checks,
        'blog_id': jQuery("#modal_compartir #modal_compartir_id_blog").val(),
        'post_id': jQuery("#modal_compartir #modal_compartir_id_post").val()
    }

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        dataType:'json',
        success: function(response){
            lnk_close_compartir_modal();
            lnk_load_posts();
        }
    })
}