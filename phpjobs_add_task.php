<?php
require_once 'phpjobs/phpjobs_tasks_manager.php';


#region Priorities
//Highest priority will be executed before lowest

for($i=0;$i<5;$i++){
	$job = new Job('PhpJobs_FileDelete', array('store/google'.$i.'.png'));
	PhpJobs_AddTaskToQueue("Remove file ".$i, PHPJOBS_PRIORITY_LOW, $job);
}

for($i=0;$i<5;$i++){
	$job = new Job('PhpJobs_FileCopy', array('store/google.png','store/google'.$i.'.png'));
	PhpJobs_AddTaskToQueue("Copy file ".$i, PHPJOBS_PRIORITY_NORMAL, $job);
}

for($i=0;$i<1;$i++){
	$job = new Job('PhpJobs_DownloadFromUrl',array('http://www.google.fr/images/srpr/logo11w.png','store/google.png'));
	PhpJobs_AddTaskToQueue("Download Google logo ".$i, PHPJOBS_PRIORITY_HIGH, $job);
}
#endregion

#region Job Before / Main / After
	for($i=0;$i<1;$i++){
		$job_before = new Job('PhpJobs_DownloadFromUrl', array('http://www.google.fr/images/srpr/logo11w.png','store/google.png'));
		$job_main = new Job('PhpJobs_FileMove', array('store/google.png','store/google_moved.png'));
		$job_after = new Job('PhpJobs_FileDelete', array('store/google.png'));
		PhpJobs_AddTaskToQueue("Multi-Jobs", PHPJOBS_PRIORITY_LOW, $job_main, $job_before, $job_after);
	}
#endregion

#region Scheduled jobs
	$job_cp = new Job('PhpJobs_FileCopy', array('store/google.png','store/google_scheduled.png'));
	$job_del = new Job('PhpJobs_FileDelete', array('store/google_scheduled.png'));
	PhpJobs_AddTaskToQueue('Delete Google Image', PHPJOBS_PRIORITY_NORMAL, $job_del, NULL, NULL, time()+(60*2));//schedule as time
	PhpJobs_AddTaskToQueue('Copy Google Image', PHPJOBS_PRIORITY_NORMAL, $job_cp, NULL, NULL, time());//schedule as time now
	PhpJobs_AddTaskToQueue('Delete Google Image', PHPJOBS_PRIORITY_NORMAL, $job_del, NULL, NULL,date('Y-m-d H:i:s', mktime(date('H')+1, date('m')+10)));//schedule as datetime
	PhpJobs_AddTaskToQueue('Copy Google Image', PHPJOBS_PRIORITY_NORMAL, $job_cp, NULL, NULL,date('Y-m-d H:i:s', mktime(date('H')+1)));//schedule as datetime
#endregion