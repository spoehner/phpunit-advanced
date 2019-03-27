<?php
namespace App\Mocking;

class Service
{
	public function get(): int
	{
		return 1;
	}
}

class Example
{
	private $service;

	public function __construct(Service $service)
	{
		$this->service = $service;
	}
}

class ExampleTest
{
	public function testSomething()
	{
		$mock = new class() extends Service
		{
			public function get(): int
			{
				return 0;
			}

		};

		$subject = new Example($mock);
	}
}