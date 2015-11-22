<?php
/*
Plugin Name: MG Exchange [MGE]
Plugin URI: https://github.com/ddimitrioglo/ggauz-exchange
Description: Exchange rate informer for Wordpress
Version: 1.1
Author: Mit Ggauz
License: MIT
*/

require_once('includes/GgauzExchange.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/**
 * Create plugin's table action
 */
function mge_create_table()
{
    $mge = new GgauzExchange();
    $name = $mge->getTableName();

    if ($mge->db->get_var('SHOW TABLES LIKE '. $name) != $name) {
        $sql = $mge->getTableSqlSchema($name);
        dbDelta($sql);

        add_option('mge_db_version', $mge->getDbVersion());
    }
}
register_activation_hook(__FILE__, 'mge_create_table');

/**
 * Drop old plugin's table if version was changed
 */
function mge_update_table()
{
    $mge = new GgauzExchange();
    $installedDbVersion = get_option('mge_db_version');

    if (isset($installedDbVersion) && $installedDbVersion != $mge->getDbVersion()) {
        $sql = $mge->getDropTableSqlSchema();
        $mge->db->query($sql);
        delete_option('mge_db_version');
        mge_create_table();
    }
}
add_action('plugins_loaded', 'mge_update_table');

/**
 * Get data and save into the plugin's table
 */
function mge_get_data()
{
    $mge = new GgauzExchange();
    $currentDate = $mge->getCurrentDate();
    $lastUpdateDate = $mge->getLastUpdatedDate();

    if ($lastUpdateDate != $currentDate) {
        $yesterdayDate = $mge->getYesterdayDate();
        $todayData = $mge->getXmlDataByDate($currentDate);
        $yesterdayData = $mge->getXmlDataByDate($yesterdayDate);

        foreach($todayData as $key => $value) {
            $difference = $value['rate'] - $yesterdayData[$key]['rate'];
            $value['diff'] = round($difference, 4);
            $mge->setXmlData($value);
        }
    }
}
mge_get_data();


/**
 * Ajax action for logged in and anonymus users
 */
function get_chart_data()
{
    $mge = new GgauzExchange();
    $code = (int) $_GET['code'];
    echo $mge->getDataSetForChart($code);

    wp_die();
}
add_action('wp_ajax_chart_data', 'get_chart_data');
add_action('wp_ajax_nopriv_chart_data', 'get_chart_data');

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
 * Add google jsAPI to the page
 */
if (!function_exists('google_chart_js')) {
    function google_chart_js() {
        wp_enqueue_script('google_chart', 'https://www.google.com/jsapi', array(), null);
    }
}
add_action('wp_enqueue_scripts', 'google_chart_js');

/**
 * Add plugin js to the page
 */
if (!function_exists('ggauz_exchange_js')) {
    function ggauz_exchange_js() {
        wp_enqueue_script('mge_js', plugins_url() .'/'. basename(dirname(__FILE__)) .'/js/mge.js', array('jquery'), null);
    }
}
add_action('wp_enqueue_scripts', 'ggauz_exchange_js');
