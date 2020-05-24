<?php
/**
 * Модуль "Новости".
 * версия 2.0.
 * Автор: Александр Бабаев.
 * @package MedColBSU
 */
/**
* САМОЗАЩИТА (не допускается доступ по http(s))
*/
if (!defined('ABSPATH')) {
	exit;
}
/* Вспомогательная функция получения URL. */
function anbpl_generateurl (int $pageid) {
	//По умолчанию, текущий URL
	$result = $_SERVER['REQUEST_URI'];
	//Поставим "ppage"
	$result = add_query_arg(array('ppage' => $pageid), $result);
	return $result;
}
/* Функция определения браузера IE. */
function anbpl_useiexplorer () {
    $result = false;
    global $is_IE;
    $result = $is_IE;
    return $result;
}
/* Функция получения пагинатора. */
function anbpl_getpaginator ($pageid, $newscount, $pagemax, $showallnewsbutton) {
	$pageid = (integer)$pageid;
	$newscount = (integer)$newscount;
	$pagemax = (integer)$pagemax;
	$showallnewsbutton = (boolean)$showallnewsbutton;
	$result = '';
	include (locate_template(plugins_url().'/anb-posts-list/inc/paginator/paginator.php'));
	return $result;
}
/* Функция формирования строки категрий. */
function anbpl_formatcategories ($categorystr, $is_ID_list) {
    $str = preg_replace('/\s/', '', $categorystr);
    if ($is_ID_list) {
        $strarray = array();
        $strarray = explode(',', $str);
        $strout = array();
        foreach ($strarray as $catslug) {
            $addstr = '';
            if (mb_substr($catslug, 0, 1) == '-') {
                $addstr = '-';
                $catslug = mb_substr($catslug, 1);
            }
            $addstr .= get_cat_ID($catslug);
            $strout[] = $addstr;
        }
        $str = implode(',', $strout);
    }
    return $str;
}
/* Функция получения постов. */
function anbpl_dbextractposts (array $search_cns, $offset, $count) {
    $result = array(
        'posts' => array(),
        'allpostcount' => 0
    );
    // Вызываем глобальную переменную $post
    global $post;
    // Делаем её бекап
    $tmp_post = $post;
    // Зададим категорию для поиска
    $category = '';
    // -- Для начала проверяем по ID
    $category = anbpl_formatcategories($search_cns['category'], true);
    // -- Если пусто, то по slug
    if ($category == '') {
        $category = anbpl_formatcategories($search_cns['categorynames'], false);
    }
    // Задаём параметры поиска
    $args = array(
		'numberposts' => -1,
		'category' => $category,
		'post_type' => 'post',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'suppress_filters' => false
    );
    // Получаем все записи из категории
    $incatposts = get_posts($args);
    //Делаем выборку
    $postsall = array();
    foreach ($incatposts as $post) {
        setup_postdata($post);
        $is_include = true;
        //Выборка по названию
        if (($search_cns['name'] != '') && (strcasecmp($search_cns['name'], esc_html(get_the_title())) != 0)) {
            $is_include = false;
        }
        //Переменные даты/времени
        $date_d = get_the_date('j');
        $date_m = get_the_date('n');
        $date_y = get_the_date('Y');
        $r_time = get_the_date('H:i:s');
        //Выборка по дню
        if (($search_cns['date_d'] > -1) && ($search_cns['date_d'] != $date_d)) {
            $is_include = false;
        }
        //Выборка по месяцу
        if (($search_cns['date_m'] > -1) && ($search_cns['date_m'] != $date_m)) {
            $is_include = false;
        }
        //Выборка по году
        if (($search_cns['date_y'] > -1) && ($search_cns['date_y'] != $date_y)) {
            $is_include = false;
        }
        //Выборка по времени
        if (($search_cns['time'] != '') && (strcasecmp($search_cns['time'], $r_time) != 0)) {
            $is_include = false;
        }
        //Выборка по меткам
        $tags = wp_get_post_tags($post->ID, array('fields' => 'names'));
        $tags = array_change_key_case((array)$tags, CASE_LOWER);
        $needtags = strtolower($search_cns['tag']);
        if (($newsconditions['tag'] != '') && (!in_array($needtags, $tags))) {
            $is_include = false;
        }
        // Добавляем элемент
        if ($is_include) {
            array_push($postsall, $post);
        }
    }
    //Получаем количество постов
    $countofnews = count($postsall);
    $result['allpostcount'] = $countofnews;
    //Если массив $postsall содержит не больше необходимого, то присваиваем выводу его
    if (($count == -1) || ($countofnews < $count)) {
        $result['posts'] = $postsall;
    } else {
        if (($offset > 0) && ($offset < $countofnews)) {
            for ($i = 1; $i <= $offset; $i++) {
                unset($postsall[$i-1]);
            }
            $postsall = array_values($postsall);
        }
        $countofnews = count($postsall);
        for ($i = $count + 1; $i <= $countofnews; $i++) {
            unset($postsall[$i-1]);
        }
        $postsall = array_values($postsall);
        $result['posts'] = $postsall;
    }
    // Возвращаем былое значение $post
	$post = $tmp_post;
    return $result;
}
/*
  ФУНКЦИЯ ВЫВОДА ПОСТОВ.
  Входящие параметры:
  $post_cns - массив, задающий выбоку постов:
    - category (string) - список ID категорий (используйте - перед цифрой, чтобы исключить категорию из списка постов);
    - categorynames (string) - список slug'ов категорий (используйте - перед строкой, чтобы исключить категорию из списка постов);
    - name (string) - имя поста;
    - date_d (integer) - день публикации поста (-1 -- любой);
    - date_m (integer) - месяц публикации поста (-1 -- любой);
    - date_y (integer) - год публикации поста (-1 -- любой);
    - time (string) - время публикации поста (писать в формате 00:00:00);
    - tag (array) - метки поста.
  $post_opt - массив параметров:
    - postinpage (integer) - количество отображаемых записей;
    - usepaginator (boolean) - использовать пагинацию;
    - showfeaturedimg (boolean) - показывать изображение записи;
    - nopoststext (string) - текст "Нет записей".
*/
function anbpl_getposts (array $post_cns, array $post_opt) {
    // Инициальзация переменных массива по умолчанию
    $cns = array(
        'category' => '',
        'categorynames' => '',
        'name' => '',
        'date_d' => -1,
        'date_m' => -1,
        'date_y' => -1,
        'time' => '',
        'tag' => ''
    );
    $cns = $post_cns;
    $opt = array(
        'postinpage' => 10,
        'usepaginator' => true,
        'showfeaturedimg' => true,
        'nopoststext' => ''
    );
    $opt = $post_opt;
    // Эта переменная позволяет задать блоки вывода на основе браузера
    $outblocks = array(
        'start' => '<div id="postlist" class="anbplcontainer">',
        'nopostsopen' => '<div class="postlistisempty">',
        'nopostsclose' => '</div>',
        'recblockopen' => '<div class="anbplblockcontainer">',
        'recblockclose' => '</div>',
        'paginatoropen' => '',
        'paginatorclose' => '',
        'finish' => '</div>',
    );
    if (anbpl_useiexplorer()) {
        $outblocks['start'] = '<table id="news" class="hiddentable"><tbody>';
        $outblocks['nopostsopen'] = '<tr><td><span class="postlistisempty">';
        $outblocks['nopostsclose'] = '</span></td></tr>';
        $outblocks['recblockopen'] = '<tr>';
        $outblocks['recblockclose'] = '</tr>';
        $outblocks['paginatoropen'] = '<tr>';
        $outblocks['paginatorclose'] = '</tr>';
        $outblocks['finish'] = '</tbody></table>';
    }
	// Текущая страница
	if (isset($_GET['ppage'])) {
		$currentpage = $_GET['ppage'];
	} else {
		$currentpage = 1;
	}
    //Инициализация переменной вывода
    $anbplout = '';
    //Открытие блока новостей
    $anbplout .= $outblocks['start'];
    // Смещение для пагинации
    $noffset = 0;
	if ($opt['usepaginator']) {
		$noffset = ($currentpage - 1)*$opt['postinpage'];
	}
    // Получаем записи
    $posts_pck = anbpl_dbextractposts($cns, $noffset, $opt['postinpage']);
	$posts = $posts_pck['posts'];
    $p_count = $posts_pck['allpostcount'];
	if ($p_count == 0) {
        // записей нет
        $msgnoposts = $outblocks['nopostsopen'].$opt['nopoststext'].$outblocks['nopostsclose'];
        $anbplout.= apply_filters('anbpl_nopostsmsg', $msgnoposts);
	} else {
        global $post;
        $t_post = $post;
		foreach ($posts as $post) {
			setup_postdata($post);
            // Загружаем URL картинки новости
            if (has_post_thumbnail()) {
                $imgurl = get_the_post_thumbnail_url();
            } else {
                $imgurl = plugins_url().'/anb-posts-list/images/no-img.png';
            }
            $imgurl = apply_filters('anbpl_featuredimgurl', $imgurl);
			// Открываем блок записей
            $str = $outblocks['recblockopen'];
            $anbplout .= apply_filters('anbpl_postblockopen', $str);
            // Получаем тип (формат) записи
			$template = get_post_format();
			if($template == NULL){
				$template = 'normal';
			}
            // -- добавляем шаблоны для браузера IE
			if (anbpl_useiexplorer()) {
                $template = 'ie/'.$template;
            }
            // Вставляем соответствующий шаблон
            include_once ('templates/'.$template.'.php');
			// Закрываем блок записей
            $str = $outblocks['recblockclose'];
            $anbplout .= apply_filters('anbpl_postblockclose', $str);
		}
		wp_reset_postdata();
		// Выводим пагинатор
		if ($usepaginator) {
            $anbplout .= $outblocks['paginatoropen'];
			$anbplout .= anbpl_getpaginator($currentpage, $newscount, $postinpage, $disp_anlinks);
            $anbplout .= $outblocks['paginatorclose'];
		}
        //В случае, если пагинация отключена, а требуется вывести ссылку на все новости
        if ((!$usepaginator) && $disp_anlinks) {
            $anbplout .= $outblocks['paginatoropen'];
			$anbplout .= $outblocks['allnewslink'];
            $anbplout .= $outblocks['paginatorclose'];
		}
        $post = $t_post;
	}
	// Закрываем блок новостей
    $anbplout .= $outblocks['finish'];
	// Вывод на экран
	return $anbplout;
}
?>