<?php
/**
 * ویجت المنتور: اسلایدر رولینگ
 *
 * @package Rolling_Slider
 */

namespace Rolling_Slider;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Widget extends Widget_Base {

	/** افکت‌های مجاز ورود متن */
	const FX = array( 'none', 'fade', 'fade-up', 'fade-down', 'slide-start', 'slide-end', 'zoom-in', 'zoom-out', 'blur', 'flip', 'reveal' );

	/** افکت‌های مجاز تغییر اسلاید */
	const TRANSITIONS = array( 'cover', 'slide', 'slide-up', 'wipe', 'wipe-up', 'circle', 'diamond', 'cinematic', 'rotate', 'flip', 'zoom', 'blur', 'fade', 'none' );

	public function get_name() {
		return 'rolling_slider';
	}

	public function get_title() {
		return esc_html__( 'Rolling Slider', 'rolling-slider-elementor' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'slider', 'hero', 'rolling', 'video', 'tabs', 'اسلایدر', 'اسلاید', 'هیرو', 'ویدیو' );
	}

	public function get_style_depends() {
		return array( 'rolling-slider' );
	}

	public function get_script_depends() {
		return array( 'rolling-slider' );
	}

	/** بدون div اضافهٔ .elementor-widget-container (DOM سبک‌تر) */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/* ======================================================================
	 * کنترل‌ها
	 * ==================================================================== */

	protected function register_controls() {
		$this->controls_slides();
		$this->controls_settings();
		$this->controls_markup();

		$this->style_box();
		$this->style_media();
		$this->style_eyebrow();
		$this->style_title();
		$this->style_content();
		$this->style_buttons();
		$this->style_tabs();
	}

	/* ── محتوا: اسلایدها ───────────────────────────────────────────────── */

	private function controls_slides() {
		$this->start_controls_section(
			'section_slides',
			array(
				'label' => esc_html__( 'Slides', 'rolling-slider-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'tab_title',
			array(
				'label'       => esc_html__( 'Navigation tab title', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'New slide', 'rolling-slider-elementor' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'slide_transition',
			array(
				'label'       => esc_html__( 'Transition into this slide', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'The effect played when this slide comes in. "Default" uses the effect from the slider settings.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array( '' => esc_html__( 'Default (from slider settings)', 'rolling-slider-elementor' ) ) + $this->transition_options(),
				'default'     => '',
				'label_block' => true,
			)
		);

		$repeater->start_controls_tabs( 'slide_tabs' );

		/* تصاویر */
		$repeater->start_controls_tab( 'slide_tab_media', array( 'label' => esc_html__( 'Media', 'rolling-slider-elementor' ) ) );

		$repeater->add_control(
			'media_type',
			array(
				'label'   => esc_html__( 'Media type', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'toggle'  => false,
				'default' => 'image',
				'options' => array(
					'image' => array(
						'title' => esc_html__( 'Image', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-image',
					),
					'video' => array(
						'title' => esc_html__( 'Video', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-video-camera',
					),
				),
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'     => esc_html__( 'Image', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array( 'url' => Utils::get_placeholder_image_src() ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'media_type' => 'image' ),
			)
		);

		$repeater->add_control(
			'video',
			array(
				'label'       => esc_html__( 'Video (MP4 / WebM)', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);

		$repeater->add_control(
			'video_mobile',
			array(
				'label'       => esc_html__( 'Lightweight mobile video (optional)', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'If set, it plays instead of the main video on small screens to save data.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);

		$repeater->add_control(
			'poster',
			array(
				'label'       => esc_html__( 'Video poster', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Shown until the video loads (or when Data Saver is on).', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);

		$media_sel = '{{WRAPPER}} {{CURRENT_ITEM}} .rsl__media';

		$repeater->add_control(
			'object_position_heading',
			array(
				'label'     => esc_html__( 'Image focal point', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_responsive_control(
			'object_position',
			array(
				'label'                => esc_html__( 'Position', 'rolling-slider-elementor' ),
				'description'          => esc_html__( 'Which part of the image/video stays in frame. Can be set separately for each device.', 'rolling-slider-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'options'              => array(
					''              => esc_html__( 'Center (default)', 'rolling-slider-elementor' ),
					'left top'      => esc_html__( 'Top left', 'rolling-slider-elementor' ),
					'center top'    => esc_html__( 'Top center', 'rolling-slider-elementor' ),
					'right top'     => esc_html__( 'Top right', 'rolling-slider-elementor' ),
					'left center'   => esc_html__( 'Center left', 'rolling-slider-elementor' ),
					'center center' => esc_html__( 'Center', 'rolling-slider-elementor' ),
					'right center'  => esc_html__( 'Center right', 'rolling-slider-elementor' ),
					'left bottom'   => esc_html__( 'Bottom left', 'rolling-slider-elementor' ),
					'center bottom' => esc_html__( 'Bottom center', 'rolling-slider-elementor' ),
					'right bottom'  => esc_html__( 'Bottom right', 'rolling-slider-elementor' ),
					'custom'        => esc_html__( 'Custom (percent)…', 'rolling-slider-elementor' ),
				),
				'default'              => '',
				'selectors_dictionary' => array( 'custom' => '50% 50%' ),
				'selectors'            => array( $media_sel => 'object-position: {{VALUE}};' ),
			)
		);

		foreach ( array(
			'x' => esc_html__( 'Horizontal position (%)', 'rolling-slider-elementor' ),
			'y' => esc_html__( 'Vertical position (%)', 'rolling-slider-elementor' ),
		) as $axis => $label ) {
			$repeater->add_responsive_control(
				"object_pos_{$axis}",
				array(
					'label'      => $label,
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( '%' ),
					'range'      => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
					'default'    => array( 'size' => 50, 'unit' => '%' ),
					'condition'  => array( 'object_position' => 'custom' ),
					'selectors'  => array(
						$media_sel => 'object-position: {{object_pos_x.SIZE}}{{object_pos_x.UNIT}} {{object_pos_y.SIZE}}{{object_pos_y.UNIT}};',
					),
				)
			);
		}

		$repeater->end_controls_tab();

		/* متن */
		$repeater->start_controls_tab( 'slide_tab_text', array( 'label' => esc_html__( 'Text', 'rolling-slider-elementor' ) ) );

		$repeater->add_control(
			'eyebrow',
			array(
				'label'       => esc_html__( 'Eyebrow', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'eyebrow_tag',
			array(
				'label'     => esc_html__( 'Eyebrow HTML tag', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->html_tags(),
				'default'   => 'span',
				'condition' => array( 'eyebrow!' => '' ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Use <b>…</b> for the accent color and <br> for a line break.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'title_tag',
			array(
				'label'       => esc_html__( 'Title HTML tag', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'For SEO, only the first slide set to H1 keeps it; any other H1 is output as H2.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->html_tags(),
				'default'     => 'h2',
				'condition'   => array( 'title!' => '' ),
			)
		);

		$repeater->add_control(
			'content',
			array(
				'label'   => esc_html__( 'Content', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::WYSIWYG,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->end_controls_tab();

		/* دکمه‌ها */
		$repeater->start_controls_tab( 'slide_tab_buttons', array( 'label' => esc_html__( 'Buttons', 'rolling-slider-elementor' ) ) );

		foreach ( array( 1, 2 ) as $n ) {
			$show = "btn{$n}_show";

			$repeater->add_control(
				"btn{$n}_heading",
				array(
					/* translators: %d: button number */
					'label'     => sprintf( esc_html__( 'Button %d', 'rolling-slider-elementor' ), $n ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 2 === $n ? 'before' : 'none',
				)
			);

			$repeater->add_control(
				$show,
				array(
					'label'        => esc_html__( 'Show button', 'rolling-slider-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$repeater->add_control(
				"btn{$n}_text",
				array(
					'label'       => esc_html__( 'Button text', 'rolling-slider-elementor' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array( 'active' => true ),
					'condition'   => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_link",
				array(
					'label'       => esc_html__( 'Link', 'rolling-slider-elementor' ),
					'type'        => Controls_Manager::URL,
					'label_block' => true,
					'dynamic'     => array( 'active' => true ),
					'condition'   => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_style",
				array(
					'label'     => esc_html__( 'Button style', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'primary' => esc_html__( 'Primary (solid)', 'rolling-slider-elementor' ),
						'ghost'   => esc_html__( 'Secondary (outline)', 'rolling-slider-elementor' ),
					),
					'default'   => 1 === $n ? 'primary' : 'ghost',
					'condition' => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_icon",
				array(
					'label'       => esc_html__( 'Icon (from the icon library)', 'rolling-slider-elementor' ),
					'description' => esc_html__( 'Any icon from the Elementor library (Font Awesome, …) or your own SVG. Clear it to remove the icon.', 'rolling-slider-elementor' ),
					'type'        => Controls_Manager::ICONS,
					'default'     => array(
						'value'   => 1 === $n ? ( is_rtl() ? 'fas fa-arrow-left' : 'fas fa-arrow-right' ) : 'fas fa-download',
						'library' => 'fa-solid',
					),
					'condition'   => array( $show => 'yes' ),
				)
			);
		}

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$this->add_control(
			'slides',
			array(
				'label'       => esc_html__( 'Slides', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $this->default_slides(),
				'title_field' => '{{{ tab_title }}}',
				'prevent_empty' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * محتوای پیش‌فرض: دو اسلاید نمونه با متن Lorem Ipsum و لینک #
	 */
	private function default_slides() {
		$up   = 'https://keyhanrolling.com/wp-content/uploads/2026/09/';
		$link = static function ( $url, $external = false ) {
			return array(
				'url'         => $url,
				'is_external' => $external ? 'on' : '',
				'nofollow'    => '',
			);
		};

		$arrow = array(
			'value'   => is_rtl() ? 'fas fa-arrow-left' : 'fas fa-arrow-right',
			'library' => 'fa-solid',
		);

		return array(
			array(
				'_id'                => 'rsl0001',
				'tab_title'          => 'Lorem ipsum',
				'media_type'         => 'video',
				'video'              => array( 'url' => $up . '4a165a2bc05e40dd94da422330188a25-1.mp4' ),
				'video_mobile'       => array( 'url' => $up . '4a165a2bc05e40dd94da422330188a25-1.mp4' ),
				'poster'             => array( 'url' => $up . 'video-hero-poster.jpg' ),
				'eyebrow'            => 'Lorem ipsum dolor',
				'title'              => 'Lorem ipsum dolor sit amet <br>consectetur adipiscing',
				'content'            => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
				'btn1_show'          => 'yes',
				'btn1_text'          => 'Lorem ipsum',
				'btn1_link'          => $link( '#' ),
				'btn1_style'         => 'primary',
				'btn1_icon'          => $arrow,
				'btn2_show'          => 'yes',
				'btn2_text'          => 'Dolor sit amet',
				'btn2_link'          => $link( '#' ),
				'btn2_style'         => 'ghost',
				'btn2_icon'          => array(
					'value'   => 'fas fa-download',
					'library' => 'fa-solid',
				),
			),
			array(
				'_id'              => 'rsl0002',
				'tab_title'        => 'Dolor sit amet',
				'media_type'       => 'image',
				'image'            => array( 'url' => $up . 'banner1-2.webp' ),
				'eyebrow'          => 'Consectetur elit',
				'title'            => 'Sed do eiusmod tempor incididunt ut labore',
				'content'          => '<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
				'btn1_show'        => 'yes',
				'btn1_text'        => 'Ut enim ad minim',
				'btn1_link'        => $link( '#' ),
				'btn1_style'       => 'primary',
				'btn1_icon'        => $arrow,
			),
		);
	}

	/* ── محتوا: تنظیمات ────────────────────────────────────────────────── */

	private function controls_settings() {
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'Slider settings', 'rolling-slider-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'Autoplay', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'autoplay_duration',
			array(
				'label'     => esc_html__( 'Time per slide (ms)', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 2000,
				'max'       => 30000,
				'step'      => 500,
				'default'   => 7000,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => esc_html__( 'Pause on hover', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'enable_swipe',
			array(
				'label'        => esc_html__( 'Swipe on touch devices', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_tabs',
			array(
				'label'        => esc_html__( 'Show navigation tabs', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'transition',
			array(
				'label'       => esc_html__( 'Default slide transition', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Each slide can override this in the "Slides" section. Wipe and slide effects look best at 1000–1400 ms.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'separator'   => 'before',
				'options'     => $this->transition_options(),
				'default'     => 'cover',
			)
		);

		$this->add_control(
			'transition_speed',
			array(
				'label'      => esc_html__( 'Transition speed (ms)', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 100, 'max' => 3000, 'step' => 50 ) ),
				'default'    => array( 'size' => 1100, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-speed: {{SIZE}}ms;' ),
			)
		);

		$this->add_control(
			'kenburns',
			array(
				'label'        => esc_html__( 'Slow image zoom (Ken Burns)', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'       => esc_html__( 'Widget direction', 'rolling-slider-elementor' ),
				'description' => esc_html__( '"Auto" follows the WordPress site language (RTL or LTR). Change it only to override this widget.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'separator'   => 'before',
				'options'     => array(
					'auto' => esc_html__( 'Auto (from WordPress)', 'rolling-slider-elementor' ),
					'rtl'  => esc_html__( 'Right to left (RTL)', 'rolling-slider-elementor' ),
					'ltr'  => esc_html__( 'Left to right (LTR)', 'rolling-slider-elementor' ),
				),
				'default'     => 'auto',
			)
		);

		$this->add_control(
			'aria_label',
			array(
				'label'       => esc_html__( 'Accessible label', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Featured slider', 'rolling-slider-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'editor_slide',
			array(
				'label'       => esc_html__( 'Slide shown in the editor', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Applies only inside the Elementor editor so you can preview a given slide. It has no effect on the live site.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'step'        => 1,
				'default'     => 1,
				'separator'   => 'before',
			)
		);

		$this->end_controls_section();
	}

	/* ── محتوا: تگ‌ها ──────────────────────────────────────────────────── */

	private function controls_markup() {
		$this->start_controls_section(
			'section_markup',
			array(
				'label' => esc_html__( 'Image size', 'rolling-slider-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'       => esc_html__( 'Image size', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'The browser picks the best fit from the available sizes (srcset).', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->image_size_options(),
				'default'     => 'full',
			)
		);

		$this->end_controls_section();
	}

	private function html_tags() {
		return array(
			'h1'   => 'H1',
			'h2'   => 'H2',
			'h3'   => 'H3',
			'h4'   => 'H4',
			'h5'   => 'H5',
			'h6'   => 'H6',
			'div'  => 'div',
			'span' => 'span',
			'p'    => 'p',
		);
	}

	/** افکت‌های تغییر اسلاید (کلیدها باید با self::TRANSITIONS و CSS یکی باشند) */
	private function transition_options() {
		return array(
			'cover'     => esc_html__( 'Cover', 'rolling-slider-elementor' ),
			'slide'     => esc_html__( 'Horizontal slide + parallax', 'rolling-slider-elementor' ),
			'slide-up'  => esc_html__( 'Vertical slide', 'rolling-slider-elementor' ),
			'wipe'      => esc_html__( 'Horizontal wipe', 'rolling-slider-elementor' ),
			'wipe-up'   => esc_html__( 'Vertical wipe', 'rolling-slider-elementor' ),
			'circle'    => esc_html__( 'Expanding circle (iris)', 'rolling-slider-elementor' ),
			'diamond'   => esc_html__( 'Expanding diamond', 'rolling-slider-elementor' ),
			'cinematic' => esc_html__( 'Cinematic (zoom + blur + light)', 'rolling-slider-elementor' ),
			'rotate'    => esc_html__( 'Rotate + zoom', 'rolling-slider-elementor' ),
			'flip'      => esc_html__( '3D flip', 'rolling-slider-elementor' ),
			'zoom'      => esc_html__( 'Fade + zoom', 'rolling-slider-elementor' ),
			'blur'      => esc_html__( 'Fade + blur', 'rolling-slider-elementor' ),
			'fade'      => esc_html__( 'Crossfade', 'rolling-slider-elementor' ),
			'none'      => esc_html__( 'None (instant)', 'rolling-slider-elementor' ),
		);
	}

	private function image_size_options() {
		$options = array();
		foreach ( get_intermediate_image_sizes() as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
		}
		$options['full'] = esc_html__( 'Full size', 'rolling-slider-elementor' );
		return $options;
	}

	/* ======================================================================
	 * کمکی‌های کنترل
	 * ==================================================================== */

	/** گزینه‌های راست‌چین/وسط‌چین/چپ‌چین که با جهت وردپرس آیکنشان جابه‌جا می‌شود */
	private function align_options() {
		$start = is_rtl() ? 'right' : 'left';
		$end   = is_rtl() ? 'left' : 'right';

		return array(
			'start'  => array(
				'title' => esc_html__( 'Start (right in RTL, left in LTR)', 'rolling-slider-elementor' ),
				'icon'  => 'eicon-text-align-' . $start,
			),
			'center' => array(
				'title' => esc_html__( 'Center', 'rolling-slider-elementor' ),
				'icon'  => 'eicon-text-align-center',
			),
			'end'    => array(
				'title' => esc_html__( 'End (left in RTL, right in LTR)', 'rolling-slider-elementor' ),
				'icon'  => 'eicon-text-align-' . $end,
			),
		);
	}

	/**
	 * تراز یک بخش. mode=block: متن + جای بلوک (برای عنصرهای با max-width)؛ mode=flex: ردیف دکمه‌ها
	 */
	private function add_align( $name, $selector, $mode = 'block' ) {
		if ( 'flex' === $mode ) {
			$dictionary = array(
				'start'  => 'justify-content: flex-start;',
				'center' => 'justify-content: center;',
				'end'    => 'justify-content: flex-end;',
			);
		} else {
			$dictionary = array(
				'start'  => 'text-align: start; margin-inline: 0 auto;',
				'center' => 'text-align: center; margin-inline: auto;',
				'end'    => 'text-align: end; margin-inline: auto 0;',
			);
		}

		$this->add_responsive_control(
			$name,
			array(
				'label'                => esc_html__( 'Alignment', 'rolling-slider-elementor' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => $this->align_options(),
				'default'              => 'start',
				'selectors_dictionary' => $dictionary,
				'selectors'            => array( $selector => '{{VALUE}}' ),
			)
		);
	}

	/** افکت ورود + مدت + تأخیر یک بخش */
	private function add_fx( $prefix, $selector, $delay ) {
		$this->add_control(
			"{$prefix}_fx",
			array(
				'label'   => esc_html__( 'Entrance effect', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'none'        => esc_html__( 'None', 'rolling-slider-elementor' ),
					'fade'        => esc_html__( 'Fade', 'rolling-slider-elementor' ),
					'fade-up'     => esc_html__( 'Fade up', 'rolling-slider-elementor' ),
					'fade-down'   => esc_html__( 'Fade down', 'rolling-slider-elementor' ),
					'slide-start' => esc_html__( 'Slide from start', 'rolling-slider-elementor' ),
					'slide-end'   => esc_html__( 'Slide from end', 'rolling-slider-elementor' ),
					'zoom-in'     => esc_html__( 'Zoom in', 'rolling-slider-elementor' ),
					'zoom-out'    => esc_html__( 'Zoom out', 'rolling-slider-elementor' ),
					'blur'        => esc_html__( 'Unblur', 'rolling-slider-elementor' ),
					'flip'        => esc_html__( '3D flip', 'rolling-slider-elementor' ),
					'reveal'      => esc_html__( 'Reveal', 'rolling-slider-elementor' ),
				),
				'default' => 'fade-up',
			)
		);

		$this->add_control(
			"{$prefix}_fx_dist",
			array(
				'label'      => esc_html__( 'Travel distance', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 4, 'max' => 120 ) ),
				'default'    => array( 'size' => 14, 'unit' => 'px' ),
				'condition'  => array( "{$prefix}_fx" => array( 'fade-up', 'fade-down', 'slide-start', 'slide-end' ) ),
				'selectors'  => array( $selector => '--rsl-fx-dist: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			"{$prefix}_fx_dur",
			array(
				'label'      => esc_html__( 'Effect duration (ms)', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 100, 'max' => 2500, 'step' => 50 ) ),
				'default'    => array( 'size' => 500, 'unit' => 'px' ),
				'condition'  => array( "{$prefix}_fx!" => 'none' ),
				'selectors'  => array( $selector => '--rsl-dur: {{SIZE}}ms;' ),
			)
		);

		$this->add_control(
			"{$prefix}_fx_delay",
			array(
				'label'      => esc_html__( 'Delay (ms)', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 2500, 'step' => 20 ) ),
				'default'    => array( 'size' => $delay, 'unit' => 'px' ),
				'condition'  => array( "{$prefix}_fx!" => 'none' ),
				'selectors'  => array( $selector => '--rsl-delay: {{SIZE}}ms;' ),
			)
		);
	}

	private function section( $id, $label ) {
		$this->start_controls_section(
			$id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
	}

	private function heading( $id, $label ) {
		$this->add_control(
			$id,
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
	}

	/* ======================================================================
	 * استایل
	 * ==================================================================== */

	private function style_box() {
		$this->section( 'style_box', esc_html__( 'Box & layout', 'rolling-slider-elementor' ) );

		$this->add_control(
			'accent_color',
			array(
				'label'     => esc_html__( 'Accent color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f2a736',
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-accent: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'     => esc_html__( 'Background color (before media loads)', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#091625',
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-deep: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'min_height',
			array(
				'label'          => esc_html__( 'Minimum height', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( 'px', 'vh' ),
				'range'          => array(
					'px' => array( 'min' => 240, 'max' => 1400 ),
					'vh' => array( 'min' => 20, 'max' => 100 ),
				),
				'default'        => array( 'size' => 90, 'unit' => 'vh' ),
				'tablet_default' => array( 'size' => 85, 'unit' => 'vh' ),
				'mobile_default' => array( 'size' => 560, 'unit' => 'px' ),
				'selectors'      => array( '{{WRAPPER}} .rsl' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'box_radius',
			array(
				'label'          => esc_html__( 'Border radius', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::DIMENSIONS,
				'size_units'     => array( 'px', '%' ),
				'mobile_default' => array(
					'top'      => 0,
					'right'    => 0,
					'bottom'   => 20,
					'left'     => 20,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'      => array( '{{WRAPPER}} .rsl' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->heading( 'box_content_heading', esc_html__( 'Content placement', 'rolling-slider-elementor' ) );

		$this->add_control(
			'container_width',
			array(
				'label'      => esc_html__( 'Site container width', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Content and tabs are centered at this width to line up with the rest of the site.', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 600, 'max' => 2000 ) ),
				'default'    => array( 'size' => 1240, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-container: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'gutter',
			array(
				'label'      => esc_html__( 'Minimum edge spacing', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-gutter: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'pad_block',
			array(
				'label'      => esc_html__( 'Vertical padding', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-pad-block: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_width',
			array(
				'label'      => esc_html__( 'Text block max width', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 240, 'max' => 1400 ),
					'%'  => array( 'min' => 20, 'max' => 100 ),
				),
				'default'    => array( 'size' => 704, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .rsl__panels' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$start = is_rtl() ? 'right' : 'left';
		$end   = is_rtl() ? 'left' : 'right';

		$this->add_responsive_control(
			'panel_h',
			array(
				'label'     => esc_html__( 'Text block position (horizontal)', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Start', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-h-align-' . $start,
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'End', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-h-align-' . $end,
					),
				),
				'default'   => 'flex-start',
				'selectors' => array( '{{WRAPPER}} .rsl__body' => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_v',
			array(
				'label'          => esc_html__( 'Text block position (vertical)', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::CHOOSE,
				'options'        => array(
					'flex-start' => array(
						'title' => esc_html__( 'Top', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Bottom', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'        => 'center',
				'mobile_default' => 'flex-end',
				'selectors'      => array( '{{WRAPPER}} .rsl__body' => 'align-items: {{VALUE}}; --rsl-v: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function style_media() {
		$this->section( 'style_media', esc_html__( 'Media & overlay', 'rolling-slider-elementor' ) );

		$this->add_control(
			'overlay_color',
			array(
				'label'     => esc_html__( 'Overlay color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#17324f',
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-ov: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'overlay_side',
			array(
				'label'       => esc_html__( 'Side gradient', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Starts from the start or end side (follows RTL/LTR automatically).', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'start' => esc_html__( 'From start', 'rolling-slider-elementor' ),
					'end'   => esc_html__( 'From end', 'rolling-slider-elementor' ),
					'none'  => esc_html__( 'No side gradient', 'rolling-slider-elementor' ),
				),
				'default'     => 'start',
			)
		);

		$this->add_control(
			'overlay_side_opacity',
			array(
				'label'     => esc_html__( 'Side gradient strength', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.02 ) ),
				'default'   => array( 'size' => 0.9 ),
				'condition' => array( 'overlay_side!' => 'none' ),
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-side: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'overlay_bottom_opacity',
			array(
				'label'     => esc_html__( 'Bottom gradient strength', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.02 ) ),
				'default'   => array( 'size' => 0.94 ),
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-bottom: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'overlay_flat_opacity',
			array(
				'label'     => esc_html__( 'Flat darkening', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.02 ) ),
				'default'   => array( 'size' => 0 ),
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-flat: {{SIZE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'media_filters',
				'selector' => '{{WRAPPER}} .rsl__media',
			)
		);

		$this->end_controls_section();
	}

	private function style_eyebrow() {
		$sel = '{{WRAPPER}} .rsl__eyebrow';

		$this->section( 'style_eyebrow', esc_html__( 'Eyebrow', 'rolling-slider-elementor' ) );

		$this->add_fx( 'eyebrow', $sel, 0 );
		$this->add_align( 'eyebrow_align', $sel );

		$this->add_control(
			'eyebrow_dot',
			array(
				'label'        => esc_html__( 'Decorative dot', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'eyebrow_dot_color',
			array(
				'label'     => esc_html__( 'Dot color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'eyebrow_dot' => 'yes' ),
				'selectors' => array( $sel => '--rsl-dot: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'eyebrow_color',
			array(
				'label'     => esc_html__( 'Text color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'eyebrow_typography',
				'selector' => $sel,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'eyebrow_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $sel,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'eyebrow_border',
				'selector' => $sel,
			)
		);

		$this->add_responsive_control(
			'eyebrow_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_padding',
			array(
				'label'      => esc_html__( 'Padding', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_gap',
			array(
				'label'      => esc_html__( 'Gap to title', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( $sel => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'eyebrow_blur',
			array(
				'label'      => esc_html__( 'Backdrop blur', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'selectors'  => array( $sel => '-webkit-backdrop-filter: blur({{SIZE}}px); backdrop-filter: blur({{SIZE}}px);' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'eyebrow_shadow',
				'selector' => $sel,
			)
		);

		$this->end_controls_section();
	}

	private function style_title() {
		$sel = '{{WRAPPER}} .rsl__title';

		$this->section( 'style_title', esc_html__( 'Title', 'rolling-slider-elementor' ) );

		$this->add_fx( 'title', $sel, 80 );
		$this->add_align( 'title_align', $sel );

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => array( $sel => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_highlight',
			array(
				'label'       => esc_html__( 'Accent text color (b / mark)', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Empty = the widget accent color.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( $sel => '--rsl-hl: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => $sel,
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'           => 'title_shadow',
				'selector'       => $sel,
				'fields_options' => array(
					'text_shadow_type' => array( 'default' => 'yes' ),
					'text_shadow'      => array(
						'default' => array(
							'horizontal' => 0,
							'vertical'   => 2,
							'blur'       => 24,
							'color'      => 'rgba(9,22,37,0.45)',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			array(
				'name'     => 'title_stroke',
				'selector' => $sel,
			)
		);

		$this->add_control(
			'title_blend',
			array(
				'label'     => esc_html__( 'Blend mode', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => esc_html__( 'Normal', 'rolling-slider-elementor' ),
					'overlay'     => 'Overlay',
					'soft-light'  => 'Soft light',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'screen'      => 'Screen',
				),
				'selectors' => array( $sel => 'mix-blend-mode: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function style_content() {
		$sel = '{{WRAPPER}} .rsl__content';

		$this->section( 'style_content', esc_html__( 'Content', 'rolling-slider-elementor' ) );

		$this->add_fx( 'content', $sel, 160 );
		$this->add_align( 'content_align', $sel );

		$this->add_control(
			'content_color',
			array(
				'label'     => esc_html__( 'Text color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.78)',
				'separator' => 'before',
				'selectors' => array( $sel => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'content_strong',
			array(
				'label'     => esc_html__( 'Bold text color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => '--rsl-strong: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'content_link',
			array(
				'label'     => esc_html__( 'Link color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => '--rsl-link: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_typography',
				'selector' => $sel,
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'content_shadow',
				'selector' => $sel,
			)
		);

		$this->add_responsive_control(
			'content_width',
			array(
				'label'      => esc_html__( 'Max width', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 200, 'max' => 1200 ),
					'%'  => array( 'min' => 20, 'max' => 100 ),
				),
				'default'    => array( 'size' => 608, 'unit' => 'px' ),
				'selectors'  => array( $sel => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'content_gap',
			array(
				'label'      => esc_html__( 'Gap from title', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( $sel => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'content_para_gap',
			array(
				'label'      => esc_html__( 'Paragraph spacing', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'em' ),
				'range'      => array( 'em' => array( 'min' => 0, 'max' => 3, 'step' => 0.1 ) ),
				'selectors'  => array( $sel . ' p' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function style_buttons() {
		$sel = '{{WRAPPER}} .rsl__btn';

		$this->section( 'style_buttons', esc_html__( 'Buttons', 'rolling-slider-elementor' ) );

		$this->add_fx( 'actions', '{{WRAPPER}} .rsl__actions', 240 );
		$this->add_align( 'actions_align', '{{WRAPPER}} .rsl__actions', 'flex' );

		$this->add_responsive_control(
			'actions_gap_top',
			array(
				'label'      => esc_html__( 'Gap from content', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__actions' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'actions_gap',
			array(
				'label'      => esc_html__( 'Space between buttons', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__actions' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'btn_hover',
			array(
				'label'     => esc_html__( 'Hover effect', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => 'before',
				'options'   => array(
					'none' => esc_html__( 'None', 'rolling-slider-elementor' ),
					'lift' => esc_html__( 'Lift', 'rolling-slider-elementor' ),
					'grow' => esc_html__( 'Grow', 'rolling-slider-elementor' ),
					'glow' => esc_html__( 'Glow (colored shadow)', 'rolling-slider-elementor' ),
					'icon' => esc_html__( 'Nudge icon', 'rolling-slider-elementor' ),
				),
				'default'   => 'lift',
			)
		);

		$this->add_control(
			'btn_icon_position',
			array(
				'label'   => esc_html__( 'Icon position', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'end'   => esc_html__( 'After text', 'rolling-slider-elementor' ),
					'start' => esc_html__( 'Before text', 'rolling-slider-elementor' ),
				),
				'default' => 'end',
			)
		);

		$this->add_responsive_control(
			'btn_icon_size',
			array(
				'label'      => esc_html__( 'Icon size', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-ico: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'btn_icon_gap',
			array(
				'label'      => esc_html__( 'Icon spacing', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'selectors'  => array( $sel => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'btn_typography',
				'selector'  => $sel,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'btn_min_height',
			array(
				'label'      => esc_html__( 'Button height', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 120 ) ),
				'selectors'  => array( $sel => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'btn_padding',
			array(
				'label'      => esc_html__( 'Padding', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'btn_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$variants = array(
			'primary' => esc_html__( 'Primary button', 'rolling-slider-elementor' ),
			'ghost'   => esc_html__( 'Secondary button (outline)', 'rolling-slider-elementor' ),
		);

		foreach ( $variants as $key => $label ) {
			$v = "{{WRAPPER}} .rsl__btn--{$key}";

			$this->heading( "btn_{$key}_heading", $label );
			$this->start_controls_tabs( "btn_{$key}_tabs" );

			/* عادی */
			$this->start_controls_tab( "btn_{$key}_normal", array( 'label' => esc_html__( 'Normal', 'rolling-slider-elementor' ) ) );

			$this->add_control(
				"btn_{$key}_color",
				array(
					'label'     => esc_html__( 'Text color', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $v => 'color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => "btn_{$key}_bg",
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $v,
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => "btn_{$key}_border",
					'selector' => $v,
				)
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => "btn_{$key}_shadow",
					'selector' => $v,
				)
			);

			$this->end_controls_tab();

			/* هاور */
			$this->start_controls_tab( "btn_{$key}_hover_tab", array( 'label' => esc_html__( 'Hover', 'rolling-slider-elementor' ) ) );

			$this->add_control(
				"btn_{$key}_color_h",
				array(
					'label'     => esc_html__( 'Text color', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $v . ':hover, ' . $v . ':focus-visible' => 'color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'     => "btn_{$key}_bg_h",
					'types'    => array( 'classic', 'gradient' ),
					'selector' => $v . ':hover, ' . $v . ':focus-visible',
				)
			);

			$this->add_control(
				"btn_{$key}_border_h",
				array(
					'label'     => esc_html__( 'Border color', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $v . ':hover, ' . $v . ':focus-visible' => 'border-color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => "btn_{$key}_shadow_h",
					'selector' => $v . ':hover, ' . $v . ':focus-visible',
				)
			);

			$this->end_controls_tab();
			$this->end_controls_tabs();
		}

		$this->add_control(
			'btn_ghost_blur',
			array(
				'label'      => esc_html__( 'Secondary button backdrop blur', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 30 ) ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .rsl__btn--ghost' => '-webkit-backdrop-filter: blur({{SIZE}}px); backdrop-filter: blur({{SIZE}}px);' ),
			)
		);

		$this->end_controls_section();
	}

	private function style_tabs() {
		$sel = '{{WRAPPER}} .rsl__tab';

		$this->section( 'style_tabs', esc_html__( 'Navigation tabs', 'rolling-slider-elementor' ) );

		// تب‌ها همیشه در شبکه چیده می‌شوند؛ «خودکار» = یک ستون برای هر اسلاید (موبایل حداکثر ۳)
		$this->add_responsive_control(
			'tabs_cols',
			array(
				'label'       => esc_html__( 'Columns', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Tabs are laid out in a grid. "Auto" gives each slide its own column (at most 3 columns on mobile).', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array( '' => esc_html__( 'Auto', 'rolling-slider-elementor' ) ) + array_combine( range( 1, 8 ), range( 1, 8 ) ),
				'default'     => '',
				'selectors'   => array( '{{WRAPPER}} .rsl' => '--rsl-cols: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'tabs_align',
			array(
				'label'          => esc_html__( 'Tab text alignment', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::CHOOSE,
				'options'        => $this->align_options(),
				'default'        => 'start',
				'mobile_default' => 'center',
				'selectors'      => array( '{{WRAPPER}} .rsl__tabs' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'tabs_gap',
			array(
				'label'      => esc_html__( 'Column gap', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__tabs' => 'column-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'tabs_row_gap',
			array(
				'label'      => esc_html__( 'Row gap', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__tabs' => 'row-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'tabs_pb',
			array(
				'label'          => esc_html__( 'Space below tabs', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( 'px' ),
				'range'          => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'default'        => array( 'size' => 35, 'unit' => 'px' ),
				'mobile_default' => array( 'size' => 0, 'unit' => 'px' ),
				'selectors'      => array( '{{WRAPPER}} .rsl__tabs' => 'padding-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'tabs_typography',
				'selector'  => $sel,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'tabs_padding',
			array(
				'label'      => esc_html__( 'Tab padding', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'tabs_state' );

		$states = array(
			'normal' => array( esc_html__( 'Normal', 'rolling-slider-elementor' ), $sel ),
			'hover'  => array( esc_html__( 'Hover', 'rolling-slider-elementor' ), $sel . ':hover' ),
			'active' => array( esc_html__( 'Active', 'rolling-slider-elementor' ), $sel . '[aria-selected="true"]' ),
		);

		foreach ( $states as $state => $info ) {
			$this->start_controls_tab( "tabs_{$state}", array( 'label' => $info[0] ) );

			$this->add_control(
				"tabs_color_{$state}",
				array(
					'label'     => esc_html__( 'Text color', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->heading( 'tabs_rail_heading', esc_html__( 'Progress rail & top line', 'rolling-slider-elementor' ) );

		$this->add_control(
			'tabs_rail',
			array(
				'label'     => esc_html__( 'Rail color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-rail: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tabs_fill',
			array(
				'label'       => esc_html__( 'Fill color', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'Empty = the widget accent color.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( '{{WRAPPER}} .rsl' => '--rsl-fill: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tabs_rail_h',
			array(
				'label'      => esc_html__( 'Rail thickness', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 12 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-rail-h: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'tabs_line_color',
			array(
				'label'     => esc_html__( 'Top line color', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .rsl__tabs' => 'border-top-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tabs_line_w',
			array(
				'label'      => esc_html__( 'Top line thickness', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 8 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__tabs' => 'border-top-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/* ======================================================================
	 * رندر
	 * ==================================================================== */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = isset( $settings['slides'] ) && is_array( $settings['slides'] ) ? array_map( array( $this, 'normalize_item' ), array_values( $settings['slides'] ) ) : array();
		$count    = count( $slides );

		if ( ! $count ) {
			return;
		}

		$wid     = $this->get_id();
		$is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();

		// جهت: پیش‌فرض از هستهٔ وردپرس (is_rtl)، مگر اینکه برای همین ویجت تغییر داده شده باشد
		$dir = isset( $settings['direction'] ) ? $settings['direction'] : 'auto';
		if ( ! in_array( $dir, array( 'rtl', 'ltr' ), true ) ) {
			$dir = is_rtl() ? 'rtl' : 'ltr';
		}

		$duration = max( 2000, (int) $settings['autoplay_duration'] );
		$autoplay = 'yes' === $settings['autoplay'] && $count > 1 && ! $is_edit;
		$show_nav = 'yes' === $settings['show_tabs'] && $count > 1;

		$start = 0;
		if ( $is_edit && ! empty( $settings['editor_slide'] ) ) {
			$start = min( $count, max( 1, (int) $settings['editor_slide'] ) ) - 1;
		}

		$choice = static function ( $value, $allowed, $fallback ) {
			return in_array( $value, $allowed, true ) ? $value : $fallback;
		};

		// افکت تغییر: پیش‌فرض ویجت، و برای هر اسلاید قابل بازنویسی
		$default_fx = $choice( isset( $settings['transition'] ) ? $settings['transition'] : '', self::TRANSITIONS, 'cover' );
		foreach ( $slides as $i => $item ) {
			$slides[ $i ]['fx'] = $choice( $item['slide_transition'], self::TRANSITIONS, $default_fx );
		}

		$classes = array(
			'rsl',
			'rsl--veil-' . $choice( $settings['overlay_side'], array( 'start', 'end', 'none' ), 'start' ),
			'rsl--hover-' . $choice( $settings['btn_hover'], array( 'none', 'lift', 'grow', 'glow', 'icon' ), 'lift' ),
		);
		if ( 'yes' === $settings['kenburns'] ) {
			$classes[] = 'rsl--kenburns';
		}
		if ( 'yes' !== $settings['eyebrow_dot'] ) {
			$classes[] = 'rsl--no-dot';
		}
		if ( 'start' === $settings['btn_icon_position'] ) {
			$classes[] = 'rsl--icon-start';
		}
		if ( ! $autoplay ) {
			$classes[] = 'rsl--static';
		}

		$this->add_render_attribute(
			'root',
			array(
				'class'                => $classes,
				'dir'                  => $dir,
				'aria-roledescription' => 'carousel',
				'aria-label'           => $settings['aria_label'],
				'data-fx'              => $default_fx,
				'data-autoplay'        => $autoplay ? 'yes' : 'no',
				'data-duration'        => $duration,
				'data-pause-hover'     => 'yes' === $settings['pause_on_hover'] ? 'yes' : 'no',
				'data-swipe'           => 'yes' === $settings['enable_swipe'] ? 'yes' : 'no',
				'style'                => sprintf( '--rsl-duration:%dms;--rsl-n:%d;--rsl-n-m:%d;', $duration, $count, min( $count, 3 ) ),
			)
		);

		$fx = array();
		foreach ( array( 'eyebrow', 'title', 'content', 'actions' ) as $part ) {
			$value       = isset( $settings[ "{$part}_fx" ] ) ? $settings[ "{$part}_fx" ] : 'fade-up';
			$fx[ $part ] = 'rsl-fx rsl-fx--' . $choice( $value, self::FX, 'fade-up' );
		}

		$size    = $settings['image_size'] ? $settings['image_size'] : 'full';
		$has_h1  = false;
		$headers = array();

		// تگ‌ها برای هر اسلاید جدا انتخاب می‌شوند؛ فقط اولین H1 می‌ماند (سئو)
		foreach ( $slides as $i => $item ) {
			$title_tag = Utils::validate_html_tag( $item['title_tag'] );
			if ( 'h1' === $title_tag ) {
				if ( $has_h1 ) {
					$title_tag = 'h2';
				}
				$has_h1 = true;
			}
			$headers[ $i ] = array(
				'eyebrow' => Utils::validate_html_tag( $item['eyebrow_tag'] ),
				'title'   => $title_tag,
			);
		}
		?>
		<section <?php $this->print_render_attribute_string( 'root' ); ?>>

			<div class="rsl__stage" aria-hidden="true">
				<?php foreach ( $slides as $i => $item ) : ?>
					<div class="rsl__slide rsl-t--<?php echo esc_attr( $item['fx'] ); ?> elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?><?php echo $i === $start ? ' is-active' : ''; ?>" data-fx="<?php echo esc_attr( $item['fx'] ); ?>">
						<?php $this->render_media( $item, $i === $start, $size ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<span class="rsl__veil" aria-hidden="true"></span>

			<div class="rsl__body">
				<div class="rsl__panels">
					<?php
					foreach ( $slides as $i => $item ) :
						$tag         = $headers[ $i ]['title'];
						$eyebrow_tag = $headers[ $i ]['eyebrow'];
						?>
						<div class="rsl__panel elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?><?php echo $i === $start ? ' is-active' : ''; ?>"
							id="rsl-panel-<?php echo esc_attr( $wid . '-' . $i ); ?>" role="tabpanel"
							aria-labelledby="rsl-tab-<?php echo esc_attr( $wid . '-' . $i ); ?>">

							<?php if ( '' !== trim( (string) $item['eyebrow'] ) ) : ?>
								<<?php echo esc_html( $eyebrow_tag ); ?> class="rsl__eyebrow <?php echo esc_attr( $fx['eyebrow'] ); ?>"><?php echo esc_html( $item['eyebrow'] ); ?></<?php echo esc_html( $eyebrow_tag ); ?>>
							<?php endif; ?>

							<?php if ( '' !== trim( (string) $item['title'] ) ) : ?>
								<<?php echo esc_html( $tag ); ?> class="rsl__title <?php echo esc_attr( $fx['title'] ); ?>"><?php echo wp_kses( $item['title'], $this->title_kses() ); ?></<?php echo esc_html( $tag ); ?>>
							<?php endif; ?>

							<?php if ( '' !== trim( wp_strip_all_tags( (string) $item['content'] ) ) ) : ?>
								<div class="rsl__content <?php echo esc_attr( $fx['content'] ); ?>"><?php echo wp_kses_post( wpautop( $this->parse_text_editor( $item['content'] ) ) ); ?></div>
							<?php endif; ?>

							<?php $this->render_buttons( $item, $fx['actions'] ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $show_nav ) : ?>
				<div class="rsl__nav">
					<div class="rsl__tabs" role="tablist" aria-label="<?php echo esc_attr( $settings['aria_label'] ); ?>">
						<?php foreach ( $slides as $i => $item ) : ?>
							<button class="rsl__tab" type="button" role="tab"
								id="rsl-tab-<?php echo esc_attr( $wid . '-' . $i ); ?>"
								aria-controls="rsl-panel-<?php echo esc_attr( $wid . '-' . $i ); ?>"
								aria-selected="<?php echo $i === $start ? 'true' : 'false'; ?>"
								tabindex="<?php echo $i === $start ? '0' : '-1'; ?>"><?php echo esc_html( $this->tab_label( $item, $i ) ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

		</section>
		<?php
	}

	/** هر کلیدی که در آیتم نباشد مقدار خالی می‌گیرد تا رندر هیچ‌وقت notice ندهد */
	private function normalize_item( $item ) {
		$defaults = array(
			'_id'          => '',
			'tab_title'    => '',
			'media_type'   => 'image',
			'image'        => array(),
			'video'        => array(),
			'video_mobile' => array(),
			'poster'       => array(),
			'eyebrow'      => '',
			'title'        => '',
			'content'      => '',
			'eyebrow_tag'  => 'span',
			'title_tag'    => 'h2',
			'slide_transition' => '',
		);
		foreach ( array( 1, 2 ) as $n ) {
			$defaults[ "btn{$n}_show" ]  = '';
			$defaults[ "btn{$n}_text" ]  = '';
			$defaults[ "btn{$n}_link" ]  = array();
			$defaults[ "btn{$n}_style" ] = 1 === $n ? 'primary' : 'ghost';
			$defaults[ "btn{$n}_icon" ]  = array();
		}
		return wp_parse_args( $item, $defaults );
	}

	private function title_kses() {
		return array(
			'br'     => array(),
			'b'      => array(),
			'strong' => array(),
			'em'     => array(),
			'i'      => array(),
			'mark'   => array(),
			'span'   => array( 'class' => true ),
		);
	}

	private function tab_label( $item, $index ) {
		$label = trim( (string) $item['tab_title'] );
		if ( '' === $label ) {
			$label = wp_trim_words( wp_strip_all_tags( (string) $item['title'] ), 3, '' );
		}
		if ( '' === $label ) {
			/* translators: %d: slide number */
			$label = sprintf( esc_html__( 'Slide %d', 'rolling-slider-elementor' ), $index + 1 );
		}
		return $label;
	}

	/**
	 * تصویر یا ویدیو. فقط اسلاید فعال src واقعی دارد؛ بقیه با data-rsl-* می‌آیند
	 * و جاوااسکریپت وقتی نوبتشان نزدیک شد بارگذاری‌شان می‌کند.
	 */
	private function render_media( $item, $is_first, $size ) {
		$type = isset( $item['media_type'] ) ? $item['media_type'] : 'image';

		if ( 'video' === $type && ! empty( $item['video']['url'] ) ) {
			$poster = $this->resolve_image( isset( $item['poster'] ) ? $item['poster'] : array(), 'full' );
			$url    = $item['video']['url'];
			$mobile = ! empty( $item['video_mobile']['url'] ) ? $item['video_mobile']['url'] : '';
			$mime   = wp_check_filetype( strtok( $url, '?' ) );

			printf(
				'<video class="rsl__media rsl__video" muted loop playsinline preload="none" disablepictureinpicture%1$s data-rsl-src="%2$s"%3$s data-rsl-type="%4$s"></video>',
				$poster ? ' poster="' . esc_url( $poster['src'] ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput
				esc_url( $url ),
				$mobile ? ' data-rsl-src-mobile="' . esc_url( $mobile ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput
				esc_attr( $mime['type'] ? $mime['type'] : 'video/mp4' )
			);
			return;
		}

		$media = 'video' === $type && isset( $item['poster'] ) ? $item['poster'] : ( isset( $item['image'] ) ? $item['image'] : array() );
		$img   = $this->resolve_image( $media, $size );
		if ( ! $img ) {
			return;
		}

		$dims = $img['width'] && $img['height'] ? sprintf( ' width="%d" height="%d"', $img['width'], $img['height'] ) : '';

		if ( $is_first ) {
			printf(
				'<img class="rsl__media" src="%1$s"%2$s sizes="100vw" alt="" loading="eager" fetchpriority="high" decoding="async"%3$s>',
				esc_url( $img['src'] ),
				$img['srcset'] ? ' srcset="' . esc_attr( $img['srcset'] ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput
				$dims // phpcs:ignore WordPress.Security.EscapeOutput
			);
			return;
		}

		printf(
			'<img class="rsl__media" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-rsl-src="%1$s"%2$s sizes="100vw" alt="" decoding="async"%3$s>',
			esc_url( $img['src'] ),
			$img['srcset'] ? ' data-rsl-srcset="' . esc_attr( $img['srcset'] ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput
			$dims // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}

	/**
	 * مدیای المنتور (id یا url) → src / srcset / ابعاد.
	 * اگر فقط url داریم و فایل از کتابخانهٔ همین سایت است، srcset هم پیدا می‌شود.
	 */
	private function resolve_image( $media, $size ) {
		$id  = ! empty( $media['id'] ) ? (int) $media['id'] : 0;
		$url = ! empty( $media['url'] ) ? $media['url'] : '';

		if ( ! $id && $url && Utils::get_placeholder_image_src() !== $url ) {
			$id = (int) attachment_url_to_postid( $url );
		}

		if ( $id && wp_attachment_is_image( $id ) ) {
			$src = wp_get_attachment_image_src( $id, $size );
			if ( $src ) {
				return array(
					'src'    => $src[0],
					'width'  => (int) $src[1],
					'height' => (int) $src[2],
					'srcset' => (string) wp_get_attachment_image_srcset( $id, $size ),
				);
			}
		}

		if ( $url ) {
			return array(
				'src'    => $url,
				'width'  => 0,
				'height' => 0,
				'srcset' => '',
			);
		}

		return null;
	}

	private function render_buttons( $item, $fx_class ) {
		$buttons = array();

		foreach ( array( 1, 2 ) as $n ) {
			if ( 'yes' !== ( isset( $item[ "btn{$n}_show" ] ) ? $item[ "btn{$n}_show" ] : '' ) ) {
				continue;
			}
			if ( '' === trim( (string) $item[ "btn{$n}_text" ] ) ) {
				continue;
			}
			$buttons[] = $n;
		}

		if ( ! $buttons ) {
			return;
		}

		echo '<div class="rsl__actions ' . esc_attr( $fx_class ) . '">';

		foreach ( $buttons as $n ) {
			$key   = "btn_{$item['_id']}_{$n}";
			$style = 'ghost' === $item[ "btn{$n}_style" ] ? 'ghost' : 'primary';

			$this->add_render_attribute( $key, 'class', array( 'rsl__btn', 'rsl__btn--' . $style ) );

			$link = isset( $item[ "btn{$n}_link" ] ) ? $item[ "btn{$n}_link" ] : array();
			if ( ! empty( $link['url'] ) ) {
				$this->add_link_attributes( $key, $link );
				if ( ! empty( $link['is_external'] ) ) {
					$this->add_render_attribute( $key, 'rel', 'noopener' );
				}
			}

			$icon = $this->button_icon( $item, $n );

			printf(
				'<a %1$s><span class="rsl__btn-text">%2$s</span>%3$s</a>',
				$this->get_render_attribute_string( $key ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $item[ "btn{$n}_text" ] ),
				$icon // phpcs:ignore WordPress.Security.EscapeOutput
			);
		}

		echo '</div>';
	}

	private function button_icon( $item, $n ) {
		$icon = isset( $item[ "btn{$n}_icon" ] ) ? $item[ "btn{$n}_icon" ] : array();

		if ( empty( $icon['value'] ) ) {
			return '';
		}

		ob_start();
		Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
		$html = ob_get_clean();

		return '' === $html ? '' : '<span class="rsl__btn-icon">' . $html . '</span>';
	}
}
