<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Enrolment expiry notification.
 *
 * @package    local_eenotify
 * @copyright 2020 Hernan Arregoces <harregoces@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Enrolment expiry notification';
$string['task_send_notifications'] = 'Task send notifications';
$string['pluginname_desc'] = 'This plugin allows you to send email notification for your users before the enrollment period expires.';
$string['subject'] = 'Subject';
$string['subject_desc'] = 'Email Subject description';
$string['message'] = 'Message';
$string['message_desc'] = 'Email Message description';
$string['expirythreshold'] = 'expiry threshold';
$string['expirythreshold_desc'] = 'expiry threshold desc';
$string['table_heading'] = 'Multiple Notifications records';
$string['add_new_email'] = 'Add New Email';
$string['back_to_settings'] = 'Back to settings';
$string['erroremptysubject'] = 'Subject cannot be empty';
$string['erroremptymessage'] = 'Message cannot be empty';
$string['privacy:metadata'] = 'The Multiple Notifications plugins do not store user data.';
