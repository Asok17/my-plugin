<?php
/**
 * My Plugin
 *
 * @package           My_Plugin
 * @author            Ashok Lama
 * @copyright         2024 Ashok Lama
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       My Plugin
 * Plugin URI:        https://my-plugin.com
 * Description:       Simple plugin to create post with front-end submission.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ashok Lama
 * Author URI:        https://ashoklama.com
 * Text Domain:       my-plugin
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

add_shortcode( 'my_plugin_shortcode', 'my_plugin_shortcode_callback' );

/**
 * Custom shortcode callback function.
 *
 * @since 1.0.0
 */
function my_plugin_shortcode_callback() {
	ob_start();
	?>
	<form id="my-plugin-form" method="POST">
		<?php
		if ( get_option( 'my_plugin_form_message', '' ) ) {
			?>
			<div class="form-row">
				<p><?php echo esc_html( get_option( 'my_plugin_form_message', '' ) ); ?></p>
			</div>
			<?php
			delete_option( 'my_plugin_form_message' );
		}
		?>

		<div class="form-row">
			<p id="form-message"></p>
		</div>
		<input type="hidden" name="my-post-form-action" id="my-post-form-action" value="my-post-form-submit" />
		<input type="hidden" name="my-post-wp-nonce" id="my-post-wp-nonce" value="<?php echo esc_attr( wp_create_nonce( 'my_plugin_nonce' ) ); ?>" />
		<div class="form-row">
			<label for="my-post-title">Title</label>
			<input id="my-post-title" class="my-form-input-text" name="my-post-title" />
		</div>
		<div class="form-row">
			<label for="my-post-content">Content</label>
			<textarea id="my-post-content" class="my-form-input-textarea" name="my-post-content" rows="15"></textarea>
		</div>
		<div class="form-row">
			<input type="submit" name="my-post-submit" id="my-post-submit" class="button" value="Submit">
		</div>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Load CSS and JS files.
 *
 * @since 1.0.0
 */
function my_plugin_assets() {

	wp_enqueue_style(
		'my-plugin-style',
		plugin_dir_url( __FILE__ ) . 'public/css/my-plugin.css',
		array(),
		'1.0.0',
		'all'
	);

	wp_enqueue_script(
		'my-plugin-javascript',
		plugin_dir_url( __FILE__ ) . 'public/js/my-plugin-javascript.js',
		array(),
		'1.0.0',
		true
	);

	// wp_enqueue_script(
	// 	'my-plugin-script',
	// 	plugin_dir_url( __FILE__ ) . 'public/js/my-plugin.js',
	// 	array( 'jquery' ),
	// 	'1.0.0',
	// 	true
	// );

	// wp_localize_script( 'my-plugin-script', 'myPluginScriptObj', array( 'ajaxURL' => esc_url( admin_url( 'admin-ajax.php' ) ) ) );
}
add_action( 'wp_enqueue_scripts', 'my_plugin_assets' );


/**
 * Handles form submission.
 *
 * @since 1.0.0
 */
function my_plugin_form_submission_handler() {

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {

		if (
			isset( $_POST['my-post-form-action'] ) &&
			'my-post-form-submit' === sanitize_text_field( wp_unslash( $_POST['my-post-form-action'] ) )
		) {

			$nonce = isset( $_POST['my-post-wp-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['my-post-wp-nonce'] ) ) : '';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'my_plugin_nonce' ) ) {
				update_option( 'my_plugin_form_message', 'Error! Invalid security token.' );
				return;
			}

			$post_title = isset( $_POST['my-post-title'] ) ? sanitize_text_field( wp_unslash( $_POST['my-post-title'] ) ) : '';

			if ( ! $post_title ) {
				update_option( 'my_plugin_form_message', 'Error! Empty post title.' );
				return;
			}

			$post_content = isset( $_POST['my-post-content'] ) ? sanitize_text_field( wp_unslash( $_POST['my-post-content'] ) ) : '';

			if ( ! $post_content ) {
				update_option( 'my_plugin_form_message', 'Error! Empty post content.' );
				return;
			}

			$insert_post = wp_insert_post(
				array(
					'post_title'   => $post_title,
					'post_content' => $post_content,
				)
			);

			if ( 0 === $insert_post || $insert_post instanceof WP_Error ) {
				update_option( 'my_plugin_form_message', 'Error! Creating post.' );
				return;
			}

			update_option( 'my_plugin_form_message', 'Success! Your post has been submitted.' );
		}
	}
}
add_action( 'init', 'my_plugin_form_submission_handler' );


/**
 * Handles form submission.
 *
 * @since 1.0.0
 */
function my_plugin_form_submission_ajax_handler() {

	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'my_plugin_nonce' ) ) {

		wp_send_json(
			array(
				'success' => false,
				'message' => 'Error! Invalid security token.',
			)
		);
	}

	$post_title = isset( $_POST['postTitle'] ) ? sanitize_text_field( wp_unslash( $_POST['postTitle'] ) ) : '';

	if ( ! $post_title ) {
		wp_send_json(
			array(
				'success' => false,
				'message' => 'Error! Empty post title.',
			)
		);
	}

	$post_content = isset( $_POST['postContent'] ) ? sanitize_text_field( wp_unslash( $_POST['postContent'] ) ) : '';

	if ( ! $post_content ) {
		wp_send_json(
			array(
				'success' => false,
				'message' => 'Error! Empty post content.',
			)
		);
	}

	$insert_post = wp_insert_post(
		array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
		)
	);

	if ( 0 === $insert_post || $insert_post instanceof WP_Error ) {
		wp_send_json(
			array(
				'success' => false,
				'message' => 'Error! Creating post.',
			)
		);
	}

	wp_send_json(
		array(
			'success' => true,
			'message' => 'Success! Your post has been submitted.',
		)
	);
}
add_action( 'wp_ajax_nopriv_post_submission_action', 'my_plugin_form_submission_ajax_handler' );
add_action( 'wp_ajax_post_submission_action', 'my_plugin_form_submission_ajax_handler' );



if ( ! function_exists( 'my_plugin_rest_init' ) ) {

	function my_plugin_rest_init() {

		register_rest_route(
			'myplugin/v1',
			'/create-post',
			array(
				'methods'             => 'POST',
				'callback'            => 'my_plugin_create_post',
				'permission_callback' => '__return_true',
			)
		);
	}

	add_action( 'rest_api_init', 'my_plugin_rest_init' );
}


function my_plugin_create_post( WP_REST_Request $request ) {

	$params = $request->get_params();

	$post_title = isset( $params['postTitle'] ) ? sanitize_text_field( wp_unslash( $params['postTitle'] ) ) : '';

	if ( ! $post_title ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => 'Error! Empty post title.',
			)
		);
	}

	$post_content = isset( $params['postContent'] ) ? sanitize_text_field( wp_unslash( $params['postContent'] ) ) : '';

	if ( ! $post_content ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => 'Error! Empty post content.',
			)
		);
	}

	$insert_post = wp_insert_post(
		array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
		)
	);

	if ( 0 === $insert_post || $insert_post instanceof WP_Error ) {
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => 'Error! Creating post.',
			)
		);
	}

	return rest_ensure_response(
		array(
			'success' => true,
			'message' => 'Success! Your post has been submitted.',
		)
	);
}
