<?php
namespace App\Exceptions;

class ExceptionThrowerTest extends \PHPUnit\Framework\TestCase
{
	public function testUsingProjectExceptionNoException()
	{
		$class = new ExceptionThrower();
		$class->usingProjectException(5);

		// Damit vermeiden wir den "risky test"
		$this->addToAssertionCount(1);
	}

	public function testUsingProjectException()
	{
		// Wir erwarten eine Exception
		$this->expectException(ProjectException::class);

		$class = new ExceptionThrower();
		$class->usingProjectException(-5);
	}

	public function testUsingProjectExceptionAlternative()
	{
		$class = new ExceptionThrower();
		try {
			$class->usingProjectException(-5);
			$this->fail('Expected Exception');
		} catch (ProjectException $e) {
			$this->assertEquals('$a must not be negative.', $e->getMessage());
		}
	}

	public function testUsingPhpException()
	{
		// Kein Unterschied bei speziellen Exceptions.
		$this->expectException(\InvalidArgumentException::class);

		$class = new ExceptionThrower();
		$class->usingPhpException(-5);
	}

	public function testUsingGlobalException()
	{
		$class = new ExceptionThrower();

		try {
			$class->usingGlobalException(-5);
			$this->fail('Expected Exception');
		} catch (\Exception $e) {
			/*
			 * PHPUnit selbst wirft \PHPUnit_Framework_Exception die natürlich von \Exception abgeleitet sind.
			 * Ein Workaround wäre explizit darauf noch zu prüfen. Generell sollte aber \Exception gar nicht erst geworfen werden.
			 */
			$this->assertNotInstanceOf(\PHPUnit\Framework\Exception::class, $e);
		}
	}
}