<?php
namespace App\RealLifeExamples;

use PHPUnit\Framework\MockObject\MockObject;

class CodaTest extends TestCase
{
	/**
	 * @var MockObject
	 */
	private $hours;

	/**
	 * @var Coda
	 */
	private $subject;

	public function setUp(): void
	{
		parent::setUp();

		$this->hours = $this->createMock(OpeningHours::class);

		$this->subject = new Coda();
		$this->subject->setServices($this->hours);
	}

	private function createModel(): \App\Component\Import\Model\Coda
	{
		return new \App\Component\Import\Model\Coda([
			'COMPANY_ID' => 'abc123',
			'NAME'       => 'n',
			'STREET'     => 's',
			'STREETNR'   => 'sn',
			'PLZ'        => '01234',
			'CITY'       => 'c',
			'FON'        => '+49 123 - 48',
			'MOBILE'     => '+49 176 123 456',
			'HOMEPAGE'   => 'www.example.com',
			'FACEBOOK'   => 'www.facebook.com/foobar',
			'OPENING'    => 'mo-fr 8-18',
		]);
	}

	public function testModel()
	{
		$model = $this->createModel();

		$this->assertEquals('abc123', $model->id);
		$this->assertEquals('n', $model->name);
		$this->assertEquals('s', $model->street);
		$this->assertEquals('sn', $model->streetNumber);
		$this->assertEquals('01234', $model->zip);
		$this->assertEquals('c', $model->city);
		$this->assertEquals('+49 123 - 48', $model->phone);
		$this->assertEquals('www.example.com', $model->website);
		$this->assertEquals('www.facebook.com/foobar', $model->facebook);
		$this->assertEquals('mo-fr 8-18', $model->hours);
	}

	public function testMapWrongModel()
	{
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage('mapper can only handle App\Component\Import\Model\Coda');

		$this->subject->map($this->createMock(AbstractImportModel::class));
	}

	public function testMap()
	{
		$hours = new \App\Component\Import\Model\Hours();

		$this->hours->expects($this->once())->method('parse')->with('mo-fr 8-18')->willReturn($hours);
		$dc = $this->subject->map($this->createModel());

		$this->assertInstanceOf(DataContainer::class, $dc);
		$this->assertInstanceOf(CompanyImport::class, $dc->getCompanyImport());

		$import = $dc->getCompanyImport();
		$this->assertEquals('coda', $import->source);
		$this->assertEquals('abc123', $import->sourceId);
		$this->assertEquals('n', $import->name);
		$this->assertEquals('s', $import->street);
		$this->assertEquals('sn', $import->streetNumber);
		$this->assertEquals('01234', $import->zip);
		$this->assertEquals('c', $import->cityName);
		$this->assertEquals('+49 123 - 48', $import->phone);
		$this->assertEquals('www.example.com', $import->homepage);
		$this->assertEquals('www.facebook.com/foobar', $import->facebook);
		$this->assertEquals($hours, $import->getHours());
	}

	public function testGetReaderOptions()
	{
		$options = $this->subject->getReaderOptions(['foo' => 'bar']);
		$this->assertEquals([
			'format'    => 'csv',
			'delimiter' => "\t",
			'foo'       => 'bar',
		], $options);
	}

	public function testGetModelClass()
	{
		$this->assertEquals(\App\Component\Import\Model\Coda::class, $this->subject->getModelClass());
	}

	public function testSkipBackoffice()
	{
		$this->assertFalse($this->subject->skipBackoffice(new DataContainer()));
	}
}