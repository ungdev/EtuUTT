<?php

namespace Etu\Module\SIABundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
// Import annotations
use Etu\Module\SIABundle\Form\createAccount;
use Etu\Module\SIABundle\Form\editAccount;
use Etu\Module\SIABundle\Ldap\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/sia")
 * @Template()
 */
class DefaultController extends Controller
{

    /**
     * @Route("/", name="sia_index")
     * @Template("@EtuModuleSIA/Main/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SIA_EDIT');

        // Test if user have an account
        try {
            $ipa = $this->get('etu.sia.ldap');
            $user = $ipa->getUserByEtuId($this->getUser()->getId());
        } catch(\Exception $e)
        {
            $logger = $this->get('logger');
            $logger->error("IPA Init fail: ".$e->getMessage());
            // Emit flash message
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'Impossible de se connecter au serveur d\'authentification du SIA !',
            ]);
            return $this->redirectToRoute('homepage');
        }

        if(!$user)
            $form = $this->createForm(createAccount::class, ['username' => $ipa->findUidFree($this->getUser()->getUsername())]);
        else
            $form = $this->createForm(editAccount::class, ['username' => $user->getLogin()]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $form_data = $form->getData();
            try {
                // Edition
                if($user)
                {
                    $user->setUserPassword($form_data['plainPassword']);
                    $ipa->modify($user);
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'Compte modifié !',
                    ]);
                } else {
                    $user = new User();
                    $user->setFirstName($this->getUser()->getFirstName())
                        ->setLastName($this->getUser()->getLastName())
                        ->setUserPassword($form_data['plainPassword'])
                        ->setLogin($form_data['username'])
                        ->setEtuUttId($this->getUser()->getId())
                        ->setMail($this->getUser()->getMail());
                    if($this->getUser()->getStudentId())
                        $user->setStudentId($this->getUser()->getStudentId());

                    $ipa->create($user);
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'Compte créé !',
                    ]);
                    return $this->redirectToRoute('sia_index');
                }
            } catch(\Exception $e)
            {
                $logger = $this->get('logger');
                $logger->error("IPA account modification fail: ".$e->getMessage());
                return $this->render('@Twig/Exception/error.html.twig');
            }
        }

        return [
            'createForm' => $form->createView(),
            'user' => $user
        ];
    }

    public function updateAutoAction()
    {
        $this->denyAccessUnlessGranted('ROLE_DAYMAIL_EDIT');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $memberships Member[] */
        $memberships = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }
    }
}
