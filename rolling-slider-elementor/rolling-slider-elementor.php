<?php
/**
 * Plugin Name:       اسلایدر رولینگ برای المنتور
 * Description:       ویجت اسلایدر تمام‌عرض (تصویر / ویدیو) با تب‌های ناوبری، متن‌های قابل ویرایش، دکمه، افکت‌های ورود و پشتیبانی کامل از راست‌چین/چپ‌چین بر اساس هسته وردپرس.
 * Version:           1.0.0 
 * Author: Saeed Amini
 * Author URI: https://github.com/saeedamini144
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Rolling Slider
 * Text Domain:       rolling-slider-elementor
 * Domain Path:       /languages
 * Elementor tested up to: 3.25
 *
 * @package Rolling_Slider
 */

defined( 'ABSPATH' ) || exit;

define( 'RSL_VERSION', '1.0.0' );
define( 'RSL_FILE', __FILE__ );
define( 'RSL_PATH', plugin_dir_path( __FILE__ ) );
define( 'RSL_URL', plugin_dir_url( __FILE__ ) );

/**
 * بارگذاری‌کنندهٔ پلاگین؛ فقط وقتی المنتور فعال و به‌روز باشد ویجت را ثبت می‌کند.
 */
final class Rolling_Slider_Plugin {

	const MIN_ELEMENTOR = '3.5.0';
	const MIN_PHP       = '7.4';

	/** @var Rolling_Slider_Plugin|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {
		load_plugin_textdomain( 'rolling-slider-elementor', false, dirname( plugin_basename( RSL_FILE ) ) . '/languages' );

		if ( version_compare( PHP_VERSION, self::MIN_PHP, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_php' ) );
			return;
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_elementor' ) );
			return;
		}

		if ( ! version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_elementor_version' ) );
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * فایل‌ها فقط «ثبت» می‌شوند؛ المنتور با get_style_depends / get_script_depends
	 * آن‌ها را تنها در صفحه‌ای که ویجت دارد بارگذاری می‌کند.
	 */
	public function register_styles() {
		wp_register_style( 'rolling-slider', RSL_URL . 'assets/css/rolling-slider.css', array(), RSL_VERSION );
	}

	public function register_scripts() {
		wp_register_script( 'rolling-slider', RSL_URL . 'assets/js/rolling-slider.js', array( 'jquery', 'elementor-frontend' ), RSL_VERSION, true );
	}

	/**
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( $widgets_manager ) {
		require_once RSL_PATH . 'includes/class-widget.php';
		$widgets_manager->register( new \Rolling_Slider\Widget() );
	}

	public function notice_php() {
		$this->notice(
			sprintf(
				/* translators: 1: required PHP version */
				esc_html__( 'پلاگین «اسلایدر رولینگ» به PHP نسخهٔ %1$s یا بالاتر نیاز دارد.', 'rolling-slider-elementor' ),
				self::MIN_PHP
			)
		);
	}

	public function notice_missing_elementor() {
		$this->notice( esc_html__( 'پلاگین «اسلایدر رولینگ» برای کار کردن به افزونهٔ المنتور (Elementor) نیاز دارد. لطفاً آن را نصب و فعال کنید.', 'rolling-slider-elementor' ) );
	}

	public function notice_elementor_version() {
		$this->notice(
			sprintf(
				/* translators: 1: required Elementor version */
				esc_html__( 'پلاگین «اسلایدر رولینگ» به المنتور نسخهٔ %1$s یا بالاتر نیاز دارد. لطفاً المنتور را به‌روزرسانی کنید.', 'rolling-slider-elementor' ),
				self::MIN_ELEMENTOR
			)
		);
	}

	private function notice( $message ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

Rolling_Slider_Plugin::instance();
