<?php

namespace ViewerBundle\Form\Type;

use Core\ProjectBundle\Component\Helper\ProjectHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use ViewerBundle\Component\Helper\ViewerHelper;

class ViewerUrlsType extends AbstractType
{
    /**
     * @var ViewerHelper
     */
    private $viewerHelper;

    public function __construct(ViewerHelper $viewerHelper)
    {
        $this->viewerHelper = $viewerHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $serverUrls = $this->viewerHelper->getServerUrls();

        foreach ($serverUrls as $name => $groupServerUrl) {
            if (is_array($groupServerUrl) && !is_int(key($groupServerUrl))) {
                foreach ($groupServerUrl as $subname => $serverUrl) {
                    $serverUrls["{$name} > {$subname}"] = $serverUrl;
                    unset($serverUrls[$name][$subname]);
                }
            }
        }
        $serverUrls = array_filter($serverUrls);

        $builder->add('servers',
                      'choice',
                      array(
                          'label' => "Server",
                          'choices' => $serverUrls,
                          'multiple' => true,
                          'expanded' => false,
                      ));

        $builder->add('relativeurl',
            'text',
            array(
                'label' => "Relative url",
                'required' => false,
            ));

//        $builder->add('resulttype',
//            'choice',
//            array(
//                'label' => "Result type",
//                'choices' => array(
//                    'contentviewer' => 'Viewer content',
//                    'contentsearch' => 'Search content',
//                ),
//            ));

//        $builder->add('contentsearch',
//            'text',
//            array(
//                'label' => "Search content",
//                'required' => false,
//            ));
    }

    public function getName()
    {
        return 'viewer_urls';
    }

}