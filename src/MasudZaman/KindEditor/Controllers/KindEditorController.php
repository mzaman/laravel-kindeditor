<?php
/**
 * Created by github.com/mzaman
 * Repository : github.com/mzaman/laravel-kindeditor
 * Author : Masud Zaman, masud.zmn@gmail.com
 * Date: 16/7/25
 * Time: 23:04
 */

namespace MasudZaman\KindEditor\Controllers;

/*use Illuminate\Support\Facades\Storage;*/
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
/*use Illuminate\Support\Facades\Input;*/
use MasudZaman\KindEditor\lib\Services_JSON;
use Unisharp\Setting\SettingFacade as Setting;
use Auth;

class KindEditorController extends Controller
{    

	public function upload(Request $request){

		$file = $request->file('imgFile');
		$mediaType = trim($request->input('dir'));
		$saveDirectory = config('filesystems.disks.upload.prefix');
		
		$media = array(
			'fileName' => $file->getClientOriginalName(),
			'fileExtension' => $file->getClientOriginalExtension(),
			'tmpName' => $file->path(),
			'fileSize'=> $file->getSize(),/*
			'type' => $file->getMimeType(),
			'getType' => $file->getType(),*/
			'valid' => $file->isValid(),
			'error' => $file->getError()
		);
		// File directory path
		$media['savePath'] = config('filesystems.disks.upload.root').$saveDirectory;

		//File directory URL	
		$media['saveUrl'] = config('filesystems.disks.upload.domain').$saveDirectory;

		$media['allowedExtensions'][$mediaType] = Setting::has( $mediaType . '_extensions' ) ? Setting::get( $mediaType . '_extensions' ) : config('kindeditor.allowedExtensions.'. $mediaType );

		//Maximum file size
		$media['maxSize'] = Setting::has( $mediaType . '_limit' ) ? (Setting::get( $mediaType . '_limit' ) * 1024 * 1024): config('kindeditor.maxSize.'. $mediaType);

		// Extract all keys
		extract($media);

		// Check if the directory name exist
		$dirName = empty($mediaType) ? 'image' : $mediaType;

		if (empty($allowedExtensions[$dirName])) {
			$this->alert('Directory name is incorrect.');
		}
		
		// Get the file extension
		$fileExt = strtolower($fileExtension);
		
		// Check extension
		if (in_array($fileExt, $allowedExtensions[$dirName]) === false) {
			$this->alert("Upload ". $mediaType ." extension is not allowed extension. \n only allowed " . implode(',', $allowedExtensions[$dirName]) . " format.");
		}
		
		//PHP upload failed
		if (!empty($error)) {
			switch($error){
				case '1':
					$error = 'The uploaded '. $mediaType .' exceeds the upload_max_filesize directive in php.ini';
					break;
				case '2':
					$error = 'The uploaded '. $mediaType .' exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
				case '3':
					$error = 'File was only partially uploaded.';
					break;
				case '4':
					$error = 'Please select '. $mediaType;
					break;
				case '6':
					$error = 'Missing a temporary directory.';
					break;
				case '7':
					$error = 'Write '. $mediaType .' to a hard drive failure.';
					break;
				case '8':
					$error = 'A PHP extension stopped the '. $mediaType .' upload';
					break;
				case '999':
				default:
					$error = 'unknown mistake.';
			}
			$this->alert($error);
		}

		// Before uploading the files
		if (empty($_FILES) === false && $valid) {

			// Check the file name
			if (!$fileName) {
				$this->alert('Please select '. $mediaType);
			}
			// Check or create the directory
			if (File::isDirectory($savePath) === false) {
				File::makeDirectory($savePath);
				// $this->alert($savePath. 'Upload directory does not exist.');
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
				$this->alert('Upload '. $mediaType .' size ( ' . $this->humanReadableFileSize($fileSize) . ' ) exceeds the limit ( maximum size = ' . $this->humanReadableFileSize($maxSize) . ' )');
			}
			
			// Create a folder
			if ($dirName !== '') {
				$savePath .= $dirName . '/';
				$saveUrl .= $dirName . '/';
				if (File::isDirectory($savePath) === false) {
					File::makeDirectory($savePath);
				}
			}
			
			$nDigit = 10;
			$nDigitRandomNumber = rand(pow(10, $nDigit-1), pow(10, $nDigit)-1);
			$userDirectory = $nDigitRandomNumber . /*User::find(*/Auth::user()/*->id)*/->id;
			$savePath .= $userDirectory . '/';
			$saveUrl .= $userDirectory . '/';
			if (File::isDirectory($savePath) === false) {
				File::makeDirectory($savePath);
			}

			// A new file name
			// $newFileName = $this->uniqueFilename($savePath, $fileName, $fileExt);
			$newFileName = $mediaType . '_' . $userDirectory . '_' . md5($fileName. time()) . '.' . $fileExt;
			
			// Moving Files
			$filePath = $savePath . $newFileName;
			$fileUrl = $saveUrl . $newFileName;

			try{ 
				// uploading file to given path
				$file->move($savePath, $newFileName);
			}catch (\Exception $e){

			}

			$category = $mediaType == 'audio' || 
						$mediaType == 'video' || 
						$mediaType == 'file'  ||
						$mediaType == 'document'  
						? ucfirst($mediaType/* == 'document' ? 'file' : $mediaType*/): 'Article';

			header('Content-type: text/html; charset=UTF-8');
			$json = new Services_JSON();
			
			echo $json->encode(['error' => 0, 'url' => $fileUrl, 'category' => $category, 'req' => Setting::has( $mediaType . '_extensions' ),  'rs' => $media, 'ro'=>  Setting::get( $mediaType . '_extensions' )]);
			exit;
		}

	}


	function alert($msg) {
		header('Content-type: text/html; charset=UTF-8');
		$json = new Services_JSON();
		echo $json->encode(array('error' => 1, 'message' => $msg));
		exit;
	}

	function humanReadableFileSize($size, $precision = 2) {
	    static $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
	    $step = 1024;
	    $i = 0;
	    while (($size / $step) > 0.9) {
	        $size = $size / $step;
	        $i++;
	    }
	    return round($size, $precision). ' ' .$units[$i];
	}

	function uniqueFilename($path, $name, $ext) {
		
		$output = $name;
		$basename = basename($name, '.' . $ext);
		$i = 2;
		
		while(File::exists($path . '/' . $output)) {
			$output = $basename . $i . '.' . $ext;
			$i ++;
		}
		
		return $output;
		
	}
}
