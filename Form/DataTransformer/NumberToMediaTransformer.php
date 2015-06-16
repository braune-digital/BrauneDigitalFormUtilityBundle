<?php

namespace BrauneDigital\FormUtilityBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class NumberToMediaTransformer implements DataTransformerInterface
{
	/**
	 * @var ObjectManager
	 */
	private $om;

	/**
	 * @param ObjectManager $om
	 */
	public function __construct(ObjectManager $om)
	{
		$this->om = $om;
	}

	/**
	 * @param mixed $media
	 * @return string
	 */
	public function transform($media)
	{
		if (null === $media) {
			return "";
		}

		return $media->getId();
	}

	/**
	 * @param mixed $id
	 * @return null|object
	 */
	public function reverseTransform($id)
	{
		if (!$id) {
			return null;
		}

		$media = $this->om
			->getRepository('ApplicationSonataMediaBundle:Media')
			->find($id)
		;

		if (null === $media) {
			throw new TransformationFailedException(sprintf(
				'An issue with number "%s" does not exist!',
				$id
			));
		}

		return $media;
	}
}