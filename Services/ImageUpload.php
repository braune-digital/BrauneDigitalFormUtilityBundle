<?php
namespace BrauneDigital\FormUtilityBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\SecurityContext;

class ImageUpload {

    protected $em;
    protected $container;
    protected $request;
    protected $securityContext;

	/**
	 * @param EntityManager $em
	 * @param RequestStack $requestStack
	 * @param ContainerInterface $container
	 * @param SecurityContext $securityContext
	 */
    public function __construct(EntityManager $em, RequestStack $requestStack, ContainerInterface $container, SecurityContext $securityContext)
    {
        $this->em = $em;
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->securityContext = $securityContext;
    }

	/**
	 * @return mixed
	 */
    public function createGallery() {
		
		$galleryManager = $this->container->get('sonata.media.manager.gallery');

		$gallery = $galleryManager->create();
		if ((int) $this->request->get('setOperator') == 1) {
			$gallery->setName($this->securityContext->getToken()->getUser()->getProvider()->getTitle());
		} else {
			$gallery->setName($this->securityContext->getToken()->getUser()->getUsername());
		}

		$gallery->setContext('default');
		$gallery->setDefaultFormat('default');
		$gallery->setEnabled(true);

		$gallery->setOwner($this->securityContext->getToken()->getUser());
		$galleryManager->save($gallery);

		return $gallery;
    }

} 