// Add to your functions.php

// 1. Register Shortcode
add_shortcode('project_gallery', 'project_gallery_slider_shortcode');

function project_gallery_slider_shortcode($atts) {
    global $post;
    
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'post_id' => $post->ID,
        'size' => 'large',
        'slides_to_show' => 3
    ), $atts);

    // Get gallery IDs
    $gallery_ids = get_post_meta($atts['post_id'], 'project_gallery_ids', true);
    
    if (empty($gallery_ids)) return '';

    // Enqueue assets
    project_gallery_enqueue_slick();

    // Generate slider HTML
    ob_start(); ?>
    <div class="gallery-slider">
        <?php foreach ($gallery_ids as $attachment_id) : ?>
            <div class="slide">
                <?php echo wp_get_attachment_image($attachment_id, $atts['size']); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    
    // Add initialization script
    project_gallery_slick_init($atts['slides_to_show']);
    
    return ob_get_clean();
}

// 2. Enqueue Slick Assets
function project_gallery_enqueue_slick() {
    static $enqueued = false;
    
    if (!$enqueued) {
        wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
        wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
        $enqueued = true;
    }
}

// 3. Add Slick Initialization
function project_gallery_slick_init($slides_to_show = 3) {
    static $initialized = false;
    
    if (!$initialized) {
        add_action('wp_footer', function() use ($slides_to_show) {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.gallery-slider').slick({
                    dots: true,
                    arrows: true,
                    infinite: true,
                    speed: 300,
                    slidesToShow: <?php echo $slides_to_show; ?>,
                    slidesToScroll: 1,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1                        },
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
            });
            </script>
            <?php
        }, 20);
        $initialized = true;
    }
}

// 4. Optional: Auto-add to CPT content
add_filter('the_content', 'add_gallery_to_cpt_content');
function add_gallery_to_cpt_content($content) {
    if (is_singular('project') && in_the_loop() && is_main_query()) {
        return $content . do_shortcode('[project_gallery]');
    }
    return $content;
}


// usage
// [project_gallery post_id="123" size="medium" slides_to_show="4"]
