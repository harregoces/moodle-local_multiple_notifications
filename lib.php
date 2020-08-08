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
 * Multiple notifications.
 *
 * @package    local_multiple_notifications
 * @copyright 2020 Hernan Arregoces - Arrby
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function send_multiple_expiry_notifications($trace) {
    global $DB, $CFG;

    $expirynotifyhour = 23;

    if (!($trace instanceof progress_trace)) {
        $trace = $trace ? new text_progress_trace() : new null_progress_trace();
        debugging('enrol_plugin::send_expiry_notifications() now expects progress_trace instance as parameter!', DEBUG_DEVELOPER);
    }

    $timenow = time();
    $trace->output('Processing multiple notifications process...');

    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    if ($notifications = $DB->get_records('multiple_notifications_email')) {
        foreach ($notifications as $notification) {
            $expirythreshold = $notification->expirythreshold;

            $sql = "SELECT ue.*, e.courseid, c.fullname as coursename, u.firstname as firstname, u.lastname as lastname
                  FROM {user_enrolments} ue 
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.status = :enabled)
                  JOIN {course} c ON (c.id = e.courseid)
                  JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0)
                  LEFT JOIN {multiple_notifications_logs} mnl ON mnl.enrolment_id = ue.id AND multiple_notification_email_id = :notification_id
                 WHERE  mnl.id is null AND ue.status = :active AND ue.timeend > 0 AND ue.timeend > :now1 AND ue.timeend < (:expirythreshold + :now2)
              ORDER BY ue.enrolid ASC, u.lastname ASC, u.firstname ASC, u.id ASC";
            $params = array('notification_id' => $notification->id, 'enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'now1'=>$timenow, 'now2'=>$timenow, 'expirythreshold' => $expirythreshold);
            $rs = $DB->get_recordset_sql($sql, $params);

            foreach($rs as $ue) {
                $user = $DB->get_record('user', array('id'=>$ue->userid));
                if ($ue->timeend - $expirythreshold + 86400 < $timenow) {
                    $trace->output("user $ue->userid was already notified that enrolment in course $ue->courseid expires on ".userdate($ue->timeend, '', $CFG->timezone), 1);
                    continue;
                }
                notify_expiry_enrolled($user, $ue, $trace, $notification);
            }
            $rs->close();
        }
        $trace->output('...notification processing finished.');
        $trace->finished();
    } else {
		$trace->output('Nothing to send');
	}
}

function notify_expiry_enrolled($user, $ue, progress_trace $trace, $notification) {
    global $CFG,$DB;
    $forcelang = force_current_language($user->lang);

    $enroller = get_admin();
    $context = context_course::instance($ue->courseid);

    $a = new stdClass();
    $a->course   = format_string($ue->fullname, true, array('context'=>$context));
    $a->user     = fullname($user, true);
    $a->timeend  = userdate($ue->timeend, '', $user->timezone);
    $a->enroller = fullname($enroller, has_capability('moodle/site:viewfullnames', $context, $user));

    $subject = $notification->subject;
    $body = replaceMessage($notification->message,$ue);

    $message = new \core\message\message();
    $message->courseid          = $ue->courseid;
    $message->notification      = 1;
    $message->component         = 'multiple_notifications';
    $message->name              = 'expiry_notification';
    $message->userfrom          = $enroller;
    $message->userto            = $user;
    $message->subject           = $subject;
    $message->fullmessage       = $body;
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml   = markdown_to_html($body);
    $message->smallmessage      = $subject;
    $message->contexturlname    = $a->course;
    $message->contexturl        = (string)new moodle_url('/course/view.php', array('id'=>$ue->courseid));

    if (message_send($message)) {
        $trace->output("notifying user $ue->userid that enrolment in course $ue->courseid expires on ".userdate($ue->timeend, '', $CFG->timezone), 1);

        $dataobject = array(
            'multiple_notification_email_id' => $notification->id,
            'enrolment_id' => $ue->id,
            'time_send' => time()
        );
        $DB->insert_record('multiple_notifications_logs', $dataobject);

    } else {
        $trace->output("error notifying user $ue->userid that enrolment in course $ue->courseid expires on ".userdate($ue->timeend, '', $CFG->timezone), 1);
    }

    force_current_language($forcelang);
}

function replaceMessage($message,$ue){
    global $CFG;

    $message = str_replace("[firstname]", $ue->firstname, $message);
    $message = str_replace("[lastname]", $ue->lastname, $message);
    $message = str_replace("[coursename]", $ue->coursename, $message);
    $message = str_replace("[timeend]", userdate($ue->timeend, '', $CFG->timezone), $message);
    $message = str_replace("[timestart]", userdate($ue->timestart, '', $CFG->timezone), $message);
    $message = str_replace("[time]", userdate(time(), '', $CFG->timezone), $message);
    return $message;
}

function local_multiple_notifications_extend_settings_navigation($settingsnav, $context) {
	global $CFG, $PAGE;

	if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
		$strfoo = get_string('pluginname', 'local_multiple_notifications');
		$url = new moodle_url('/local/multiple_notifications/settings.php', array('id' => $PAGE->course->id));
		$foonode = navigation_node::create(
			$strfoo,
			$url,
			navigation_node::NODETYPE_LEAF,
			'myplugin',
			'myplugin',
			new pix_icon('t/addcontact', $strfoo)
		);
		if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
			$foonode->make_active();
		}
		$settingnode->add_node($foonode);
	}
}
