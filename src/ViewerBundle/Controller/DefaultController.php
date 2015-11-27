<?php

namespace ViewerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        $viewer_helper = $this->container->get('viewer_helper');

        $form = $this->createForm('viewer_urls');
        $form->add('send', 'submit', array('label' => 'Envoyer'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $servers = $form->get('servers')->getData();
            $relativeUrl = $form->get('relativeurl')->getData();

            $urls = array();
            foreach ($servers as $serverId) {
                $server = $viewer_helper->getServerUrl($serverId);
                if (preg_match('#^(.*)/$#', $server->getUrl(), $matches)) {
                    $server = $matches[1];
                }
                $urls[] = $server . $relativeUrl;
            }

            $subform = $this->get('form.factory')->createNamedBuilder('changeviewer', 'form')
                            ->add('mode',
                                  'choice',
                                  array(
                                      'label' => "Mode d'affichage",
                                      'choices' => array(
                                          'mobile' => 'Mobile (<400px)',
                                          'tablet' => 'Tablette portrait (<620px)',
                                          'tabletlandscape' => 'Tablette paysage (<768px)',
                                          'desktop' => 'Ecran (>1000px)',
                                      ),
                                      'expanded' => true,
                                  ))
                            ->getForm();


            return $this->render('ViewerBundle:Default:result.html.twig',
                                 array(
                                     'form' => $form->createView(),
                                     'subform' => $subform->createView(),
                                     'urls' => $urls,
                                 ));
        }

        return $this->render('ViewerBundle:Default:form.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }

    public function getUrlAction()
    {
        $url = $this->container->get('request_stack')->getMasterRequest()->request->get('url');
        $url = '***REMOVED***particuliers';
//        var_dump($this->container->get('request_stack')->getMasterRequest()->request->all());
//        exit();

        $request = new Request();


//        $httpKernel = $this->container->get('http_kernel');
//        $httpKernel->handle();
//        $response = $buzz->get('http://google.com');

//        echo $response->getContent();

        //curl -X GET -i ***REMOVED***particuliers --max-time 10 --connect-timeout 10 -k

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
        curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($c, CURLOPT_REFERER, $url);
        curl_setopt($c, CURLOPT_TIMEOUT, 60);
        curl_setopt($c, CURLOPT_AUTOREFERER, true);
        curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
        $content = curl_exec($c);
        $info = curl_getinfo($c);
        $error = curl_error($c);
        curl_close($c);

        var_dump($info, $error);

//        $content = file_get_contents($url);
        print($content);
        exit();
    }
}
