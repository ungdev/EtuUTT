<?php


namespace Etu\Module\UVBundle\Controller;


use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\UVBundle\Entity\UV;

use Symfony\Component\HttpFoundation\Response;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;





/**
 * @Route ("/uv")
 */

class DefaultController extends Controller
{

    /**
     * @Route ("/")
     */

    public function indexAction()
    {


        echo 'salut rien';
        /*if($codeUV == 'NF05' || $codeUV == 'nf05') {
            echo 'NF05 powa';è

        }*/

        return new Response('');
    }

    /**
     * @Route ("/{codeUV}")
     *
     *
     */
    public function showAction($codeUV)
    {


        $uv2 = $this->getDoctrine()
            ->getRepository('EtuModuleUVBundle:UV')
            ->find($codeUV);
        if (!$uv2) {
            $this->createAction($codeUV);
        }

        $code= $uv2->getCode();
        $nom = $uv2->getNom();
        echo $code;
        echo $nom;
        echo $uv2->getTd();

        return new Response('');
    }


    public function createAction($codeUV)
    {

        $uv = new UV($codeUV);
        $uv->setNom($nom);
        $uv->setCm($cm) ;
        $uv->setTd($td) ;
        $uv->setTp($tp) ;
        $uv->setThe($the) ;
        $uv->setAutomne($automne) ;
        $uv->setPrintemps($printemps) ;
        $uv->setCredits($credits) ;
        $uv->setObjectifs($objectifs) ;
        $uv->setProgramme($programme) ;


        $em = $this->getDoctrine()->getManager();
        $em->persist($uv);
        $em->flush();

        echo $codeUV . ' has been created';

        return new Response('');
    }
}