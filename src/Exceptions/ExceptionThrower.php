<?php
namespace App\Exceptions;

class ExceptionThrower
{
	public function usingProjectException(int $a): void
	{
		if ($a < 0) {
			throw new ProjectException('$a must not be negative.');
		}
	}

	public function usingPhpException(int $a): void
	{
		if ($a < 0) {
			throw new \InvalidArgumentException('$a must not be negative.');
		}
	}

	public function usingGlobalException(int $a): void
	{
		if ($a < 0) {
			throw new \Exception('$a must not be negative.');
		}
	}
}