<?php
/**
 * Класс "Событие календаря".
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
/*********************************
 * I. Класс calevent.
 *********************************
 * Событие календаря.
 * в. 1.0 (А. Бабаев)
 ********************************/
class calevent
{
    public $id;                         #int - ID события;
    public $type = CAL_EVENT_DEFAULT;   #int - тип события;
    public $date;                       #caldate - дата события;
    public $description = array();      #array - зависимый от типа содержимое события.
    /* PUBLIC */
    public function __construct () {
        $this->date = new caldate ((int)date('j'), (int)date('n'), (int)date('Y'));
    }
    public function __destruct() {
        unset($this->date);
    }
    public function setevent (int $type, caldate $date, array $description) {
        $this->type = $type;
        $this->date = $date;
        $this->description = $description;
    }
}
/*********************************
 * II. Класс calevents.
 *********************************
 * Список событий calevent.
 * в. 1.0 (А. Бабаев)
 ********************************/
class calevents {
    private $list = [];      #массив calevent
    public function __construct() {
        $list = array();
    }
    public function __destruct() {
        $this->clear();
        unset($this->list);
    }
    public function add (int $id, int $type, caldate $date, array $description) :int {
        $event = new calevent;
        $event->id = $id;
        $event->setevent($type, $date, $description);
        $this->list[] = $event;
        return count($this->list);
    }
    public function assign (calevent &$event) :int {
        $event_new = $event;
        $this->list[] = $event_new;
        return count($this->list);
    }
    public function count() :int {
        return count($this->list);
    }
    public function get (int $ind) :calevent {
        if (isset($this->list[$ind])) {
            $result = $this->list[$ind];
        }
        return $result;
    }
    public function delete (int $ind) {
        if (isset($this->list[$ind])) {
            unset($this->list[$ind]);
        }
        return $result;
    }
    public function clear() {
        foreach ($this->list as $teacher) {
            unset($teacher);
        }
    }
}
?>