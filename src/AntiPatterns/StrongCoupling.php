<?php
namespace App\AntiPatterns;

class StrongCoupling
{
	public function foo()
	{
		$aThing = new Thing();

		return $aThing->foo();
	}
}

// -----------------------
class DependencyInjection
{
	private $thing;

	private $service;

	public function __construct(Thing $thing)
	{
		$this->thing = $thing;
	}

	public function setService(Service $service)
	{
		$this->service = $service;
	}

	public function foo()
	{
		return $this->thing->foo();
	}
}