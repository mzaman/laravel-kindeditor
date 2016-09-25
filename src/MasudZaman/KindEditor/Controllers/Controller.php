<?php
/**
 * Created by mzaman.
 * Copyright MasudZaman
 * User: mzaman
 * Date: 16/7/25
 * Time: 23:04
 */

namespace MasudZaman\KindEditor\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use MasudZaman\KindEditor\lib\Services_JSON;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
	public function kindeditor(Request $request){
		$file = $request->file('imgFile');
		
		$media = array(
			'name' => $file->getClientOriginalName(),
			'extension' => $file->extension(),
			'path' => $file->path(),
			'size'=> $file->getSize(),
			'type' => $file->getMimeType(),
			'filetype' => $file->getType(),
			'valid' => $file->isValid(),
			'error' => $file->getError()
		);
		/*$php_path = dirname(__FILE__) . '/';
		$php_url = dirname($_SERVER['PHP_SELF']) . '/';*/

		// File directory path
		//$save_path = $php_path . '../attached/';
		$save_path = '/attached/';
		//File directory URL
		//$save_url = $php_url . '../attached/';
		$save_url = '/attached/';
		
		$media['save_path'] = $save_path = config("filesystems.disks.upload.root").config("filesystems.disks.upload.prefix");
		$media['save_url'] = $save_url = config("filesystems.disks.upload.domain").config("filesystems.disks.upload.prefix");

		;

		$media['support'] = $ext_arr = array(
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'flash' => array('swf', 'flv'),
			'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
			'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
		);

		//Maximum file size
		$media['max_size'] = $max_size = 1000000;


		//$save_path = realpath($save_path) . '/';

		//PHP upload failed
		if (!empty($_FILES['imgFile']['error'])) {
			switch($_FILES['imgFile']['error']){
				case '1':
					$error = 'Over php.ini allowable size.';
					break;
				case '2':
					$error = 'Form allows more than size.';
					break;
				case '3':
					$error = 'Picture was only partially uploaded.';
					break;
				case '4':
					$error = 'Please select an image.';
					break;
				case '6':
					$error = 'Missing a temporary directory.';
					break;
				case '7':
					$error = 'Write files to a hard drive failure.';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = 'unknown mistake.';
			}
			$this->alert($error);
		}

		// Before uploading files
		if (empty($_FILES) === false) {
			// The original file name
			$file_name = $file->getClientOriginalName();
			// Temporary file name on server
			$tmp_name = $file->path();
			// File size
			$file_size = $file->getSize();
			// Check the file name
			if (!$file_name) {
				$this->alert("Please select a file.");
			}
			// Check the directory
			if (File::isDirectory($save_path) === false) {
				$this->alert($save_path. "Upload directory does not exist.");
			}
			// Check the directory write permission
			if (File::isWritable($save_path) === false) {
				$this->alert("Upload directory does not have write permission.");
			}
			// Check if uploaded
			if (@is_uploaded_file($tmp_name) === false) {
				$this->alert("upload failed.");
			}
			// Check the file size
			if ($file_size > $max_size) {
				$this->alert("Upload file size exceeds the limit.");
			}
			// Check if the directory name exist
			$uri_dir = $request->only('dir');
			$dir_name = empty($uri_dir[0]) ? 'image' : trim($uri_dir[0]);
			if (empty($ext_arr[$dir_name])) {
				$this->alert("Directory name is incorrect.");
			}
			// Get the file extension
			/*$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);*/
			$file_ext = strtolower($file->extension());
			// Check extension
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$this->alert("Upload file extension is not allowed extension. \n only allowed" . implode(",", $ext_arr[$dir_name]) . "format.");
			}
			// Create a folder
			if ($dir_name !== '') {
				$save_path .= $dir_name . "/";
				$save_url .= $dir_name . "/";
				if (!file_exists($save_path)) {
					File::makeDirectory($save_path);
				}
			}
			$ymd = date("Ymd");
			$save_path .= $ymd . "/";
			$save_url .= $ymd . "/";
			if (!file_exists($save_path)) {
				File::makeDirectory($save_path);
			}
			// A new file name
			$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
			// Moving Files
			$file_path = $save_path . $new_file_name;
/*
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$this->alert("Upload file failed.");
			}*/
			@chmod($file_path, 0644);
			
			$file_url = $save_url . $new_file_name;

			try{
				// echo $file_path; 
				// uploading file to given path
				$file->move($save_path, $new_file_name);
			}catch (\Exception $e){

			}

			// $file_url = config("filesystems.upload.domain").str_replace('//','/',config("filesystems.upload.prefix").$file_url);
			header('Content-type: text/html; charset=UTF-8');
			$json = new Services_JSON();
			echo $json->encode(array('error' => 0, 'url' => $file_url, 'req' => $request->only('dir'),  'rs' => $media, 'ro'=> $_FILES['imgFile']));
			exit;
		}

	}




	function alert($msg) {
		header('Content-type: text/html; charset=UTF-8');
		$json = new Services_JSON();
		echo $json->encode(array('error' => 1, 'message' => $msg));
		exit;
	}
}
