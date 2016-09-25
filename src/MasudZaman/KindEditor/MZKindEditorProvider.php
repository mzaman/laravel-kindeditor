<?php
/**
 * Created by mzaman.
 * Copyright MasudZaman
 * User: mzaman
 * Date: 26/9/26
 * Time: 12:47
 */

namespace MasudZaman\KindEditor;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MZKindEditorProvider extends ServiceProvider
{

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$configPath = __DIR__."/../../../config/mzkindeditor.php";
		$this->mergeConfigFrom($configPath, 'mzkindeditor');
	}

	public function boot(){
		$configPath = __DIR__."/../../../config/mzkindeditor.php";

		$this->publishes([$configPath => $this->getConfigPath()],'config');

		$routeConfig = [
			'namespace' => 'MasudZaman\KindEditor\Controllers',
		];

		$this->getRouter()->group($routeConfig,function($router){
			$router->any('laravel-kindeditor',["uses"=>"Controller@kindeditor"]);
		});
	}

	protected function getRouter()
	{
		return $this->app['router'];
	}

	public function getConfigPath(){
		return config_path("mzkindeditor.php");
	}
	protected function publishConfig($configPath)
	{
		$this->publishes([$configPath => config_path('mzkindeditor.php')], 'config');
	}
}