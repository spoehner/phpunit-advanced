<?php
namespace App\AntiPatterns;

class NativeFunctions
{
	public function theBadWay()
	{
		// get external resource
		$data = file_get_contents('http://www.example.com');

		return (empty($data) ? null : $data);
	}
	// ----------------------------------------------

	/** @var FileOperator */
	private $fo;

	public function setFileOperator(FileOperator $fo)
	{
		$this->fo = $fo;
	}

	public function theGoodWay()
	{
		$data = $this->fo->get('http://www.example.com');

		return (empty($data) ? null : $data);
	}
}

/**
 * This is a facade for PHP file operations.
 */
class FileOperator
{
	/**
	 * Load a file with file_get_contents.
	 *
	 * @param string $fileName
	 *
	 * @return string
	 */
	public function get($fileName)
	{
		return file_get_contents($fileName);
	}

	/**
	 * Save to file with file_put_contents.
	 *
	 * @param string $fileName
	 * @param string $data
	 *
	 * @return int
	 */
	public function put($fileName, $data)
	{
		return file_put_contents($fileName, $data);
	}
}