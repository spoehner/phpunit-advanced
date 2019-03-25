<?php
namespace App\Mocking;

class GoodExampleTest extends \PHPUnit\Framework\TestCase
{
	public function testProcessForm()
	{
		$handlerMock = $this->createMock(DependencyOne::class);
		$handlerMock->expects($this->once())->method('getById')->with(27)->will($this->returnValue((object)['id' => 27, 'name' => 'old name']));
		$handlerMock->expects($this->once())->method('save')->with((object)['id' => 27, 'name' => 'new name']);
		$class = new GoodExample($handlerMock);
		$class->processForm(27, ['name' => 'new name']);
	}

	public function testUsingFinalMethodFail()
	{
		$depMock = $this->createMock(DependencyTwo::class);
		$depMock->expects($this->once())->method('setUser')->with((object)['id' => 15]);
		$class = new GoodExample(new DependencyOne());
		$class->setDependency($depMock);
		$class->usingFinalMethod();
	}

	public function testUsingFinalMethod()
	{
		$depMock = $this->createMock(DependencyInterface::class);
		$depMock->expects($this->once())->method('setUser')->with((object)['id' => 12]);
		$class = new GoodExample(new DependencyOne());
		$class->setDependency($depMock);
		$class->usingFinalMethod();
	}
}
/*
Optionen für expects()
	$this->once();
	$this->any();
	$this->atLeastOnce();
	$this->exactly(2);
	$this->at(1);

Optionen für with() (constraints)
	$this->equalTo('') // wird impliziert wenn keine Methode verwendet wird
	$this->isInstanceOf('Chapter3\DependencyOne')
	$this->isNull()

Optionen für will()
	$this->returnValue('something');
	$this->onConsecutiveCalls('beim ersten aufruf', 'beim zweiten');
	$this->returnCallback(function($param1, $param2) { return $param1 + $param2; });
	$this->returnValueMap([['paramValue', 'returnValue']]);
	$this->throwException(new \Exception());
	$this->returnSelf();
*/