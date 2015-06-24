<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// check currencies
if ( !array_key_exists( 'IRR', get_woocommerce_currencies() )
		and !array_key_exists( 'IRT', get_woocommerce_currencies()) ) {

	function add_my_currency( $currencies ) {
		$currencies['IRR'] = __( 'ریال', 'woocommerce' );
		$currencies['IRT'] = __( 'تومان', 'woocommerce' );
		$currencies['IRHT'] = __( 'هزار تومان', 'woocommerce' );
		return $currencies;
	}
	add_filter( 'woocommerce_currencies', 'add_my_currency' );

	function add_my_currency_symbol( $currency_symbol, $currency ) {
		switch ( $currency ) {
			case 'IRR': $currency_symbol = 'ریال'; break;
			case 'IRT': $currency_symbol = 'تومان'; break;
			case 'IRHT': $currency_symbol = 'هزار تومان'; break;
		}
		return $currency_symbol;
	}
	add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);
}

// check for iran states
if (! function_exists ('iran_states') ) {
	function iran_states( $states ) {
		$states['IR'] = array(
			'AL'  => __( 'البرز', 'woocommerce' ),
			'AR'  => __( 'اردبیل', 'woocommerce' ),
			'AE'  => __( 'آذربایجان شرقی', 'woocommerce' ),
			'AW'  => __( 'آذربایجان غربی', 'woocommerce' ),
			'BU'  => __( 'بوشهر', 'woocommerce' ),
			'CM'  => __( 'چهارمحال و بختیاری', 'woocommerce' ),
			'FA'  => __( 'فارس', 'woocommerce' ),
			'GI'  => __( 'گیلان', 'woocommerce' ),
			'GO'  => __( 'گلستان', 'woocommerce' ),
			'HD'  => __( 'همدان', 'woocommerce' ),
			'HG'  => __( 'هرمزگان', 'woocommerce' ),
			'IL'  => __( 'ایلام', 'woocommerce' ),
			'IS'  => __( 'اصفهان', 'woocommerce' ),
			'KE'  => __( 'کرمان', 'woocommerce' ),
			'BK'  => __( 'کرمانشاه', 'woocommerce' ),
			'KS'  => __( 'خراسان شمالی', 'woocommerce' ),
			'KV'  => __( 'خراسان رضوی', 'woocommerce' ),
			'KJ'  => __( 'خراسان جنوبی', 'woocommerce' ),
			'KZ'  => __( 'خوزستان', 'woocommerce' ),
			'KB'  => __( 'کهگیلویه و بویراحمد', 'woocommerce' ),
			'KD'  => __( 'کردستان', 'woocommerce' ),
			'LO'  => __( 'لرستان', 'woocommerce' ),
			'MK'  => __( 'مرکزی', 'woocommerce' ),
			'MN'  => __( 'مازندران', 'woocommerce' ),
			'QZ'  => __( 'قزوین', 'woocommerce' ),
			'QM'  => __( 'قم', 'woocommerce' ),
			'SM'  => __( 'سمنان', 'woocommerce' ),
			'SB'  => __( 'سیستان و بلوچستان', 'woocommerce' ),
			'TE'  => __( 'تهران', 'woocommerce' ),
			'YA'  => __( 'یزد', 'woocommerce' ),
			'ZA'  => __( 'زنجان', 'woocommerce' ),
		);
		return $states;
	}
	add_filter( 'woocommerce_states', 'iran_states' );
}