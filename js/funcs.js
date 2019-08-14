jQuery(document).ready(function() {
    load_posts();
});

function load_posts(){
    console.log("get posts...")
    var data = {
        'action': 'lnk_multisite_manager_get_posts_action'
    }

    jQuery.ajax({
        url: ajaxurl,
        data: data,
        success: function(response){
            console.log(response);
        }
    })
}