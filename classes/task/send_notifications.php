<?php

namespace local_multiple_notifications\task;

class send_notifications extends \core\task\scheduled_task
{

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_send_notifications', 'local_multiple_notifications');
    }

    /**
     * Execute the task.
     */
    public function execute()
    {
		global $CFG;
		require_once($CFG->dirroot."/local/multiple_notifications/lib.php");
		$trace = new \text_progress_trace();
        $result = send_multiple_expiry_notifications($trace);
    }

}
