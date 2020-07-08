<?php

function send_multiple_expiry_notifications($trace) {
    global $DB, $CFG;

    $name = 'manual';
    if (!enrol_is_enabled($name)) {
		$trace->output('Processing '.$name.' enrol method not enabled...');
        $trace->finished();
        return;
    }

    $expirynotifyhour = 23;

    if (!($trace instanceof progress_trace)) {
        $trace = $trace ? new text_progress_trace() : new null_progress_trace();
        debugging('enrol_plugin::send_expiry_notifications() now expects progress_trace instance as parameter!', DEBUG_DEVELOPER);
    }

    $timenow = time();
    $notifytime = usergetmidnight($timenow, $CFG->timezone) + ($expirynotifyhour * 3600);

    /*if ($timenow < $notifytime) {
        $trace->output($name.' enrolment expiry notifications will be sent at '.userdate($notifytime, '', $CFG->timezone).'.');
        $trace->finished();
        return;
    }*/

    $trace->output('Processing '.$name.' enrolment expiration notifications...');

    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    if ($notifications = $DB->get_records('multiple_notifications_email')) {
        foreach ($notifications as $notification) {
            $id = $notification->id;
            $expirythreshold = $notification->expirythreshold;

            $sql = "SELECT ue.*, e.courseid, c.fullname as coursename, u.firstname as firstname, u.lastname as lastname
                  FROM {user_enrolments} ue 
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :name AND e.status = :enabled)
                  JOIN {course} c ON (c.id = e.courseid)
                  JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0 AND u.suspended = 0)
                  LEFT JOIN {multiple_notifications_logs} mnl ON mnl.enrolment_id = ue.id AND multiple_notification_email_id = :notification_id
                 WHERE  mnl.id is null AND c.id IN (75,77,78,79) AND ue.status = :active AND ue.timeend > 0 AND ue.timeend > :now1 AND ue.timeend < (:expirythreshold + :now2)
              ORDER BY ue.enrolid ASC, u.lastname ASC, u.firstname ASC, u.id ASC";
            $params = array('notification_id' => $notification->id, 'enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'now1'=>$timenow, 'now2'=>$timenow, 'name'=>$name, 'expirythreshold' => $expirythreshold);

            $rs = $DB->get_recordset_sql($sql, $params);

            $users = array();

            foreach($rs as $ue) {

                $context = context_course::instance($ue->courseid);
                $enroller = get_admin();

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

    $name = 'manual';

    $oldforcelang = force_current_language($user->lang);

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
    $message->component         = 'enrol_'.$name;
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

    force_current_language($oldforcelang);
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
