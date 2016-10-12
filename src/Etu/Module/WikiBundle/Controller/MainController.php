<?php

namespace Etu\Module\WikiBundle\Controller;

use CalendR\Calendar;
use CalendR\Period\Month;
use CalendR\Period\Range;
use CalendR\Period\Week;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\EventsBundle\Entity\Answer;
use Etu\Module\EventsBundle\Entity\Event;
use Etu\Module\WikiBundle\Entity\WikiPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/wiki/{category}/{slug}", name="wiki_view")
     * @Template()
     */
    public function viewAction($slug, $category = '', $categoryPrefix = '')
    {
        // Find last version of a page
        $repo = $this->getDoctrine()->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => $slug,
            'category' => $categoryPrefix.$category,
        ], [ 'createdAt' => 'DESC' ]);

        // If not found
        if (count($page) != 1) {
            throw $this->createNotFoundException('Wiki page not found');
        }

        // Check rights
        $membership = null;
        if ($page->getOrganization() && $this->isGranted('ROLE_STUDENT')) {
            $repo = $this->getDoctrine()->getRepository('EtuUserBundle:Member');
            $membership = $repo->findOneBy([
                'user' => $this->getUser()->getId(),
                'organization' => $page->getOrganization()->getId(),
            ]);
        }
        switch ($page->getReadRight()) {
            case WikiPage::RIGHT_ADMIN:
                $this->denyAccessUnlessGranted('ROLE_WIKI_ADMIN');
                break;
            case WikiPage::RIGHT_ORGA_ADMIN:
                if (!count($membership) || !$membership->hasPermission('wiki')) {
                    return $this->createAccessDeniedResponse();
                }
                break;
            case WikiPage::RIGHT_ORGA_MEMBER:
                if (!count($membership)) {
                    return $this->createAccessDeniedResponse();
                }
                break;
            case WikiPage::RIGHT_STUDENT:
                $this->denyAccessUnlessGranted('ROLE_STUDENT');
                break;
            case WikiPage::RIGHT_ALL:
                break;
            default:
                return $this->createAccessDeniedResponse();
        }

        return array(
            'page' => $page
        );
    }
}
