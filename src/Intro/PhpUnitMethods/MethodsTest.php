<?php

class MethodsTest
{
	/**
	 * Wird VOR DEM ERSTEN Test ausgeführt bevor die Klasse instanziert wird.
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}

	/**
	 * Wird VOR JEDEM Test ausgeführt.
	 */
	protected function setUp()
	{
		parent::setUp();
	}

	/**
	 * Wird NACH JEDEM Test ausgeführt.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Wird NACH DEM LETZTEN Test ausgeführt.
	 */
	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
	}

	/**
	 * @param Exception $e
	 *
	 * @throws Exception
	 */
	protected function onNotSuccessfulTest(Exception $e)
	{
		parent::onNotSuccessfulTest($e);
	}
}