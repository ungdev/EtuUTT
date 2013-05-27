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
        $uv = new UV('nf05', 'initiation au C', '51', '42', '69', '0', true, false, 6, 'UV SUBLIME', 'Honnêtement, rien.' );

        $em = $this->getDoctrine()->getManager();
        $em->persist($uv);
        $em->flush();

        echo 'salut rien';
        /*if($codeUV == 'NF05' || $codeUV == 'nf05') {
            echo 'NF05 powa';è

        }*/

        return new Response('');
    }

    /**
     * @Route ("/{codeUV}")
     * @Template()
     *
     */

    public function createAction($codeUV)
    {

        echo 'salut' . $codeUV;
        /*
        $product = new UV();
        $product->setCm($cm) ;
        $product->setTd($td) ;
        $product->setTp($tp) ;
        $product->setThe($the) ;
        $product->setAutomne($automne) ;
        $product->setPrintemps($printemps) ;
        $product->setCredits($credits) ;
        $product->setObjectifs($objectifs) ;
        $product->setProgramme($programme) ;
        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();
*/
        return new Response('');
    }



}
