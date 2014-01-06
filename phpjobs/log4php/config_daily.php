<?php
function Log4php_Config($category=NULL){
	$config_daily = array(
		'appenders' => array(
			'default' => array(
				'class' => 'LoggerAppenderDailyFile',
				'layout' => array(
					//'class' => 'LoggerLayoutSimple',
					'class' => 'LoggerLayoutPattern',
					'params' => array(
						'conversionPattern' => PHPJOBS_LOG4PHP_LAYOUT
						)
				),
				'params' => array(
					'datePattern' => 'Y-m-d',
					'file' => PHPJOBS_LOG_FOLDER.$category.'-%s'.PHPJOBS_LOG_EXTENSION,
				),
			),
		),
		'rootLogger' => array(
			'appenders' => array('default'),
		),
	);
	if(!is_dir(PHPJOBS_LOG_FOLDER))
		mkdir (PHPJOBS_LOG_FOLDER);

	return Logger::configure($config_daily);
}
?>