<?php
/*
Plugin Name: Mit Ggauz Exchange [MGE]
Plugin URI: http://test.com/
Description: Exchange rate informer for Wordpress
Version: 1.1
Author: Mit Ggauz
License: MIT
*/

require_once('includes/GgauzExchange.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function mge_create_table()
{
    $mge = new GgauzExchange();
    $name = $mge->getTableName();

    if ($mge->db->get_var('SHOW TABLES LIKE '. $name) != $name) {
        $sql = $mge->getTableSqlSchema($name);
        dbDelta($sql);
        add_option('mge_db_version', '1.0');
    }
}
register_activation_hook(__FILE__, 'mge_create_table');

function mge_get_data()
{
    $mge = new GgauzExchange();
    $currentDate = $mge->getCurrentDate();
    $lastUpdateDate = $mge->getLastUpdatedDate();
    $url = "http://www.bnm.md/ru/official_exchange_rates?get_xml=1&date=". $currentDate;

    if ($lastUpdateDate != $currentDate) {
        $get_xml_today = file_get_contents($url, 0);
        $xml_today = new SimplexmlElement($get_xml_today);
        $xml_date = (string) $xml_today->attributes()->{'Date'};

        if ($xml_date == $currentDate) {
            foreach ($xml_today->Valute as $ind => $item) {
                $rates_num = (string) trim($item->NumCode);
                $rates_char = (string) trim($item->CharCode);
                $rates_value = (string) trim($item->Value);
                $rates_nominal = (string) trim($item->Nominal);

                $mge->setXmlData(array(
                    'code'=>$rates_num,
                    'symb'=>$rates_char,
                    'nominal'=>$rates_nominal,
                    'rate'=>$rates_value
                ));
            }
        }
    }
}
mge_get_data();


/**
 * Ajax action
 * for logged in and anonymus users
 */
add_action('wp_ajax_chart_data', 'get_chart_data');
add_action('wp_ajax_nopriv_chart_data', 'get_chart_data');

function get_chart_data()
{
    $mge = new GgauzExchange();
    $code = (int) $_GET['code'];
    echo $mge->getDataSetForChart($code);

    wp_die();
}

/**
 * Add stylesheet to the page
 */
if (!function_exists('ggauz_exchange_css')) {
    function ggauz_exchange_css() {
        wp_enqueue_style('mge_css', plugins_url() .'/'. basename(dirname(__FILE__)) .'/css/mge.css', array(), null);
    }
}
add_action('wp_enqueue_scripts', 'ggauz_exchange_css');

/**
 * Add js to the page
 */
if (!function_exists('google_chart_js')) {
    function google_chart_js() {
        wp_enqueue_script('google_chart', 'https://www.google.com/jsapi', array(), null);
    }
}
add_action('wp_enqueue_scripts', 'google_chart_js');

if (!function_exists('ggauz_exchange_js')) {
    function ggauz_exchange_js() {
        wp_enqueue_script('mge_js', plugins_url() .'/'. basename(dirname(__FILE__)) .'/js/mge.js', array('jquery'), null);
    }
}
add_action('wp_enqueue_scripts', 'ggauz_exchange_js');
