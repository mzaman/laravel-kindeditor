<?php
/**
 * Created by github.com/mzaman
 * Repository : github.com/mzaman/laravel-kindeditor
 * Author : Masud Zaman, masud.zmn@gmail.com
 * Date: 26/9/26
 * Time: 12:47
 */

namespace MasudZaman\KindEditor;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class KindEditorServiceProvider extends ServiceProvider
{

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$configPath = __DIR__."/../../../config/kindeditor.php";
		$this->mergeConfigFrom($configPath, 'kindeditor');
	}

	public function boot(){
		$configPath = __DIR__."/../../../config/kindeditor.php";

		$this->publishes([$configPath => $this->getConfigPath()],'config');

		$routeConfig = [
			'namespace' => 'MasudZaman\KindEditor\Controllers',
		];

		$this->getRouter()->group($routeConfig,function($router){
			$uri = \Config::has('kindeditor.url') ? \Config::get('kindeditor.url') : 'laravel-kindeditor';

			$router->any($uri, ['as'=>'kindeditor.upload','uses'=>'KindeditorController@upload']);
		});
	}

	protected function getRouter()
	{
		return $this->app['router'];
	}

	public function getConfigPath(){
		return config_path("kindeditor.php");
	}
	protected function publishConfig($configPath)
	{
		$this->publishes([$configPath => config_path('kindeditor.php')], 'config');
	}
}
