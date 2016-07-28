<?php


namespace Eliepse\Lang;


class Chunk
{

	private $name;
	private $content;

	/**
	 * Chunk constructor.
	 * @param $path
	 * @param null $chunk_name
	 */
	public function __construct($path, $chunk_name = null)
	{
		$this->name = !empty($chunk_name) ? $chunk_name : pathinfo($path, PATHINFO_FILENAME);
		/** @noinspection PhpIncludeInspection */
		$this->content = include $path;
	}


	/**
	 * @param $path string|array
	 * @return null|object
	 */
	public function get($path)
	{

		if (is_string($path))
			$nodes_name = explode('.', $path);
		else
			$nodes_name = $path;

		$node = $this->content;

		foreach ($nodes_name as $element) {

			if (!empty($node[$element])) {

				$node = $node[$element];

			}

		}

		return $node;

	}

	public function getName()
	{
		return $this->name;
	}

}