<?php
/**
 * Трейт "Общие функции".
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
trait sharefunctions
{
    public function getmonthlastday (int $month, int $year) :int {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }
    public function do_replace (array $replace, string $str) :string {
        $replace_from = array();
        $replace_to = array();
        foreach ($replace as $key => $value) {
            $replace_from[] = $key;
            $replace_to[] = $value;
        }
        return (string)str_replace($replace_from, $replace_to, $str);
    }
    public function is_dateequal (mcbsu_calendar_date $date1, mcbsu_calendar_date $date2) :bool {
        $ye = ($date1->getyear() == $date2->getyear());
        $me = ($date1->getmonth() == $date2->getmonth());
        $de = ($date1->getday() == $date2->getday());
        return ($ye && $me && $de) ? true : false;
    }
    public function getmonthnames() :array {
        return array(
            //Именительный
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
            //Родительный
            13 => 'Января',
            14 => 'Февраля',
            15 => 'Марта',
            16 => 'Апреля',
            17 => 'Мая',
            18 => 'Июня',
            19 => 'Июля',
            20 => 'Августа',
            21 => 'Сентября',
            22 => 'Октября',
            23 => 'Ноября',
            24 => 'Декабря',
            //Дательный
            25 => 'Январю',
            26 => 'Февралю',
            27 => 'Марту',
            28 => 'Апрелю',
            29 => 'Маю',
            30 => 'Июню',
            31 => 'Июлю',
            32 => 'Августу',
            33 => 'Сентябрю',
            34 => 'Октябрю',
            35 => 'Ноябрю',
            36 => 'Декабрю',
            //Винительный
            37 => 'Январь',
            38 => 'Февраль',
            39 => 'Март',
            40 => 'Апрель',
            41 => 'Май',
            42 => 'Июнь',
            43 => 'Июль',
            44 => 'Август',
            45 => 'Сентябрь',
            46 => 'Октябрь',
            47 => 'Ноябрь',
            48 => 'Декабрь',
            //Творительный
            49 => 'Январём',
            50 => 'Февралём',
            51 => 'Мартом',
            52 => 'Апрелем',
            53 => 'Маем',
            54 => 'Июнем',
            55 => 'Июлем',
            56 => 'Августом',
            57 => 'Сентябрём',
            58 => 'Октябрём',
            59 => 'Ноябрём',
            60 => 'Декабрём',
            //Предложный
            61 => 'Январе',
            62 => 'Феврале',
            63 => 'Марте',
            64 => 'Апреле',
            65 => 'Мае',
            66 => 'Июне',
            67 => 'Июле',
            68 => 'Августе',
            69 => 'Сентябре',
            70 => 'Октябре',
            71 => 'Ноябре',
            72 => 'Декабре'
        );
    }
    public function assignarray (array $from, array &$to) {
        foreach ($from as $key => $value) {
            $to[$key] = $value;
        }
    }
}
?>