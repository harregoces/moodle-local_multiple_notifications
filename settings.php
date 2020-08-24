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
 * @package    local_multiple_notifications
 * @copyright 2020 Hernan Arregoces <harregoces@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_login();

if (has_capability('local/multiple_notifications:configmagement', context_system::instance()) && $hassiteconfig) {


    $settings = new admin_settingpage('local_multiple_notifications', get_string('pluginname', 'local_multiple_notifications'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(
        new admin_setting_heading('local_multiple_notifications', '', get_string('pluginname', 'local_multiple_notifications'))
    );

    $table = new html_table();
    $table->attributes['class'] = 'generaltable mod_index';
    $table->head = array(
        get_string('subject', 'local_multiple_notifications'),
        get_string('message', 'local_multiple_notifications'),
        get_string('expirythreshold', 'local_multiple_notifications'),
        get_string('actions', 'local_multiple_notifications')
    );
    $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap');

    $addnewstr = get_string('add_new_email', 'local_multiple_notifications');
    $addnewurl = new moodle_url('/local/multiple_notifications/manage_email.php', array());
    $addnewrow = '<a href="' . $addnewurl->out() . '" title="">' . $addnewstr . '</a>';

    $table->data = array();

    $records = $DB->get_records('local_multiple_notifications_email');
    foreach ($records as $record) {
        $url = new moodle_url('/local/multiple_notifications/manage_email.php', array('id' => $record->id));
        $editlink = $OUTPUT->action_icon($url,
            new pix_icon('t/edit', get_string('edit', 'local_multiple_notifications', $record->id))
        );

        $url = new moodle_url('/local/multiple_notifications/manage_email.php', array('id' => $record->id, 'action' => 'delete'));
        $deletelink = $OUTPUT->action_icon($url,
            new pix_icon('i/trash', get_string('delete', 'local_multiple_notifications', $record->id))
        );

        $row = array(
            $record->subject,
            $record->message,
            $record->expirythreshold,
            new html_table_cell($editlink . "&nbsp;&nbsp;&nbsp;&nbsp;" . $deletelink)
        );
        $table->data[] = $row;
    }

    $table->data[] = array($addnewrow);

    $settings->add(new admin_setting_heading('local_multiple_notifications', '', html_writer::table($table)));

}
