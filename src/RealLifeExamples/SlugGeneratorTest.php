<?php
namespace App\RealLifeExamples;

class SlugGeneratorTest extends TestCase
{
	/**
	 * @dataProvider textProvider
	 */
	public function testGenerate(string $name, string $expected)
	{
		$subject = new SlugGenerator();
		$result  = $subject->generate($name);

		$this->assertEquals($expected, $result);
	}

	public function textProvider()
	{
		yield ['Nürnberg', 'nürnberg'];
		yield ['Ülzen', 'ülzen'];
		yield ['wierd-accents-âéì', 'wierd-accents-aei'];
		yield ['save-german-äüöß', 'save-german-äüöß'];
		yield ['El Gaucho', 'el-gaucho'];
		yield ['some <!"§$%&/()=?> thing', 'some-thing'];
		yield ['Hårkällar\'n', 'harkällar-n'];
	}
}