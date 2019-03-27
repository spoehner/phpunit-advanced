<?php
namespace App\AntiPatterns;

final class FinalClass
{

}

class FinalClassTest
{
	public function testSomething()
	{
		$subject = new FinalClass();
	}
}

class ClassUsingFinalClassTest
{
	public function testSomething()
	{
		$mock = new class() extends FinalClass
		{

		};

		$subject = new ClassUsingFinalClass($mock);
	}
}

// --------------------------------------------------------------
class ClassWithFinal
{
	final public function pubf()
	{

	}

	final protected function probf()
	{

	}

	final private function prif()
	{

	}
}

class ClassWithFinalTest
{
	public function testPubf()
	{
		$subject = new ClassWithFinal();
		$subject->pubf();
	}
}

// --------------------------------------------------------------
class ClassWithStatic
{
	public static function foo()
	{

	}
}

class ClassWithStaticTest
{
	public function testFoo()
	{
		ClassWithStatic::foo();
	}
}

class ClassUsingClassWithStatic
{
	public function foo()
	{
		return ClassWithStatic::foo();
	}
}