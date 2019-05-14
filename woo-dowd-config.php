<?php
/**
 * Define the global arrays
 *
 * @package  Order-Delivery-Date-for-WooCommerce-Day-of-Week-Discount
 */

global $woo_dowd_weekdays, $woo_dowd_languages, $woo_dowd_languages_locale, $woo_dowd_tested_plugin_version_pairs;

/**
 * Define the constants
 *
 * @since 1.0
 */
define( 'WOO_DOWD_VALUE_REPLACE', '@amt@' );

/**
 * This defines the combination of the versions of this plugin and the Order Delivery Date Day plugin
 * that have been tested together in order to let the user know if the the version have or have
 * not been tested together.
 */
$woo_dowd_tested_plugin_version_pairs = array(
	'1.0.0-3.6.1',
	'1.1.0-3.6.1',
	'1.2.0-3.6.1',
	'1.2.0-3.8.1',
	'1.2.1-3.8.1',
);

/**
 * Define the days of week available for delivery
 *
 * @since 1.0
 */
$woo_dowd_weekdays = array(
	'woo_dowd_weekday_0' => __( 'Sunday', 'woo-dowd-delivery-day-discount' ),
	'woo_dowd_weekday_1' => __( 'Monday', 'woo-dowd-delivery-day-discount' ),
	'woo_dowd_weekday_2' => __( 'Tuesday', 'woo-dowd-delivery-day-discount' ),
	'woo_dowd_weekday_3' => __( 'Wednesday', 'woo-dowd-delivery-day-discount' ),
	'woo_dowd_weekday_4' => __( 'Thursday', 'woo-dowd-delivery-day-discount' ),
	'woo_dowd_weekday_5' => __( 'Friday', 'woo-dowd-delivery-day-discount' ),
	'woo_dowd_weekday_6' => __( 'Saturday', 'woo-dowd-delivery-day-discount' ),
);

/**
 * Define the Language locales
 *
 * @since 1.0
 */
$woo_dowd_languages_locale = array(
	'Afrikaans'           => array( 'af_ZA.utf8', 'afr' ),
	'Arabic'              => array( 'ar_SA.utf8', 'ara', 'ar-SA' ),
	'Algerian Arabic'     => array( 'ar_DZ.utf8', 'ara', 'ar-DZ' ),
	'Azerbaijani'         => array( 'az_AZ.utf8', 'aze' ),
	'Indonesian'          => array( 'id_ID.utf8', 'ind', 'id-ID' ),
	'Malaysian'           => array( 'ms_MY.utf8', 'msa', 'ms-MY' ),
	'Dutch Belgian'       => array( 'nl_BE.utf8', 'nld', 'nl-BE', 'nl_NL.utf8' ),
	'Bosnian'             => array( 'bs_BA.utf8', 'bos' ),
	'Bulgarian'           => array( 'bg_BG.utf8', 'bul', 'bg-BG' ),
	'Catalan'             => array( 'ca_ES.utf8', 'cat', 'ca-ES' ),
	'Czech'               => array( 'cs_CZ.utf8', 'ces', 'cs-CZ' ),
	'Welsh'               => array( 'cy_GB.utf8', 'cym' ),
	'Danish'              => array( 'da_DK.UTF8', 'dan', 'da-DK' ),
	'German'              => array( 'de_DE.utf8', 'deu', 'de-DE' ),
	'Estonian'            => array( 'et_EE.utf8', 'est', 'et-EE' ),
	'Greek'               => array( 'el_GR.utf8', 'ell', 'el-GR' ),
	'English Australia'   => array( 'en-AU.utf8', 'eng' ),
	'English New Zealand' => array( 'en_NZ.utf8', 'eng', 'en-NZ' ),
	'English UK'          => array( 'en_GB.utf8', 'eng', 'en-GB' ),
	'Spanish'             => array( 'es_ES.utf8', 'spa', 'es-ES' ),
	'Esperanto'           => array( 'eo_EO.utf8', 'epo' ),
	'Basque'              => array( 'eu_ES.utf8', 'eus' ),
	'Faroese'             => array( 'fo_FO.utf8', 'fao' ),
	'French'              => array( 'fr_FR.utf8', 'fra', 'fr-FR' ),
	'French Swiss'        => array( 'fr_CH.utf8', 'fra', 'fr-Ch' ),
	'Galician'            => array( 'gl_ES.utf8', 'glg' ),
	'Albanian'            => array( 'sq_AL.utf8', 'sqi', 'sq-AL' ),
	'Korean'              => array( 'ko_KR.utf8', 'kor', 'ko-KR' ),
	'Hindi India'         => array( 'hi_IN.utf8', 'hin', 'hi-IN' ),
	'Hebrew'              => array( 'he_IL.utf8', 'heb', 'he_IL' ),
	'Croatian'            => array( 'hr_HR.utf8', 'hrv', 'hr-HR' ),
	'Armenian'            => array( 'hy_AM.utf8', 'hye' ),
	'Icelandic'           => array( 'is_IS.utf8', 'isl', 'is-IS' ),
	'Italian'             => array( 'it_IT.utf8', 'ita', 'it-IT' ),
	'Georgian'            => array( 'ka_GE.utf8', 'kat' ),
	'Khmer'               => array( 'km_KH.utf8', 'khm' ),
	'Latvian'             => array( 'lv_LV.utf8', 'lav' ),
	'Lithuanian'          => array( 'lt_LT.utf8', 'lit', 'lt-LT' ),
	'Macedonian'          => array( 'mk_MK.utf8', 'mkd', 'mk-MK' ),
	'Hungarian'           => array( 'hu_HU.utf8', 'hun', 'hu-HU' ),
	'Malayam'             => array( 'ml_IN.utf8', 'mal' ),
	'Dutch'               => array( 'nl_NL.utf8', 'nld', 'nl-NL' ),
	'Japanese'            => array( 'ja_JP.utf8', 'jpn', 'ja-JP' ),
	'Norwegian'           => array( 'no_NO.utf8', 'nob' ),
	'Thai'                => array( 'th_TH.utf8', 'tha', 'th-TH' ),
	'Persian'             => array( 'fa_IR.utf8', 'fa' ),
	'Polish'              => array( 'pl_PL.utf8', 'pol', 'pl-PL' ),
	'Portuguese'          => array( 'pt_PT.utf8', 'por', 'pt-PT' ),
	'Portuguese Brazil'   => array( 'pt_BR.utf8', 'por', 'pt-BR' ),
	'Romanian'            => array( 'ro_RO.utf8', 'ron', 'ro-RO' ),
	'Romansh'             => array( 'rm_RM.utf8', 'roh' ),
	'Russian'             => array( 'ru_RU.utf8', 'rus', 'ru-RU' ),
	'Slovak'              => array( 'sk_SK.utf8', 'slk', 'sk-SK' ),
	'Slovenian'           => array( 'sl_SI.utf8', 'slv', 'sl-SI' ),
	'Serbian'             => array( 'sr_CS.utf8' ),
	'Finnish'             => array( 'fi_FI.utf8', 'fin', 'fi-FI' ),
	'Swedish'             => array( 'sv_SE.utf8', 'swe', 'sv-SE' ),
	'Tamil'               => array( 'ta_IN.utf8', 'tam' ),
	'Vietnamese'          => array( 'vi_VN.utf8', 'vie', 'vi-VN' ),
	'Turkish'             => array( 'tr_TR.utf8', 'tur', 'tr-TR' ),
	'Ukrainian'           => array( 'uk_UA.utf8', 'ukr', 'uk-UA' ),
	'Chinese Hong Kong'   => array( 'zh_HK.utf8', 'zho' ),
	'Chinese Simplified'  => array( 'zh_CN.utf8', 'zho' ),
	'Chinese Traditional' => array( 'zh_TW.utf8', 'zho' ),
);

/**
 * Define the Languages
 *
 * @since 1.0
 */
$woo_dowd_languages = array(
	'af'    => 'Afrikaans',
	'ar'    => 'Arabic',
	'ar-DZ' => 'Algerian Arabic',
	'az'    => 'Azerbaijani',
	'id'    => 'Indonesian',
	'ms'    => 'Malaysian',
	'nl-BE' => 'Dutch Belgian',
	'bs'    => 'Bosnian',
	'bg'    => 'Bulgarian',
	'ca'    => 'Catalan',
	'cs'    => 'Czech',
	'cy-GB' => 'Welsh',
	'da'    => 'Danish',
	'de'    => 'German',
	'et'    => 'Estonian',
	'el'    => 'Greek',
	'en-AU' => 'English Australia',
	'en-NZ' => 'English New Zealand',
	'en-GB' => 'English UK',
	'es'    => 'Spanish',
	'eo'    => 'Esperanto',
	'eu'    => 'Basque',
	'fa'    => 'Persian',
	'fo'    => 'Faroese',
	'fr'    => 'French',
	'fr-CH' => 'French Swiss',
	'gl'    => 'Galician',
	'sq'    => 'Albanian',
	'ko'    => 'Korean',
	'hi'    => 'Hindi India',
	'hr'    => 'Croatian',
	'hy'    => 'Armenian',
	'he'    => 'Hebrew',
	'is'    => 'Icelandic',
	'it'    => 'Italian',
	'ka'    => 'Georgian',
	'km'    => 'Khmer',
	'lv'    => 'Latvian',
	'lt'    => 'Lithuanian',
	'mk'    => 'Macedonian',
	'hu'    => 'Hungarian',
	'ml'    => 'Malayam',
	'nl'    => 'Dutch',
	'ja'    => 'Japanese',
	'no'    => 'Norwegian',
	'th'    => 'Thai',
	'pl'    => 'Polish',
	'pt'    => 'Portuguese',
	'pt-BR' => 'Portuguese Brazil',
	'ro'    => 'Romanian',
	'rm'    => 'Romansh',
	'ru'    => 'Russian',
	'sk'    => 'Slovak',
	'sl'    => 'Slovenian',
	'sr'    => 'Serbian',
	'fi'    => 'Finnish',
	'sv'    => 'Swedish',
	'ta'    => 'Tamil',
	'vi'    => 'Vietnamese',
	'tr'    => 'Turkish',
	'uk'    => 'Ukrainian',
	'zh-HK' => 'Chinese Hong Kong',
	'zh-CN' => 'Chinese Simplified',
	'zh-TW' => 'Chinese Traditional',
);
