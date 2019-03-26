<?php
namespace App\FlmExamples;

class RequestStack
{
	public function getCurrentRequest(): Request
	{
		return new Request();
	}
}