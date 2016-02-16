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
        // Init data
        if (!array_key_exists('data', $options)) {
            $options['data'] = array();
        }
        if (!array_key_exists('_route', $options['data']) || is_null($options['data']['_route'])) {
            $options['data']['_route'] = $this->masterRequest->attributes->get('_route');
        }
        if (!array_key_exists('_route_params', $options['data']) || is_null($options['data']['_route_params'])) {
            $options['data']['_route_params']
                = http_build_query($this->masterRequest->attributes->get('_route_params'));
        }

        $builder
            ->setAction($this->router->generate('core_search_submit'))
            ->add('query')
            ->add('_route', 'hidden', array('data' => $options['data']['_route']))
            ->add('_route_params', 'hidden', array('data' => $options['data']['_route_params']));
    }

    public function getName() {
        return 'core_search';
    }
}