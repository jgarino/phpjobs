<?php
require_once 'phpjobs_req_config.php';
require_once 'phpjobs_req_security.php';
require_once 'phpjobs_req_log.php';
require_once 'phpjobs_req_tools.php';
require_once 'phpjobs_class.job.php';
require_once 'phpjobs_class.task.php';
require_once 'phpjobs_tasks_manager.php';
require_once 'phpjobs_class.queue.php';

function PhpJobs_ShowExecuteQueue($limit_jobs_to_process=Queue::MAX_TASKS){
	$queue_object = new Queue($limit_jobs_to_process);
	$task_list = $queue_object->QueueGetList();
	echo "<table border='1'>\r\n";
	echo "<tr>".PHPJOBS_TASK_TD_HEADER."</tr>\r\n";
	if(count($task_list)>0){
		foreach($task_list as $index => $task){
			if(is_a($task, 'Task')){
				echo "<tr>";
				$task->Execute();
				PhpJobs_TaskStore($task);
				$fields = explode("\t", $task->Show());
				foreach($fields as $field){
					echo "<td>".$field."</td>\r\n";
				}//foreach
				echo "</tr>";
			}//if
		}//foreach
	}//if count
	else{
		echo "<tr><td colspan='".PHPJOBS_TASK_NUMBER_OF_CELLS."' style='text-align: center;'>There is no task in queue.</td></tr>\r\n";
	}//else
}

function PhpJobs_ShowFinishedTasks($limit=Queue::MAX_TASKS){
	$queue_object = new Queue($limit);
	$task_list = $queue_object->QueueGetList(PHPJOBS_TASKS_DONE_FOLDER);
	echo "<span style='text-decoration: underline; font-weight: bold;'>Finished Tasks :</span>";
	echo "<table border='1'>\r\n";
	echo "<tr>".PHPJOBS_TASK_TD_HEADER."</tr>\r\n";
	if(count($task_list)>0){
		foreach($task_list as $index => $task){
			if(is_a($task, 'Task')){
				echo "<tr>";
				$fields = explode("\t", $task->Show());
				foreach($fields as $field){
					echo "<td>".$field."</td>\r\n";
				}//foreach
				echo "</tr>";
			}//if
		}//foreach
	}
	else
		echo "<tr><td colspan='".PHPJOBS_TASK_NUMBER_OF_CELLS."' style='text-align: center;'>No Task in current queue.</td></tr>";
	echo "</table>";
}

function PhpJobs_ShowTasksInQueue($limit=Queue::MAX_TASKS){
	$queue_object = new Queue($limit);
	$task_list = $queue_object->QueueGetList();
	//var_dump($task_list);
	echo "<table border='1'>\r\n";
	echo "<tr>".PHPJOBS_TASK_TD_HEADER."</tr>\r\n";
	if(count($task_list)>0){
		foreach($task_list as $index => $task){
			if(is_a($task, 'Task')){
				echo "<tr>";
				$fields = explode("\t", $task->Show());
				foreach($fields as $field){
					echo "<td>".$field."</td>\r\n";
				}//foreach
				echo "</tr>";
			}//if
		}//foreach
	}
	else
		echo "<tr><td colspan='15' style='text-align: center;'>No Task in current queue.</td></tr>";
	echo "</table>";
}

function PhpJobs_ForceTaskExecution($limit=  Queue::MAX_TASKS,$task_id){
	$queue_object = new Queue($limit);
	$task_list = $queue_object->QueueGetList();
	$mode_force = TRUE;
	echo "<table border='1'>\r\n";
	echo "<tr>".PHPJOBS_TASK_TD_HEADER."</tr>\r\n";
	foreach($task_list as $index => $task){
		if(is_a($task, 'Task')){
			if($task->GetId()==$task_id){
				$task->Execute($mode_force);
				PhpJobs_TaskStore($task);
				echo "<tr>";
				$fields = explode("\t", $task->Show());
				foreach($fields as $field){
					echo "<td>".$field."</td>\r\n";
				}//foreach
				echo "</tr>";
			}//if
		}//if
	}//foreach
}