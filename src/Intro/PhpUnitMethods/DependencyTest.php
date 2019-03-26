<?php

class DependencyTest
{
	public function testConstruct()
	{
		$class = new ExampleClass(['some' => 'thing']);
		// do some testing
		/*
		 * Ein Test, der einen Nachfolger hat, muss etwas zurück geben (normalerweise das Testsubjekt).
		 */

		return $class;
	}

	/**
	 * @depends testConstruct
	 *
	 * @param ExampleClass $class
	 */
	public function testProcessData(ExampleClass $class)
	{
		/*
		 * Ein abhängiger Test muss als Parameter den Rückgabewert des Vorgängers akzeptieren.
		 */
		$class->processData();
	}
}