<?php
// Render the wishlist button
function wishListButton()
{
    $nonce = wp_create_nonce('wishlist_nonce'); // Create a nonce
    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = [];
    }

    $post_id = get_the_ID();
    $status = in_array($post_id, $wishlist) ? 1 : 0; // Check if the post is in the wishlist
?>
    <span
        id="wishlist-action"
        data-id="<?= esc_attr($post_id) ?>"
        data-status="<?= esc_attr($status) ?>"
        data-nonce="<?= esc_attr($nonce) ?>">
        <?= $status ? 'Remove from wishlist' : 'Add to wishlist' ?>
    </span>
<?php
}
add_shortcode('wishlist_button', 'wishListButton');


// Handle the AJAX request to update the wishlist
function handle_update_wishlist()
{
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    if (!isset($_POST['post_id']) || !isset($_POST['status']) || !is_user_logged_in()) {
        wp_send_json_error(['message' => 'Invalid request or not logged in']);
    }

    $post_id = intval($_POST['post_id']);
    $status = intval($_POST['status']);
    $user_id = get_current_user_id();

    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = [];
    }

    if ($status === 1 && !in_array($post_id, $wishlist)) {
        $wishlist[] = $post_id;
    } elseif ($status === 0) {
        $wishlist = array_diff($wishlist, [$post_id]);
    }

    if (empty($wishlist)) {
        delete_user_meta($user_id, 'user_wishlist');
    } else {
        update_user_meta($user_id, 'user_wishlist', $wishlist);
    }

    wp_send_json_success(['new_status' => $status]);
}

add_action('wp_ajax_update_wishlist', 'handle_update_wishlist');

// Enqueue the wishlist script
function enqueue_wishlist_script()
{
    wp_enqueue_script('wishlist-handler', get_template_directory_uri() . '/wish-list.js', ['jquery'], '1.0', true);
    wp_localize_script('wishlist-handler', 'custom_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wishlist_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_wishlist_script');

// Render the wishlist display
function display_wishlist()
{
    if (!is_user_logged_in()) {
        return '<p>Please <a href="' . esc_url(wp_login_url()) . '">log in</a> to view your wishlist.</p>';
    }

    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'user_wishlist', true);
    $nonce = wp_create_nonce('wishlist_nonce');
    if (empty($wishlist) || !is_array($wishlist)) {
        return '<p>Your wishlist is empty.</p>';
    }

    ob_start();
?>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <section class="container_swiper_wishlist">
        <div class="whishlist_perv ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <mask id="mask0_1118_601" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
                    <rect x="24" y="24" width="24" height="24" transform="rotate(-180 24 24)" fill="#D9D9D9" />
                </mask>
                <g mask="url(#mask0_1118_601)">
                    <path d="M9.59961 6L15.5996 12L9.59961 18L8.32461 16.725L13.0496 12L8.32461 7.275L9.59961 6Z" fill="#EF9E07" />
                </g>
            </svg>
        </div>
        <div class="swiper-container wishlist-swiper swiper col-12">
            <div class="swiper-wrapper">
                <?php foreach ($wishlist as $post_id): ?>
                    <?php if (get_post($post_id)): ?>
                        <div class="swiper-slide" data-id="<?= esc_attr($post_id) ?>">
                            <?php
                            // Get product object
                            $product = wc_get_product($post_id);
                            if ($product):
                                // Get product details
                                $thumbnail = $product->get_image([216, 216], ['class' => 'image_whishlist']); // Product thumbnail HTML
                                $price = $product->get_price_html(); // Formatted price
                                $regular_price = $product->get_regular_price(); // Regular price
                                $sale_price = $product->get_sale_price(); // Sale price
                                $url = get_permalink($post_id); // Product URL
                                $stock_status = $product->is_in_stock() ? true : false; // Stock status
                            ?>
                                <div class="wishlist-product">
                                    <button class="remove-from-wishlist"  data-nonce="<?=esc_attr($nonce)?>" data-id="<?= esc_attr($post_id) ?>">حذف
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                                            <mask id="mask0_1118_6173" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="25" height="24">
                                                <rect x="0.5" width="24" height="24" fill="#D9D9D9" />
                                            </mask>
                                            <g mask="url(#mask0_1118_6173)">
                                                <path d="M6.9 19L5.5 17.6L11.1 12L5.5 6.4L6.9 5L12.5 10.6L18.1 5L19.5 6.4L13.9 12L19.5 17.6L18.1 19L12.5 13.4L6.9 19Z" fill="#A91F02" />
                                            </g>
                                        </svg>
                                    </button>
                                    <div class="wishlist-product-thumbnail <?= $stock_status ? null : 'out_of_stock_wishlist' ?>">
                                        <a href="<?= esc_url($url) ?>"><?= $thumbnail ?></a>
                                    </div>
                                    <div class="wishlist-product-details">
                                        <div class="container_price_wishliste <?= $stock_status ? null : 'out_of_stock_wishlist' ?>">
                                            <h3><a href="<?= esc_url($url) ?>" class="line_clamp_1"><?= esc_html(get_the_title($post_id)) ?></a></h3>
                                            <p class="price_whishlist"><?= $price ?></p>
                                        </div>
                                        <?php if ($sale_price): ?>
                                            <!-- <p>Discount Price: <del><?= wc_price($regular_price) ?></del> <?= wc_price($sale_price) ?></p> -->
                                        <?php endif; ?>
                                        <?php
                                        if ($stock_status === false) {  ?>
                                            <p class="outofstock_text"><?= 'ناموجود' ?></p>
                                        <?php } elseif (true === $stock_status) {  ?>
                                            <a class="wishlist_product_view" href="<?= esc_url($url) ?>"><?= 'مشاهده محصول' ?></a>
                                        <?php } ?>
                                    </div>

                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="whishlist_next">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <mask id="mask0_1118_1265" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
                    <rect width="24" height="24" fill="#D9D9D9" />
                </mask>
                <g mask="url(#mask0_1118_1265)">
                    <path d="M14.4004 18L8.40039 12L14.4004 6L15.6754 7.275L10.9504 12L15.6754 16.725L14.4004 18Z" fill="#EF9E07" />
                </g>
            </svg>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.wishlist-swiper', {
                slidesPerView: 1, // Adjust number of visible slides
                spaceBetween: 10, // Space between slides
                navigation: {
                    nextEl: '.whishlist_next',
                    prevEl: '.whishlist_perv',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 12,
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                },
            });
        });
    </script>
<?php
    return ob_get_clean();
}


add_shortcode('wishlist_display', 'display_wishlist');


// 1. Add new endpoint for "Wish List"
function add_wishlist_endpoint()
{
    add_rewrite_endpoint('wish-list', EP_ROOT | EP_PAGES);
}
add_action('init', 'add_wishlist_endpoint');

// 2. Add the new query var
function wishlist_query_var($vars)
{
    $vars[] = 'wish-list';
    return $vars;
}
add_filter('query_vars', 'wishlist_query_var', 0);

// 3. Insert the new endpoint into the My Account menu
function wishlist_profile_page($items)
{
    $new_items = array();
    foreach ($items as $key => $value) {
        $new_items[$key] = $value; // اضافه کردن آیتم‌های پیش‌فرض
        if ($key === 'orders') { // بررسی تب سفارش‌ها
            $new_items['wish-list'] = __('علاقه‌مندی‌ها', 'your-text-domain'); // افزودن تب علاقه‌مندی‌ها
        }
    }

    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'wishlist_profile_page');

// 4. Add content to the new tab
function wishlist_content_tab()
{
    echo do_shortcode('[wishlist_display]'); // Replace with your desired shortcode or content
}
add_action('woocommerce_account_wish-list_endpoint', 'wishlist_content_tab');

// 5. Flush rewrite rules (only required once, after adding the new endpoint)
function wishlist_flush_rewrite_rules()
{
    add_wishlist_endpoint();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wishlist_flush_rewrite_rules');
