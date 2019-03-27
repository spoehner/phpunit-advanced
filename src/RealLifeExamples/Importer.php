<?php
namespace App\RealLifeExamples;

class Importer
{
	public function __construct(EntityManagerInterface $em, ImportToCompanyConverter $conv, SlugGenerator $sg, GeoCoder $gc)
	{
	}

	public function setDebugger(Debugger $debugger): void
	{
	}

	public function import(ReaderInterface $reader, MapperInterface $mapper): \stdClass
	{
	}
}