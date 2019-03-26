<?php
namespace App\FlmExamples;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\MessageInterface;

class ClientTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var MockObject
	 */
	private $guzzle, $logger;

	private $subject;

	protected function setUp(): void
	{
		parent::setUp();

		$this->guzzle = $this->createMock(\GuzzleHttp\Client::class);
		$this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);

		$this->subject = new Client($this->logger, $this->guzzle, 'abc123');
	}

	public function testIsFakeEmailFalseDueToException()
	{
		$exception = new \Exception('the_message');
		$this->guzzle->expects($this->once())->method('request')->willThrowException($exception);
		$this->logger->expects($this->once())->method('critical')->with($exception);

		$result = $this->subject->isFakeEmail('isFake@example.com');

		$this->assertFalse($result);
	}

	/**
	 * @dataProvider responseProvider
	 */
	public function testIsFakeEmail(array $responseData, bool $expectedResult)
	{
		$response = $this->createMock(MessageInterface::class);
		$response->expects($this->once())->method('getBody')->willReturn(json_encode($responseData));

		$this->guzzle->expects($this->once())->method('request')
			->with('get','https://www.mogelmail.de/api/v1/abc123/email/isFake@example.com', ['timeout' => 3])
			->willReturn($response);

		$result = $this->subject->isFakeEmail('isFake@example.com');

		$this->assertSame($expectedResult, $result);
	}

	public function responseProvider()
	{
		yield [['error' => true], false];
		yield [['error' => true, 'suspected' => true], false];
		yield [['error' => false, 'suspected' => true], true];
		yield [['error' => false, 'suspected' => false], false];
		yield [['suspected' => true], true];
		yield [[], false];
	}


	public function testIsFakeEmailErrorLogging()
	{
		$response = $this->createMock(MessageInterface::class);
		$response->expects($this->once())->method('getBody')->willReturn(json_encode(['error' => 'true', 'message' => 'the_message']));

		$this->guzzle->expects($this->once())->method('request')->willReturn($response);
		$this->logger->expects($this->once())->method('error')->with('Error: the_message');

		$result = $this->subject->isFakeEmail('isFake@example.com');

		$this->assertFalse($result);
	}
}