<?php
function Log4php_Config($category=NULL){
	$config_syslog = array(
		'appenders' => array(
			'default' => array(
				'class' => 'LoggerAppenderSyslog',
				'layout' => array(
					//'class' => 'LoggerLayoutSimple',
					'class' => 'LoggerLayoutPattern',
					'params' => array(
						'conversionPattern' => PHPJOBS_LOG4PHP_LAYOUT
						)
				),
				'params' => array(
					//'ident' => 'WebApp - '.CG_WEBSITE_NAME.' - '.$category,//Applicaiton Identity
					'ident' => 'Test',//Applicaiton Identity
					//'facility' => 'LOCAL0',
					'facility' => 'USER',//Only Facility available on Windows Systems
					'option' => 'ODELAY|PID'//Let the system handle when to log
				),
			),
		),
		'rootLogger' => array(
			'appenders' => array('default'),
		),
	);

	return Logger::configure($config_syslog);
}
?>