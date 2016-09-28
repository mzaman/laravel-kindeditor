<?php
/**
 * Created by github.com/mzaman
 * Repository : github.com/mzaman/laravel-kindeditor
 * Author : Masud Zaman, masud.zmn@gmail.com
 * Date: 26/9/26
 * Time: 16:48
 */
return [
	'url' => 'laravel-kindeditor',
	'support' => [
			'image' => [ 'gif', 'jpg', 'jpeg', 'png', 'bmp',
				'size' => 1024*1024*0.2],
			'audio' => [ 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi',
				'size' => 1024*1024*20],
			'video' => [ 'mp4','mpg', 'asf', 'ogg', 'rmvb',
				'size' => 1024*1024*100],
			'flash' => [ 'rm', 'rmvb',
				'size' => 1024*1024*20],
			'media' => [ 'swf', 'flv', 'mp3', 'mp4', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb',
				'size' => 1024*1024*200],
			'file'  => [ 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2',
				'size' => 1024*1024*2]
		]
];