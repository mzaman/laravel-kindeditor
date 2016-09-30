<?php
/**
 * Created by github.com/mzaman
 * Repository : github.com/mzaman/laravel-kindeditor
 * Author : Masud Zaman, masud.zmn@gmail.com
 * Date: 26/9/26
 * Time: 16:48
 */
return [
	'allowedExtensions' => [
		'image' => [ 'gif', 'jpg', 'jpeg', 'png', 'bmp'],
		'audio' => [ 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi'],
		'video' => [ 'mp4','mpg', 'asf', 'ogg', 'rmvb'],
		'flash' => [ 'rm', 'rmvb'],
		'media' => [ 'swf', 'flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'],
		'file'  => [ 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2']
	],
	// Maximum filesize in MB
	'maxSize' => [
		'image' => 1024*1024*0.2,
		'audio' => 1024*1024*20,
		'video' => 1024*1024*100,
		'flash' => 1024*1024*20,
		'media' => 1024*1024*200,
		'file'  => 1024*1024*2
	]

];