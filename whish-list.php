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

    if (empty($wishlist) || !is_array($wishlist)) {
        return '<p>Your wishlist is empty.</p>';
    }

    ob_start();
?>
    <ul id="wishlist-container">
        <?php foreach ($wishlist as $post_id): ?>
            <?php if (get_post($post_id)): ?>
                <li data-id="<?= esc_attr($post_id) ?>" class="wishlist-item">
                    <?php
                    // Get product object
                    $product = wc_get_product($post_id);
                    if ($product):
                        // Get product details
                        $thumbnail = $product->get_image(); // Product thumbnail HTML
                        $price = $product->get_price_html(); // Formatted price
                        $regular_price = $product->get_regular_price(); // Regular price
                        $sale_price = $product->get_sale_price(); // Sale price
                        $url = get_permalink($post_id); // Product URL
                        $stock_status = $product->is_in_stock() ? 'In Stock' : 'Out of Stock'; // Stock status
                    ?>
                        <!-- Display product details -->
                        <div class="wishlist-product-thumbnail">
                            <a href="<?= esc_url($url) ?>"><?= $thumbnail ?></a>
                        </div>
                        <div class="wishlist-product-details">
                            <h3><a href="<?= esc_url($url) ?>"><?= esc_html(get_the_title($post_id)) ?></a></h3>
                            <p>Price: <?= $price ?></p>
                            <?php if ($sale_price): ?>
                                <p>Discount Price: <del><?= wc_price($regular_price) ?></del> <?= wc_price($sale_price) ?></p>
                            <?php endif; ?>
                            <p>Stock Status: <?= esc_html($stock_status) ?></p>
                        </div>
                        <button class="remove-from-wishlist" data-id="<?= esc_attr($post_id) ?>">Remove</button>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
<?php
    return ob_get_clean();
}

add_shortcode('wishlist_display', 'display_wishlist');



function add_wishlist_endpoint()
{
    add_rewrite_endpoint('wish-list', EP_ROOT | EP_PAGES);
}

add_action('init', 'add_wishlist_endpoint');

// ------------------
// 2. Add new query var

function wishlist_query_var($vars)
{
    $vars[] = 'wish-list';
    return $vars;
}

add_filter('query_vars', 'wishlist_query_var', 0);

// ------------------
// 3. Insert the new endpoint into the My Account menu

function wishlist_profile_page($items)
{
    $items['whish list'] = 'علاقه مندی ها';
    return $items;
}

add_filter('woocommerce_account_menu_items', 'wishlist_profile_page');

// ------------------
// 4. Add content to the new tab

function whishlist_content_tab()
{
    echo do_shortcode('[wishlist_display]');
}

add_action('woocommerce_account_premium-support_endpoint', 'whishlist_content_tab');
 