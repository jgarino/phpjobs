<?php
class Job {// implements Serializable{
	private $function_to_execute = NULL;
	private $params = NULL;
	private $execution_duration = NULL;

	public function __construct($function_name, $params=NULL, $execution_duration=0){
		$this->function_to_execute = $function_name;
		$this->params = $params;
		$this->execution_duration = $execution_duration;
	}//__construct

	public function Execute(){
		$execution_succeeded = FALSE;
		$this->execution_duration = 0;
		$start = $end = 0;
		if(function_exists($this->function_to_execute)){
			$start = microtime(TRUE);
			try{
				call_user_func_array($this->function_to_execute, $this->params);
				$execution_succeeded = TRUE;
				PhpJobs_LogWrite('job', PHPJOBS_LOG4PHP_LEVEL_INFO, 'Call to user function '.$this->function_to_execute.' with params succeeded.', PHPJOBS_LOG4PHP_DISPLAY);
			}//try
			catch(BadFunctionCallException $bf){
				PhpJobs_LogWrite('job', PHPJOBS_LOG4PHP_LEVEL_ERROR, $bf->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
			}
			catch(BadMethodCallException $bm){
				PhpJobs_LogWrite('job', PHPJOBS_LOG4PHP_LEVEL_ERROR, $bm->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
			}
			catch(Exception $ex){
				PhpJobs_LogWrite('job', PHPJOBS_LOG4PHP_LEVEL_ERROR, $ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
			}
		}//if
		else{
			PhpJobs_LogWrite('job', PHPJOBS_LOG4PHP_LEVEL_WARNING, 'The function '.$this->function_to_execute.' doesn\'t exist or has not been found !', PHPJOBS_LOG4PHP_DISPLAY);
		}//else
		$end = microtime(TRUE);
		$this->execution_duration = number_format($end - $start, 4);
		PhpJobs_LogWrite('job', PHPJOBS_LOG4PHP_LEVEL_TRACE, 'The function '.$this->function_to_execute.' has been executed in '.$this->execution_duration.' seconds.', PHPJOBS_LOG4PHP_DISPLAY);
		return $execution_succeeded;
	}//Execute

	#region Getters
	public function GetFunctionToExecute(){
		return $this->function_to_execute;
	}
	public function GetParams(){
		return $this->params;
	}
	public function GetExecutionDuration(){
		return $this->execution_duration;
	}
	public function GetParametersToString($array){
		$parameters_to_string = NULL;
		if(is_array($array)){
			foreach($array as $index => $arg){
				if(is_array($arg)){
					$parameters_to_string .= $this->GetParametersToString($arg);
				}//if
				else{
					$parameters_to_string .= ", ".$arg;
				}//else
			}//foreach
		}//if
		return $parameters_to_string;
	}
	#endregion
}