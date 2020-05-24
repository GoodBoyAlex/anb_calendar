<?php
/**
 * Класс "Дата".
 * версия 1.0.
 * Автор: Александр Бабаев.
 * @package ANB CALENDAR
 */
/**
* САМОЗАЩИТА (не допускается доступ по http(s))
*/
namespace anb_calendar;
if (!defined('ABSPATH')) {
	exit;
}
class caldate {
    use sharefunctions {getmonthlastday as private;}
    const MINYEAR = 2000;       #минимально возможный год
    const MAXYEAR = 2100;       #максимально возможный год
    private $day = 1;           #int 1..31 - день каледндаря;
    private $month = 1;         #int 1..12 - месяц каледндаря;
    private $year = 2000;       #int 2000..2100 - год каледндаря;
    /* PUBLIC */
    public function __construct (int $day, int $month, int $year) {
        $this->SetDate($day, $month, $year);
    }
    public function getyear() :int {
        return $this->year;
    }
    public function getmonth() :int {
        return $this->month;
    }
    public function getday() :int {
        return $this->day;
    }
    public function setdate (int $day, int $month, int $year) {
        //Внимание! Очень важен порядок установки свойств!
        $this->setyear($year);
        $this->setmonth($month);
        $this->setday($day);
    }
    /* PRIVATE */
    private function is_yearcorrect (int $year) :bool {
        return (($year > (int)self::MINYEAR) && ($month < (int)self::MAXYEAR)) ? true : false;
    }
    private function is_monthcorrect (int $month) :bool {
        return (($month > 0) && ($month < 13)) ? true : false;
    }
    private function is_daycorrect (int $day) :bool {
        $uplimit = $this->getmonthlastday($this->month, $this->year);
        return (($day > 0) && ($day < ($uplimit + 1))) ? true : false;
    }
    private function setyear (int $year) {
        if ($this->is_yearcorrect($year)) $this->year = $year;
    }
    private function setmonth (int $month) {
        if ($this->is_monthcorrect($month)) $this->month = $month;
    }
    private function setday (int $day) {
        if ($this->is_daycorrect($day)) $this->day = $day;
    }
}
?>