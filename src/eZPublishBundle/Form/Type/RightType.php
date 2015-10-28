<?php

namespace eZPublishBundle\Form\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RightType extends AbstractType
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('_loader',
                      'file',
                      array(
                          'required' => false,
                      ));

        $builder->add('module',
                      'choice',
                      array(
                          'label' => "Module",
                          'choices' => array('content' => 'content'),
                      ));

        $builder->add('function',
                      'choice',
                      array(
                          'label' => "Fonction",
                          'choices' => array(
                              'create' => 'create',
                              'edit' => 'edit',
                              'remove' => 'remove',
                              'hide' => 'hide'),
                      ));

        $builder->add('class',
                      'choice',
                      array(
                          'label' => "Classe",
                          'choices' => array(
                              "71" => "Banniere CRM",
                              "86" => "Bloc accompagnement",
                              "133" => "Bloc Associations",
                              "85" => "Bloc bandeau, synthèse et accompagnement",
                              "94" => "Bloc catégories push",
                              "105" => "Bloc citation",
                              "82" => "Bloc connexion produit",
                              "43" => "Bloc contact autre",
                              "111" => "Bloc contenu colonne",
                              "99" => "Bloc Contenu riche illustré mono-ligne - Vignette",
                              "98" => "Bloc contenu riche illustré mono-ligne",
                              "90" => "Bloc contenu riche illustré",
                              "88" => "Bloc contenu riche",
                              "103" => "Bloc contenus deux colonnes",
                              "123" => "Bloc coordonnées Particuliers",
                              "129" => "Bloc coordonnées Professionnels",
                              "124" => "Bloc disponibilités",
                              "83" => "Bloc en-tête",
                              "132" => "Bloc Entreprises",
                              "126" => "Bloc étape suivante",
                              "92" => "Bloc FAQ - Temoignage",
                              "91" => "Bloc FAQ",
                              "56" => "Bloc gamme",
                              "101" => "Bloc iframe",
                              "106" => "Bloc ils en parlent",
                              "95" => "Bloc images",
                              "137" => "Bloc Info Résa Produit",
                              "109" => "Bloc infos pratiques",
                              "59" => "Bloc lexique",
                              "77" => "Bloc lien page traduite",
                              "102" => "Bloc Liste de vidéos",
                              "125" => "Bloc message",
                              "49" => "Bloc mise en avant",
                              "62" => "Bloc Mon banquier en ligne",
                              "93" => "Bloc process par étapes",
                              "130" => "Bloc Professionnels",
                              "70" => "Bloc push",
                              "108" => "Bloc rebond contenu",
                              "84" => "Bloc régionalisation",
                              "87" => "Bloc services",
                              "134" => "Bloc Structure",
                              "97" => "Bloc synthèse des pushs",
                              "96" => "Bloc tableau",
                              "55" => "Bloc thématique mono-panel",
                              "50" => "Bloc thématique multi-panels",
                              "104" => "Bloc voir plus",
                              "131" => "Configuration des formulaires EVI",
                              "27" => "Configuration région",
                              "42" => "Contact",
                              "135" => "Contenu externe",
                              "67" => "Dossier conseils",
                              "1" => "Dossier",
                              "120" => "Erreur",
                              "25" => "Espace transactionnel",
                              "116" => "FAQ",
                              "12" => "Fichier",
                              "138" => "Formulaire EVI",
                              "127" => "Formulaire",
                              "69" => "Gammes",
                              "3" => "Groupe d'utilisateur",
                              "31" => "Image",
                              "110" => "Lexique clé définition",
                              "21" => "Lien accès rapide",
                              "11" => "Lien",
                              "17" => "Marché",
                              "22" => "Menu",
                              "81" => "Modèle de bloc",
                              "72" => "Modèle de classe",
                              "79" => "Page conseil",
                              "114" => "Page cycle court",
                              "140" => "Page d'authentification EVI",
                              "75" => "Page d'authentification",
                              "47" => "Page de choix d'une région",
                              "73" => "Page Iframe",
                              "80" => "Page opération",
                              "78" => "Page produit",
                              "74" => "Page recherche d'agence",
                              "64" => "Paramétrage agence",
                              "34" => "Push Mineur/Corporate",
                              "115" => "Résultat de recherche",
                              "117" => "Rubrique FAQ",
                              "36" => "Rubrique régionale",
                              "26" => "Rubrique",
                              "139" => "Simulateur Retraite",
                              "128" => "Suivi des achats medias",
                              "18" => "Univers",
                              "4" => "Utilisateur",
                              "63" => "Variable",
                              "112" => "Vidéo",
                              "32" => "Vignette Majeure",
                              "33" => "Vignette Mineure",
                          ),
                          'multiple' => true,
                          'required' => false,
                      ));
        $builder->add('arbo',
                      'choice',
                      array(
                          'label' => "Arbo",
                          'choices' => array(
                              "59" => "/national",
                              "489" => "/alsace",
                              "490" => "/aquitaine poitou-charentes",
                              "491" => "/auvergne limousin",
                              "492" => "/bourgogne franche-comte",
                              "493" => "/bretagne-pays de loire",
                              "494" => "/cote d azur",
                              "495" => "/île de france",
                              "496" => "/languedoc roussillon",
                              "498" => "/loire-centre",
                              "497" => "/loire drome ardeche",
                              "499" => "/lorraine champagne-ardenne",
                              "378" => "/midi-pyrenees",
                              "500" => "/nord france europe",
                              "501" => "/normandie",
                              "502" => "/picardie",
                              "503" => "/provence-alpes-corse",
                              "504" => "/rhone alpes",
                              "5342" => "/media/national",
                              "5379" => "/media/alsace",
                              "5397" => "/media/aquitaine poitou-charentes",
                              "5400" => "/media/auvergne limousin",
                              "5402" => "/media/bourgogne franche-comte",
                              "5403" => "/media/bretagne-pays de loire",
                              "5404" => "/media/cote d'azur",
                              "5405" => "/media/île de france",
                              "5406" => "/media/languedoc roussillon",
                              "5408" => "/media/loire drome ardeche",
                              "5407" => "/media/loire-centre",
                              "5409" => "/media/lorraine champagne-ardenne",
                              "5410" => "/media/midi-pyrenees",
                              "5411" => "/media/nord france europe",
                              "5412" => "/media/normandie",
                              "5413" => "/media/picardie",
                              "5414" => "/media/provence-alpes-corse",
                              "5415" => "/media/rhone alpes",
                              "6434" => "/media/variables",
                          ),
                          'multiple' => true,
                          'required' => false,
                      ));

        $builder->add('language',
                      'choice',
                      array(
                          'label' => "Langue",
                          'choices' => array(
                              "fre-AE" => "Alsace",
                              "fre-AP" => "Aquitaine Poitou-Charentes",
                              "fre-AL" => "Auvergne Limousin",
                              "fre-BF" => "Bourgogne Franche-Comté",
                              "fre-BL" => "Bretagne-Pays de Loire",
                              "fre-CZ" => "Côte d Azur",
                              "fre-IF" => "Île de France",
                              "fre-LR" => "Languedoc Roussillon",
                              "fre-LD" => "Loire Drôme Ardèche",
                              "fre-LC" => "Loire-Centre",
                              "fre-LA" => "Lorraine Champagne-Ardenne",
                              "fre-MP" => "Midi-Pyrénées",
                              "fre-FR" => "National",
                              "fre-NF" => "Nord France Europe",
                              "fre-NO" => "Normandie",
                              "fre-PI" => "Picardie",
                              "fre-PA" => "Provence-Alpes-Corse",
                              "fre-RH" => "Rhône Alpes",
                          ),
                          'multiple' => true,
                          'required' => false,
                      ));

    }

    public function getName()
    {
        return 'ezpublish_right';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
//        $resolver->setDefaults(array(
//                                   'logType' => '',
//                               ));
    }

}