<?php
namespace App\RealLifeExamples;

use PHPUnit\Framework\MockObject\MockObject;

class ImporterTest extends TestCase
{
	/**
	 * @var MockObject
	 */
	private $converter, $reader, $mapper, $slugGenerator, $geoCoder, $repoImport;

	/**
	 * @var Importer
	 */
	private $subject;

	public function setUp(): void
	{
		parent::setUp();
		$this->mockEntityManager();

		$this->repoImport = $this->createMock(EntityRepository::class);

		$this->em->expects($this->any())->method('getRepository')->with(CompanyImport::class)->willReturn($this->repoImport);

		$this->converter     = $this->createMock(ImportToCompanyConverter::class);
		$this->reader        = $this->createMock(ReaderInterface::class);
		$this->mapper        = $this->createMock(MapperInterface::class);
		$this->slugGenerator = $this->createMock(SlugGenerator::class);
		$this->geoCoder      = $this->createMock(GeoCoder::class);

		$this->subject = new Importer($this->em, $this->converter, $this->slugGenerator, $this->geoCoder);
	}

	public function testImportNothingToDo()
	{
		$this->reader->expects($this->once())->method('readEntry')->willReturnCallback(function () {
			if (false) {
				// force this function to be a generator, but do not yield anything
				yield [];
			}
		});

		$result = $this->subject->import($this->reader, $this->mapper);
		$this->assertInstanceOf(\stdClass::class, $result);
		$this->assertEquals(0, $result->count);
	}

	public function testImportNeedsNoProcessing()
	{
		$e1 = $this->createMock(AbstractImportModel::class);

		$dc1 = $this->createMock(DataContainer::class);
		$dc1->expects($this->once())->method('needsProcessing')->willReturn(false);

		$this->reader->expects($this->once())->method('readEntry')->willReturnCallback(function () use ($e1) {
			yield $e1;
		});

		$this->mapper->expects($this->once())->method('map')->with($e1)->willReturn($dc1);

		$this->em->expects($this->never())->method('persist');
		$this->em->expects($this->once())->method('flush');

		$result = $this->subject->import($this->reader, $this->mapper);

		$this->assertInstanceOf(\stdClass::class, $result);
		$this->assertEquals(1, $result->count);
	}

	public function testImportUpdate()
	{
		$e1 = $this->createMock(AbstractImportModel::class);

		$i1               = new CompanyImport();
		$i1->source       = 'test_source';
		$i1->sourceId     = 123;
		$i1->cityName     = 'Nürnberg';
		$i1->zip          = '90478';
		$i1->street       = 'Pretzfelder Str.';
		$i1->streetNumber = '7-11';

		$city = new City();
		$city->setName('Nürnberg');

		$c1       = new Company();
		$c1->id   = 22;
		$c1->slug = 'the-name';
		$c1->setCity($city);
		$c1->zip          = '90478';
		$c1->street       = 'Pretzfelder Str.';
		$c1->streetNumber = '7-11';

		$prev1 = clone $i1;
		$prev1->setCompany($c1);

		$prev2 = clone $i1;

		$dc1 = $this->createMock(DataContainer::class);
		$dc1->expects($this->exactly(2))->method('needsProcessing')->willReturn(true);
		$dc1->expects($this->atLeastOnce())->method('getCompanyImport')->willReturn($i1);
		$dc1->expects($this->once())->method('markAsUpdate')->with($c1);
		$dc1->expects($this->any())->method('isNew')->willReturn(false);
		$dc1->expects($this->any())->method('isUpdate')->willReturn(true);
		$dc1->expects($this->any())->method('getCompany')->willReturn($c1);

		$this->converter->expects($this->never())->method('create');
		$this->converter->expects($this->once())->method('update')->with($i1, $c1);

		$this->reader->expects($this->once())->method('readEntry')->willReturnCallback(function () use ($e1) {
			yield $e1;
		});

		$this->mapper->expects($this->once())->method('map')->with($e1)->willReturn($dc1);
		$this->mapper->expects($this->once())->method('skipBackoffice')->willReturn(true);

		$this->slugGenerator->expects($this->never())->method('generate');
		$this->geoCoder->expects($this->never())->method('getCoordinatesForAddress');

		$this->repoImport->expects($this->once())->method('findBy')
			->with(['source' => 'test_source', 'sourceId' => 123], ['created' => 'DESC'])
			->willReturn([$prev1, $prev2]);

		$this->em->expects($this->once())->method('persist')->with($i1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->import($this->reader, $this->mapper);

		$this->assertEquals('the-name', $i1->slug);
	}

	public function testImportUpdateChangedAddress()
	{
		$e1 = $this->createMock(AbstractImportModel::class);

		$i1               = new CompanyImport();
		$i1->source       = 'test_source';
		$i1->sourceId     = 123;
		$i1->cityName     = 'Nürnberg';
		$i1->zip          = '90478';
		$i1->street       = 'Berliner Ring';
		$i1->streetNumber = '8';

		$city = new City();
		$city->setName('Nürnberg');

		$c1       = new Company();
		$c1->id   = 22;
		$c1->slug = 'the-name';
		$c1->setCity($city);
		$c1->zip          = '90478';
		$c1->street       = 'Pretzfelder Str.';
		$c1->streetNumber = '7-11';

		$prev1 = clone $i1;
		$prev1->setCompany($c1);

		$dc1 = $this->createMock(DataContainer::class);
		$dc1->expects($this->any())->method('needsProcessing')->willReturn(true);
		$dc1->expects($this->atLeastOnce())->method('getCompanyImport')->willReturn($i1);
		$dc1->expects($this->once())->method('markAsUpdate')->with($c1);
		$dc1->expects($this->any())->method('isNew')->willReturn(false);
		$dc1->expects($this->any())->method('isUpdate')->willReturn(true);
		$dc1->expects($this->any())->method('getCompany')->willReturn($c1);

		$this->reader->expects($this->once())->method('readEntry')->willReturnCallback(function () use ($e1) {
			yield $e1;
		});

		$this->mapper->expects($this->once())->method('map')->with($e1)->willReturn($dc1);
		$this->mapper->expects($this->once())->method('skipBackoffice')->willReturn(false);

		$gcar           = new GeoCoderAddressResult();
		$gcar->found    = true;
		$gcar->lat      = 51.1;
		$gcar->lng      = 11.2;
		$gcar->district = 'Thon';
		$this->geoCoder->expects($this->once())->method('getCoordinatesForAddress')
			->with('Berliner Ring 8, 90478 Nürnberg')
			->willReturn($gcar);

		$this->repoImport->expects($this->once())->method('findBy')->with(['source' => 'test_source', 'sourceId' => 123])->willReturn([$prev1]);

		$this->em->expects($this->once())->method('persist')->with($i1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->import($this->reader, $this->mapper);

		$this->assertEquals('the-name', $i1->slug);
		$this->assertEquals(51.1, $i1->lat);
		$this->assertEquals(11.2, $i1->lng);
		$this->assertEquals('Thon', $i1->district);
	}

	public function testImportCreate()
	{
		$e1 = $this->createMock(AbstractImportModel::class);

		$i1               = new CompanyImport();
		$i1->source       = 'test_source';
		$i1->sourceId     = 123;
		$i1->name         = 'The Name';
		$i1->zip          = '90478';
		$i1->cityName     = 'Nürnberg';
		$i1->street       = 'Pretzfelder Str.';
		$i1->streetNumber = '7-11';

		$dc1 = $this->createMock(DataContainer::class);
		$dc1->expects($this->exactly(2))->method('needsProcessing')->willReturn(true);
		$dc1->expects($this->atLeastOnce())->method('getCompanyImport')->willReturn($i1);
		$dc1->expects($this->never())->method('markAsUpdate');
		$dc1->expects($this->any())->method('isNew')->willReturn(true);
		$dc1->expects($this->any())->method('isUpdate')->willReturn(false);
		$dc1->expects($this->never())->method('getCompany');

		$this->converter->expects($this->once())->method('create')->with($i1);
		$this->converter->expects($this->never())->method('update');

		$this->reader->expects($this->once())->method('readEntry')->willReturnCallback(function () use ($e1) {
			yield $e1;
		});

		$this->mapper->expects($this->once())->method('map')->with($e1)->willReturn($dc1);
		$this->mapper->expects($this->once())->method('skipBackoffice')->willReturn(true);

		$this->slugGenerator->expects($this->once())->method('generate')->with('The Name')->willReturn('the-name');

		$gcar           = new GeoCoderAddressResult();
		$gcar->found    = true;
		$gcar->lat      = 51.1;
		$gcar->lng      = 11.2;
		$gcar->district = 'Thon';
		$this->geoCoder->expects($this->once())->method('getCoordinatesForAddress')
			->with('Pretzfelder Str. 7-11, 90478 Nürnberg')
			->willReturn($gcar);

		$this->repoImport->expects($this->once())->method('findBy')
			->with(['source' => 'test_source', 'sourceId' => 123], ['created' => 'DESC'])
			->willReturn([]);

		$this->em->expects($this->once())->method('persist')->with($i1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->import($this->reader, $this->mapper);

		$this->assertEquals('the-name', $i1->slug);
		$this->assertEquals(51.1, $i1->lat);
		$this->assertEquals(11.2, $i1->lng);
		$this->assertEquals('Thon', $i1->district);
	}

	public function testImportDoNotSkipBackoffice()
	{
		$e1 = $this->createMock(AbstractImportModel::class);

		$i1           = new CompanyImport();
		$i1->source   = 'test_source';
		$i1->sourceId = 123;
		$i1->name     = 'The Name';

		$dc1 = $this->createMock(DataContainer::class);
		$dc1->expects($this->any())->method('needsProcessing')->willReturn(true);
		$dc1->expects($this->atLeastOnce())->method('getCompanyImport')->willReturn($i1);
		$dc1->expects($this->any())->method('isNew')->willReturn(true);

		$this->converter->expects($this->never())->method('create');
		$this->converter->expects($this->never())->method('update');

		$this->reader->expects($this->once())->method('readEntry')->willReturnCallback(function () use ($e1) {
			yield $e1;
		});

		$this->mapper->expects($this->once())->method('map')->with($e1)->willReturn($dc1);
		$this->mapper->expects($this->once())->method('skipBackoffice')->willReturn(false);

		$this->em->expects($this->once())->method('persist')->with($i1);
		$this->em->expects($this->once())->method('flush');

		$this->subject->import($this->reader, $this->mapper);
	}
}