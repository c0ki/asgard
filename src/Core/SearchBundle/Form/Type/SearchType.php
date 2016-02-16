<?php

namespace Core\SearchBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;

class SearchType extends AbstractType
{
    /**
     * @var Request
     */
    protected $masterRequest;

    /**
     * @var Router
     */
    protected $router;

    public function __construct(RequestStack $requestStack, Router $router) {
        $this->masterRequest = $requestStack->getMasterRequest();
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->setAction($this->router->generate('core_search_submit'))
            ->add('query')
            ->add('_route', 'hidden', array('data' => $this->masterRequest->attributes->get('_route')))
            ->add('_route_params', 'hidden', array('data' => http_build_query($this->masterRequest->attributes->get('_route_params'))));
    }

    public function getName() {
        return 'core_search';
    }
}