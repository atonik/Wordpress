$taxonomy = "brands";

//get top layer terms only by setting parent to 0.
$brand = get_terms($taxonomy, array('parent' => 0, 'orderby' => 'slug', 'hide_empty' => false));

    <a class="showsub" href="#">Brands</a>
    <div class="cbp-hrsub shadow">
        <div class="cbp-hrsub-inner">
            <div class="cbp-hrsub-details">
                <h3>Brands</h3>
            </div>
            <div class="cbp-hrsub-list">
                <ul>
                    <?php
                    foreach ($brand as $b) {
                        echo '<li><a href="/brands/' . $b->slug . '">' . $b->name . '</a></li>';
                        echo '<ul>';
                        $terms = get_terms($taxonomy, array('parent' => $b->term_id, 'orderby' => 'slug', 'hide_empty' => false));
                        foreach ($terms as $term) {
                            echo '<li><a href="/brands/' . $term->slug . '">' . $term->name . '</a></li>';
                        }
                        echo '</ul>';
                    }
                ?>
                </ul>
            </div>
        </div>
    </div>
