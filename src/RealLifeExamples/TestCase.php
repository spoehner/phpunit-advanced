<?php
namespace App\RealLifeExamples;

class TestCase extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Client
	 */
	protected $client;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var \Doctrine\ORM\EntityManagerInterface|MockObject
	 */
	protected $em;

	/**
	 * @var \Doctrine\Bundle\DoctrineBundle\Registry|MockObject
	 */
	protected $doctrine;

	/**
	 * @var \Symfony\Component\Routing\RouterInterface|MockObject
	 */
	protected $router;

	/**
	 * @var Request|MockObject
	 */
	protected $request;

	public function setUp(): void
	{
		$this->container = new Container();
	}

	protected function mockEntityManager(array $repositories = [])
	{
		$this->em = $this->createMock(EntityManagerInterface::class);

		$this->doctrine = $this->createMock(\Doctrine\Bundle\DoctrineBundle\Registry::class);
		$this->doctrine->expects($this->any())->method('getManager')->willReturn($this->em);

		$this->container->set('doctrine.orm.entity_manager', $this->em);
		$this->container->set(EntityManagerInterface::class, $this->em);
		$this->container->set('doctrine', $this->doctrine);

		if (!empty($repositories)) {
			$repoClasses = [];
			foreach ($repositories as $className => &$repo) {
				$mocks = [
					\App\Entity\Company\Company::class => \App\Entity\Repository\CompanyRepository::class,
					\App\Entity\Translation\Translation::class => \App\Repository\Translation\TranslationRepository::class,
				];
				if (isset($mocks[$className])) {
					$repo = $this->createMock($mocks[$className]);
				} else {
					$repo = $this->createMock(EntityRepository::class);
				}
				$repoClasses[$className] = $repo;
			}
			unset($repo);

			$this->em->expects($this->any())->method('getRepository')->willReturnCallback(function (string $className) use ($repoClasses) {
				if (isset($repoClasses[$className])) {
					return $repoClasses[$className];
				}

				$this->fail("Unspecified repository for $className.");
			});
		}
	}

	protected function mockRouter()
	{
		$this->router = $this->createMock(\Symfony\Component\Routing\RouterInterface::class);
		$this->container->set('router', $this->router);
	}

	protected function mockRequest()
	{
		$this->request = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
	}
}