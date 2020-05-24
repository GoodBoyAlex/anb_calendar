<?php
/**
 * Модуль "Константы".
 * версия 0.1.
 * Автор: Александр Бабаев.
 * @package ANB CALENDAR
 */
/**
* САМОЗАЩИТА (не допускается доступ по http(s))
*/
if (!defined('ABSPATH')) {
	exit;
}
/* События календаря. */
//Пустое событие
define('CAL_EVENT_DEFAULT', 0);
//Меропрятие
define('CAL_EVENT_EVENT', 1);
//Встреча
define('CAL_EVENT_MEETING', 2);
//Напоминание
define('CAL_EVENT_REMINDER', 3);
//Праздник
define('CAL_EVENT_HOLIDAY', 4);
//День рождения
define('CAL_EVENT_BIRTHDAY', 5);
//Задача
define('CAL_EVENT_TASK', 6);
?>