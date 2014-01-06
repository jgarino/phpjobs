<?php
define("PHPJOBS_TASKS_STORE_AS_DB", "DB");
define("PHPJOBS_TASKS_STORE_AS_FILES", "FILES");
define("PHPJOBS_TASKS_STORE_DEFAULT", "FILES");
define("PHPJOBS_TASKS_STORE_MODE",PHPJOBS_TASKS_STORE_DEFAULT);//DB or FILES

define("PHPJOBS_TASK_COUNTER_FILE","cpt.txt");
define("PHPJOBS_TASKS_QUEUE_FOLDER","phpjobs/queue/");
define("PHPJOBS_TASKS_DONE_FOLDER","phpjobs/done/");
define("PHPJOBS_TASK_PREFIX","task_");
define("PHPJOBS_TASK_SUFFIX",".tsk");
define("PHPJOBS_TASK_ID","{task_id}");
define("PHPJOBS_TASK_PRIORITY","{task_priority}");
define("PHPJOBS_TASK_MASK",PHPJOBS_TASK_PREFIX.PHPJOBS_TASK_PRIORITY."_".PHPJOBS_TASK_ID.PHPJOBS_TASK_SUFFIX);
define("PHPJOBS_TASK_REPRESENTATION","{task_id}\t{task_title}\t{task_priority}\t{task_status}\t{job_before}\t{params_before}\t{job_main}\t{params_main}\t{job_ater}\t{params_after}\t{task_creation_time}\t{task_execution_time}\t{task_execution_duration}\t{task_finish_time}\t{task_total_duration}");
define("PHPJOBS_TASK_TD_HEADER","<td>ID</td><td>Title</td><td>Priority</td><td>Status</td><td>Job Before</td><td>Job before arguments</td><td>Job main</td><td>Job main arguments</td><td>Job after</td><td>Job after arguments</td><td>Scheduled</td><td>Created</td><td>Executed</td><td>Execution duration</td><td>Finished</td><td>Total duration</td>");

define("PHPJOBS_TASK_STATUS_PROCESSING","processing");
define("PHPJOBS_TASK_STATUS_EXECUTING","executing");
define("PHPJOBS_TASK_STATUS_FINISHED","finished");

define("PHPJOBS_PRIORITY_LOW",3);
define("PHPJOBS_PRIORITY_NORMAL",2);
define("PHPJOBS_PRIORITY_HIGH",1);
define("PHPJOBS_PRIORITY_CRITICAL",0);

#region Security
define("PHPJOBS_CRYPT_KEY","DG#*G{>N,8v4fE42?^9r_K3xjtT!V37a");//can be change with your own security key
define("PHPJOBS_ENCRYPTION",TRUE);//TRUE or FALSE : encryption of task files is enabled or disabled
define("PHPJOBS_ROOT_PATH", dirname(__FILE__) );//Example : /var/www
#endregion

#region Log
define("PHPJOBS_LOG_FOLDER","logs/");
define("PHPJOBS_LOG_EXTENSION",".log");
define("PHPJOBS_LOG4PHP_LOGGER_SCRIPT","log4php/Logger.php");
define("PHPJOBS_LOG4PHP_CONFIG_DAILY","log4php/config_daily.php");
define("PHPJOBS_LOG4PHP_CONFIG_ROLLING","log4php/config_rolling.php");
define("PHPJOBS_LOG4PHP_CONFIG_SYSLOG","log4php/config_syslog.php");
define("PHPJOBS_LOG4PHP_CONFIG_DEFAULT",PHPJOBS_LOG4PHP_CONFIG_ROLLING);
define("PHPJOBS_LOG4PHP_LAYOUT", '[%date{Y-m-d H:i:s,u}] [Process : %process] [Level : %level] [Logger : %logger] [Request : %server{SERVER_NAME}:%server{SERVER_PORT}%server{SCRIPT_NAME} FROM %server{REMOTE_ADDR} ] : %message%newline');//list available on : https://logging.apache.org/log4php/docs/layouts/pattern.html
define("PHPJOBS_LOG4PHP_LEVEL_DEBUG",'debug');
define("PHPJOBS_LOG4PHP_LEVEL_TRACE",'trace');
define("PHPJOBS_LOG4PHP_LEVEL_INFO",'info');
define("PHPJOBS_LOG4PHP_LEVEL_WARNING",'warn');
define("PHPJOBS_LOG4PHP_LEVEL_ERROR",'error');
define("PHPJOBS_LOG4PHP_LEVEL_FATAL",'fatal');

define("PHPJOBS_LOG4PHP_DISPLAY",TRUE);
#endregion