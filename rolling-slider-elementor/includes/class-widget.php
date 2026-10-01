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

	public function get_name() {
		return 'rolling_slider';
	}

	public function get_title() {
		return esc_html__( 'اسلایدر رولینگ', 'rolling-slider-elementor' );
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
				'label' => esc_html__( 'اسلایدها', 'rolling-slider-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'tab_title',
			array(
				'label'       => esc_html__( 'عنوان تب ناوبری', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'اسلاید جدید', 'rolling-slider-elementor' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->start_controls_tabs( 'slide_tabs' );

		/* رسانه */
		$repeater->start_controls_tab( 'slide_tab_media', array( 'label' => esc_html__( 'رسانه', 'rolling-slider-elementor' ) ) );

		$repeater->add_control(
			'media_type',
			array(
				'label'   => esc_html__( 'نوع رسانه', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'toggle'  => false,
				'default' => 'image',
				'options' => array(
					'image' => array(
						'title' => esc_html__( 'تصویر', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-image',
					),
					'video' => array(
						'title' => esc_html__( 'ویدیو', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-video-camera',
					),
				),
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'     => esc_html__( 'تصویر', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array( 'url' => Utils::get_placeholder_image_src() ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'media_type' => 'image' ),
			)
		);

		$repeater->add_control(
			'video',
			array(
				'label'       => esc_html__( 'ویدیو (MP4 / WebM)', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);

		$repeater->add_control(
			'video_mobile',
			array(
				'label'       => esc_html__( 'ویدیوی سبک موبایل (اختیاری)', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'اگر انتخاب شود در صفحه‌های کوچک به‌جای ویدیوی اصلی پخش می‌شود و مصرف دیتا را کم می‌کند.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);

		$repeater->add_control(
			'poster',
			array(
				'label'       => esc_html__( 'پوستر ویدیو', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'تا لود شدن ویدیو (یا وقتی دیتا‌سیور فعال است) نمایش داده می‌شود.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'media_type' => 'video' ),
			)
		);

		$repeater->add_responsive_control(
			'object_position',
			array(
				'label'     => esc_html__( 'کانون تصویر', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''              => esc_html__( 'وسط', 'rolling-slider-elementor' ),
					'center top'    => esc_html__( 'بالا', 'rolling-slider-elementor' ),
					'center bottom' => esc_html__( 'پایین', 'rolling-slider-elementor' ),
					'left center'   => esc_html__( 'چپ', 'rolling-slider-elementor' ),
					'right center'  => esc_html__( 'راست', 'rolling-slider-elementor' ),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .rsl__media' => 'object-position: {{VALUE}};',
				),
			)
		);

		$repeater->end_controls_tab();

		/* متن */
		$repeater->start_controls_tab( 'slide_tab_text', array( 'label' => esc_html__( 'متن', 'rolling-slider-elementor' ) ) );

		$repeater->add_control(
			'eyebrow',
			array(
				'label'       => esc_html__( 'زیرعنوان', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => esc_html__( 'عنوان', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'برای رنگ تاکیدی از <b>…</b> و برای شکستن خط از <br> استفاده کنید.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'content',
			array(
				'label'   => esc_html__( 'محتوا', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::WYSIWYG,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->end_controls_tab();

		/* دکمه‌ها */
		$repeater->start_controls_tab( 'slide_tab_buttons', array( 'label' => esc_html__( 'دکمه‌ها', 'rolling-slider-elementor' ) ) );

		foreach ( array( 1, 2 ) as $n ) {
			$show = "btn{$n}_show";

			$repeater->add_control(
				"btn{$n}_heading",
				array(
					/* translators: %d: button number */
					'label'     => sprintf( esc_html__( 'دکمه %d', 'rolling-slider-elementor' ), $n ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 2 === $n ? 'before' : 'none',
				)
			);

			$repeater->add_control(
				$show,
				array(
					'label'        => esc_html__( 'نمایش دکمه', 'rolling-slider-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$repeater->add_control(
				"btn{$n}_text",
				array(
					'label'       => esc_html__( 'متن دکمه', 'rolling-slider-elementor' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array( 'active' => true ),
					'condition'   => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_link",
				array(
					'label'       => esc_html__( 'لینک', 'rolling-slider-elementor' ),
					'type'        => Controls_Manager::URL,
					'label_block' => true,
					'dynamic'     => array( 'active' => true ),
					'condition'   => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_style",
				array(
					'label'     => esc_html__( 'ظاهر دکمه', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'primary' => esc_html__( 'اصلی (پر)', 'rolling-slider-elementor' ),
						'ghost'   => esc_html__( 'ثانویه (شفاف)', 'rolling-slider-elementor' ),
					),
					'default'   => 1 === $n ? 'primary' : 'ghost',
					'condition' => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_icon_preset",
				array(
					'label'     => esc_html__( 'آیکن', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'none'     => esc_html__( 'بدون آیکن', 'rolling-slider-elementor' ),
						'arrow'    => esc_html__( 'فلش (جهت خودکار)', 'rolling-slider-elementor' ),
						'download' => esc_html__( 'دانلود', 'rolling-slider-elementor' ),
						'award'    => esc_html__( 'گواهینامه', 'rolling-slider-elementor' ),
						'phone'    => esc_html__( 'تلفن', 'rolling-slider-elementor' ),
						'mail'     => esc_html__( 'ایمیل', 'rolling-slider-elementor' ),
						'play'     => esc_html__( 'پخش', 'rolling-slider-elementor' ),
						'external' => esc_html__( 'لینک خارجی', 'rolling-slider-elementor' ),
						'custom'   => esc_html__( 'آیکن دلخواه…', 'rolling-slider-elementor' ),
					),
					'default'   => 'arrow',
					'condition' => array( $show => 'yes' ),
				)
			);

			$repeater->add_control(
				"btn{$n}_icon",
				array(
					'label'     => esc_html__( 'آیکن دلخواه', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::ICONS,
					'condition' => array(
						$show                 => 'yes',
						"btn{$n}_icon_preset" => 'custom',
					),
				)
			);
		}

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		$this->add_control(
			'slides',
			array(
				'label'       => esc_html__( 'اسلایدها', 'rolling-slider-elementor' ),
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
				'btn1_icon_preset'   => 'arrow',
				'btn2_show'          => 'yes',
				'btn2_text'          => 'Dolor sit amet',
				'btn2_link'          => $link( '#' ),
				'btn2_style'         => 'ghost',
				'btn2_icon_preset'   => 'download',
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
				'btn1_icon_preset' => 'arrow',
			),
		);
	}

	/* ── محتوا: تنظیمات ────────────────────────────────────────────────── */

	private function controls_settings() {
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'تنظیمات اسلایدر', 'rolling-slider-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'پخش خودکار', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'autoplay_duration',
			array(
				'label'     => esc_html__( 'مدت نمایش هر اسلاید (میلی‌ثانیه)', 'rolling-slider-elementor' ),
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
				'label'        => esc_html__( 'توقف با هاور موس', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'enable_swipe',
			array(
				'label'        => esc_html__( 'سوایپ در موبایل', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_tabs',
			array(
				'label'        => esc_html__( 'نمایش تب‌های ناوبری', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'transition',
			array(
				'label'     => esc_html__( 'افکت گذار بین اسلایدها', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => 'before',
				'options'   => array(
					'fade' => esc_html__( 'محو شدن', 'rolling-slider-elementor' ),
					'zoom' => esc_html__( 'محو + زوم', 'rolling-slider-elementor' ),
					'blur' => esc_html__( 'محو + بلور', 'rolling-slider-elementor' ),
				),
				'default'   => 'fade',
			)
		);

		$this->add_control(
			'transition_speed',
			array(
				'label'      => esc_html__( 'سرعت گذار (ms)', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 100, 'max' => 3000, 'step' => 50 ) ),
				'default'    => array( 'size' => 900, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-speed: {{SIZE}}ms;' ),
			)
		);

		$this->add_control(
			'kenburns',
			array(
				'label'        => esc_html__( 'حرکت آرام تصویر (Ken Burns)', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'       => esc_html__( 'جهت ویجت', 'rolling-slider-elementor' ),
				'description' => esc_html__( '«خودکار» یعنی بر اساس زبان سایت در هستهٔ وردپرس (راست‌چین یا چپ‌چین). فقط اگر خواستید برای همین ویجت تغییرش دهید.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'separator'   => 'before',
				'options'     => array(
					'auto' => esc_html__( 'خودکار (طبق وردپرس)', 'rolling-slider-elementor' ),
					'rtl'  => esc_html__( 'راست‌چین (RTL)', 'rolling-slider-elementor' ),
					'ltr'  => esc_html__( 'چپ‌چین (LTR)', 'rolling-slider-elementor' ),
				),
				'default'     => 'auto',
			)
		);

		$this->add_control(
			'aria_label',
			array(
				'label'       => esc_html__( 'برچسب دسترس‌پذیری', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'اسلایدر معرفی', 'rolling-slider-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'editor_slide',
			array(
				'label'       => esc_html__( 'اسلاید نمایشی در ویرایشگر', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'فقط در ویرایشگر المنتور اعمال می‌شود تا بتوانید اسلاید دلخواه را ببینید؛ در سایت اثری ندارد.', 'rolling-slider-elementor' ),
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
				'label' => esc_html__( 'تگ‌ها و تصویر', 'rolling-slider-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$tags = array(
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

		$this->add_control(
			'eyebrow_tag',
			array(
				'label'   => esc_html__( 'تگ زیرعنوان', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $tags,
				'default' => 'span',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'       => esc_html__( 'تگ عنوان', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'اگر H1 را انتخاب کنید فقط اسلاید اول H1 می‌شود و بقیه خودکار H2 خواهند بود (برای سئو).', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $tags,
				'default'     => 'h2',
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'       => esc_html__( 'اندازهٔ تصویر', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'مرورگر خودش از بین اندازه‌های موجود (srcset) مناسب‌ترین را برمی‌دارد.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->image_size_options(),
				'default'     => 'full',
			)
		);

		$this->end_controls_section();
	}

	private function image_size_options() {
		$options = array();
		foreach ( get_intermediate_image_sizes() as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
		}
		$options['full'] = esc_html__( 'اندازهٔ کامل (Full)', 'rolling-slider-elementor' );
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
				'title' => esc_html__( 'ابتدای خط (راست در RTL، چپ در LTR)', 'rolling-slider-elementor' ),
				'icon'  => 'eicon-text-align-' . $start,
			),
			'center' => array(
				'title' => esc_html__( 'وسط', 'rolling-slider-elementor' ),
				'icon'  => 'eicon-text-align-center',
			),
			'end'    => array(
				'title' => esc_html__( 'انتهای خط (چپ در RTL، راست در LTR)', 'rolling-slider-elementor' ),
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
				'label'                => esc_html__( 'تراز', 'rolling-slider-elementor' ),
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
				'label'   => esc_html__( 'افکت ورود', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'none'        => esc_html__( 'بدون افکت', 'rolling-slider-elementor' ),
					'fade'        => esc_html__( 'محو شدن', 'rolling-slider-elementor' ),
					'fade-up'     => esc_html__( 'محو + از پایین', 'rolling-slider-elementor' ),
					'fade-down'   => esc_html__( 'محو + از بالا', 'rolling-slider-elementor' ),
					'slide-start' => esc_html__( 'لغزش از ابتدا', 'rolling-slider-elementor' ),
					'slide-end'   => esc_html__( 'لغزش از انتها', 'rolling-slider-elementor' ),
					'zoom-in'     => esc_html__( 'زوم از کوچک', 'rolling-slider-elementor' ),
					'zoom-out'    => esc_html__( 'زوم از بزرگ', 'rolling-slider-elementor' ),
					'blur'        => esc_html__( 'ظاهر شدن از بلور', 'rolling-slider-elementor' ),
					'flip'        => esc_html__( 'چرخش سه‌بعدی', 'rolling-slider-elementor' ),
					'reveal'      => esc_html__( 'پرده‌ای (Reveal)', 'rolling-slider-elementor' ),
				),
				'default' => 'fade-up',
			)
		);

		$this->add_control(
			"{$prefix}_fx_dist",
			array(
				'label'      => esc_html__( 'مسافت حرکت', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'مدت افکت (ms)', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'تأخیر (ms)', 'rolling-slider-elementor' ),
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
		$this->section( 'style_box', esc_html__( 'کادر و چیدمان', 'rolling-slider-elementor' ) );

		$this->add_control(
			'accent_color',
			array(
				'label'     => esc_html__( 'رنگ تاکیدی', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f2a736',
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-accent: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'     => esc_html__( 'رنگ پس‌زمینه (قبل از لود رسانه)', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#091625',
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-deep: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'min_height',
			array(
				'label'          => esc_html__( 'حداقل ارتفاع', 'rolling-slider-elementor' ),
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
				'label'          => esc_html__( 'گردی گوشه‌ها', 'rolling-slider-elementor' ),
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

		$this->heading( 'box_content_heading', esc_html__( 'جایگاه محتوا', 'rolling-slider-elementor' ) );

		$this->add_control(
			'container_width',
			array(
				'label'      => esc_html__( 'عرض کانتینر سایت', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'محتوا و تب‌ها با این عرض هم‌تراز با بقیهٔ سایت وسط‌چین می‌شوند.', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'حداقل فاصله از لبه‌ها', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-gutter: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'pad_block',
			array(
				'label'      => esc_html__( 'فاصلهٔ عمودی', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-pad-block: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'panel_width',
			array(
				'label'      => esc_html__( 'حداکثر عرض بلوک متن', 'rolling-slider-elementor' ),
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
				'label'     => esc_html__( 'جای بلوک متن (افقی)', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'ابتدا', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-h-align-' . $start,
					),
					'center'     => array(
						'title' => esc_html__( 'وسط', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'انتها', 'rolling-slider-elementor' ),
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
				'label'          => esc_html__( 'جای بلوک متن (عمودی)', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::CHOOSE,
				'options'        => array(
					'flex-start' => array(
						'title' => esc_html__( 'بالا', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => esc_html__( 'وسط', 'rolling-slider-elementor' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'پایین', 'rolling-slider-elementor' ),
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
		$this->section( 'style_media', esc_html__( 'رسانه و پوشش', 'rolling-slider-elementor' ) );

		$this->add_control(
			'overlay_color',
			array(
				'label'     => esc_html__( 'رنگ پوشش', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#17324f',
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-ov: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'overlay_side',
			array(
				'label'       => esc_html__( 'گرادیان کناری', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'از سمت ابتدا یا انتهای خط شروع می‌شود (جهت بر اساس RTL/LTR خودکار است).', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'start' => esc_html__( 'از ابتدا', 'rolling-slider-elementor' ),
					'end'   => esc_html__( 'از انتها', 'rolling-slider-elementor' ),
					'none'  => esc_html__( 'بدون گرادیان کناری', 'rolling-slider-elementor' ),
				),
				'default'     => 'start',
			)
		);

		$this->add_control(
			'overlay_side_opacity',
			array(
				'label'     => esc_html__( 'شدت گرادیان کناری', 'rolling-slider-elementor' ),
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
				'label'     => esc_html__( 'شدت گرادیان پایین', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.02 ) ),
				'default'   => array( 'size' => 0.94 ),
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-bottom: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'overlay_flat_opacity',
			array(
				'label'     => esc_html__( 'تیره‌کنندهٔ یکدست', 'rolling-slider-elementor' ),
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

		$this->section( 'style_eyebrow', esc_html__( 'زیرعنوان', 'rolling-slider-elementor' ) );

		$this->add_fx( 'eyebrow', $sel, 0 );
		$this->add_align( 'eyebrow_align', $sel );

		$this->add_control(
			'eyebrow_dot',
			array(
				'label'        => esc_html__( 'نقطهٔ تزئینی', 'rolling-slider-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'eyebrow_dot_color',
			array(
				'label'     => esc_html__( 'رنگ نقطه', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'eyebrow_dot' => 'yes' ),
				'selectors' => array( $sel => '--rsl-dot: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'eyebrow_color',
			array(
				'label'     => esc_html__( 'رنگ متن', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'گردی گوشه‌ها', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_padding',
			array(
				'label'      => esc_html__( 'فاصلهٔ داخلی', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'eyebrow_gap',
			array(
				'label'      => esc_html__( 'فاصلهٔ تا عنوان', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( $sel => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'eyebrow_blur',
			array(
				'label'      => esc_html__( 'بلور پس‌زمینه', 'rolling-slider-elementor' ),
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

		$this->section( 'style_title', esc_html__( 'عنوان', 'rolling-slider-elementor' ) );

		$this->add_fx( 'title', $sel, 80 );
		$this->add_align( 'title_align', $sel );

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'رنگ متن', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => array( $sel => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_highlight',
			array(
				'label'       => esc_html__( 'رنگ بخش تاکیدی (b / mark)', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'خالی = همان رنگ تاکیدی ویجت.', 'rolling-slider-elementor' ),
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
				'label'     => esc_html__( 'حالت ترکیب (Blend)', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''            => esc_html__( 'عادی', 'rolling-slider-elementor' ),
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

		$this->section( 'style_content', esc_html__( 'محتوا', 'rolling-slider-elementor' ) );

		$this->add_fx( 'content', $sel, 160 );
		$this->add_align( 'content_align', $sel );

		$this->add_control(
			'content_color',
			array(
				'label'     => esc_html__( 'رنگ متن', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.78)',
				'separator' => 'before',
				'selectors' => array( $sel => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'content_strong',
			array(
				'label'     => esc_html__( 'رنگ متن پررنگ', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $sel => '--rsl-strong: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'content_link',
			array(
				'label'     => esc_html__( 'رنگ لینک‌ها', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'حداکثر عرض', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'فاصله از عنوان', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( $sel => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'content_para_gap',
			array(
				'label'      => esc_html__( 'فاصلهٔ بین پاراگراف‌ها', 'rolling-slider-elementor' ),
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

		$this->section( 'style_buttons', esc_html__( 'دکمه‌ها', 'rolling-slider-elementor' ) );

		$this->add_fx( 'actions', '{{WRAPPER}} .rsl__actions', 240 );
		$this->add_align( 'actions_align', '{{WRAPPER}} .rsl__actions', 'flex' );

		$this->add_responsive_control(
			'actions_gap_top',
			array(
				'label'      => esc_html__( 'فاصله از محتوا', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 120 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__actions' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'actions_gap',
			array(
				'label'      => esc_html__( 'فاصلهٔ بین دکمه‌ها', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__actions' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'btn_hover',
			array(
				'label'     => esc_html__( 'افکت هاور', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => 'before',
				'options'   => array(
					'none' => esc_html__( 'بدون افکت', 'rolling-slider-elementor' ),
					'lift' => esc_html__( 'بالا رفتن', 'rolling-slider-elementor' ),
					'grow' => esc_html__( 'بزرگ شدن', 'rolling-slider-elementor' ),
					'glow' => esc_html__( 'درخشش (سایه رنگی)', 'rolling-slider-elementor' ),
					'icon' => esc_html__( 'حرکت آیکن', 'rolling-slider-elementor' ),
				),
				'default'   => 'lift',
			)
		);

		$this->add_control(
			'btn_icon_position',
			array(
				'label'   => esc_html__( 'جای آیکن', 'rolling-slider-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'end'   => esc_html__( 'بعد از متن', 'rolling-slider-elementor' ),
					'start' => esc_html__( 'قبل از متن', 'rolling-slider-elementor' ),
				),
				'default' => 'end',
			)
		);

		$this->add_responsive_control(
			'btn_icon_size',
			array(
				'label'      => esc_html__( 'اندازهٔ آیکن', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-ico: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'btn_icon_gap',
			array(
				'label'      => esc_html__( 'فاصلهٔ آیکن و متن', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'ارتفاع دکمه', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 120 ) ),
				'selectors'  => array( $sel => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'btn_padding',
			array(
				'label'      => esc_html__( 'فاصلهٔ داخلی', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'btn_radius',
			array(
				'label'      => esc_html__( 'گردی گوشه‌ها', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$variants = array(
			'primary' => esc_html__( 'دکمهٔ اصلی', 'rolling-slider-elementor' ),
			'ghost'   => esc_html__( 'دکمهٔ ثانویه (شفاف)', 'rolling-slider-elementor' ),
		);

		foreach ( $variants as $key => $label ) {
			$v = "{{WRAPPER}} .rsl__btn--{$key}";

			$this->heading( "btn_{$key}_heading", $label );
			$this->start_controls_tabs( "btn_{$key}_tabs" );

			/* عادی */
			$this->start_controls_tab( "btn_{$key}_normal", array( 'label' => esc_html__( 'عادی', 'rolling-slider-elementor' ) ) );

			$this->add_control(
				"btn_{$key}_color",
				array(
					'label'     => esc_html__( 'رنگ متن', 'rolling-slider-elementor' ),
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
			$this->start_controls_tab( "btn_{$key}_hover_tab", array( 'label' => esc_html__( 'هاور', 'rolling-slider-elementor' ) ) );

			$this->add_control(
				"btn_{$key}_color_h",
				array(
					'label'     => esc_html__( 'رنگ متن', 'rolling-slider-elementor' ),
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
					'label'     => esc_html__( 'رنگ کادر', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'بلور پس‌زمینهٔ دکمهٔ ثانویه', 'rolling-slider-elementor' ),
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

		$this->section( 'style_tabs', esc_html__( 'تب‌های ناوبری', 'rolling-slider-elementor' ) );

		$this->add_responsive_control(
			'tabs_layout',
			array(
				'label'                => esc_html__( 'چیدمان تب‌ها', 'rolling-slider-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'options'              => array(
					'scroll' => esc_html__( 'یک ردیف (اسکرول افقی)', 'rolling-slider-elementor' ),
					'grid'   => esc_html__( 'شبکه (بدون اسکرول)', 'rolling-slider-elementor' ),
				),
				'default'              => 'scroll',
				'tablet_default'       => 'scroll',
				'mobile_default'       => 'grid',
				'selectors_dictionary' => array(
					'scroll' => 'display: flex; overflow-x: auto; scroll-snap-type: x proximity; text-align: start;',
					'grid'   => 'display: grid; grid-template-columns: repeat(var(--rsl-cols, 3), minmax(0, 1fr)); overflow: visible; scroll-snap-type: none; text-align: center;',
				),
				'selectors'            => array( '{{WRAPPER}} .rsl__tabs' => '{{VALUE}}' ),
			)
		);

		$this->add_responsive_control(
			'tabs_cols',
			array(
				'label'          => esc_html__( 'تعداد ستون (حالت شبکه)', 'rolling-slider-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6 ),
				'default'        => 5,
				'tablet_default' => 5,
				'mobile_default' => 3,
				'selectors'      => array( '{{WRAPPER}} .rsl' => '--rsl-cols: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'tabs_gap',
			array(
				'label'      => esc_html__( 'فاصلهٔ بین تب‌ها', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl__tabs' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'tabs_pb',
			array(
				'label'          => esc_html__( 'فاصلهٔ پایین نوار تب', 'rolling-slider-elementor' ),
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
				'label'      => esc_html__( 'فاصلهٔ داخلی تب', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $sel => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'tabs_state' );

		$states = array(
			'normal' => array( esc_html__( 'عادی', 'rolling-slider-elementor' ), $sel ),
			'hover'  => array( esc_html__( 'هاور', 'rolling-slider-elementor' ), $sel . ':hover' ),
			'active' => array( esc_html__( 'فعال', 'rolling-slider-elementor' ), $sel . '[aria-selected="true"]' ),
		);

		foreach ( $states as $state => $info ) {
			$this->start_controls_tab( "tabs_{$state}", array( 'label' => $info[0] ) );

			$this->add_control(
				"tabs_color_{$state}",
				array(
					'label'     => esc_html__( 'رنگ متن', 'rolling-slider-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $info[1] => 'color: {{VALUE}};' ),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->heading( 'tabs_rail_heading', esc_html__( 'ریل پیشرفت و خط بالا', 'rolling-slider-elementor' ) );

		$this->add_control(
			'tabs_rail',
			array(
				'label'     => esc_html__( 'رنگ ریل', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .rsl' => '--rsl-rail: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tabs_fill',
			array(
				'label'       => esc_html__( 'رنگ پرشونده', 'rolling-slider-elementor' ),
				'description' => esc_html__( 'خالی = رنگ تاکیدی ویجت.', 'rolling-slider-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( '{{WRAPPER}} .rsl' => '--rsl-fill: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tabs_rail_h',
			array(
				'label'      => esc_html__( 'ضخامت ریل', 'rolling-slider-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 12 ) ),
				'selectors'  => array( '{{WRAPPER}} .rsl' => '--rsl-rail-h: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'tabs_line_color',
			array(
				'label'     => esc_html__( 'رنگ خط بالای تب‌ها', 'rolling-slider-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .rsl__tabs' => 'border-top-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'tabs_line_w',
			array(
				'label'      => esc_html__( 'ضخامت خط بالای تب‌ها', 'rolling-slider-elementor' ),
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

		$classes = array(
			'rsl',
			'rsl--fx-' . $choice( $settings['transition'], array( 'fade', 'zoom', 'blur' ), 'fade' ),
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
				'data-autoplay'        => $autoplay ? 'yes' : 'no',
				'data-duration'        => $duration,
				'data-pause-hover'     => 'yes' === $settings['pause_on_hover'] ? 'yes' : 'no',
				'data-swipe'           => 'yes' === $settings['enable_swipe'] ? 'yes' : 'no',
				'style'                => '--rsl-duration:' . $duration . 'ms;',
			)
		);

		$fx = array();
		foreach ( array( 'eyebrow', 'title', 'content', 'actions' ) as $part ) {
			$value       = isset( $settings[ "{$part}_fx" ] ) ? $settings[ "{$part}_fx" ] : 'fade-up';
			$fx[ $part ] = 'rsl-fx rsl-fx--' . $choice( $value, self::FX, 'fade-up' );
		}

		$eyebrow_tag = Utils::validate_html_tag( $settings['eyebrow_tag'] );
		$title_tag   = Utils::validate_html_tag( $settings['title_tag'] );
		$size        = $settings['image_size'] ? $settings['image_size'] : 'full';
		?>
		<section <?php $this->print_render_attribute_string( 'root' ); ?>>

			<div class="rsl__stage" aria-hidden="true">
				<?php foreach ( $slides as $i => $item ) : ?>
					<div class="rsl__slide elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?><?php echo $i === $start ? ' is-active' : ''; ?>">
						<?php $this->render_media( $item, $i === $start, $size ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<span class="rsl__veil" aria-hidden="true"></span>

			<div class="rsl__body">
				<div class="rsl__panels">
					<?php
					foreach ( $slides as $i => $item ) :
						$tag = ( 'h1' === $title_tag && 0 !== $i ) ? 'h2' : $title_tag;
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
		);
		foreach ( array( 1, 2 ) as $n ) {
			$defaults[ "btn{$n}_show" ]        = '';
			$defaults[ "btn{$n}_text" ]        = '';
			$defaults[ "btn{$n}_link" ]        = array();
			$defaults[ "btn{$n}_style" ]       = 1 === $n ? 'primary' : 'ghost';
			$defaults[ "btn{$n}_icon_preset" ] = 'none';
			$defaults[ "btn{$n}_icon" ]        = array();
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
			$label = sprintf( esc_html__( 'اسلاید %d', 'rolling-slider-elementor' ), $index + 1 );
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
		$preset = isset( $item[ "btn{$n}_icon_preset" ] ) ? $item[ "btn{$n}_icon_preset" ] : 'none';

		if ( 'none' === $preset ) {
			return '';
		}

		if ( 'custom' === $preset ) {
			if ( empty( $item[ "btn{$n}_icon" ]['value'] ) ) {
				return '';
			}
			ob_start();
			Icons_Manager::render_icon( $item[ "btn{$n}_icon" ], array( 'aria-hidden' => 'true' ) );
			return '<span class="rsl__btn-icon">' . ob_get_clean() . '</span>';
		}

		$paths = array(
			'arrow'    => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
			'download' => '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',
			'award'    => '<circle cx="12" cy="9" r="5.5"/><path d="m8.5 13.4-1.6 7.1 5.1-2.7 5.1 2.7-1.6-7.1"/>',
			'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
			'mail'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/>',
			'play'     => '<path d="M6 4v16l14-8z"/>',
			'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/>',
		);

		if ( ! isset( $paths[ $preset ] ) ) {
			return '';
		}

		return sprintf(
			'<span class="rsl__btn-icon rsl__ico--%1$s"><svg class="rsl__svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">%2$s</svg></span>',
			esc_attr( $preset ),
			$paths[ $preset ]
		);
	}
}
