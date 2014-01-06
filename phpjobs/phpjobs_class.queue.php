<?php
//Jobs in Process...
class Queue {
	const MAX_TASKS = 2147483647;
	private $maximum_number_of_tasks_to_process = self::MAX_TASKS;
	private $number_of_tasks_in_queue = 0;
	private $tasks_files_array = NULL;
	private $tasks_object_array = NULL;

	public function __construct($limit=self::MAX_TASKS) {
		$this->maximum_number_of_tasks_to_process = $limit;
	}
	public function QueueGetNumberOfTasks($dir = PHPJOBS_TASKS_QUEUE_FOLDER){
		$this->tasks_files_array = $this->QueueGetFiles($dir);
		$this->number_of_tasks_in_queue = count($this->tasks_files_array);
		return $this->number_of_tasks_in_queue;
	}
	private function QueueGetFiles($dir = PHPJOBS_TASKS_QUEUE_FOLDER){
		if(substr($dir, -1, 1)=='/')
			$dir = substr ($dir, 0, (strlen($dir)-1));
		$files = NULL;
		$handle = opendir($dir) or PhpJobs_LogWrite('queue', PHPJOBS_LOG4PHP_LEVEL_FATAL, 'Error : cannot open directory : '.$dir, TRUE, TRUE);
		while($entry = @readdir($handle)) {
			if(is_file($dir.'/'.$entry) && $entry != '.' && $entry != '..' && $entry!='.htaccess' && preg_match("#^".PHPJOBS_TASK_PREFIX."[0-3]{1}_[0-9]+"."\\".PHPJOBS_TASK_SUFFIX."$#i",$entry)) {
				//$files[] = array(filectime($dir.'/'.$entry) => $dir.'/'.$entry);
				$files[] = $dir.'/'.$entry;
			}//if
		}//while
		closedir($handle);
		if(is_array($files))
			sort($files);
		//var_dump($files);
		return $files;
	}

	public function QueueExecuteTask(\Task $task){
		PhpJobs_LogWrite('queue', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Task ['.$task->GetId().' / '.$task->GetTitle().'] starts its execution from Queue.', PHPJOBS_LOG4PHP_DISPLAY);
		$task = PhpJobs_TaskExecute($task->Execute());
		PhpJobs_LogWrite('queue', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Task ['.$task->GetId().' / '.$task->GetTitle().'] has finished its execution from Queue.', PHPJOBS_LOG4PHP_DISPLAY);
		return $task;
	}
	public function QueueMoveFinishedTask(\Task $task){
		PhpJobs_LogWrite('queue', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Task ['.$task->GetId().' / '.$task->GetTitle().'] is being moved from queue ['.PHPJOBS_TASKS_QUEUE_FOLDER.'] to archive ['.PHPJOBS_TASKS_DONE_FOLDER.'].', PHPJOBS_LOG4PHP_DISPLAY);
		PhpJobs_TaskStore($task);
		PhpJobs_LogWrite('queue', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Task ['.$task->GetId().' / '.$task->GetTitle().'] is moved into archive ['.PHPJOBS_TASKS_DONE_FOLDER.'].', PHPJOBS_LOG4PHP_DISPLAY);
	}
	/*
	 * Returns an array of Tasks
	 */
	public function QueueGetList($dir = PHPJOBS_TASKS_QUEUE_FOLDER){
		$this->QueueGetNumberOfTasks($dir);
		$this->tasks_object_array = NULL;
		echo "NUMBER OF TASKS IN QUEUE : ".$this->number_of_tasks_in_queue."<br />";
		for($i=0;$i<$this->number_of_tasks_in_queue;$i++){
			if($i>$this->maximum_number_of_tasks_to_process)
				break;
			if(isset($this->tasks_files_array[$i])){
				$filename =  $this->tasks_files_array[$i];
				$temp_task = PhpJobs_TaskRestoreFromFile($filename);
				$this->tasks_object_array[] = $temp_task;
			}//if
		}//for
		return $this->tasks_object_array;
	}//GetList
	public function QueueExecuteTaskList(){
		$this->QueueGetList();
		for($i=0;$i<$this->number_of_tasks_in_queue;$i++){
			if($i>$this->maximum_number_of_tasks_to_process)
				break;
			if(isset($this->tasks_object_array[$i])){
				$this->tasks_object_array[] = $temp_task;
			}//if
		}//for
	}//ExecuteTaskList
}