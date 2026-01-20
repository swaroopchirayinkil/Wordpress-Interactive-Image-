<?php
/**
 * Plugin Name: Interactive Image Display
 * Plugin URI:  https://github.com/swaroopchirayinkil/bludit-floating-image-v2
 * Description: Displays a floating interactive image with click behaviors (redirect, close) and animation control.
 * Version:     1.0.0
 * Author:      Antigravity
 * Text Domain: wp-floating-image
 * License:     MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WP_Floating_Image {

	/**
	 * Option name for storing settings.
	 */
	const OPTION_NAME = 'wp_floating_image_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_footer', array( $this, 'render_frontend' ) );
	}

	/**
	 * Add options page to the Settings menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Interactive Image Settings', 'wp-floating-image' ),
			__( 'Interactive Image', 'wp-floating-image' ),
			'manage_options',
			'wp-floating-image',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings and sanitization callback.
	 */
	public function register_settings() {
		register_setting(
			'wp_floating_image_group',
			self::OPTION_NAME,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'wp_floating_image_main_section',
			__( 'Configuration', 'wp-floating-image' ),
			null,
			'wp-floating-image'
		);

		$fields = array(
			'image_url'    => __( 'Image URL', 'wp-floating-image' ),
			'redirect_url' => __( 'Redirect URL', 'wp-floating-image' ),
			'image_size'   => __( 'Image Width (px)', 'wp-floating-image' ),
			'position_x'   => __( 'Horizontal Position (%)', 'wp-floating-image' ),
			'position_y'   => __( 'Vertical Position (%)', 'wp-floating-image' ),
			'z_index'      => __( 'Z-Index', 'wp-floating-image' ),
			'opacity'      => __( 'Opacity (0.1 - 1.0)', 'wp-floating-image' ),
		);

		foreach ( $fields as $id => $label ) {
			add_settings_field(
				$id,
				$label,
				array( $this, 'render_field' ),
				'wp-floating-image',
				'wp_floating_image_main_section',
				array( 'id' => $id )
			);
		}
	}

	/**
	 * Sanitize all settings.
	 * 
	 * @param array $input Raw input.
	 * @return array Sanitized input.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		
		$sanitized['image_url']    = esc_url_raw( $input['image_url'] );
		$sanitized['redirect_url'] = esc_url_raw( $input['redirect_url'] );
		$sanitized['image_size']   = absint( $input['image_size'] );
		$sanitized['position_x']   = absint( $input['position_x'] );
		$sanitized['position_y']   = absint( $input['position_y'] );
		$sanitized['z_index']      = absint( $input['z_index'] );
		$sanitized['opacity']      = floatval( $input['opacity'] );

		// Validate ranges
		if ( $sanitized['image_size'] < 50 ) $sanitized['image_size'] = 50;
		if ( $sanitized['image_size'] > 500 ) $sanitized['image_size'] = 500;
		
		if ( $sanitized['position_x'] > 100 ) $sanitized['position_x'] = 100;
		if ( $sanitized['position_y'] > 100 ) $sanitized['position_y'] = 100;
		
		if ( $sanitized['opacity'] < 0.1 ) $sanitized['opacity'] = 0.1;
		if ( $sanitized['opacity'] > 1.0 ) $sanitized['opacity'] = 1.0;

		return $sanitized;
	}

	/**
	 * Render individual settings field.
	 * 
	 * @param array $args Field arguments.
	 */
	public function render_field( $args ) {
		$options = get_option( self::OPTION_NAME );
		$id      = $args['id'];
		$value   = isset( $options[ $id ] ) ? $options[ $id ] : $this->get_default( $id );

		switch ( $id ) {
			case 'image_size':
			case 'position_x':
			case 'position_y':
			case 'z_index':
				echo '<input type="number" name="' . esc_attr( self::OPTION_NAME . '[' . $id . ']' ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
				break;
			case 'opacity':
				echo '<input type="number" step="0.1" min="0.1" max="1.0" name="' . esc_attr( self::OPTION_NAME . '[' . $id . ']' ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
				break;
			default:
				echo '<input type="text" name="' . esc_attr( self::OPTION_NAME . '[' . $id . ']' ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
		}
	}

	/**
	 * Get default value for a setting.
	 * 
	 * @param string $key Setting key.
	 * @return mixed Default value.
	 */
	private function get_default( $key ) {
		$defaults = array(
			'image_url'    => '',
			'redirect_url' => '',
			'image_size'   => 150,
			'position_x'   => 10,
			'position_y'   => 80,
			'z_index'      => 999,
			'opacity'      => 0.9,
		);
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	/**
	 * Render the settings page HTML.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_floating_image_group' );
				do_settings_sections( 'wp-floating-image' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the frontend HTML and JS.
	 */
	public function render_frontend() {
		$options = get_option( self::OPTION_NAME );
		
		// Defaults if not set
		$image_url    = isset( $options['image_url'] ) ? $options['image_url'] : '';
		$redirect_url = isset( $options['redirect_url'] ) ? $options['redirect_url'] : '';
		
		if ( empty( $image_url ) ) {
			return;
		}

		$width      = isset( $options['image_size'] ) ? intval( $options['image_size'] ) : 150;
		$pos_x      = isset( $options['position_x'] ) ? intval( $options['position_x'] ) : 10;
		$pos_y      = isset( $options['position_y'] ) ? intval( $options['position_y'] ) : 80;
		$z_index    = isset( $options['z_index'] ) ? intval( $options['z_index'] ) : 999;
		$opacity    = isset( $options['opacity'] ) ? floatval( $options['opacity'] ) : 0.9;
		
		$is_gif = ( strtolower( pathinfo( $image_url, PATHINFO_EXTENSION ) ) === 'gif' );
		
		// Escape all outputs for HTML
		$safe_image_url    = esc_url( $image_url );
		$safe_redirect_url = esc_url( $redirect_url );
		
		?>
		<div id="wp-floating-image-container" style="
			position: fixed;
			left: <?php echo intval( $pos_x ); ?>%;
			top: <?php echo intval( $pos_y ); ?>%;
			z-index: <?php echo intval( $z_index ); ?>;
			pointer-events: auto;
			cursor: pointer;
			transition: opacity 0.3s ease;
			display: block;
		">
			<img id="wp-floating-image" 
				src="<?php echo $safe_image_url; ?>" 
				data-animated-src="<?php echo $safe_image_url; ?>"
				data-is-gif="<?php echo $is_gif ? 'true' : 'false'; ?>"
				width="<?php echo intval( $width ); ?>"
				alt="Interactive Image" 
				style="
					width: <?php echo intval( $width ); ?>px;
					height: auto;
					opacity: <?php echo floatval( $opacity ); ?>;
					display: block;
					user-select: none;
					-webkit-user-drag: none;
				"
			/>
		</div>

		<script>
		(function() {
			var container = document.getElementById("wp-floating-image-container");
			var image = document.getElementById("wp-floating-image");

			if (!container || !image) return;

			// Check session storage
			if (sessionStorage.getItem("wpFloatingImageClosed") === "true") {
				container.style.display = "none";
				return;
			}

			var clickCount = 0;
			var clickTimer = null;
			var firstClickDone = false;
			var isGif = image.getAttribute("data-is-gif") === "true";
			var animatedSrc = image.getAttribute("data-animated-src");
			var redirectUrl = "<?php echo esc_js( $safe_redirect_url ); ?>";
			var defaultOpacity = "<?php echo floatval( $opacity ); ?>";

			// Freeze GIF on load by adding timestamp
			if (isGif && !firstClickDone) {
				var staticUrl = animatedSrc + (animatedSrc.indexOf("?") > -1 ? "&" : "?") + "t=" + Date.now();
				image.src = staticUrl;
			}

			container.addEventListener("click", function(e) {
				e.preventDefault();
				clickCount++;

				if (clickTimer) {
					clearTimeout(clickTimer);
				}

				clickTimer = setTimeout(function() {
					if (clickCount >= 3) {
						// Triple Click: Close
						container.style.opacity = "0";
						setTimeout(function() {
							container.style.display = "none";
							sessionStorage.setItem("wpFloatingImageClosed", "true");
						}, 300);
					} else if (clickCount === 1) {
						// Single Click: Animate & Redirect
						if (!firstClickDone) {
							firstClickDone = true;
							if (isGif) {
								image.src = animatedSrc;
							}
						}
						
						if (redirectUrl) {
							window.open(redirectUrl, "_blank");
						}
					}
					clickCount = 0;
				}, 400);
			});

			container.addEventListener("mouseenter", function() {
				image.style.opacity = "1";
			});

			container.addEventListener("mouseleave", function() {
				image.style.opacity = defaultOpacity;
			});
		})();
		</script>
		<?php
	}
}

new WP_Floating_Image();
