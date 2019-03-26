<?php
namespace App\FlmExamples;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IpLoggerServiceTest extends TestCase
{
	/**
	 * @var MockObject
	 */
	private $request, $requestStack;

	protected function setUp(): void
	{
		parent::setUp();

		$this->request      = $this->createMock(Request::class);
		$this->requestStack = $this->createMock(RequestStack::class);

		$this->requestStack->expects($this->once())->method('getCurrentRequest')->willReturn($this->request);
	}

	public function testLog()
	{
		$user = new FakeUser(11);

		$this->request->expects($this->once())->method('getClientIp')->willReturn('::1');

		$subject = new class($this->requestStack) extends IpLoggerService
		{
			protected function save($object)
			{
				TestCase::assertInstanceOf(IpLog::class, $object);
				TestCase::assertInstanceOf(\DateTime::class, $object->created);

				TestCase::assertLessThanOrEqual(2, (int)(new \DateTime('now'))->diff($object->created)->format('YmdHis'));
				TestCase::assertEquals('::1', $object->ip);
				TestCase::assertEquals('t', $object->type);
				TestCase::assertEquals(new FakeUser(11), $object->user);

				return true;
			}
		};

		$subject->log('t', $user);
	}
}

class FakeUser
{
	public $id;

	public function __construct(int $id)
	{
		$this->id = $id;
	}
}