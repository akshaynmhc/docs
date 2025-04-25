// 1. Register Custom Post Type (if not already registered)
function register_custom_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Projects',
        'supports' => array('title', 'editor'),
    );
    register_post_type('project', $args);
}
add_action('init', 'register_custom_post_type');

// 2. Add Metabox
function add_gallery_metabox() {
    add_meta_box(
        'project_gallery',
        'Project Gallery',
        'render_gallery_metabox',
        'project', // Your custom post type
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_gallery_metabox');

// 3. Metabox Content
function render_gallery_metabox($post) {
    wp_nonce_field('project_gallery_nonce', 'project_gallery_nonce');
    $ids = get_post_meta($post->ID, 'project_gallery_ids', true);
    ?>
    <div class="gallery-metabox">
        <ul class="gallery-images">
            <?php
            if ($ids) : foreach ($ids as $key => $value) :
                $image = wp_get_attachment_image_src($value);
                if ($image) :
                    ?>
                    <li>
                        <input type="hidden" name="project_gallery_ids[<?php echo $key; ?>]" value="<?php echo $value; ?>">
                        <img src="<?php echo $image[0]; ?>">
                        <a href="#" class="change-image">Replace</a>
                        <a href="#" class="remove-image">Remove</a>
                    </li>
                    <?php
                endif;
            endforeach; endif;
            ?>
        </ul>
        <input type="hidden" id="project_gallery_ids" name="project_gallery_ids" value="<?php echo implode(',', (array) $ids); ?>">
        <button class="button upload-gallery">Add Images</button>
    </div>
    <style>
        .gallery-images li {
            display: inline-block;
            width: 150px;
            margin: 5px;
            position: relative;
        }
        .gallery-images img {
            width: 100%;
            height: auto;
        }
        .gallery-images a {
            display: block;
            text-align: center;
            background: #f1f1f1;
            padding: 5px;
        }
    </style>
    <?php
}

// 4. Save Metabox Data
function save_gallery_metabox($post_id) {
    if (!isset($_POST['project_gallery_nonce']) || 
        !wp_verify_nonce($_POST['project_gallery_nonce'], 'project_gallery_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['project_gallery_ids'])) {
        $ids = array_map('intval', (array)$_POST['project_gallery_ids']);
        update_post_meta($post_id, 'project_gallery_ids', $ids);
    } else {
        delete_post_meta($post_id, 'project_gallery_ids');
    }
}
add_action('save_post', 'save_gallery_metabox');

// 5. Enqueue Media Uploader
function enqueue_gallery_scripts($hook) {
    if ('post.php' != $hook && 'post-new.php' != $hook) return;
    
    wp_enqueue_media();
    wp_enqueue_script('gallery-metabox', get_template_directory_uri() . '/js/gallery-metabox.js', array('jquery'));
}
add_action('admin_enqueue_scripts', 'enqueue_gallery_scripts');
