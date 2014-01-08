<?php
require_once 'phpjobs_req_config.php';
require_once 'phpjobs_req_security.php';
require_once 'phpjobs_req_log.php';
require_once 'phpjobs_req_tools.php';
require_once 'phpjobs_class.job.php';
require_once 'phpjobs_class.task.php';
/*
CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(7) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Task ID',
  `task_title` varchar(255) DEFAULT NULL COMMENT 'Task Title / Label',
  `task_function_to_execute` varchar(255) DEFAULT NULL COMMENT 'Function to execute',
  `task_parameters` text COMMENT 'Parameters to that function',
  `task_datetime_creation` timestamp NULL DEFAULT NULL COMMENT 'Task creation',
  `task_datetime_execution` timestamp NULL DEFAULT NULL COMMENT 'Task execution',
  `task_datetime_finish` timestamp NULL DEFAULT NULL COMMENT 'Task finished datetime',
  `task_datetime_duration` float unsigned DEFAULT NULL COMMENT 'Task execution duration',
  PRIMARY KEY (`task_id`),
  KEY `task_title` (`task_title`,`task_function_to_execute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tasks' AUTO_INCREMENT=1 ;
*/

/*
require_once 'async_tasks/class.job.php';
$job = new Job('DownloadWithCurl',array('http://julien.garino.free.fr/images/logo.png','async_tasks/logo.png'));
$data = serialize($job);
echo "Job serializé !<br /><pre>";
print_r($data);
echo "</pre><br />";

$job2 = unserialize($data);
echo "<br />Job désérialisé !<br />";
if($job2 instanceof Job){
	echo "Execution du job...";
	$job2->Execute();
	echo " terminée";
}
else{
	echo "Le job2 n'est pas une instance de la class Job !";
	var_dump($job2);
}
*/
if(!function_exists('PhpJobs_GetFilesFromDirectory')){
	function PhpJobs_GetFilesFromDirectory($dir){
		if(substr($dir, -1, 1)=='/')
			$dir = substr ($dir, 0, (strlen($dir)-1));

		$files = NULL;
		$handle = opendir($dir) or die('Error : cannot open directory : '.$dir);
		while($entry = @readdir($handle)) {
			if(is_file($dir.'/'.$entry) && $entry != '.' && $entry != '..' && $entry != '.htaccess' && $entry != $dir.'/'.PHPJOBS_TASK_COUNTER_FILE){
				$files[] = $dir.'/'.$entry;
			}//if
		}//while
		closedir($handle);
		return $files;
	}
}
function PhpJobs_GetCurrentDirectory(){
	$path_current = dirname(__FILE__); // /var/www/subdir
	$path_relative = str_replace(PHPJOBS_ROOT_PATH, '', $path_current); // /subdir
	/*
	if(substr($path_relative, -1)!="/"){
		$path_relative.= "/";
	}//if
	*/
	return $path_relative;
}
function PhpJobs_TaskCounterAdd(){
	$cpt = NULL;
	try{
		if(file_exists(PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE)){
			$cpt = file_get_contents(PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE);
			$cpt++;
			PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_DEBUG, 'File exists : '.PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE.' / counter = '.$cpt, PHPJOBS_LOG4PHP_DISPLAY);
		}//if
		else{
			$cpt = 0;
			if(!is_dir(PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER))
				mkdir(PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER);
			PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_DEBUG, 'File does not exist : '.PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE.' / counter = '.$cpt, PHPJOBS_LOG4PHP_DISPLAY);
		}//else
		PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_DEBUG, 'Adding : file_put_contents('.PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE.' , '.($cpt).');' , PHPJOBS_LOG4PHP_DISPLAY);
		file_put_contents(PhpJobs_GetCurrentDirectory().PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE, $cpt);
	}//try
	catch (Exception $ex) {
		PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_ERROR, 'The counter file has not been written correctly : '.$ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
	}//catch
	return $cpt;
}
function PhpJobs_TaskCounter(){
	$cpt = 0;
	try{
		$cpt = file_get_contents(PHPJOBS_TASKS_QUEUE_FOLDER.PHPJOBS_TASK_COUNTER_FILE);
	}//try
	catch (Exception $ex) {
		PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_ERROR, 'The counter file has not been written correctly : '.$ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
	}//catch
	return $cpt;
}
function PhpJobs_TaskFilename($id, $priority){
	$task_filename = PHPJOBS_TASK_MASK;
	//$task_filename = strtolower(str_replace(array('{task_id}','{task_name}'), array($id, $title), $task_filename));
	$task_filename = strtolower(str_replace(array(PHPJOBS_TASK_PRIORITY, PHPJOBS_TASK_ID), array($priority, $id), $task_filename));
	return $task_filename;
}
function PhpJobs_TaskFilePath($id, $priority, $status=PHPJOBS_TASK_STATUS_PROCESSING){
	$folder = NULL;
	switch($status){
		case PHPJOBS_TASK_STATUS_PROCESSING:
		case PHPJOBS_TASK_STATUS_EXECUTING:
			$folder = PHPJOBS_TASKS_QUEUE_FOLDER;break;
		case PHPJOBS_TASK_STATUS_FINISHED:
			if(!is_dir(PHPJOBS_TASKS_DONE_FOLDER))
				mkdir (PHPJOBS_TASKS_DONE_FOLDER);
			$folder = PHPJOBS_TASKS_DONE_FOLDER;
			break;
		default:
			$folder = PHPJOBS_TASKS_QUEUE_FOLDER;break;
	}
	return $folder.PhpJobs_TaskFilename($id, $priority);
}
function PhpJobs_AddTaskToQueue($title=NULL, $priority=PHPJOBS_PRIORITY_NORMAL, \Job $job_main, \Job $job_before=NULL, \Job $job_after=NULL, $scheduled_time=NULL){
	$id = PhpJobs_GetNewTaskId();
	$task_object = new Task($id, $title, $priority, $job_main, PHPJOBS_TASK_STATUS_PROCESSING, $job_before, $job_after, $scheduled_time);
	PhpJobs_TaskStore($task_object);
	PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_INFO, "Task ID [".$id."] named [".$title."] stored successfully at ".date("Y-m-d H:i:s"), PHPJOBS_LOG4PHP_DISPLAY);
}
function PhpJobs_GetNewTaskId(){
	return PhpJobs_TaskCounterAdd();
}
function PhpJobs_TaskExecute(\Task $task_object){
	$execution = $task_object->Execute();
	if($execution==TRUE){
		PhpJobs_TaskStore($task_object);
	}//if
	else{
		//Rety
		$task_object->Execute();
		PhpJobs_TaskStore($task_object);
	}//else
	return $task_object;
}
function PhpJobs_TaskRemoveFromQueue($id, $priority, $status){
	if($status==PHPJOBS_TASK_STATUS_FINISHED){
		$filename = PhpJobs_TaskFilePath($id, $priority);
		if(file_exists($filename)){
			unlink($filename);
		}//if
	}//if
}
function PhpJobs_TaskWrite($id, $priority, $status, $serialized_content){
	$filepath = NULL;
	try{
		$filepath = PhpJobs_TaskFilePath($id, $priority, $status);
		$handle = fopen($filepath, 'w');
		fwrite($handle, $serialized_content);
		fclose($handle);
		PhpJobs_TaskRemoveFromQueue($id, $priority, $status);
	}//try
	catch (Exception $ex) {
		PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_ERROR, 'The file '.$filepath.' has not been written : '.$ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
	}//catch
	return $filepath;
}
function PhpJobs_TaskStore(\Task $task_object){
	$filename = $serialized = NULL;
	try{
		$serialized = serialize($task_object);
		if(PHPJOBS_ENCRYPTION==TRUE){
		    $serialized = PhpJobs_Encrypt($serialized);
		}
	}//try
	catch(Exception $ex){
		PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_ERROR, 'Error during serialization Task ID ['.$task_object->GetId().'] : '.$ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
	}//catch
	return PhpJobs_TaskWrite($task_object->GetId(), $task_object->GetPriority(), $task_object->GetStatus(), $serialized);
}
function PhpJobs_TaskRead($id, $serialized_object){
	$filepath = NULL;
	try{
		$filepath = PhpJobs_TaskFilePath($id);
		$handle = fopen($filepath, 'w');
		fwrite($handle, $serialized_object);
		fclose($handle);
	}//try
	catch (Exception $ex) {
		PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_ERROR, 'The file '.$filepath.' has not been written : '.$ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
	}//catch
	return $filepath;
}
function PhpJobs_TaskRestore($id){
	$content = file_get_contents(TaskFilePath($id));
	if(PHPJOBS_ENCRYPTION==TRUE){
		$content = PhpJobs_Decrypt($content);
	}
	$task_object = unserialize($content);
	return $task_object;
}
function PhpJobs_TaskRestoreFromFile($filepath){
	$content = file_get_contents($filepath);
	if(PHPJOBS_ENCRYPTION==TRUE){
		$content = PhpJobs_Decrypt($content);
	}
	$task_object = unserialize($content);
	return $task_object;
}