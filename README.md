# WooCommerce Wishlist Plugin

This is a simple and powerful plugin for creating a wishlist system in WooCommerce stores. The plugin allows users to add their favorite products to a wishlist and view them easily. By using the provided shortcodes, you can quickly display the "Add to Wishlist" button and also show the complete list of favorite products on different pages of your site.

## Features

- **Add products to wishlist**: Users can add their favorite products to the wishlist.
- **Display wishlist**: Easily display a list of all the products saved in the wishlist using a shortcode.
- **Simple to use**: Quick installation and setup for use on WooCommerce stores.
- **Customizable shortcodes**: Easily customize where the button and wishlist list are displayed across different pages of your site.

## Installation

1. Download the project from GitHub.
2. Upload the files to the `wp-content/themes/...your theme` directory on your WordPress site.
3. Add the following code to your `functions.php` file:

``` require_once get_template_directory() . '/whish-list.php'; ```
 

To display the "Add to Wishlist" button on different pages, use the following shortcode:
``[wishlist_button]``

To display the entire wishlist, use the following shortcode:
``[wishlist_display]``

Usage
Adding the Wishlist Button
To add the "Add to Wishlist" button anywhere on your page, simply insert the shortcode [wishlist_button] in the content or WordPress block editor. This button will allow users to add the selected product to the wishlist.

Displaying the Wishlist
To display a list of all the products added to the wishlist, insert the shortcode [wishlist_display] on any page where you want it to appear. The list will automatically show all the products saved in the wishlist.

Settings
This plugin does not require complex settings to function. All you need to do is insert the relevant shortcodes where you want the button and wishlist to be displayed.

Developers
This project is fully extendable and open for contributions. If you'd like to add new features or improve its functionality, we'd be happy to accept your contributions. Please feel free to create a pull request with your changes.

Support
For any issues or questions, please visit the GitHub issues page for this project or contact us directly.

License
This project is licensed under the MIT License. For more information on the license and terms of use, please refer to the LICENSE file.
