<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Iran_Pishtaz_Shipping' ) ) {
	class WC_Shipping_Iran_Pishtaz extends WC_Shipping_Method {
		/**
		 * Constructor for shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id                 = 'iran_pishtaz_shipping'; // Id for your shipping method. Should be uunique.
			$this->method_title       = __( 'پست پیشتاز' );  // Title shown in admin
			$this->method_description = __( 'روش پست پیشتاز ایران برای ووکامرس - این روش مبلغ پست پیشتاز را بر اساس نرخ پستی سال 1394 محاسبه می کند، نحوه محاسبه بر اساس نرخ پستی بر اساس وزن ، مبلغ بیمه ، مالیات پستی و مسافت از مبدا تا مقصد بر اساس موقعیت دو استان نسبت به هم می باشد. لازم به ذکر است که مبلغ محاسباتی به صورت اتوماتیک بر اساس واحد پول ایران و واحد وزن تعیین شده در تنظیمات ووکامرس محاسبه و به صورت اتوماتیک به واحد های تعیین شده تبدیل میشود. واحد های پول قابل پذیرش ریال ، تومان و هزار تومان است و واحد های وزنی مورد قبول ، گرم و کیلوگرم می باشد. <a href="http://parsmizban.com/" title="تالار گفتمان پارس میزبان" target="_blank" clain">پشتیبانی از طریق تالار گفتمان پارس میزبان</a>' ); // Description shown in admin
			$this->init();
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			// Load the settings API
			$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
			$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

			// Define user set variables
			$this->enabled				= $this->get_option( 'enabled' );
			$this->title				= $this->get_option( 'title' );
			$this->extra_cost			= $this->get_option( 'extra_cost' );
			$this->extra_cost_percent	= $this->get_option( 'extra_cost_percent' );
			$this->source_state			= $this->get_option( 'source_state' );
			$this->current_currency		= get_woocommerce_currency(); // IRR or IRT or IRHT
			$this->current_weight_unit	= get_option('woocommerce_weight_unit'); // g or kg
			$cart = WC()->cart;
			$this->cart_weight = $cart->cart_contents_weight;
			
			// Save settings in admin if you have any defined
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Initialise Gateway Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable this shipping method', 'woocommerce' ),
					'default' 		=> 'no',
				),
				'title' => array(
					'title' 		=> __( 'Method Title', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'		=> __( 'پست پیشتاز' ),
					'desc_tip'		=> true
				),
				'extra_cost' => array(
					'title' 		=> __( 'هزینه های اضافی' ),
					'type' 			=> 'text',
					'description' 	=> __( 'در این قسمت هزینه های اضافی علاوه بر نرخ پستی را می توانید وارد نمائید ، از قبیل هزینه های بسته بندی و غیره - مبلغ ثابت را به ریال وارد نمائید.' ),
					'default'		=> __( '' ),
					'desc_tip'		=> true
				),
				'extra_cost_percent' => array(
					'title' 		=> __( 'هزینه های اضافی به درصد' ),
					'type' 			=> 'text',
					'description' 	=> __( 'در این قسمت هزینه های اضافی علاوه بر نرخ پستی را می توانید به درصد وارد نمائید - در این قسمت فقط عدد را وارد نمائید برای مثال برای 2% ، عدد 2 را وارد نمائید' ),
					'default'		=> __( '' ),
					'desc_tip'		=> true
				),
				'source_state' => array(
					'title' 		=> __( 'استان مبدا (فروشنده)' ),
					'type' 			=> 'select',
					'description' 	=> __( 'لطفا در این قسمت استانی که محصولات از آنجا ارسال می شوند را انتخاب نمائید' ),
					'default'		=> 'TE',
					'desc_tip'		=> true,
					'options' 		=> array(
						'AE' => __( 'آذربایجان شرقی' ),
						'AW' => __( 'آذربایجان غربی' ),
						'AR' => __( 'اردبیل' ),
						'IS' => __( 'اصفهان' ),
						'AL' => __( 'البرز' ),
						'IL' => __( 'ایلام' ),
						'BU' => __( 'بوشهر' ),
						'TE' => __( 'تهران' ),
						'CM' => __( 'چهارمحال و بختیاری' ),
						'KJ' => __( 'خراسان جنوبی' ),
						'KV' => __( 'خراسان رضوی' ),
						'KS' => __( 'خراسان شمالی' ),
						'KZ' => __( 'خوزستان' ),
						'ZA' => __( 'زنجان' ),
						'SM' => __( 'سمنان' ),
						'SB' => __( 'سیستان و بلوچستان' ),
						'FA' => __( 'فارس' ),
						'QZ' => __( 'قزوین' ),
						'QM' => __( 'قم' ),
						'KD' => __( 'کردستان' ),
						'KE' => __( 'کرمان' ),
						'BK' => __( 'کرمانشاه' ),
						'KB' => __( 'کهگیلویه و بویراحمد' ),
						'GO' => __( 'گلستان' ),
						'GI' => __( 'گیلان' ),
						'LO' => __( 'لرستان' ),
						'MN' => __( 'مازندران' ),
						'MK' => __( 'مرکزی' ),
						'HG' => __( 'هرمزگان' ),
						'HD' => __( 'همدان' ),
						'YA' => __( 'یزد' ),
					)
				)
			);
		}

		/**
		 * check if states are beside each other
		 *
		 * @access public
		 * @param string $source
		 * @param string destination
		 * @return string
		 * in     = same
		 * beside = beside
		 * out    = non beside
		 */
		public function check_states_beside( $source, $destination){
			$isbeside["AE"]["AW"] = true;
			$isbeside["AE"]["AR"] = true;
			$isbeside["AE"]["ZA"] = true;
			
			$isbeside["AW"]["AE"] = true;
			$isbeside["AW"]["KD"] = true;
			$isbeside["AW"]["ZA"] = true;

			$isbeside["AR"]["AE"] = true;
			$isbeside["AR"]["GI"] = true;
			$isbeside["AR"]["ZA"] = true;
			
			$isbeside["IS"]["CM"] = true;
			$isbeside["IS"]["LO"] = true;
			$isbeside["IS"]["KB"] = true;
			$isbeside["IS"]["MK"] = true;
			$isbeside["IS"]["QM"] = true;
			$isbeside["IS"]["SM"] = true;
			$isbeside["IS"]["KJ"] = true;
			$isbeside["IS"]["YA"] = true;
			$isbeside["IS"]["FA"] = true;

			$isbeside["AL"]["TE"] = true;
			$isbeside["AL"]["MK"] = true;
			$isbeside["AL"]["QZ"] = true;
			$isbeside["AL"]["MN"] = true;
			
			$isbeside["IL"]["BK"] = true;
			$isbeside["IL"]["LO"] = true;
			$isbeside["IL"]["KZ"] = true;
			
			$isbeside["BU"]["KB"] = true;
			$isbeside["BU"]["KZ"] = true;
			$isbeside["BU"]["FA"] = true;
			$isbeside["BU"]["HG"] = true;
			
			$isbeside["TE"]["AL"] = true;
			$isbeside["TE"]["MK"] = true;
			$isbeside["TE"]["QM"] = true;
			$isbeside["TE"]["MN"] = true;
			$isbeside["TE"]["SM"] = true;
			
			$isbeside["CM"]["KB"] = true;
			$isbeside["CM"]["KZ"] = true;
			$isbeside["CM"]["LO"] = true;
			$isbeside["CM"]["IS"] = true;
			
			$isbeside["KJ"]["SB"] = true;
			$isbeside["KJ"]["KE"] = true;
			$isbeside["KJ"]["YA"] = true;
			$isbeside["KJ"]["IS"] = true;
			$isbeside["KJ"]["SM"] = true;
			$isbeside["KJ"]["KV"] = true;
			
			$isbeside["KV"]["KJ"] = true;
			$isbeside["KV"]["KS"] = true;
			$isbeside["KV"]["SM"] = true;
			
			$isbeside["KS"]["KV"] = true;
			$isbeside["KS"]["GO"] = true;
			$isbeside["KS"]["SM"] = true;
			
			$isbeside["KZ"]["IL"] = true;
			$isbeside["KZ"]["BU"] = true;
			$isbeside["KZ"]["LO"] = true;
			$isbeside["KZ"]["KB"] = true;
			$isbeside["KZ"]["CM"] = true;
			
			$isbeside["ZA"]["GI"] = true;
			$isbeside["ZA"]["AR"] = true;
			$isbeside["ZA"]["AE"] = true;
			$isbeside["ZA"]["AW"] = true;
			$isbeside["ZA"]["KD"] = true;
			$isbeside["ZA"]["HD"] = true;
			$isbeside["ZA"]["QZ"] = true;
			
			$isbeside["SM"]["MN"] = true;
			$isbeside["SM"]["TE"] = true;
			$isbeside["SM"]["QM"] = true;
			$isbeside["SM"]["IS"] = true;
			$isbeside["SM"]["KS"] = true;
			$isbeside["SM"]["KV"] = true;
			$isbeside["SM"]["KJ"] = true;
			
			$isbeside["SB"]["KJ"] = true;
			$isbeside["SB"]["KE"] = true;
			$isbeside["SB"]["HG"] = true;
			
			$isbeside["FA"]["IS"] = true;
			$isbeside["FA"]["YA"] = true;
			$isbeside["FA"]["BU"] = true;
			$isbeside["FA"]["HG"] = true;
			$isbeside["FA"]["KB"] = true;
			$isbeside["FA"]["KE"] = true;
			
			$isbeside["QZ"]["ZA"] = true;
			$isbeside["QZ"]["HD"] = true;
			$isbeside["QZ"]["MK"] = true;
			$isbeside["QZ"]["AL"] = true;
			$isbeside["QZ"]["MN"] = true;
			$isbeside["QZ"]["GI"] = true;
			
			$isbeside["QM"]["TE"] = true;
			$isbeside["QM"]["MK"] = true;
			$isbeside["QM"]["SM"] = true;
			$isbeside["QM"]["IS"] = true;
			
			$isbeside["KD"]["AW"] = true;
			$isbeside["KD"]["BK"] = true;
			$isbeside["KD"]["HD"] = true;
			$isbeside["KD"]["ZA"] = true;
			
			$isbeside["KE"]["YA"] = true;
			$isbeside["KE"]["FA"] = true;
			$isbeside["KE"]["HG"] = true;
			$isbeside["KE"]["SB"] = true;
			$isbeside["KE"]["KJ"] = true;
			
			$isbeside["BK"]["KD"] = true;
			$isbeside["BK"]["HD"] = true;
			$isbeside["BK"]["LO"] = true;
			$isbeside["BK"]["IL"] = true;
			
			$isbeside["KB"]["CM"] = true;
			$isbeside["KB"]["KZ"] = true;
			$isbeside["KB"]["BU"] = true;
			$isbeside["KB"]["FA"] = true;
			$isbeside["KB"]["IS"] = true;
			
			$isbeside["GO"]["MN"] = true;
			$isbeside["GO"]["KS"] = true;
			$isbeside["GO"]["SM"] = true;
			
			$isbeside["GI"]["MN"] = true;
			$isbeside["GI"]["AR"] = true;
			$isbeside["GI"]["ZA"] = true;
			$isbeside["GI"]["QZ"] = true;
			
			$isbeside["LO"]["IL"] = true;
			$isbeside["LO"]["BK"] = true;
			$isbeside["LO"]["HD"] = true;
			$isbeside["LO"]["MK"] = true;
			$isbeside["LO"]["IS"] = true;
			$isbeside["LO"]["CM"] = true;
			$isbeside["LO"]["KZ"] = true;
			
			$isbeside["MN"]["GO"] = true;
			$isbeside["MN"]["SM"] = true;
			$isbeside["MN"]["TE"] = true;
			$isbeside["MN"]["AL"] = true;
			$isbeside["MN"]["IS"] = true;
			$isbeside["MN"]["QZ"] = true;
			$isbeside["MN"]["GI"] = true;
			
			$isbeside["MK"]["IS"] = true;
			$isbeside["MK"]["QM"] = true;
			$isbeside["MK"]["TE"] = true;
			$isbeside["MK"]["AL"] = true;
			$isbeside["MK"]["LO"] = true;
			$isbeside["MK"]["QZ"] = true;
			$isbeside["MK"]["HD"] = true;
			
			$isbeside["HG"]["BU"] = true;
			$isbeside["HG"]["FA"] = true;
			$isbeside["HG"]["KE"] = true;
			$isbeside["HG"]["SB"] = true;
			
			$isbeside["HD"]["BK"] = true;
			$isbeside["HD"]["LO"] = true;
			$isbeside["HD"]["KD"] = true;
			$isbeside["HD"]["MK"] = true;
			$isbeside["HD"]["QZ"] = true;
			$isbeside["HD"]["ZA"] = true;
			
			$isbeside["YA"]["IS"] = true;
			$isbeside["YA"]["FA"] = true;
			$isbeside["YA"]["KE"] = true;
			$isbeside["YA"]["KJ"] = true;

			if ($isbeside[$source][$destination] === true)
				return 'beside';
			elseif ( $source == $destination )
				return 'in';
			else return 'out';
		}
		 
		
		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param mixed $package
		 * @return void
		 */
		public function calculate_shipping( $package ) {
			
			$shipping_total = 0;
			
			// convert current currency to rial
			if ($this->current_currency =='IRT') {
				$this->extra_cost = $this->extra_cost * 10;
				$tmp_package_cost = $package['contents_cost'] * 10;
			} elseif ($this->current_currency =='IRHT'){
				$this->extra_cost = $this->extra_cost * 10000;
				$tmp_package_cost = $package['contents_cost'] * 10000;
			}
			
			//convert current weight unit to gram
			if ($this->current_weight_unit == 'kg') {
				$tmp_weight = $this->cart_weight * 1000;
			} else $tmp_weight = $this->cart_weight;
			
			// Iran Post Prices (prices are in Rial)
			//http://www.post.ir/DesktopModules/Articles/ArticlesView.aspx?TabID=1&Site=postportal&Lang=fa-IR&ItemID=12350&mid=33125
			$rate_price['250']['in'] 		= 47000;
			$rate_price['250']['beside'] 	= 49000;
			$rate_price['250']['out'] 		= 62000;
			
			$rate_price['500']['in'] 		= 57000;
			$rate_price['500']['beside'] 	= 59000;
			$rate_price['500']['out'] 		= 76000;
			
			$rate_price['1000']['in'] 		= 69000;
			$rate_price['1000']['beside'] 	= 72000;
			$rate_price['1000']['out'] 		= 94000;
			
			$rate_price['2000']['in'] 		= 91000;
			$rate_price['2000']['beside'] 	= 94000;
			$rate_price['2000']['out'] 		= 126000;
			
			$rate_price['9999']['in'] 		= 20000;
			$rate_price['9999']['beside'] 	= 21000;
			$rate_price['9999']['out'] 		= 27000;
			
			// invalid post code price
			$invalid_postcode = 3100;
			
			// insurance (bime)
			$insurance = 5000;

			// post tax percent (#%)
			$post_tax = 9; // 9%

			// detect the weight plan
			if ( $tmp_weight < 250 )
				$weight_indicator = '250';
			elseif ( $tmp_weight > 251 &&  $tmp_weight < 501 )
				$weight_indicator = '500';
			elseif ( $tmp_weight > 501 && $tmp_weight < 1000 )
				$weight_indicator = '1000';
			elseif ( $tmp_weight > 1001 && $tmp_weight < 2000 )
				$weight_indicator = '2000';
			elseif ( $tmp_weight > 2001 )
				$weight_indicator = '9999';

			// find destination state
			if ( $package['destination']['country'] == 'IR' ) {//Iran country
				$this->destination_state = $package['destination']['state']; //example: TE
										//$package['destination']['postcode'] //1234567890
										//$package['destination']['city']
			}

			// if states are beside or are same or not beside each other
			$checked_state = $this->check_states_beside( $this->source_state , $this->destination_state);

			// calculate
			if ( $weight_indicator != '9999' ) { // is less than 2000 gram
				$shipping_total = $rate_price[$weight_indicator][$checked_state];
			} elseif ( $weight_indicator == '9999' ) { // is more than 2000 gram
				$shipping_total = $rate_price[$weight_indicator][$checked_state] * ceil( $tmp_weight / 1000);
			}

			// check invalid post code
			switch ( $package['destination']['postcode'] ){
				case '1234567890':
				case '1111111111':
				case '2222222222':
				case '3333333333':
				case '4444444444':
				case '5555555555':
				case '6666666666':
				case '7777777777':
				case '8888888888':
				case '9999999999':
				case '0000000000':
				case '0987654321':
				case '1234567891':
				case '0123456789':
				case '7894561230':
				case ( strlen ( $package['destination']['postcode'] ) < 10 ):
				case ( strlen ( $package['destination']['postcode'] ) > 10 ):
					$shipping_total += $invalid_postcode;
					break;
			}
			
			// insurance (bime)
			$shipping_total += $insurance;
			
			// post tax
			$shipping_total += ceil( ( $shipping_total * $post_tax ) / 100 );

			// round to up for amounts fewer than 1000 rials
			$shipping_total = ( ceil ( $shipping_total / 1000 ) ) * 1000;

			// convert currency to current selected currency
			if ( $this->current_currency == 'IRT' ) {
				$shipping_total = ceil ( $shipping_total / 10 );
			} elseif ( $this->current_currency == 'IRHT' ) {
				$shipping_total = ceil ( $shipping_total / 10000 );
			}
			
			$this->extra_cost_percent   = intval ($this->extra_cost_percent);
			$this->extra_cost			= intval ($this->extra_cost);
			$shipping_total +=  ceil ( ( $shipping_total * $this->extra_cost_percent) / 100 );
			$shipping_total += $this->extra_cost;
			
			// Register the rate
			$rate = array(
				'id' => $this->id,
				'label' => $this->title,
				'cost' => $shipping_total,
				'calc_tax' => 'per_order'
			);
			$this->add_rate( $rate );
		}
	}
}

function iran_pishtaz_shipping( $methods ) {
	$methods[] = 'WC_Shipping_Iran_Pishtaz';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'iran_pishtaz_shipping' );
