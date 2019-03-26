<?php
namespace App\FlmExamples;

class IpLoggerService extends DoctrineManagerAbstract
{
	/** @var RequestStack */
	private $requestStack;

	public function __construct(RequestStack $requestStack)
	{
		$this->requestStack = $requestStack;
	}

	/**
	 * @param      $type
	 * @param null $user
	 */
	public function log($type, $user = null)
	{
		$request = $this->getRequest();

		$clientIp = null === $request ? 'unknown' : $request->getClientIp();

		$log          = new IpLog();
		$log->created = new \DateTime();
		$log->ip      = $clientIp;
		$log->type    = $type;
		$log->user    = $user;

		$this->save($log);
	}

	/**
	 * @param      $type
	 * @param User $user
	 *
	 * @return array
	 */
	public function findLogByType($type, User $user)
	{
		$search = [
			'type' => $type,
		];

		if (null !== $user) {
			$search['user'] = $user;
		}

		return $this->getRepository()->findBy($search);
	}

	/**
	 * @param      $type
	 * @param User $user
	 *
	 * @return null|IpLog
	 */
	public function findOneLogByType($type, User $user)
	{
		$search = [
			'type' => $type,
		];

		if (null !== $user) {
			$search['user'] = $user;
		}

		return $this->getRepository()->findOneBy($search);
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	public function getRepository()
	{
		return $this->getManager()->getRepository('Entity\IpLog');
	}

	/**
	 * @return null|\Symfony\Component\HttpFoundation\Request
	 */
	private function getRequest()
	{
		return $this->requestStack->getCurrentRequest();
	}

}