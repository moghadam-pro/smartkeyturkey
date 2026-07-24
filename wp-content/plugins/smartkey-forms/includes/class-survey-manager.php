<?php

namespace SmartKeyTurkey\Forms;

defined( 'ABSPATH' ) || exit;

final class Survey_Manager {
	private const VERSION = '3';
	private const SLUG    = 'experience-survey';
	private const MARKER  = '_skf_standalone_landing';

	public static function init(): void {
		add_action( 'init', array( self::class, 'provision' ), 60 );
		add_filter( 'template_include', array( self::class, 'template' ), 99 );
		add_filter( 'wp_robots', array( self::class, 'robots' ) );
		add_filter( 'rank_math/frontend/robots', array( self::class, 'rank_math_robots' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'assets' ), 20 );
	}

	public static function provision(): void {
		if ( get_option( 'skf_experience_survey_version' ) === self::VERSION ) {
			return;
		}

		$form = get_page_by_path( 'smartkeyturkey-experience-survey', OBJECT, 'skf_form' );
		$form_id = $form ? (int) $form->ID : wp_insert_post(
			array(
				'post_type'   => 'skf_form',
				'post_status' => 'publish',
				'post_title'  => 'SmartKeyTurkey Experience Survey',
				'post_name'   => 'smartkeyturkey-experience-survey',
			)
		);
		if ( ! $form_id || is_wp_error( $form_id ) ) {
			return;
		}

		update_post_meta( $form_id, '_skf_submission_type', 'ux_research' );
		update_post_meta( $form_id, '_skf_fields', self::fields() );
		update_option( 'skf_experience_survey_form_en', $form_id, false );

		$form_fa = get_page_by_path( 'smartkeyturkey-experience-survey-fa', OBJECT, 'skf_form' );
		$form_fa_id = $form_fa ? (int) $form_fa->ID : wp_insert_post(
			array(
				'post_type'   => 'skf_form',
				'post_status' => 'publish',
				'post_title'  => 'SmartKeyTurkey Experience Survey — Persian',
				'post_name'   => 'smartkeyturkey-experience-survey-fa',
			)
		);
		if ( ! $form_fa_id || is_wp_error( $form_fa_id ) ) {
			return;
		}
		update_post_meta( $form_fa_id, '_skf_submission_type', 'ux_research' );
		update_post_meta( $form_fa_id, '_skf_fields', self::fields_fa() );
		update_option( 'skf_experience_survey_form_fa', $form_fa_id, false );

		$page = get_page_by_path( self::SLUG, OBJECT, 'page' );
		$page_data = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'SmartKeyTurkey Website Experience Survey',
			'post_name'    => self::SLUG,
			'post_content' => self::content( $form_id ),
		);
		$page_id = $page ? wp_update_post( array_merge( $page_data, array( 'ID' => $page->ID ) ) ) : wp_insert_post( $page_data );
		if ( ! $page_id || is_wp_error( $page_id ) ) {
			return;
		}

		update_post_meta( $page_id, self::MARKER, '1' );
		update_post_meta( $page_id, 'rank_math_robots', array( 'noindex', 'nofollow' ) );
		update_post_meta( $page_id, 'rank_math_title', 'SmartKeyTurkey Website Experience Survey' );
		update_post_meta( $page_id, 'rank_math_description', 'Private stakeholder research survey for the SmartKeyTurkey website.' );
		update_option( 'skf_experience_survey_version', self::VERSION, false );
	}

	public static function template( string $template ): string {
		return self::is_survey() ? SKF_DIR . 'templates/standalone-survey.php' : $template;
	}

	public static function assets(): void {
		if ( self::is_survey() ) {
			wp_enqueue_style( 'smartkey-forms' );
			wp_enqueue_style( 'smartkey-vazirmatn', 'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css', array(), '33.003' );
			wp_enqueue_style( 'smartkey-survey', plugins_url( 'assets/css/survey.css', SKF_FILE ), array( 'smartkey-forms', 'smartkey-vazirmatn' ), SKF_VERSION );
		}
	}

	public static function robots( array $robots ): array {
		if ( self::is_survey() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			$robots['noarchive'] = true;
		}
		return $robots;
	}

	public static function rank_math_robots( array $robots ): array {
		return self::is_survey() ? array( 'noindex', 'nofollow', 'noarchive' ) : $robots;
	}

	public static function is_persian(): bool {
		return isset( $_GET['lang'] ) && 'fa' === sanitize_key( wp_unslash( $_GET['lang'] ) );
	}

	public static function render(): string {
		$persian = self::is_persian();
		$form_id = (int) get_option( $persian ? 'skf_experience_survey_form_fa' : 'skf_experience_survey_form_en' );
		return $persian ? self::content_fa( $form_id ) : self::content( $form_id );
	}

	private static function is_survey(): bool {
		return is_singular( 'page' ) && '1' === get_post_meta( get_queried_object_id(), self::MARKER, true );
	}

	private static function content( int $form_id ): string {
		return '<div class="skf-survey-intro"><p class="skf-eyebrow">Stakeholder research · 15–20 minutes</p><h1>Help us improve SmartKeyTurkey</h1><p>Please explore the live website before answering. We are evaluating the experience—not you. Your candid feedback will guide the next design phase.</p><div class="skf-task-list"><h2>Three short tasks</h2><ol><li>Start at the <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">Home page</a>, find an Istanbul property and locate its inquiry action.</li><li>Find a grade in <a href="' . esc_url( home_url( '/petrochemical-products/' ) ) . '" target="_blank" rel="noopener">Petrochemical Products</a> and locate the Request a Quote action.</li><li>Find one <a href="' . esc_url( home_url( '/turkey-attractions/' ) ) . '" target="_blank" rel="noopener">Turkey attraction</a>, one <a href="' . esc_url( home_url( '/blog/' ) ) . '" target="_blank" rel="noopener">latest insight</a>, and review <a href="' . esc_url( home_url( '/about-us/' ) ) . '" target="_blank" rel="noopener">About Us</a>.</li></ol></div></div>[smartkey_form id="' . $form_id . '" button="Submit feedback"]';
	}

	private static function content_fa( int $form_id ): string {
		return '<div class="skf-survey-intro"><p class="skf-eyebrow">پژوهش ذی‌نفعان · ۱۵ تا ۲۰ دقیقه</p><h1>به ما در بهبود SmartKeyTurkey کمک کنید</h1><p>لطفاً پیش از پاسخ‌دادن، بخش‌های مشخص‌شده از وب‌سایت را بررسی کنید. هدف این نظرسنجی ارزیابی تجربه وب‌سایت است، نه ارزیابی شما. بازخورد صادقانه شما مسیر مرحله بعدی طراحی را مشخص می‌کند.</p><div class="skf-task-list"><h2>سه مأموریت کوتاه</h2><ol><li>از <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">صفحه نخست</a> شروع کنید، یک ملک در استانبول پیدا کنید و به بخش ثبت درخواست آن برسید.</li><li>در صفحه <a href="' . esc_url( home_url( '/petrochemical-products/' ) ) . '" target="_blank" rel="noopener">محصولات پتروشیمی</a> یک محصول یا گرید پیدا کنید و به گزینه درخواست پیش‌فاکتور برسید.</li><li>یک <a href="' . esc_url( home_url( '/turkey-attractions/' ) ) . '" target="_blank" rel="noopener">جاذبه گردشگری</a>، یک <a href="' . esc_url( home_url( '/blog/' ) ) . '" target="_blank" rel="noopener">مقاله جدید</a> و صفحه <a href="' . esc_url( home_url( '/about-us/' ) ) . '" target="_blank" rel="noopener">درباره ما</a> را پیدا و بررسی کنید.</li></ol></div></div>[smartkey_form id="' . $form_id . '" button="ارسال بازخورد" sent="سپاسگزاریم. پاسخ شما با موفقیت ثبت شد." error="لطفاً فیلدهای الزامی را بررسی و دوباره تلاش کنید."]';
	}

	private static function fields(): string {
		return implode(
			"\n",
			array(
				'section||About you|||This context helps us interpret feedback; responses are reviewed internally.',
				'text|participant_name|Name (optional)|||',
				'select|role|Your primary role|required|Executive or manager,Property or real estate specialist,Petrochemical or procurement specialist,Marketing UX or digital specialist,Operations or customer service,Other|',
				'scale|industry_familiarity|How familiar are you with property or petrochemical websites?|required|1,2,3,4,5|1 = Not familiar · 5 = Very familiar',
				'section||First impression|||Think about what you understood in the first minute.',
				'scale|home_clarity|The Home page clearly explains what SmartKeyTurkey offers.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'scale|business_clarity|I could quickly distinguish the property and petrochemical services.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'radio|easiest_path|Which business path was easiest to recognise?|required|Property,Petrochemicals,Both equally,Neither|',
				'section||Navigation and goal completion|||Base these answers on the three tasks above.',
				'scale|property_find|How easy was it to find a relevant Istanbul property?|required|1,2,3,4,5|1 = Very difficult · 5 = Very easy',
				'scale|property_inquiry|How easy was it to reach the property inquiry action?|required|1,2,3,4,5|1 = Very difficult · 5 = Very easy',
				'scale|product_find|How easy was it to find a petrochemical product or grade?|required|1,2,3,4,5|1 = Very difficult · 5 = Very easy',
				'scale|quote_action|How easy was it to reach Request a Quote?|required|1,2,3,4,5|1 = Very difficult · 5 = Very easy',
				'scale|content_find|How easy was it to find Attractions, Insights and About Us?|required|1,2,3,4,5|1 = Very difficult · 5 = Very easy',
				'section||Interface, content and trust|||Evaluate the pages you visited as a connected experience.',
				'scale|hierarchy|Information was well organised and easy to scan.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'scale|visual_consistency|The visual design felt consistent across pages.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'scale|mobile_confidence|The experience felt comfortable on the device I used.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'scale|trust|The content and presentation made SmartKeyTurkey feel credible.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'scale|role_clarity|SmartKeyTurkey’s role in property and petrochemical services was clear.|required|1,2,3,4,5|1 = Strongly disagree · 5 = Strongly agree',
				'radio|request_pricing|Was it clear that prices and commercial terms are provided on request?|required|Yes,Partly,No|',
				'section||Your recommendations|||Please be specific; page names or examples are especially useful.',
				'textarea|hesitation|Where did you hesitate, feel lost or need to go back?|required||',
				'textarea|priority_improvement|What is the single most valuable improvement we should make next?|required||',
				'textarea|missing_information|What information or capability felt missing or unclear?|||',
				'checkbox|research_consent|I agree that this feedback may be used internally to improve the website.|required||',
			)
		);
	}

	private static function fields_fa(): string {
		return implode(
			"\n",
			array(
				'section||درباره شما|||این اطلاعات به تحلیل بهتر پاسخ‌ها کمک می‌کند و فقط به‌صورت داخلی بررسی می‌شود.',
				'text|participant_name|نام (اختیاری)|||',
				'select|role|نقش اصلی شما|required|مدیر یا مدیر ارشد,متخصص املاک,متخصص پتروشیمی یا تأمین,متخصص بازاریابی تجربه کاربری یا دیجیتال,عملیات یا خدمات مشتریان,سایر|',
				'scale|industry_familiarity|تا چه اندازه با وب‌سایت‌های املاک یا پتروشیمی آشنا هستید؟|required|۱,۲,۳,۴,۵|۱ = آشنایی ندارم · ۵ = کاملاً آشنا هستم',
				'section||برداشت اولیه|||به چیزی فکر کنید که در یک دقیقه نخست متوجه شدید.',
				'scale|home_clarity|صفحه نخست خدمات SmartKeyTurkey را به‌وضوح معرفی می‌کند.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'scale|business_clarity|توانستم خدمات املاک و پتروشیمی را به‌سرعت از هم تشخیص دهم.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'radio|easiest_path|تشخیص کدام مسیر خدمات ساده‌تر بود؟|required|املاک,پتروشیمی,هر دو به یک اندازه,هیچ‌کدام|',
				'section||پیمایش و رسیدن به هدف|||پاسخ‌ها را بر اساس سه مأموریت بالا ثبت کنید.',
				'scale|property_find|پیداکردن یک ملک مرتبط در استانبول چقدر آسان بود؟|required|۱,۲,۳,۴,۵|۱ = بسیار دشوار · ۵ = بسیار آسان',
				'scale|property_inquiry|رسیدن به گزینه ثبت درخواست ملک چقدر آسان بود؟|required|۱,۲,۳,۴,۵|۱ = بسیار دشوار · ۵ = بسیار آسان',
				'scale|product_find|پیداکردن یک محصول یا گرید پتروشیمی چقدر آسان بود؟|required|۱,۲,۳,۴,۵|۱ = بسیار دشوار · ۵ = بسیار آسان',
				'scale|quote_action|رسیدن به گزینه درخواست پیش‌فاکتور چقدر آسان بود؟|required|۱,۲,۳,۴,۵|۱ = بسیار دشوار · ۵ = بسیار آسان',
				'scale|content_find|پیداکردن جاذبه‌ها، مقالات و درباره ما چقدر آسان بود؟|required|۱,۲,۳,۴,۵|۱ = بسیار دشوار · ۵ = بسیار آسان',
				'section||رابط کاربری، محتوا و اعتماد|||صفحاتی را که دیدید به‌عنوان یک تجربه یکپارچه ارزیابی کنید.',
				'scale|hierarchy|اطلاعات منظم و قابل مرور سریع بود.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'scale|visual_consistency|طراحی بصری در صفحات مختلف هماهنگ بود.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'scale|mobile_confidence|کار با سایت روی دستگاهی که استفاده کردم راحت بود.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'scale|trust|محتوا و شیوه ارائه، حس اعتبار SmartKeyTurkey را منتقل می‌کرد.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'scale|role_clarity|نقش SmartKeyTurkey در خدمات املاک و پتروشیمی روشن بود.|required|۱,۲,۳,۴,۵|۱ = کاملاً مخالفم · ۵ = کاملاً موافقم',
				'radio|request_pricing|آیا مشخص بود که قیمت‌ها و شرایط تجاری پس از درخواست ارائه می‌شوند؟|required|بله,تاحدی,خیر|',
				'section||پیشنهادهای شما|||لطفاً دقیق پاسخ دهید؛ اشاره به نام صفحه یا مثال مشخص بسیار مفید است.',
				'textarea|hesitation|در کجا مکث کردید، مسیر را گم کردید یا مجبور شدید به عقب برگردید؟|required||',
				'textarea|priority_improvement|مهم‌ترین بهبودی که باید در مرحله بعد انجام دهیم چیست؟|required||',
				'textarea|missing_information|چه اطلاعات یا قابلیتی ناقص یا نامفهوم بود؟|||',
				'checkbox|research_consent|موافقم که این بازخورد برای بهبود داخلی وب‌سایت استفاده شود.|required||',
			)
		);
	}
}
