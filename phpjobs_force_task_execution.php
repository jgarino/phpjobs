<?php
require_once 'phpjobs/phpjobs_queue_manager.php';

$limit_tasks_to_process = 50;
$task_id = 15;
PhpJobs_ForceTaskExecution($limit_tasks_to_process, $task_id);