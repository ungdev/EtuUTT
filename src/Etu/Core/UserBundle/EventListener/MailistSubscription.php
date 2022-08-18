<?php

namespace Etu\Core\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\OrganizationGroupAction;

class MailistSubscription implements EventSubscriber
{
    protected $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ((!$entity instanceof OrganizationGroupAction) && (!$entity instanceof Member)) {
            return;
        }

        $sympaCommands = '';

        if ($entity instanceof OrganizationGroupAction && OrganizationGroupAction::ACTION_MAILIST_ADD_MEMBER == $entity->getAction()) {
            foreach ($entity->getGroup()->getMembers() as $key => $user) {
                if (!empty($user->getUser()->getMail())) {
                    $sympaCommands .= 'QUIET ADD '.$entity->getData()['mailist'].' '.$user->getUser()->getMail().' '.$user->getUser()->getFullName()."\n";
                }
            }

            $message = \Swift_Message::newInstance($entity->getData()['mailist'].' subscription')
                ->setFrom([$entity->getGroup()->getOrganization()->getSympaMail() => $entity->getGroup()->getOrganization()->getName()])
                ->setTo(['sympa@utt.fr'])
                ->setBody($sympaCommands);
            $this->mailer->send($message);

            return;
        }

        if ($entity instanceof Member && $entity->getGroup() && !empty($entity->getGroup()->getActions())) {
            foreach ($entity->getGroup()->getActions() as $action) {
                if (OrganizationGroupAction::ACTION_MAILIST_ADD_MEMBER == $action->getAction()) {
                    if ($entity->getUser()->getMail()) {
                        $sympaCommands = 'QUIET ADD '.$action->getData()['mailist'].' '.$entity->getUser()->getMail().' '.$entity->getUser()->getFullName()."\n";
                        $message = \Swift_Message::newInstance($action->getData()['mailist'].' subscription')
                            ->setFrom([$entity->getGroup()->getOrganization()->getSympaMail() => $entity->getGroup()->getOrganization()->getName()])
                            ->setTo(['sympa@utt.fr'])
                            ->setBody($sympaCommands);
                        $this->mailer->send($message);
                    }
                }
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($args->getEntity());

        if (!$entity instanceof Member || !in_array('group', array_keys($changeSet))) {
            return;
        }

        $old_group = $changeSet['group'][0];
        $new_group = $changeSet['group'][1];

        $em = $args->getObjectManager();
        $sympaCommands = '';

        if ($entity->getUser()->getMail()) {
            if ($changeSet['group'][0] && $changeSet['group'][0]->getActions()) {
                foreach ($changeSet['group'][0]->getActions() as $action) {
                    if (OrganizationGroupAction::ACTION_MAILIST_ADD_MEMBER == $action->getAction()) {
                        $sympaCommands .= 'QUIET DELETE '.$action->getData()['mailist'].' '.$entity->getUser()->getMail().' '.$entity->getUser()->getFullName()."\n";
                    }
                }
            }

            if ($changeSet['group'][1] && $changeSet['group'][1]->getActions()) {
                foreach ($changeSet['group'][1]->getActions() as $action) {
                    if (OrganizationGroupAction::ACTION_MAILIST_ADD_MEMBER == $action->getAction()) {
                        $sympaCommands .= 'QUIET ADD '.$action->getData()['mailist'].' '.$entity->getUser()->getMail().' '.$entity->getUser()->getFullName()."\n";
                    }
                }
            }

            if ('' != $sympaCommands) {
                $message = \Swift_Message::newInstance('Membership update')
                    ->setFrom([$entity->getGroup()->getOrganization()->getSympaMail() => $entity->getGroup()->getOrganization()->getName()])
                    ->setTo(['sympa@utt.fr'])
                    ->setBody($sympaCommands);
                $this->mailer->send($message);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Member) {
            return;
        }

        foreach ($entity->getGroup()->getActions() as $action) {
            if (OrganizationGroupAction::ACTION_MAILIST_ADD_MEMBER == $action->getAction()) {
                if (!empty($entity->getUser()->getMail())) {
                    $sympaCommands = 'QUIET DELETE '.$action->getData()['mailist'].' '.$entity->getUser()->getMail().' '.$entity->getUser()->getFullName()."\n";
                    $message = \Swift_Message::newInstance($action->getData()['mailist'].' subscription')
                        ->setFrom([$entity->getGroup()->getOrganization()->getSympaMail() => $entity->getGroup()->getOrganization()->getName()])
                        ->setTo(['sympa@utt.fr'])
                        ->setBody($sympaCommands);
                    $this->mailer->send($message);
                }
            }
        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ];
    }
}
