<?php
function Log4php_Config($category=NULL){
	$config_rolling = array(
		'appenders' => array(
			'default' => array(
				'class' => 'LoggerAppenderRollingFile',
				'layout' => array(
					//'class' => 'LoggerLayoutSimple',
					'class' => 'LoggerLayoutPattern',
					'params' => array(
						'conversionPattern' => PHPJOBS_LOG4PHP_LAYOUT
						)
				),
				'params' => array(
					'file' => PHPJOBS_LOG_FOLDER.$category.PHPJOBS_LOG_EXTENSION,
					'append' => TRUE,
					'maxFileSize' => '10MB',
					'maxBackupIndex' => 5,
					'compress' => TRUE,
				),
			),
		),
		'rootLogger' => array(
			'appenders' => array('default'),
		),
	);
	if(!is_dir(PHPJOBS_LOG_FOLDER))
		mkdir (PHPJOBS_LOG_FOLDER);

	return Logger::configure($config_rolling);
}
?>