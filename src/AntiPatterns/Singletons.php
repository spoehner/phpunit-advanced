<?php
namespace App\AntiPatterns;

use PHPUnit\Framework\TestCase;

class Singletons
{
	private static $instance;

	private function __construct()
	{
	}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

class SingletonsTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		/*
		 * Vor jedem Test löschen wir die Instanz
		 */
		$ref = new \ReflectionProperty(Singletons::class, 'instance');
		$ref->setAccessible(true);
		$ref->setValue(null, null);
	}
}

/*
	Als Alternative kann PHPUnit mit der Kommandozeilenoption --static-backup entsprechend ausgeführt werden.
*/
