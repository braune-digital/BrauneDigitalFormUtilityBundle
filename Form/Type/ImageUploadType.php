<?php

namespace BrauneDigital\FormUtilityBundle\Form\Type;

use BrauneDigital\FormUtilityBundle\Form\DataTransformer\NumberToGalleryTransformer;
use BrauneDigital\FormUtilityBundle\Form\DataTransformer\NumberToMediaTransformer;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageUploadType extends AbstractType
{

	protected $pool;

	protected $options;

	/**
	 * @var ObjectManager
	 */
	private $om;

	protected $container;


	/**
	 * @param Pool   $pool
	 * @param string $class
	 * @param array  $options
	 */
	public function __construct(Pool $pool, $class, $om, ContainerInterface $container, $options = array())
	{
		$this->pool    = $pool;
		$this->options = $options;
		$this->class   = $class;
		$this->om = $om;
		$this->container = $container;

	}


	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'compound' => false,
			'multiple' => false,
			'thumbnailMaxWidth' => 250,
			'thumbnailMaxHeight' => 250,
			'mode' => 'crop',
			'limit' => 1,
			'setProvider' => 1
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if ($options['multiple']) {
			$builder->addModelTransformer(new NumberToGalleryTransformer($this->om));
		} else {
			$builder->addModelTransformer(new NumberToMediaTransformer($this->om));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['name'] = $form->getName();
		if ($options['multiple']) {
			if ($form->getData()) {
				$view->vars['gallery'] = $form->getData();
			} else {
				$imageUpload = $this->container->get('braunedigital.formutility.imageupload');
				$view->vars['gallery'] = $imageUpload->createGallery();
			}
		} else {
			$view->vars['media'] = $form->getData();
		}
		$view->vars['thumbnailMaxWidth'] = $options['thumbnailMaxWidth'];
		$view->vars['thumbnailMaxHeight'] = $options['thumbnailMaxHeight'];
		$view->vars['multiple'] = $options['multiple'];
		$view->vars['setProvider'] = $options['setProvider'];
		$view->vars['mode'] = $options['mode'];
		$view->vars['limit'] = $options['limit'];
	}

	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'imageupload';
	}
}
