<?php
namespace App\RealLifeExamples;

class CRUDEndpointTest extends TestCase
{
	/**
	 * @var TestCRUDEndpoint
	 */
	private $subject;

	private $session;

	private $repoCompany;

	public function setUp(): void
	{
		parent::setUp();
		$this->mockEntityManager();

		$this->session     = new Session();
		$this->repoCompany = $this->createMock(CompanyRepository::class);

		$this->em->expects($this->any())->method('getRepository')->with(Company::class)->willReturn($this->repoCompany);

		$this->subject = $this->createPartialMock(TestCRUDEndpoint::class, [
			'createPrePersist',
			'afterCreate',
			'record2Array',
			'preventRaceCondition',
			'updatePrePersist',
			'onDelete',
		]);
		$this->subject->setGlobalServices($this->em);
		$this->subject->setSession($this->session);
	}

	public function testCreateExceptionInCreatePrePersist()
	{
		$this->expectException(AdminApiException::class);
		$this->expectExceptionMessage('testing_foo');

		$input = new CreateInput();

		$this->em->expects($this->never())->method('persist');
		$this->em->expects($this->never())->method('flush');

		$this->subject->expects($this->once())->method('createPrePersist')
			->willThrowException(new AdminApiMalformedDataException('testing_foo'));

		$this->subject->expects($this->never())->method('afterCreate');

		$this->subject->run($input);
	}

	public function testCreateExceptionInAfterCreate()
	{
		$this->expectException(AdminApiMalformedDataException::class);
		$this->expectExceptionMessage('testing_foo');

		$input = new CreateInput();

		$c1 = new Company();

		$this->em->expects($this->once())->method('persist')->with($c1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->expects($this->once())->method('createPrePersist')->with($input, $c1);
		$this->subject->expects($this->once())->method('afterCreate')
			->willThrowException(new AdminApiMalformedDataException('testing_foo'));

		$this->subject->run($input);
	}

	public function testCreate()
	{
		$input = new CreateInput();

		$c1 = new Company();

		$this->em->expects($this->once())->method('persist')->with($c1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->expects($this->once())->method('createPrePersist')->with($input, $c1);
		$this->subject->expects($this->once())->method('afterCreate')->with($c1);
		$this->subject->expects($this->once())->method('record2Array')->with($c1);

		$this->subject->run($input);
	}

	public function testUpdateNotFound()
	{
		$this->expectException(AdminApiException::class);
		$this->expectExceptionMessage('Record not found');

		$input     = new UpdateInput();
		$input->id = 11;

		$this->repoCompany->expects($this->once())->method('find')->willReturn(null);

		$this->em->expects($this->never())->method('persist');
		$this->em->expects($this->never())->method('flush');

		$this->subject->expects($this->never())->method('preventRaceCondition');
		$this->subject->expects($this->never())->method('updatePrePersist');
		$this->subject->expects($this->never())->method('record2Array');

		$this->subject->run($input);
	}

	public function testUpdateRaceCondition()
	{
		$this->expectException(AdminApiRaceConditionException::class);

		$input = new UpdateInput();

		$c1 = new Company();

		$this->repoCompany->expects($this->once())->method('find')->willReturn($c1);

		$this->em->expects($this->never())->method('persist');
		$this->em->expects($this->never())->method('flush');

		$this->subject->expects($this->once())->method('preventRaceCondition')->willThrowException(new AdminApiRaceConditionException());
		$this->subject->expects($this->never())->method('updatePrePersist');
		$this->subject->expects($this->never())->method('record2Array');

		$this->subject->run($input);
	}

	public function testUpdateExceptionInUpdatePrePersist()
	{
		$this->expectException(AdminApiException::class);

		$input = new UpdateInput();

		$c1 = new Company();

		$this->repoCompany->expects($this->once())->method('find')->willReturn($c1);

		$this->em->expects($this->never())->method('persist');
		$this->em->expects($this->never())->method('flush');

		$this->subject->expects($this->once())->method('preventRaceCondition');
		$this->subject->expects($this->once())->method('updatePrePersist')->willThrowException(new AdminApiException());
		$this->subject->expects($this->never())->method('record2Array');

		$this->subject->run($input);
	}

	public function testUpdate()
	{
		$input               = new UpdateInput();
		$input->id           = 11;
		$input->previousData = (object)['foo' => 'bar'];
		$input->data         = (object)['name' => 'new name'];

		$c1       = new Company();
		$c1->id   = 11;
		$c1->name = 'old name';

		$this->repoCompany->expects($this->once())->method('find')->with(11)->willReturn($c1);

		$this->em->expects($this->once())->method('persist')->with($c1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->expects($this->once())->method('preventRaceCondition')->with((array)$input->previousData, $c1);
		$this->subject->expects($this->once())->method('updatePrePersist')
			->willReturnCallback(function (UpdateInput $input, Company $changed, Company $unchanged) use ($c1) {
				$this->assertSame($c1, $changed);
				$this->assertEquals('new name', $changed->name);

				$this->assertEquals(11, $unchanged->id);
				$this->assertEquals('old name', $unchanged->name);
			});
		$this->subject->expects($this->once())->method('record2Array')->with($c1);

		$this->subject->run($input);
	}

	public function testDeleteExceptionInOnDelete()
	{
		$this->expectException(AdminApiException::class);

		$input     = new DeleteInput();
		$input->id = 11;

		$c1 = new Company();

		$this->repoCompany->expects($this->once())->method('find')->with(11)->willReturn($c1);

		$this->em->expects($this->never())->method('remove');
		$this->em->expects($this->never())->method('flush');

		$this->subject->expects($this->once())->method('onDelete')->willThrowException(new AdminApiException());

		$this->subject->run($input);
	}

	public function testDeleteNotFound()
	{
		$this->expectException(AdminApiException::class);
		$this->expectExceptionMessage('Record not found');

		$input     = new DeleteInput();
		$input->id = 11;

		$this->repoCompany->expects($this->once())->method('find')->willReturn(null);

		$this->em->expects($this->never())->method('remove');
		$this->em->expects($this->never())->method('flush');

		$this->subject->expects($this->never())->method('onDelete');

		$this->subject->run($input);
	}

	public function testDeleteWithActualRemove()
	{
		$input         = new DeleteInput();
		$input->id     = 11;
		$input->reason = 'foobar';

		$c1     = new Company();
		$c1->id = 11;

		$this->repoCompany->expects($this->once())->method('find')->with(11)->willReturn($c1);

		$this->em->expects($this->once())->method('remove')->with($c1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->expects($this->once())->method('onDelete')->with($input, $c1)->willReturn(true);

		$output = $this->subject->run($input);
		$this->assertEquals(11, $output->data['id']);
	}

	public function testDeleteNoRemove()
	{
		$input         = new DeleteInput();
		$input->id     = 11;
		$input->reason = 'foobar';

		$c1     = new Company();
		$c1->id = 11;

		$this->repoCompany->expects($this->once())->method('find')->with(11)->willReturn($c1);

		$this->em->expects($this->never())->method('remove');
		$this->em->expects($this->once())->method('flush');

		$this->subject->expects($this->once())->method('onDelete')->with($input, $c1)->willReturn(false);

		$output = $this->subject->run($input);
		$this->assertEquals(11, $output->data['id']);
	}

	public function testPreventRaceConditionUpdatePropertyJavaScriptDate()
	{
		$this->expectException(AdminApiRaceConditionException::class);
		$this->expectExceptionMessage('Detected race condition: updated mismatch');

		$prev = [
			'updated' => (object)['date' => '2018-11-28 10:00:14'],
		];

		$record          = new Company();
		$record->updated = new \DateTime('2018-11-28 10:00:15');

		$subject = new TestCRUDEndpointRaceCondition();
		$subject->raceCondition($prev, $record);
	}

	public function testPreventRaceConditionUpdateProperty()
	{
		$this->expectException(AdminApiRaceConditionException::class);
		$this->expectExceptionMessage('Detected race condition: updated mismatch');

		$prev = [
			'updated' => '2018-11-28 10:00:14',
		];

		$record          = new Company();
		$record->updated = new \DateTime('2018-11-28 10:00:15');

		$subject = new TestCRUDEndpointRaceCondition();
		$subject->raceCondition($prev, $record);
	}

	public function testPreventRaceConditionNoUpdateProperty()
	{
		$this->expectException(AdminApiRaceConditionException::class);
		$this->expectExceptionMessage('Detected race condition');

		$prev = [
			'name' => 'after name',
		];

		$record       = new Company();
		$record->name = 'prev name';

		$subject = new TestCRUDEndpointRaceCondition();
		$subject->raceCondition($prev, $record);
	}

	public function testPreventRaceConditionTimeShift()
	{
		$prev = [
			'updated' => '2018-11-28 11:00:15',
		];

		$record          = new Company();
		$record->updated = new \DateTime('2018-11-28 10:00:15');

		$subject = new TestCRUDEndpointRaceCondition();
		$subject->raceCondition($prev, $record);
		$this->addToAssertionCount(1);
	}
}

class TestCRUDEndpoint extends CRUDEndpoint
{
	protected function getEntityClass(): string
	{
		return Company::class;
	}

	protected function record2Array($record): array
	{
		return $this->extractPublicPropertiesFromRecord($record);
	}
}

class TestCRUDEndpointRaceCondition extends CRUDEndpoint
{
	protected function getEntityClass(): string
	{
		return Company::class;
	}

	protected function record2Array($record): array
	{
		return $this->extractPublicPropertiesFromRecord($record);
	}

	public function raceCondition($prev, $record)
	{
		$this->preventRaceCondition($prev, $record);
	}
}