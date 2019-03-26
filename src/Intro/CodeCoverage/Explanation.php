<?php
namespace CodeCoverage;

class Explanation
{
	public function simpleMethod()
	{
		/*
		 * Eine Funktion ohne Komplexität und nur einem Ausführungspfad.
		 */
		return 5;
	}

	public function ifThenElse($a)
	{
		/*
		 * Etwas mehr Komplexität: 2 Ausführungspfade -> 2 Tests
		 */
		if ($a == 1) {
			return 'foo';
		} else {
			return 'bar';
		}
	}

	public function misleadingCoverage($a)
	{
		/*
		 * Selbe Logik wie oben, allerdings kann hier mit nur einem Test eine code coverage von 100 % erreicht werden.
		 */
		return ($a == 1 ? 'foo' : 'bar');
	}

	public function output()
	{
		echo 'OK';
	}
}