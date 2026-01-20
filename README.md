# WordPress Interactive Image Plugin

A lightweight, secure WordPress plugin that displays a floating, interactive image (GIF/PNG) on your site. It features smart click behaviors (redirect on single click, close on triple click) and GIF animation control.

## Features

- **Floating Image**: Fixed-position image overlay on your site.
- **Interactive Behaviors**:
  - **Single Click**: Opens a configured Redirect URL in a new tab.
  - **Triple Click**: Hides the image for the current browsing session (persisted via `sessionStorage`).
- **GIF Control**: Static on load, animates on first click (tech-dependent, works by reloading source).
- **Customizable**:
  - Image URL & Redirect URL
  - Size (50px - 500px)
  - Position (X / Y percentages)
  - Opacity & Z-Index
- **Secure**: Built with the WordPress Settings API, fully sanitized and escaped.

## Installation

1. Download the plugin folder `wp-floating-image`.
2. Upload the folder to your WordPress `wp-content/plugins/` directory.
   - Or zip the folder and upload via **Plugins > Add New > Upload Plugin**.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Go to **Settings > Interactive Image** to configure your image.

## Configuration

- **Image URL**: The full URL to your image (e.g., from your Media Library).
- **Redirect URL**: The destination URL when a user clicks the image.
- **Image Width**: Width in pixels.
- **Position**: Horizontal and vertical position in percentage (0-100%).
- **Opacity**: Transparency level (0.1 to 1.0).
- **Z-Index**: Stack order (default 999).

## License

MIT License.
