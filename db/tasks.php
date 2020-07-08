<?php
/**
 * Created by PhpStorm.
 * User: hernan.arregoces
 * Date: 3/10/2019
 * Time: 3:55 PM
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_multiple_notifications\task\send_notifications',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    )
);

