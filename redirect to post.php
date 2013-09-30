// if there is only one post, redirect to post instead of showing empty menu

function redirect_to_post(){
    global $wp_query;

    if( is_archive() && $wp_query->post_count == 1 ){
        the_post();
        $post_url = get_permalink();
        wp_redirect( $post_url );
    }

} add_action('template_redirect', 'redirect_to_post')
