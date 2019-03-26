<?php
namespace App\FlmExamples;

use Psr\Log\LoggerInterface;

class Client
{
	const ENDPIONT = 'https://www.mogelmail.de/api/v1';

	/**
	 * @var \GuzzleHttp\Client
	 */
	private $httpClient;

	/**
	 * @var string
	 */
	private $apiKey;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	// WILL NOT WORK
//    public function __construct(LoggerInterface $logger, $apiKey)
//    {
//        $this->httpClient = new \GuzzleHttp\Client([
//            'timeout' => 3
//        ]);
//
//        $this->logger = $logger;
//        $this->apiKey = $apiKey;
//    }

	public function __construct(LoggerInterface $logger, \GuzzleHttp\Client $httpClient, $apiKey)
	{
		$this->httpClient = $httpClient;

		$this->logger = $logger;
		$this->apiKey = $apiKey;
	}

	public function isFakeEmail($email)
	{
		$endpoint = 'email/'.$email;

		try {
			$response = $this->get($endpoint);

			$reponseData = \GuzzleHttp\json_decode((string)$response->getBody(), 1);

			if (isset($reponseData['error']) && $reponseData['error'] == true) {
				if ($reponseData['message']) {
					$this->logger->error('Error: '.$reponseData['message']);
				}

				return false;
			}

			if (isset($reponseData['suspected']) && $reponseData['suspected'] == true) {
				return true;
			}

			return false;
		} catch (\Exception $e) {
			$this->logger->critical($e);

			return false;
		}
	}

	private function get($endpoint)
	{
		//return $this->httpClient->get(self::ENDPIONT.'/'.$this->apiKey.'/'.$endpoint);
		return $this->httpClient->request('get', self::ENDPIONT.'/'.$this->apiKey.'/'.$endpoint, ['timeout' => 3]);
	}
}