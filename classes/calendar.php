<?php
/**
 * Класс "Календарь".
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
class anbcalendar
{
    use sharefunctions {
        getmonthlastday as private;
        do_replace as private;
        is_dateequal as private;
        getmonthnames as private;
    }
    public $date;               #anbcalendar_date - дата каледндаря;
    public $current;            #anbcalendar_date - текущая дата;
    private $db;                #mcbsu_db_tbdays - БД дней рождения
    private $bdlist;            #mcbsu_tbirthdays - контенер списка именниников
    /* PUBLIC */
    public function __construct() {
        //Установка текущей даты
        $this->current = new anbcalendar_date ((int)date('j'), (int)date('n'), (int)date('Y'));
        //Получение параметров
        $selday = (isset($_POST['cal_day'])) ? (int)$_POST['cal_day'] : $this->current->getday();
		$selmonth = (isset($_POST['cal_month'])) ? (int)$_POST['cal_month'] : $this->current->getmonth();
		$selyear = (isset($_POST['cal_year'])) ? (int)$_POST['cal_year'] : $this->current->getyear();
        $this->date = new anbcalendar_date ($selday, $selmonth, $selyear);
        $this->db = new mcbsu_db_tbdays;
		$this->bdlist = new mcbsu_tbirthdays;
    }
    public function __destruct() {
        unset($date);
        unset($current);
        unset($this->db);
        unset($this->bdlist);
        unset($_POST['cal_day']);
        unset($_POST['cal_month']);
        unset($_POST['cal_year']);
    }
    public function getcalendar (): string {
        return $this->frmtstr(mcbsu_gettemplate('calendar.html', false));
    }
    /* PROTECTED */
    protected function buildcalendar() :string {
        $result = '';
        $lastday = 0;
        //Получаем первую строку
        $result .= $this->buildrow(0, $lastday);
        //Получаем остальные строки
        while ($lastday > 0) {
            $day_start = $lastday + 1;
            $result .= $this->buildrow($day_start, $lastday);
        }
        return $result;
    }
    /* PRIVATE */
    private function frmtstr (string $str) :string {
        $replace = array(
            '#DAY#' => (string)$this->date->getday(),
            '#MONTH#' => (string)$this->date->getmonth(),
            '#MONTHNAME#' => (string)$this->getmonthname($this->date->getmonth(), 0),
            '#MONTHNAME_A#' => (string)$this->getmonthname($this->date->getmonth(), 1),
            '#YEAR#' => (string)$this->date->getyear(),
            '#CURDAY#' => (string)$this->current->getday(),
            '#CURMONTH#' => (string)$this->current->getmonth(),
            '#CURMONTHNAME#' => (string)$this->getmonthname($this->current->getmonth(), 0),
            '#CURMONTHNAME_A#' => (string)$this->getmonthname($this->current->getmonth(), 1),
            '#CURYEAR#' => (string)$this->current->getyear(),
            '#CALENDAR#' => $this->buildcalendar(),
            '#DAYBIRTHDAYS#' => $this->bd_buildbirthdaysindate(),
            '#TODAYBIRTHDAYS#' => $this->bd_buildlist()
        );
        return $this->do_replace($replace, $str);
    }
    private function getfirstmonthdow (int $month, int $year) :int {
        return (int)date('N', strtotime(date("$year-$month-01")));
    }
    private function getprevmonthyear (int $month) :int {
        return ($month > 1) ? $this->date->getyear() : $this->date->getyear() - 1;
    }
    private function getnextmonthyear (int $month) :int {
        return ($month < 12) ? $this->date->getyear() : $this->date->getyear() + 1;
    }
    private function prevMonth (int $month) :int {
        return ($month > 1) ? $month - 1 : 12;
    }
    private function nextMonth (int $month) :int {
        return ($month < 12) ? $month + 1 : 1;
    }
    private function buildrow (int $fromday, int &$lastday) :string {
        $disable_cell_class = "anbcl_cell anbcl_prevnextmonthcell";
        $result = '<div class="anbcl_row">';
        if ($fromday == 0) {
            //Это первая строка.
            $cellmax = 0;
            //Если первое число не является понедельником
            if ($this->getfirstmonthdow($this->date->getmonth(), $this->date->getyear()) > 1) $cellmax = $this->getfirstmonthdow($this->date->getmonth(), $this->date->getyear()) - 1;
            //то строим числа предыдущего месяца
            for ($cell = 1; $cell < $cellmax + 1; $cell++) {
                $cell_day = $this->getmonthlastday($this->prevMonth($this->date->getmonth()), $this->getprevmonthyear($this->date->getmonth())) - $cellmax + $cell;
                $result .= "<div class=\"$disable_cell_class\" title=\"$cell_title\">$cell_day</div>";
            }
            //Строим числа текущего месяца
            for ($cell = $cellmax + 1; $cell < 8; $cell++) {
                $cell_day = $cell - $cellmax;
                $this->do_monthcell($result, $cell_day);
            }
            $lastday = $cell_day;
        }
        if (($fromday > 0) && (($fromday + 7) <= $this->getmonthlastday($this->date->getmonth(), $this->date->getyear()))) {
            //Это внутренняя строка
            for ($cell = $fromday; $cell < $fromday + 7; $cell++) {
                $cell_day = $cell;
                $this->do_monthcell($result, $cell_day);
            }
            $lastday = (($fromday + 7) < $this->getmonthlastday($this->date->getmonth(), $this->date->getyear())) ? $cell_day : 0;
        }
        if (($fromday + 7) > $this->getmonthlastday($this->date->getmonth(), $this->date->getyear())) {
            //Это последняя строка
            $cellmax = $this->getfirstmonthdow($this->nextMonth($this->date->getmonth()), $this->getnextmonthyear($this->nextMonth($this->date->getmonth()))) - 1;
            //Заканчиваем месяц
            for ($cell = 1; $cell < $cellmax + 1; $cell++) {
                $cell_day = $this->getmonthlastday($this->date->getmonth(), $this->date->getyear()) - $cellmax + $cell;
                $this->do_monthcell($result, $cell_day);
            }
            //Строим числа следующего месяца
            for ($cell = $cellmax + 1; $cell < 8; $cell++) {
                $cell_day = $cell - $cellmax;
                $result .= "<div class=\"$disable_cell_class\">$cell_day</div>";
            }
            $lastday = 0;
        }
        $result .= "</div>";
        return $result;
    }
    /*
    ** $cal_case - падеж:
    **** именительный = 0
    **** родительный = 1
    **** дательный = 2
    **** винительный = 3
    **** творительный = 4
    **** предложный = 5
    */
    private function getmonthname (int $month, int $cal_case) :string {
        $monthnames = $this->getmonthnames();
        $month_name = $monthnames[$month + $cal_case*12];
        return (string)$month_name;
    }
    private function builttitle() : string {
        $title = "";
        if ($this->bdlist->count() > 0) {
            for ($ind = 0; $ind < $this->bdlist->count(); $ind++) {
                $teacher = $this->bdlist->get($ind);
                $title .= (($title == "") ? "" : ", ")."{$teacher->getfio()}" ;
                unset($teacher);
            }
        }
        return $title;
    }
    private function do_monthcell (string &$cell, int $day) {
        //Инициализация параметров ячейки
        $cell_class = "anbcl_cell";
        $cell_title = "";
        $cell_onclick = "";
        //Форма для активации ONCLICK
        $cell_form = "<form id=\"bdays_$day\" action=\"\" method=\"POST\"><input type=\"hidden\" name=\"cal_day\" value=\"$day\"><input type=\"hidden\" name=\"cal_month\" value=\"{$this->date->getmonth()}\"><input type=\"hidden\" name=\"cal_year\" value=\"{$this->date->getyear()}\"></form>";
        //Получение именниников
        $this->bdlist->clear();
        $this->db->get(-1, $this->date->getmonth(), $day, '', $this->bdlist, 3);
        //Выставление CSS класса ячейки
        $cell_class .= ($this->bdlist->count() > 0) ? " anbcl_dateisbirthday" : "";
        //Выставление title ячейки
        $cell_title = $this->builttitle();
        //Выставление CSS класса ячейки текущего дня
        $cell_class .= (($this->date->getmonth() == $this->current->getmonth()) && ($day == $this->current->getday())) ? " anbcl_curday" : "";
        //Выставление CSS класса выбранного дня
        $cell_class .= ($day == $this->date->getday()) ? " anbcl_dateisselected" : "";
        //Формирование ячейки
        $cell .= "<div class=\"$cell_class\" onclick=\"document.getElementById('bdays_$day').submit(); return false;\" title=\"$cell_title\">$cell_form $day</div>";
        //Очистка списка
        unset($this->bdlist);
        $this->bdlist = new mcbsu_tbirthdays;
    }
    private function bd_buildsep() :string {
        return (string)mcbsu_gettemplate('calendar-curbd-sep.html', medcolthm_is_iexplorer());
    }
    function bd_buildlist () :string {
        $list = new mcbsu_tbirthdays;
        $this->db->get(-1, $this->current->getmonth(), $this->current->getday(), '', $list, 3);
        $bd_list = '';
        if ($list->count() > 0) {
            for ($ind = 0; $ind < $list->count(); $ind++) {
                $teacher = $list->get($ind);
                $replace = array(
                    '#ID#' => $teacher->getid(),
                    '#FIO#' => ($teacher->getteacher_id() > 0) ? do_shortcode("[mcbsu_teacherslistlink tid={$teacher->getteacher_id()}]{$teacher->getfio()}[/mcbsu_teacherslistlink]", false) : $teacher->getfio(),
                    '#STAFFFUNCT#' => $teacher->getstafffunct()
                );
                $bd_list .= $this->do_replace($replace, mcbsu_gettemplate('calendar-curbd-item.html', medcolthm_is_iexplorer()));
                unset($teacher);
                if ($ind < $list->count() - 1) $bd_list .= $this->bd_buildsep();
            }
        }
        unset($list);
        $replace = array(
            '#BDPAGEURL#' => "/birthdays",
            '#MONTHNAME#' => (string)$this->getmonthname($this->date->getmonth(), 1),
            '#BDLIST#' => $bd_list
        );
        if ($bd_list != '') $bd_list = $this->do_replace($replace, mcbsu_gettemplate('calendar-curbd.html', medcolthm_is_iexplorer()));
        return $bd_list;
    }
    function bd_buildbirthdaysindate() :string {
        $list = new mcbsu_tbirthdays;
        $this->db->get(-1, $this->date->getmonth(), $this->date->getday(), '', $list, 3);
        $bd_list = '';
        if (($list->count() > 0) && (!$this->is_dateequal($this->date, $this->current))) {
            for ($ind = 0; $ind < $list->count(); $ind++) {
                $teacher = $list->get($ind);
                $replace = array(
                    '#FIO#' => ($teacher->getteacher_id() > 0) ? do_shortcode("[mcbsu_teacherslistlink tid={$teacher->getteacher_id()}]{$teacher->getfio()}[/mcbsu_teacherslistlink]", false) : $teacher->getfio(),
                    '#STAFFFUNCT#' => $teacher->getstafffunct()
                );
                $bd_list .= $this->do_replace($replace, mcbsu_gettemplate('calendar_onday-item.html', medcolthm_is_iexplorer()));
                unset($teacher);
                if ($ind < $list->count() - 1) $bd_list .= $this->bd_buildsep();
            }
        }
        unset($list);
        if ($bd_list != '') {
            $replace = array(
                '#LISTOFBIRTHDAYS#' => $bd_list,
                '#DAY#' => (string)$this->date->getday(),
                '#LCMONTHNAMEA#' => (string)$this->getmonthname($this->date->getmonth(), 1, true)
            );
            $bd_list = $this->do_replace($replace, mcbsu_gettemplate('calendar_onday.html', medcolthm_is_iexplorer()));
        }
        return (string)$bd_list;
    }
}
?>