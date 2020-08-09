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

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class email_form extends moodleform
{

    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);

        $mform->addElement('text', 'subject', get_string('subject', 'local_eenotify'));
        $mform->setType('subject', PARAM_RAW);

        $mform->addElement('editor', 'message', get_string('message', 'local_eenotify'), null, array());
        $mform->setType('message', PARAM_RAW);

        $mform->addElement('text', 'expirythreshold', get_string('expirythreshold', 'local_eenotify'));
        $mform->setType('expirythreshold', PARAM_RAW);
        $mform->setDefault('expirythreshold', 1);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

    }

    function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        if (empty($data['message']['text'])) {
            $errors['message'] = get_string('erroremptymessage', 'local_eenotify');
        }
        if (empty($data['subject'])) {
            $errors['subject'] = get_string('erroremptysubject', 'local_eenotify');
        }
        return $errors;
    }
}
