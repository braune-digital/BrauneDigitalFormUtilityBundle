<?php

namespace BrauneDigital\FormUtilityBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class NumberToGalleryTransformer implements DataTransformerInterface
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
	 * @param mixed $gallery
	 * @return string
	 */
	public function transform($gallery)
	{
		if (null === $gallery) {
			return "";
		}

		return $gallery->getId();
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

		$gallery = $this->om
			->getRepository('ApplicationSonataMediaBundle:Gallery')
			->find($id)
		;

		if (null === $gallery) {
			throw new TransformationFailedException(sprintf(
				'An issue with number "%s" does not exist!',
				$id
			));
		}

		return $gallery;
	}
}