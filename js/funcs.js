jQuery(document).ready(function() {
    lnk_load_posts();
});

var lnkPosts = [];

function lnk_load_posts(){
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
        }
    })
}

function lnk_render_list(){
    
}