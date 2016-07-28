<?php

namespace Eliepse\Lang;

use Locale;

class Lang
{

	private static $_instance;

	/* Connfigurations  */
	protected $root_path;
	private $cache = false; // TODO Système de cache
	private $languages_relative_path = 'ressources/languages/';
	private $language_full_path;
	private $cookie_spec = [
		'name'       => 'lang',
		'expireTime' => 60 * 60 * 24 * 7,
		'path'       => '/',
		'domain'     => null,
	];
	private $default_index = 'fr';
	private $indexes = [
		'fr' => 'fr',
	];

	/* Working values  */
	private $current_index;
	private $chunks = [];

//	private $jumper_path = ''; // TODO Système de jumper

	private function __construct($loc)
	{

		$rootpath = explode('\\', __DIR__);

		if (count($rootpath) === 1)
			$rootpath = explode('/', __DIR__);

		for ($i = 3; $i > 0; $i--) {
			array_pop($rootpath);
		}

		$this->root_path = implode('/', $rootpath);

		$configs = include $this->root_path . '/config/lang_config.php';

		// Fill the configurations
		foreach ($configs as $key => $val) {

			if (property_exists(__CLASS__, $key)) {
				$this->$key = $val;
			}

		}

		if (!empty($loc)) {
			$this->setLanguage($loc);
		} else {
			$this->autoCheckLanguage();
		}

		// Get available chunks
		$this->language_full_path = $this->root_path . '/' . $this->languages_relative_path . $this->current_index . '/';

		$directory = glob($this->language_full_path . '*.php', GLOB_NOSORT);

		if ($directory !== false) {

			foreach ($directory as $element) {
				$chunk = new Chunk($element);
				$this->chunks[$chunk->getName()] = $chunk;
			}

		}

	}

	private function setLanguage($index)
	{

		$accepted = Locale::lookup(array_keys($this->indexes), $index, $this->default_index);

		$this->current_index = $this->indexes[$accepted];

		setcookie($this->cookie_spec['name'], $this->current_index,
			time() + $this->cookie_spec['expireTime'],
			$this->cookie_spec['path'],
			$this->cookie_spec['domain']);

	}

	private function autoCheckLanguage()
	{

		if (!empty($_GET['lang']))
			$this->setLanguage(htmlentities($_GET['lang']));

		elseif (!empty($_COOKIE[$this->cookie_spec['name']]))
			$this->setLanguage(htmlentities($_COOKIE['lang']));

		elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			$this->setLanguage(Locale::acceptFromHttp(htmlentities($_SERVER['HTTP_ACCEPT_LANGUAGE'])));

		else
			$this->current_index = $this->default_index;

	}

	/**
	 * La méthode statique qui permet d'instancier ou de récupérer l'instance unique
	 * @param null $loc
	 * @return Lang
	 */
	public static function getInstance($loc = null)
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new Lang($loc);
		}
		return self::$_instance;
	}


	/**
	 * @param string $path Le chemin vers l'élément
	 * @param bool $echo
	 * @return object|string
	 */
	public function get($path, $echo = false)
	{

		if (empty($path) || !is_string($path)) {

			$result = null;

		} else {

			$nodes_name = explode('.', $path);

			$chunk = $this->getChunk($nodes_name[0]);

			array_shift($nodes_name);

			$result = $chunk->get($nodes_name);

		}

		if ($echo === true) {
			echo $result;
			return;
		} else {
			return $result;
		}

	}

	/**
	 * @param $name string Le nom du Chunk à récupérer
	 * @return bool|Chunk Retour
	 */
	public function getChunk($name)
	{

		if (array_key_exists($name, $this->chunks)) {
			return $this->chunks[$name];
		}

		return false;
	}

	public function fillPattern($path, $pattern)
	{
	} // TODO Système de completion dans un texte

	public function fillRepeatPattern($path, $pattern)
	{
	} // TODO Système de completion dans un texte avec répétition du contenu

}