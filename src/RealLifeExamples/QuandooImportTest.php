<?php
namespace App\RealLifeExamples;

class QuandooImportTest extends TestCase
{
	public function testConstruct()
	{
		$json = file_get_contents(__DIR__.'/quandoo_valid.json');
		$data = json_decode($json, true);

		$subject = new ImportObject($data);

		$this->assertInstanceOf(ImportObject::class, $subject);
		$this->assertEquals("Rosso", $subject->name);
	}

	public function testAddressGetCompleteHouseNumber()
	{
		$json = file_get_contents(__DIR__.'/quandoo_valid.json');
		$data = json_decode($json, true);

		$subject = new ImportObject($data);

		$this->assertEquals("konrad-wolf-str 62d", $subject->location->address->street);
	}

	public function testCity()
	{
		$json = file_get_contents(__DIR__.'/quandoo_valid.json');
		$data = json_decode($json, true);

		$subject = new ImportObject($data);

		$this->assertEquals("Berlin", $subject->location->address->city);
	}
}