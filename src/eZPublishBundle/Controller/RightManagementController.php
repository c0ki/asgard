<?php

namespace eZPublishBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RightManagementController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createForm('ezpublish_role');
        $form->add('choice', 'submit', array('label' => 'Envoyer'));

        $formIsValid = false;
//        foreach ($request->request->all() as $paramName => $paramValue) {
//            if (preg_match('/^ezpublish_right/', $paramName)) {
//                $formIsValid = true;
//                $newRequest = $request;
//                $newRequest->request->set('ezpublish_right',
//                                          array_merge($request->request->get('ezpublish_right'), $paramValue));
//                $form2 = $this->createForm('ezpublish_right');
//                $form2->add('choice', 'submit', array('label' => 'Envoyer'));
//                $form2->handleRequest($newRequest);
//                if (!$form2->isValid()) {
//                    $formIsValid = false;
//                    break;
//                }
//            }
//        }
        if ($formIsValid) {
            $macro = "VERSION BUILD=8940826 RECORDER=FX\n";
            $macro .= "TAB T=1\n";
            foreach ($request->request->all() as $paramName => $paramValue) {
                if (!preg_match('/^ezpublish_right_/', $paramName)) {
                    continue;
                }
                $macro .= "'{$paramName}\n";
                $macro .= "TAG POS=1 TYPE=INPUT:SUBMIT FORM=NAME:roleedit ATTR=NAME:CreatePolicy\n";
                $macro .= "TAG POS=1 TYPE=SELECT FORM=ID:createpolicyform ATTR=ID:ezrole-createpolizy-module CONTENT=%{$paramValue['module']}\n";
                $macro .= "TAG POS=1 TYPE=SELECT FORM=ID:createpolicyform ATTR=ID:ezrole-createpolizy-function CONTENT=%{$paramValue['function']}\n";
                if (array_key_exists('class', $paramValue)
                    || array_key_exists('arbo', $paramValue)
                    || array_key_exists('language', $paramValue)
                ) {
                    $macro .= "TAG POS=1 TYPE=INPUT:SUBMIT FORM=ID:createpolicyform ATTR=NAME:Limitation\n";
                    if (array_key_exists('class', $paramValue)) {
                        $value = '%' . implode(':%', $paramValue['class']);
                        $macro .= "TAG POS=1 TYPE=SELECT ATTR=ID:ezrole_createpolizy_limitation_Class CONTENT={$value}\n";
                    }
                    if (array_key_exists('language', $paramValue)) {
                        $value = '%' . implode(':%', $paramValue['language']);
                        $macro .= "TAG POS=1 TYPE=SELECT ATTR=ID:ezrole_createpolizy_limitation_Language CONTENT={$value}\n";
                    }
                    if (array_key_exists('arbo', $paramValue)) {
                        $macro .= "TAG POS=1 TYPE=INPUT:SUBMIT ATTR=NAME:BrowseLimitationSubtreeButton\n";
                        if ($paramValue['arbo'][0] > 5000) {
                            $macro .= "TAG POS=1 TYPE=A ATTR=HREF:/content/browse/43\n";
                        }
                        foreach ($paramValue['arbo'] as $value) {
                            $macro .= "TAG POS=1 TYPE=INPUT:CHECKBOX FORM=NAME:browse ATTR=VALUE:{$value} CONTENT=YES\n";
                        }
                        $macro .= "TAG POS=1 TYPE=INPUT:SUBMIT FORM=NAME:browse ATTR=NAME:SelectButton\n";
                    }
                    $macro .= "TAG POS=1 TYPE=INPUT:SUBMIT ATTR=NAME:AddLimitation\n";
                }
                else {
                    $macro .= "TAG POS=1 TYPE=INPUT:SUBMIT FORM=ID:createpolicyform ATTR=NAME:AddFunction\n";
                }
            }
print('<pre>');
            print($macro);
            exit();
        }

        return $this->render('eZPublishBundle:Right:form.html.twig',
                             array(
                                 'form' => $form->createView(),
                             ));
    }
}
