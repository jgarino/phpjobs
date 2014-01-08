<?php
require_once(PHPJOBS_LOG4PHP_LOGGER_SCRIPT);
require_once(PHPJOBS_LOG4PHP_CONFIG_DEFAULT);
if(!defined("PhpJobs_LogWrite")){
	function PhpJobs_LogWrite($categorie='task', $level='info',$message, $display=TRUE, $throwableException=NULL){
		if(!is_dir(PHPJOBS_LOG_FOLDER)){
			mkdir(substr(PHPJOBS_LOG_FOLDER,0,intval(strlen(PHPJOBS_LOG_FOLDER)-1)));
		}
		$config = Log4php_Config($categorie);
		$logger = Logger::getLogger($categorie);
		$command = strtolower($level);

		$logger->$command($message, $throwableException);
		if($display==TRUE)
			echo $message."<br />";
	}//LogWrite
}