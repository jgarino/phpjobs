<?php
if(!defined("PhpJobs_DownloadFromUrl")){
	function PhpJobs_DownloadFromUrl($url,$target_file_name){
		$is_downloaded_ok = FALSE;
		$start = microtime(TRUE);
		PhpJobs_LogWrite("job", PHPJOBS_LOG4PHP_LEVEL_INFO, "Download of [".$url."] starts to [".$target_file_name."].");
		$temp_file_name = $target_file_name."_".time();
		$fp = fopen($temp_file_name,"w");

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		// grab URL and pass it to the browser
		$data = curl_exec($ch);
		$curl_error_number = curl_errno($ch);
		$curl_error = curl_error($ch);

		// close cURL resource, and free up system resources
		curl_close($ch);
		fclose($fp);

		if ($curl_error_number > 0){
			PhpJobs_LogWrite("job", PHPJOBS_LOG4PHP_LEVEL_ERROR, "Download error : cURL Error ($curl_error_number): $curl_error for [".$url."].");
		}//if
		else{
			rename($temp_file_name, $target_file_name);
			$is_downloaded_ok = TRUE;
			PhpJobs_LogWrite("job", PHPJOBS_LOG4PHP_LEVEL_INFO, "Download of [".$url."] successfully stored as ".$target_file_name.".");
		}//else
		$end = microtime(TRUE);
		PhpJobs_LogWrite("job", PHPJOBS_LOG4PHP_LEVEL_INFO, "Download of [".$url."] finished in ".  number_format($end-$start,4)." seconds.");
		return $is_downloaded_ok;
	}
}
if(!defined("PhpJobs_FileGetDirectoryFromFile")){
	function PhpJobs_FileGetDirectoryFromFile($file){
		$pathinfo = pathinfo($file);
		$dir = $pathinfo["dirname"];
		return $dir;
	}
}
if(!defined('PhpJobs_FileMove')){
	function PhpJobs_FileMove($current_filepath, $new_filepath){
		$is_file_moved = FALSE;
		//move
		$new_directory = PhpJobs_FileGetDirectoryFromFile($new_filepath);
		try{
			if(!is_dir($new_directory)){
				mkdir($new_directory);
			}//if
			$is_file_moved = rename($current_filepath, $new_filepath);
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_INFO, 'File move from '.$current_filepath.' to '.$new_filepath.' successfully.', PHPJOBS_LOG4PHP_DISPLAY);
		}//try
		catch(Exception $ex){
			$is_file_moved = FALSE;
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_ERROR, $ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
		}//catch
		return $is_file_moved;
	}
}
if(!defined('PhpJobs_FileCopy')){
	function PhpJobs_FileCopy($current_filepath, $new_filepath){
		//Copy
		$is_file_copied = FALSE;
		$new_directory = PhpJobs_FileGetDirectoryFromFile($new_filepath);
		try{
			if(!is_dir($new_directory)){
				mkdir($new_directory);
			}//if
			$is_file_copied = copy($current_filepath, $new_filepath);
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_INFO, 'File copied from '.$current_filepath.' to '.$new_filepath.' successfully.', PHPJOBS_LOG4PHP_DISPLAY);
		}//try
		catch(Exception $ex){
			$is_file_copied = FALSE;
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_ERROR, $ex->getMessage(), PHPJOBS_LOG4PHP_DISPLAY);
		}//catch
		return $is_file_copied;
	}
}
if(!defined('PhpJobs_FileDelete')){
	function PhpJobs_FileDelete($filepath){
		//Delete
		$is_file_deleted = FALSE;
		if(file_exists($filepath)){
		    try{
				$is_file_deleted = unlink($filepath);
		    }//try
		    catch(Exception $ex){
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_ERROR
				, 'File ['.$filepath.'] is not deleted ['.$filepath.'] '.$ex->getMessage().'.', PHPJOBS_LOG4PHP_DISPLAY);
		    }//catch
		    if($is_file_deleted==TRUE){
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_INFO
				, 'File ['.$filepath.'] is deleted OK : '.$filepath.'.', PHPJOBS_LOG4PHP_DISPLAY);
		    }//if
		    else{
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_WARNING
				, 'File ['.$filepath.'] is not deleted :  doesn\'t exists.', PHPJOBS_LOG4PHP_DISPLAY);
		    }//else
		}//if
		else{
			PhpJobs_LogWrite('tools', PHPJOBS_LOG4PHP_LEVEL_WARNING
				, 'File ['.$filepath.'] is not deleted : '.$filepath.' because it doesn\'t exists.', PHPJOBS_LOG4PHP_DISPLAY);
		}//else
		return $is_file_deleted;
	}
}