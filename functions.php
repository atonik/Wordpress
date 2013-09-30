// ##########################################
// if there is only one post, redirect to post instead of showing empty menu

function redirect_to_post(){
    global $wp_query;

    if( is_archive() && $wp_query->post_count == 1 ){
        the_post();
        $post_url = get_permalink();
        wp_redirect( $post_url );
    }

} add_action('template_redirect', 'redirect_to_post')

// ##########################################
// end if there is only one post, redirect to post instead of showing empty menu

// ##########################################
// convert category to taxonomy and enable featured image for the category

add_action('admin_head', 'wpds_admin_head');
add_action('edit_term', 'wpds_save_tax_pic');
add_action('create_term', 'wpds_save_tax_pic');

function wpds_admin_head() {
    $taxonomies = get_taxonomies();
    //$taxonomies = array('category'); // uncomment and specify particular taxonomies you want to add image feature.
    if (is_array($taxonomies)) {
        foreach ($taxonomies as $z_taxonomy) {
            add_action($z_taxonomy . '_add_form_fields', 'wpds_tax_field_pic');
            add_action($z_taxonomy . '_edit_form_fields', 'wpds_tax_field_pic');
            add_action($z_taxonomy . '_add_form_fields', 'wpds_tax_field_logo');
            add_action($z_taxonomy . '_edit_form_fields', 'wpds_tax_field_logo');
        }
    }
}

// add image field in add form
function wpds_tax_field_pic($taxonomy) {
    wp_enqueue_style('thickbox');
    wp_enqueue_script('thickbox');
    if(empty($taxonomy)) {
        echo '<div class="form-field">
                <label for="wpds_tax_pic">Picture</label>
                <input type="text" name="wpds_tax_pic" id="wpds_tax_pic" value="" />
            </div>';
    }
    else{
        $wpds_tax_pic_url = get_option('wpds_tax_pic' . $taxonomy->term_id);
        echo '<tr class="form-field">
        <th scope="row" valign="top"><label for="wpds_tax_pic">Picture</label></th>
        <td><input type="text" name="wpds_tax_pic" id="wpds_tax_pic" value="' . $wpds_tax_pic_url . '" /><br />';
        if(!empty($wpds_tax_pic_url))
            echo '<img src="'.$wpds_tax_pic_url.'" style="max-width:200px;border: 1px solid #ccc;padding: 5px;box-shadow: 5px 5px 10px #ccc;margin-top: 10px;" >';
        echo '</td></tr>';
    }
    echo '<script type="text/javascript">
        jQuery(document).ready(function() {
                jQuery("#wpds_tax_pic").click(function() {
                    tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
                    return false;
                });
                window.send_to_editor = function(html) {
                    jQuery("#wpds_tax_pic").val( jQuery("img",html).attr("src") );
                    tb_remove();
                }
        });
    </script>';
}

// save our taxonomy image while edit or save term
function wpds_save_tax_pic($term_id) {
    if (isset($_POST['wpds_tax_pic']))
        update_option('wpds_tax_pic' . $term_id, $_POST['wpds_tax_pic']);
}

// output taxonomy image url for the given term_id (NULL by default)
function wpds_tax_pic_url($term_id = NULL) {
    if ($term_id)
        return get_option('wpds_tax_pic' . $term_id);
    elseif (is_category())
        return get_option('wpds_tax_pic' . get_query_var('cat')) ;
    elseif (is_tax()) {
        $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
        return get_option('wpds_tax_pic' . $current_term->term_id);
    }
}

// ##########################################
// end convert category to taxonomy

// ##########################################
// include custom taxonomies in wp search

function atom_search_where($where){
  global $wpdb;
  if (is_search())
    $where .= "OR (t.name LIKE '%".get_search_query()."%' AND {$wpdb->posts}.post_status = 'publish')";
  return $where;
}

function atom_search_join($join){
  global $wpdb;
  if (is_search())
    $join .= "LEFT JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.ID = tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id";
  return $join;
}

function atom_search_groupby($groupby){
  global $wpdb;

  // we need to group on post ID
  $groupby_id = "{$wpdb->posts}.ID";
  if(!is_search() || strpos($groupby, $groupby_id) !== false) return $groupby;

  // groupby was empty, use ours
  if(!strlen(trim($groupby))) return $groupby_id;

  // wasn't empty, append ours
  return $groupby.", ".$groupby_id;
}

add_filter('posts_where','atom_search_where');
add_filter('posts_join', 'atom_search_join');
add_filter('posts_groupby', 'atom_search_groupby');

// ##########################################
// end include custom taxonomies in wp search
