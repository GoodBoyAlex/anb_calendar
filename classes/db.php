<?php
/**
 * Классы для работы с БД.
 * версия 1.0.
 * Автор: Александр Бабаев.
 * @package ANB CALENDAR
 */
namespace anb_calendar;
/**
* САМОЗАЩИТА (не допускается доступ по http(s))
*/
if (!defined('ABSPATH')) {
	exit;
}
/*
	**********************************************
	I. Класс для работы с БД событий.
	**********************************************
	Добавление, редактирование или удаление
	записей БД преподавателей колледжа.

	Используется класс calevent.

	Автор: Александр Бабаев
	Версия: 1.0
*/
class db_eventlist
{
	use sharefunctions {
        assignarray as private;
    }
	const DBNAME = 'anb_caleventlist';		#имя БД
	private $event;							#ссылка на элемент класса calevent
	private $wpdblink;						#ссылка на wpdb
    /* PUBLIC */
    public function __construct() {
		global $wpdb;
		$this->wpdblink = &$wpdb;
		$this->resetcls();
    }
    public function __destruct() {
		unset($this->event);
		unset($this->wpdblink);
	}
	/* 1. Добавление события. */
    public function add (calevent &$event) {
		$this->wpdblink->insert(self::DBNAME, array(
			"event_type" => $event->type,
			"event_date_day" => $event->date->getday(),
			"event_date_month" => $event->date->getmonth(),
			"event_date_year" => $event->date->getyear(),
			"event_description" => \serialize($event->description)
		), array("%d", "%d", "%d", "%d", "%s"));
		$event->id = (int)$this->wpdblink->insert_id;
	}
	/* 2. Получение событий. */
	/** 2.1. Получение одного конкретного события по ID. **/
	public function getevent (calevent &$event) {
		$req_query = "SELECT * FROM ".self::DBNAME." WHERE id={$event->id}";
		$t_row = $this->wpdblink->get_row($req_query);
		$event->type = (int)$t_row->{'event_type'};
		$event->date->setdate((int)$t_row->{'event_date_day'}, (int)$t_row->{'event_date_month'}, (int)$t_row->{'event_date_year'});
		$event->description = \unserialize($t_row->{'event_description'});
	}
	/** 2.2. Получение списка событий по параметрам. */
	/*
		Вы можете сделать выборку по разным параметрам, указав их в ассоциативном
		массиве - параметре $params:
		----------------------------------------------------------------------------------------------------------
		Параметр	|Тип параметра		|Значение  по умолчанию	|Отключение параметра		|Описание
		----------------------------------------------------------------------------------------------------------
		id			|int				|0						|0 (любой)					|id события
		----------------------------------------------------------------------------------------------------------
		type		|int				|-1						|<0 (любой)					|тип события
		----------------------------------------------------------------------------------------------------------
		day			|int (1..31)		|0						|<1 (любой)					|день события
		----------------------------------------------------------------------------------------------------------
		month		|int (1..12)		|0						|<1 (любой)					|месяц события
		----------------------------------------------------------------------------------------------------------
		year		|int (2000..2100)	|-1						|<0 (любой),0 (ежегодные)	|год события
		----------------------------------------------------------------------------------------------------------
		connectword	|int (0..1)*		|0						|невозможно					|объединение условий
		----------------------------------------------------------------------------------------------------------
		orderby		|int (0..4)**		|0						|невозможно					|сортировка результата
		----------------------------------------------------------------------------------------------------------
		ПРИМЕЧАНИЯ:
			* - возможные параметры: 0 - AND, 1 - OR.
			** - возможные параметры: 0 - по ID, 1 - по type, 2 - по day, 3 - по month, 4 - по year.
		Параметр $eventslist (тип calevents) - список событий.
	*/
	public function getevents (array $params, calevents &$eventslist) {
		$options = array(
			'id' => 0,
			'type' => -1,
			'day' => 0,
			'month' => 0,
			'year' => -1,
			'connectword' => 0,
			'orderby' => 0
		);
		$this->assignarray($params, $options);
		$where_query = array();
		if ($optionid['id'] > 0) $where_query[] = "(event_id='{$options['id']}')";
		if ($optionid['type'] > -1) $where_query[] = "(event_type='{$options['type']}')";
		if ($optionid['day'] > 0) $where_query[] = "(event_date_day='{$options['day']}')";
		if ($optionid['month'] > 0) $where_query[] = "(event_date_month='{$options['month']}')";
		if ($optionid['year'] > 0) $where_query[] = "(event_date_year='{$options['year']}')";
		$req_query = "SELECT * FROM ".SELF::DBNAME;
		if (!empty($where_query)) $req_query .= '  WHERE '.join(($options['connectword'] = 1) ? " OR " : " AND ", $where_query);
		switch ($options['orderby']) {
			case 0:
				$req_query .= ' ORDER BY event_id';
			break;
			case 1:
				$req_query .= ' ORDER BY event_type';
			break;
			case 2:
				$req_query .= ' ORDER BY event_date_day';
			break;
			case 3:
				$req_query .= ' ORDER BY event_date_month';
			break;
			case 4:
				$req_query .= ' ORDER BY event_date_year';
			break;
			default: $req_query .= ' ORDER BY event_id';
		}
		$events = $this->wpdblink->get_results($req_query);
		$evenlist->clear();
		$date = new caldate;
		foreach ($events as $event) {
			$date->setdate($event->{'event_date_day'}, $event->{'event_date_month'}, $event->{'event_date_year'});
			$evenlist->add(
				(int)$event->{'event_id'},
				(int)$event->{'event_type'},
				$date,
				(array)$event->{'event_description'}
			);
		}
	}
	/* 3. Редактирование информации о преподавателе. */
	//С помощью класса mcbsu_teacher.
	public function edit (mcbsu_teacher $mcbsu_teacher) {
		$this->wpdblink->update(self::DBNAME, array(
			"name" => $mcbsu_teacher->getname(),
			"img_name" => $mcbsu_teacher->getimg(),
			"linkbsu" => $mcbsu_teacher->getlinkbsu(),
			"t_type" => $mcbsu_teacher->gettype(),
			"t_group" => $mcbsu_teacher->getgroup(),
			"t_category" => $mcbsu_teacher->getcategory(),
		), array("id" => $mcbsu_teacher->getid()), array("%s", "%s", "%s", "%d", "%s"), array("%d"));
	}
	//С помощью массива.
	public function editlist (array $mcbsu_teacher) {
		$this->resetcls();
		$this->teacher->addlist($mcbsu_teacher);
		$this->edit($this->teacher);
	}
	/* 4. Удаление преподавателя из БД. */
	public function delete (int $teacher_id) {
		$req_query = "DELETE FROM ".self::DBNAME." WHERE id='$teacher_id'";
		$sql = $this->wpdblink->prepare($req_query, $teacher_id);
		$this->wpdblink->query($sql);
	}

	/* 6. Получение количества записей в таблице. */
	public function count() :int {
		$req_query = "SELECT COUNT(*) FROM ".self::DBNAME;
		$count = $this->wpdblink->get_results($req_query);
		return (int)$count[0]->{'COUNT(*)'};
	}
	/* PRIVATE */
	//Очистка данных.
	private function resetcls () {
		if (isset($this->teacher)) {
			unset($this->teacher);
		}
		$this->teacher = new mcbsu_teacher;
	}
}
/*
	***************************************************************
	II. Класс для работы с БД соотношения записей и преподавателей.
	***************************************************************
	Добавление, редактирование или удаление сопоставления постов с
	преподавателем колледжа.

	Автор: Александр Бабаев
	Версия: 2.0
*/
class mcbsu_db_teacherslist_relationships
{
	const DBNAME = 'mcbsu_teacherslist_relationships';		#имя БД
	private $wpdblink;										#ссылка на wpdb
	/* PUBLIC */
    public function __construct() {
		global $wpdb;
		$this->wpdblink = &$wpdb;
    }
    public function __destruct() {
		unset($this->wpdblink);
	}
	/* 1. Проверка, существует ли уже запись */
	function isrelexists (int $postid, int $teacherid) {
		$rel_exists = false;
		$id = $this->getid($postid, $teacherid);
		if ($id > 0) {
			$rel_exists = true;
		}
		return $rel_exists;
	}
	/* 2. Добавление соотношения записи и преподавателя. */
	function add (int $postid, int $teacherid) :int {
		$result = -1;
		if (!$this->isrelexists($postid, $teacherid)) {
			$this->wpdblink->insert(self::DBNAME, array(
				"postid" => $postid,
				"teacherid" => $teacherid
			), array("%d", "%d"));
			$result = (int)$this->wpdblink->insert_id;
		}
		return $result;
	}
	/* 3. Удаление соотношения. */
	function delete (int $recid) {
		$req_query = "DELETE FROM ".self::DBNAME." WHERE id='%d'";
		$sql = $this->wpdblink->prepare($req_query, $recid);
		$this->wpdblink->query($sql);
	}
	/* 4. Получение соотношения преподавателей по записи. */
	function getbypostid (int $postid) {
		$db_query = "SELECT teacherid FROM ".self::DBNAME." WHERE postid=$postid";
		$db_result = $this->wpdblink->get_col($db_query);
		return $db_result;
	}
	/* 5. Получение соотношения записей по преподавателю. */
	function getbyteacherid (int $teacherid) {
		$db_query = "SELECT postid FROM ".self::DBNAME." WHERE teacherid=$teacherid";
		$db_result = $this->wpdblink->get_col($db_query);
		return $db_result;
	}
	/* 6. Получение идентификатора соотношения. */
	function getid (int $postid, int $teacherid) {
		$db_query = "SELECT id FROM ".self::DBNAME." WHERE postid='$postid' AND teacherid='$teacherid'";
		$db_result = $this->wpdblink->get_var($db_query);
		return $db_result;
	}
}
/*
	***************************************************************
	III. Класс для работы с БД настроек темы.
	***************************************************************
	Добавление, получение или удаление настроек темы.

	Автор: Александр Бабаев
	Версия: 2.0
*/
class mcbsu_db_thmoptions
{
	const DBNAME = 'mcbsu_theme_options';	#имя БД
	private $wpdblink;						#ссылка на wpdb
	/* PUBLIC */
    public function __construct() {
		global $wpdb;
		$this->wpdblink = &$wpdb;
    }
    public function __destruct() {
		unset($this->wpdblink);
	}
	/* 1. Добавление \ обновление опции. */
	//добавление строки
	function set (string $optionname, string $optionvalue) {
		$is_optexists = (boolean)($this->getoptid($optionname) > 0);
		if ($is_optexists) {
			$this->wpdblink->update(self::DBNAME, array("option_value" => $optionvalue), array("option_name" => $optionname), array("%s"), array("%s"));
		} else {
			$this->wpdblink->insert(self::DBNAME, array("option_name" => $optionname, "option_value" => $optionvalue), array("%s", "%s"));
		}
	}
	//добавление целого числа
	function set_asint (string $optionname, int $optionvalue) {
		$opt_value = (string)$optionvalue;
		$this->set($optionname, $opt_value);
	}
	//добавление нецелого числа
	function set_asfloat (string $optionname, float $optionvalue) {
		$opt_value = (string)$optionvalue;
		$this->set($optionname, $opt_value);
	}
	//добавление bool-выражения
	function set_asbool (string $optionname, bool $optionvalue) {
		$opt_value = $optionvalue ? 'true' : 'false';
		$this->set($optionname, $opt_value);
	}
	//добавление массива
	function set_asarray (string $optionname, array $optionvalue) {
		$opt_value = serialize($optionvalue);
		$this->set($optionname, $opt_value);
	}
	/* 2. Получение опции. */
	//получение строки
	function get (string $optionname) :string {
		$db_query = "SELECT option_value FROM ".self::DBNAME." WHERE option_name='$optionname'";
		$db_result = $this->wpdblink->get_var($db_query);
		return (string)$db_result;
	}
	//получение целого числа
	function get_asint (string $optionname) :int {
		return (int)$this->get($optionname);
	}
	//получение нецелого числа
	function get_asfloat (string $optionname) :float {
		return (float)$this->get($optionname);
	}
	//получение bool-выражения
	function get_asbool (string $optionname) :bool {
		$opt_value = strtolower($this->get($optionname));
		$result = ($opt_value == 'true') ? true : false;
		return (bool)$result;
	}
	//получение массива
	function get_asarray (string $optionname, bool $need_to_htmldecode = false) :array {
		$opt_value = $this->get($optionname, $opt_value);
		if ($need_to_htmldecode) {
			$opt_value = htmlspecialchars_decode($opt_value);
		}
		return (array)unserialize($opt_value);
	}
	/* 3. Получение индификатора опции. */
	function getoptid (string $optionname) :int {
		$db_query = "SELECT option_id FROM ".self::DBNAME." WHERE option_name='$optionname'";
		$db_result = $this->wpdblink->get_var($db_query);
		if ($db_result == null) {
			$db_result = 0;
		} else {
			$db_result = (integer)$db_result;
		}
		return (int)$db_result;
	}
	/* 4. Удаление опции. */
	function delete (int $optionid) {
		$db_query = "DELETE FROM ".$db." WHERE option_id='%d'";
		$db_query = $this->wpdblink->prepare($db_query, $optionid);
		$this->wpdblink->query($db_query);
	}
}
/*
	***************************************************************
	IV. Класс для работы с БД дней рождения преподавателей.
	***************************************************************
	Добавление, редактирование или удаление записей БД о днях
	рождения преподавателей колледжа.

	Используется класс mcbsu_tbirthday или массив,
	аналогичный классу.

	Автор: Александр Бабаев
	Версия: 2.0
*/
class mcbsu_db_tbdays
{
    const DBNAME = 'mcbsu_teachersbirthdays';	#имя БД
	private $tbirthday;							#ссылка на элемент класса mcbsu_tbirthday
	private $wpdblink;							#ссылка на wpdb
    /* PUBLIC */
    public function __construct() {
		global $wpdb;
		$this->wpdblink = &$wpdb;
		$this->resetcls();
    }
    public function __destruct() {
		unset($this->tbirthday);
		unset($this->wpdblink);
	}
	/* 1. Добавление дня рождения преподавателя. */
	//С помощью класса mcbsu_tbirthday.
    public function add (mcbsu_tbirthday &$birthday) {
		$this->wpdblink->insert(self::DBNAME,
		array(
			"fio" => $birthday->getfio(),
			"position" => $birthday->getstafffunct(),
			"date_of" => $birthday->getdate_of(),
			"month_of" => $birthday->getmonth_of(),
			"teacher_id" => $birthday->getteacher_id()
		),
		array("%s", "%s", "%d", "%d", "%d"));
		$birthday->setid((int)$this->wpdblink->insert_id);
	}
	//С помощью массива.
    public function addlist (array &$birthday) {
		$this->resetcls();
		$this->tbirthday->addlist($birthday);
		$this->add($this->tbirthday);
		$birthday['id'] = $this->tbirthday->getid();
	}
	/* 2. Получение информации о дне рождения преподавателя. */
	/*
		Вы можете сделать выборку по ID ($byID), по месяцу ($byMONTH), по дню ($byDAY)
		и по ФИО ($byFIO). Выборку можно комбинировать, то есть выбрать, например,
		по ID и месяцу. Если вы не хотите параметру, то в соответствующей переменной
		поставьте -1 или пустую строку (для $byFIO). Если все параметры
		(кроме результата $bdlist) будут установлены в -1 (или пустую строку, в случае
		с $byFIO), то в результате будет вся БД. Полученные данные записываются в
		заранее созданный элемент класса mcbsu_tbirthdays $bdlist. Вы также можете
		указать столбец сортировки результата: 0 (по умолчанию) - по ID, 1 - по ФИО,
		2 - по месяцу и 3 - по дню.
	*/
	public function get (int $byID, int $byMONTH, int $byDAY, string $byFIO, mcbsu_tbirthdays &$bdlist, int $orderby = 0) {
		$where_query = array();
		if ($byID > -1) $where_query[] = "(id='$byID')";
		if ($byMONTH > -1) $where_query[] = "(month_of='$byMONTH')";
		if ($byDAY > -1) $where_query[] = "(date_of='$byDAY')";
		if (trim($byFIO) !== '') $where_query[] = "(fio LIKE '%$byFIO%')";
		$req_query = 'SELECT * FROM '.self::DBNAME;
		if (!empty($where_query)) $req_query .= '  WHERE '.join(' AND ', $where_query);
		switch ($orderby) {
			case 0:
				$req_query .= ' ORDER BY id';
			break;
			case 1:
				$req_query .= ' ORDER BY fio';
			break;
			case 2:
				$req_query .= ' ORDER BY month_of';
			break;
			case 3:
				$req_query .= ' ORDER BY date_of';
			break;
			default: $req_query .= ' ORDER BY id';
		}
		$bday = $this->wpdblink->get_results($req_query);
		$bdlist->clear();
		foreach ($bday as $bdrec) {
			$bdlist->add((int)$bdrec->{'id'}, (string)$bdrec->{'fio'}, (string)$bdrec->{'position'}, (int)$bdrec->{'date_of'}, (int)$bdrec->{'month_of'}, (int)$bdrec->{'teacher_id'});
		}
	}
	/* 3. Редактирование информации о дне рождения преподавателя. */
	//С помощью класса mcbsu_tbirthday.
	public function edit (mcbsu_tbirthday $birthday) {
		$this->wpdblink->update(self::DBNAME, array(
			"fio" => $birthday->getfio(),
			"position" => $birthday->getstafffunct(),
			"date_of" => $birthday->getdate_of(),
			"month_of" => $birthday->getmonth_of(),
			"teacher_id" => $birthday->getteacher_id()
		), array("id" => $birthday->getid()), array("%s", "%s", "%d", "%d", "%d"), array("%d"));
	}
	//С помощью массива.
	public function editlist (array $birthday) {
		$this->resetcls();
		$this->tbirthday->addlist($birthday);
		$this->edit($this->tbirthday);
	}
	/* 4. Удаление преподавателя из БД. */
	public function delete (int $birthday_id) {
		$req_query = "DELETE FROM ".self::DBNAME." WHERE id='$birthday_id'";
		$sql = $this->wpdblink->prepare($req_query, $birthday_id);
		$this->wpdblink->query($sql);
	}
	/* PRIVATE */
	//Очистка данных.
	private function resetcls () {
		if (isset($this->tbirthday)) {
			unset($this->tbirthday);
		}
		$this->tbirthday = new mcbsu_tbirthday;
	}
}
?>