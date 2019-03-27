<?php
namespace App\RealLifeExamples;

use PHPUnit\Framework\MockObject\MockObject;

class BannerBuddyTest extends TestCase
{
	/**
	 * @var MockObject
	 */
	private $repo;

	/**
	 * @var BannerBuddy
	 */
	private $subject;

	public function setUp(): void
	{
		parent::setUp();
		$this->mockEntityManager();

		$this->repo = $this->createMock(EntityRepository::class);

		$this->em->expects($this->atMost(1))->method('getRepository')->with(Advertisement::class)->willReturn($this->repo);

		$this->subject = new BannerBuddy($this->em);
	}

	public function testGetBannerCode()
	{
		$ad       = new Advertisement();
		$ad->code = 'bar';

		$this->repo->expects($this->once())->method('findOneBy')->with(['key' => 'foo'])->willReturn($ad);
		$result = $this->subject->getBannerCode('foo');

		$this->assertEquals('bar', $result);
	}

	public function testGetBannerCodeCreateIfNotExists()
	{
		$this->repo->expects($this->once())->method('findOneBy')->with(['key' => 'foo'])->willReturn(null);

		$ad      = new Advertisement();
		$ad->key = 'foo';

		$this->em->expects($this->once())->method('persist')->with($ad);
		$this->em->expects($this->once())->method('flush')->with($ad);

		$result = $this->subject->getBannerCode('foo');

		$this->assertEquals('', $result);
	}
}