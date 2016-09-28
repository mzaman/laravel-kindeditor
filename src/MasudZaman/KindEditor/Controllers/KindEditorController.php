<?php
/**
 * Created by github.com/mzaman
 * Repository : github.com/mzaman/laravel-kindeditor
 * Author : Masud Zaman, masud.zmn@gmail.com
 * Date: 16/7/25
 * Time: 23:04
 */

namespace MasudZaman\KindEditor\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Unisharp\Setting\SettingFacade as Setting;
use MasudZaman\KindEditor\lib\Services_JSON;

class KindEditorController extends Controller
{
	public function upload(Request $request){

		$file = $request->file('imgFile');
		$mediaType = trim($request->input('dir'));
		
		$media = array(
			'fileName' => $file->getClientOriginalName(),
			'fileExtension' => $file->getClientOriginalExtension(),
			'tmpName' => $file->path(),
			'fileSize'=> $file->getSize(),/*
			'type' => $file->getMimeType(),*/
			'gettype' => $file->getType(),
			'valid' => $file->isValid(),
			'error' => $file->getError()
		);

		// File directory path
		$media['savePath'] = config('filesystems.disks.upload.root').config('filesystems.disks.upload.prefix');

		//File directory URL	
		$media['saveUrl'] = config('filesystems.disks.upload.domain').config('filesystems.disks.upload.prefix');

		$media['support'][$mediaType] = Setting::has( $mediaType . '_extensions' ) ? Setting::get( $mediaType . '_extensions' ) : config('kindeditor.support.'. $mediaType );

		//Maximum file size
		$media['maxSize'] = Setting::has( $mediaType . '_limit' ) ? (Setting::get( $mediaType . '_limit' ) * 1024 * 1024): config('kindeditor.support.'. $mediaType .'.size');

		extract($media);

		//PHP upload failed
		if (!empty($error)) {
			switch($error){
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

			// Check the file name
			if (!$fileName) {
				$this->alert('Please select a file.');
			}
			// Check the directory
			if (File::isDirectory($savePath) === false) {
				$this->alert($savePath. 'Upload directory does not exist.');
			}
			// Check the directory write permission
			if (File::isWritable($savePath) === false) {
				$this->alert('Upload directory does not have write permission.');
			}
			// Check if uploaded
			if (@is_uploaded_file($tmpName) === false) {
				$this->alert('upload failed.');
			}
			// Check the file size
			if ($fileSize > $maxSize) {
				$this->alert('Upload file size ( ' . $this->FileSizeConvert($fileSize) . ' ) exceeds the limit ( maximum size = ' . $this->FileSizeConvert($maxSize) . ' )');
			}
			// Check if the directory name exist
			$dirName = empty($mediaType) ? 'image' : $mediaType;

			if (empty($support[$dirName])) {
				$this->alert('Directory name is incorrect.');
			}
			// Get the file extension
			$fileExt = strtolower($fileExtension);
			// Check extension
			if (in_array($fileExt, $support[$dirName]) === false) {
				$this->alert("Upload file extension is not allowed extension. \n only allowed " . implode(',', $support[$dirName]) . " format.");
			}
			// Create a folder
			if ($dirName !== '') {
				$savePath .= $dirName . '/';
				$saveUrl .= $dirName . '/';
				if (!file_exists($savePath)) {
					File::makeDirectory($savePath);
				}
			}

			$ymd = date('Ymd');
			$savePath .= $ymd . '/';
			$saveUrl .= $ymd . '/';
			if (!file_exists($savePath)) {
				File::makeDirectory($savePath);
			}
			// A new file name
			$newFileName = date('YmdHis') . '_' . rand(10000, 99999) . '.' . $fileExt;
			// Moving Files
			$filePath = $savePath . $newFileName;
			
			$fileUrl = $saveUrl . $newFileName;

			try{ 
				// uploading file to given path
				$file->move($savePath, $newFileName);
			}catch (\Exception $e){

			}

			header('Content-type: text/html; charset=UTF-8');
			$json = new Services_JSON();
			
			echo $json->encode(array('error' => 0, 'url' => $fileUrl, 'req' => Setting::has( $mediaType . '_extensions' ),  'rs' => $media, 'ro'=>  Setting::get( $mediaType . '_extensions' )));
			exit;
		}

	}


	function alert($msg) {
		header('Content-type: text/html; charset=UTF-8');
		$json = new Services_JSON();
		echo $json->encode(array('error' => 1, 'message' => $msg));
		exit;
	}

	function FileSizeConvert($size, $precision = 2) {
	    static $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
	    $step = 1024;
	    $i = 0;
	    while (($size / $step) > 0.9) {
	        $size = $size / $step;
	        $i++;
	    }
	    return round($size, $precision). ' ' .$units[$i];
	}
}
