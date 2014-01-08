<?php
/*
CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(7) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Task ID',
  `task_title` varchar(255) DEFAULT NULL COMMENT 'Task Title / Label',
  `task_function_to_execute` varchar(255) DEFAULT NULL COMMENT 'Function to execute',
  `task_parameters` text COMMENT 'Parameters to that function',
  `task_status` enum('processing','execting','finished') CHARACTER SET ascii DEFAULT 'processing' COMMENT 'Task Status',
  `task_creation_time` timestamp NULL DEFAULT NULL COMMENT 'Task creation time',
  `task_execution_time` timestamp NULL DEFAULT NULL COMMENT 'Task execution time',
  `task_finish_time` timestamp NULL DEFAULT NULL COMMENT 'Task finished time',
  `task_execution_duration` float unsigned DEFAULT NULL COMMENT 'Task execution duration',
  `task_total_duration` float unsigned DEFAULT NULL COMMENT 'Task total duration',
  PRIMARY KEY (`task_id`),
  KEY `task_title` (`task_title`,`task_function_to_execute`),
  KEY `task_status` (`task_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tasks' AUTO_INCREMENT=1 ;
 */
class Task{
	private $id = NULL;
	private $title = NULL;
	private $job_main = NULL;
	private $job_before = NULL;
	private $job_after = NULL;
	private $status = NULL;
	private $priority = NULL;
	private $creation_time = NULL;
	private $execution_time = NULL;
	private $is_scheduled = FALSE;
	private $scheduled_time = NULL;
	private $finish_time = NULL;
	private $execution_duration = NULL;
	private $total_duration = NULL;

	public function __construct($id=NULL, $title=NULL, $priority=PHPJOBS_PRIORITY_NORMAL, \Job $job_main, $status=NULL, \Job $job_before=NULL, \Job $job_after=NULL, $scheduled_time=NULL) {
		$this->creation_time = time();
		//Set Task members
		$this->id = $id;
		$this->title = $title;
		$this->priority = $priority;
		$this->job_before = $job_before;
		$this->job_main = $job_main;
		$this->job_after = $job_after;
		$this->SetStatus($status);
		if(empty($scheduled_time)){
			//echo "EMPTY SCHEDULER<br />";
			$this->is_scheduled = FALSE;
			$this->scheduled_time = NULL;
		}//if
		else{
			//echo "SCHEDULER : ".$scheduled_time."<br />";
			//Time format
			if(is_int($scheduled_time)){
				$this->is_scheduled = TRUE;
				$this->scheduled_time = $scheduled_time;
			}//if
			//Datetime format
			else{
				$this->is_scheduled = TRUE;
				$this->scheduled_time = strtotime($scheduled_time);
			}//else
		}//else
	}

	#region Getters
	public function GetId(){return $this->id;}
	public function GetTitle(){return $this->title;}
	public function GetPriority(){return $this->priority;}
	public function ShowPriority(){
		$show_priority = NULL;
		switch($this->priority){
			case PHPJOBS_PRIORITY_CRITICAL:$show_priority="CRITIAL";break;
			case PHPJOBS_PRIORITY_HIGH:$show_priority="HIGH";break;
			case PHPJOBS_PRIORITY_NORMAL:$show_priority="NORMAL";break;
			case PHPJOBS_PRIORITY_LOW:$show_priority="LOW";break;
		}//switch
		return $show_priority;
	}
	public function GetStatus(){return $this->status;}
	public function GetCreationTime(){return $this->creation_time;}
	public function GetExecutionTime(){return $this->execution_time;}
	public function GetScheduledTime(){return $this->scheduled_time;}
	public function GetScheduledDatetime(){
		$return = NULL;
		if($this->is_scheduled==TRUE)
			$return = $this->GetDatetime($this->scheduled_time);
		else
			$return = NULL;
		return $return;
	}
	public function GetFinishTime(){return $this->finish_time;}
	public function GetExecutionDuration(){return $this->execution_duration;}
	public function GetTotalDuration(){return $this->total_duration;}
	public function GetFunction(){return $this->job_object->GetFunctionToExecute();}
	public function GetArguments(){return $this->job_object->GetParams();}
	#endregion

	#region Duration
	public function SetExecutionTime(){
		$this->execution_time = time();
		$this->execution_duration = microtime(TRUE);
		$this->total_duration = $this->execution_time - $this->creation_time;
	}
	public function SetFinishTime(){
		$this->finish_time = time();
		$this->execution_duration = number_format(microtime(TRUE) - $this->execution_duration, 4);
		$this->total_duration = $this->finish_time - $this->creation_time;
	}
	#endregion

	#region Status
	public function SetStatus($status=NULL){
		if(empty($status)){
			$status = PHPJOBS_TASK_STATUS_PROCESSING;
		}//if
		else{
			$this->status = $status;
		}//else
	}
	#endregion
	private function LaunchExecution(){
		$this->SetStatus(PHPJOBS_TASK_STATUS_EXECUTING);
		$this->SetExecutionTime();
		$execution = empty($this->job_before)?TRUE:$this->job_before->Execute();
		$execution = $this->job_main->Execute();
		$execution = empty($this->job_after)?TRUE:$this->job_after->Execute();
		if($execution==TRUE){
			PhpJobs_LogWrite ('task', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Job execution complete successfully for Task ID ['.$this->GetId().'] named function ['.$this->GetTitle().'].', PHPJOBS_LOG4PHP_DISPLAY);
		}//if
		else{
			PhpJobs_LogWrite ('task', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Job execution complete with errors for Task ID ['.$this->GetId().'] named ['.$this->GetTitle().'].', PHPJOBS_LOG4PHP_DISPLAY);
		}//else
		$this->SetFinishTime();
		$this->SetStatus(PHPJOBS_TASK_STATUS_FINISHED);
		return $execution;
	}
	public function Execute($forced=FALSE){
		$launch_execution = FALSE;
		if($this->is_scheduled == FALSE){
			$launch_execution = $this->LaunchExecution();
		}//if
		else{
			$now = time();
			if($now > $this->scheduled_time){
				$launch_execution = $this->LaunchExecution();
			}//if
			else{
				if($forced==TRUE){
					$launch_execution = $this->LaunchExecution();
				}//if
				else{
					PhpJobs_LogWrite('task', PHPJOBS_LOG4PHP_LEVEL_TRACE, 'Task ['.$this->GetId().'] has not been executed because it is scheduled later at : ['.$this->GetScheduledDatetime().'].', PHPJOBS_LOG4PHP_DISPLAY);
					$launch_execution = FALSE;
				}//else
			}//else
		}//else
		return $launch_execution;
	}
	private function GetDatetime($time){
		$return = NULL;
		if(empty($time) || $time==0){
			$return = NULL;
		}
		else
			$return = date("Y-m-d H:i:s",$time);
		return $return;
	}
	private function ShowArgs($args_array=NULL){
		$args = NULL;
		if(!empty($args_array)){
			$args = implode(", ",$args_array);
		}
		return $args;
	}
	public function Show() {
		return $this->GetId()."\t".$this->GetTitle()."\t".$this->ShowPriority()."\t".$this->GetStatus()."\t".(empty($this->job_before)?NULL:$this->job_before->GetFunctionToExecute())."\t".(empty($this->job_before)?NULL:"[".$this->ShowArgs($this->job_before->GetParams())."]")."\t".$this->job_main->GetFunctionToExecute()."\t[".$this->ShowArgs($this->job_main->GetParams())."]\t".(empty($this->job_after)?NULL:$this->job_after->GetFunctionToExecute())."\t".(empty($this->job_after)?NULL:"[".$this->ShowArgs($this->job_after->GetParams())."]")."\t".$this->GetScheduledDatetime()."\t".$this->GetDatetime($this->creation_time)."\t".$this->GetDatetime($this->execution_time)."\t".$this->execution_duration."\t".$this->GetDatetime($this->finish_time)."\t".$this->total_duration;
	}
	public function GetRepresentation(){
		return $this->Show();
	}
}