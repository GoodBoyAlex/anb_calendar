<?php
/*
Plugin Name: ANB Calendar
Plugin URI:  https://babaev-an.ru/my_projects/anb-calendar
Description: Плагин, выводящий на страницы сайта простой html-календарь. Он обладает массой настроек.
Version:     0.1
Author:      Alexander Babaev
Author URI:  https://babaev-an.ru/
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl.html
*/
/* Подключаем глобальные переменные. */
require_once plugins_url().'/anb-calendar/inc/global_vars.php';


/* Подключение CSS. */
function anbpl_import_css() {
    $is_css_connect = true;
    $pluginbaseurl = plugins_url().'/anb-calendar';
    //Массив страниц с постами
    $csspageslist = array();
    //Добавляем фильтр на страницы с постами
    $csspageslist = apply_filters('anbpl_csspageslist', $csspageslist);
    //Получаем текущую страницу
    $curpage = $GLOBALS['post']->post_name;
    //и переводим её в нижний регистр
    $curpage = strtolower($curpage);
    //Проверяем, является ли текущая страницей с постами (если задан фильтр)
	if (!empty($csspageslist)) {
        if (!in_array($curpage, $csspageslist)){
            $is_css_connect = false;
        }
    }
    //Выводим css
    if ($is_css_connect) {
        //Событие anbpl_cssload_before
        do_action('anbpl_cssload_before');
        //Выводим главный css
        wp_enqueue_style('anb-posts-list-main-css', $pluginbaseurl.'/css/style.css');
        //Подключаем quicktextfrmt
        $is_qttformat_include = true;
        $is_qttformat_include = apply_filters('anbpl_includequicktextfrmt', $is_qttformat_include);
        if ($is_qttformat_include) {
            wp_enqueue_style('quicktextfrmt-style', $pluginbaseurl.'/ext/quicktextfrmt/quicktextfrmt.css');
        }
        //Событие anbpl_cssload_after
        do_action('anbpl_cssload_after');
    }
    //Событие anbpl_loadunfilteredcss
    do_action('anbpl_loadunfilteredcss');
}
add_action('wp_enqueue_scripts', 'anbpl_import_css');
/* Подключаем скрипты JS. */
function anbpl_import_scripts() {
    $is_js_connect = true;
    $pluginbaseurl = plugins_url().'/anb-posts-list';
    //Массив страниц с постами
    $jspageslist = array();
    //Добавляем фильтр на страницы с постами
    $jspageslist = apply_filters('anbpl_jspageslist', $jspageslist);
    //Получаем текущую страницу
    $curpage = $GLOBALS['post']->post_name;
    //и переводим её в нижний регистр
    $curpage = strtolower($curpage);
    //Проверяем, является ли текущая страницей с постами (если задан фильтр)
	if (!empty($jspageslist)) {
        if (!in_array($curpage, $jspageslist)){
            $is_js_connect = false;
        }
    }
    //Подключаем JS
    if ($is_js_connect) {
        //Событие anbpl_jsload_before
        do_action('anbpl_jsload_before');
        //Выводим главный js
        wp_enqueue_script('anb-posts-list-main-js', $pluginbaseurl.'/js/main.js');
        //Подключаем anb.openurl
        $is_anbopenurl_include = true;
        $is_anbopenurl_include = apply_filters('anbpl_includeanbopenurl', $is_anbopenurl_include);
        if ($is_anbopenurl_include) {
            wp_enqueue_script('anb_openurl', $pluginbaseurl.'/ext/anb.openurl/anb.openurl.js');
        }
        //Событие anbpl_jsload_after
        do_action('anbpl_jsload_after');
    }
    //Событие anbpl_loadunfilteredjs
    do_action('anbpl_loadunfilteredjs');
}
add_action('wp_enqueue_scripts', 'anbpl_import_scripts');
/* Вспомогательная функция конвертации строкового типа в булевый. */
function anbpl_str2bool ($str) {
    $str = strtolower($str);
    switch ($str) {
    case "true":
        $result = true;
        break;
    case "t":
        $result = true;
        break;
    case "yes":
        $result = true;
        break;
    case "y":
        $result = true;
        break;
    case "false":
        $result = false;
        break;
    case "f":
        $result = false;
        break;
    case "no":
        $result = false;
        break;
    case "n":
        $result = false;
        break;
    default:
       $result = false;
    }
    return $result;
}
/* Подключаем модули. */
include_once ('inc/functions.php');
/* Шорткод anb_postslist показывает список записей. */
function anbpl_shortcode_postslist ($atts = [], $content = null, $tag = '') {
	$defatts = array(
        'category' => '0',
        'categorynames' => '',
        'name' => '',
        'date_d' => -1,
        'date_m' => -1,
        'date_y' => -1,
        'time' => '',
        'tag' => '',
        'postinpage' => 20,
        'usepaginator' => true,
        'showfeaturedimg' => true,
        'nopoststext' => 'No posts in the specified category...'
    );
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
	extract(shortcode_atts($defatts, $atts));
	$result = '';
    $post_cns = array(
        'category' => (string)$category,
        'categorynames' => (string)$categorynames,
        'name' => (string)$name,
        'date_d' => (integer)$date_d,
        'date_m' => (integer)$date_m,
        'date_y' => (integer)$date_y,
        'time' => (string)$time,
        'tag' => (string)$tag
    );
    $post_opt = array(
        'postinpage' => (integer)$postinpage,
        'usepaginator' => (boolean)anbpl_str2bool($usepaginator),
        'showfeaturedimg' => (boolean)anbpl_str2bool($showfeaturedimg),
        'nopoststext' => (string)$nopoststext
    );
    $result = anbpl_getposts($post_cns, $post_opt);
	return $result;
}
/* Инициализация Shortcode.
* в. 1.0 (А. Бабаев)  */
function anbpl_shortcodes_init() {
    add_shortcode('anb_postslist', 'anbpl_shortcode_postslist');
}
add_action('init', 'anbpl_shortcodes_init');
?>