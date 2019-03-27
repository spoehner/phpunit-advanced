<?php
namespace App\RealLifeExamples;

abstract class CRUDEndpoint extends ReadEndpoint implements NeedsSession
{
	/**
	 * @var Session
	 */
	protected $session;

	public function setSession(Session $session): void
	{
		$this->session = $session;
	}

	/**
	 * Called after entity is created, but before saving.
	 *
	 * @param CreateInput $input
	 * @param mixed       $record
	 *
	 * @throws AdminApiMalformedDataException
	 */
	protected function createPrePersist(CreateInput $input, $record): void
	{
	}

	/**
	 * Called after create action is done.
	 *
	 * @param mixed $record Newly created record.
	 */
	protected function afterCreate($record): void
	{
	}

	final protected function create(CreateInput $input): CreateOutput
	{
		$class  = $this->getEntityClass();
		$record = new $class();
		$this->fillRecord((array)$input->data, $record);

		$this->createPrePersist($input, $record);

		$this->em->persist($record);
		$this->em->flush();

		$this->afterCreate($record);

		return new CreateOutput($this->record2Array($record));
	}

	/**
	 * Called before update is doing something.
	 *
	 * @param UpdateInput $input
	 * @param mixed       $record
	 * @param mixed       $unchangedRecord Clone of the record with original data.
	 *
	 * @throws AdminApiException
	 */
	protected function updatePrePersist(UpdateInput $input, $record, $unchangedRecord): void
	{
	}

	final protected function update(UpdateInput $input): UpdateOutput
	{
		$record = $this->em->getRepository($this->getEntityClass())->find($input->id);
		if (!$record) {
			throw new AdminApiException('Record not found.');
		}
		$unchangedRecord = clone $record;

		$this->preventRaceCondition((array)$input->previousData, $record);
		$this->fillRecord((array)$input->data, $record);

		$this->updatePrePersist($input, $record, $unchangedRecord);

		$this->em->persist($record);
		$this->em->flush();

		return new UpdateOutput($this->record2Array($record));
	}

	/**
	 * @param DeleteInput $input
	 * @param             $record
	 *
	 * @return bool Return true to actually delete the record.
	 * @throws AdminApiException
	 */
	protected function onDelete(DeleteInput $input, $record): bool
	{
		// delete is not available until you overwrite this
		throw new AdminApiException(__METHOD__.' is not implemented.');
	}

	final protected function delete(DeleteInput $input): DeleteOutput
	{
		$record = $this->em->getRepository($this->getEntityClass())->find($input->id);
		if (!$record) {
			throw new AdminApiException('Record not found.');
		}

		if ($this->onDelete($input, $record)) {
			$this->em->remove($record);
		}

		$this->em->flush();

		return new DeleteOutput(['id' => $input->id]);
	}

	/**
	 * Fill properties from a key-value array. Setters will be respected.
	 *
	 * @param iterable $data
	 * @param mixed    $record E.g. an entity.
	 */
	protected function fillRecord(iterable $data, $record): void
	{
		// remove timestamps
		unset($data['created'], $data['updated']);
		$skip = $this->skipFields();

		foreach ($data as $property => $value) {
			if (in_array($property, $skip)) {
				continue;
			}

			$setter = 'set'.ucfirst($property);
			if (method_exists($record, $setter)) {
				$record->$setter($value);
			} elseif (property_exists($record, $property)) {
				$record->$property = $value;
			}
		}
	}

	/**
	 * These fields will not be filled to the record.
	 *
	 * @return array
	 */
	protected function skipFields(): array
	{
		return [];
	}

	/**
	 * @param iterable $previousData
	 * @param object   $record
	 *
	 * @throws AdminApiRaceConditionException
	 */
	protected function preventRaceCondition(iterable $previousData, $record): void
	{
		if (property_exists($record, 'updated') && $record->updated instanceof \DateTime) {
			// this is the safest way to detect race conditions
			if (isset($previousData['updated']->date)) {
				$previousUpdated = new \DateTime($previousData['updated']->date);
			} else {
				$previousUpdated = new \DateTime($previousData['updated']);
			}

			// due to timezone shenanigans, the hour could be off
			if ($record->updated->format('is') !== $previousUpdated->format('is')) {
				throw new AdminApiRaceConditionException("Detected race condition: updated mismatch.");
			}

			return;
		}

		foreach ($record as $property => $value) {
			if (!is_string($value)) {
				continue;
			}
			if (array_key_exists($property, $previousData) && $value !== $previousData[$property]) {
				throw new AdminApiRaceConditionException("Detected race condition.");
			}
		}
	}
}