<?php

namespace Etu\Module\SIABundle\Command;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\OrganizationGroup;
use Etu\Core\UserBundle\Sync\Synchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:sync-sia')
            ->setDescription('Synchronize users and groups with EtuSIA.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $output->writeln('This command will sync organization group and user with ETU SIA.');

        // First create default groups
        $em = $this->getContainer()->get('doctrine')->getManager();
        $ipa = $this->getContainer()->get('etu.sia.ldap');
        $organisationGroups = $em->getRepository(OrganizationGroup::class)->findAll();

        $output->writeln("Collecting groups from IPA");
        $ipa_groups = $ipa->getConnection()->group()->find();
        $ipa_users_req = $ipa->getConnection()->user()->find();

        $ipa_users = [];
        $to_check_groups = [];
        $etu_existing_groups = [];

        foreach ($ipa_groups as $group)
        {
            if(isset($group->description) && strpos($group->description[0], 'ETUSIA') !== false)
                $to_check_groups[$group->cn[0]] = $group;
        }

        foreach ($ipa_users_req as $user)
        {
            if(isset($user->carlicense) && ($match = preg_grep('/^etu:([0-9]+)$/', $user->carlicense)))
            {
                preg_match('/^etu:([0-9]+)$/', $match[0], $m);
                $user->etu_id = $m[1];
                $ipa_users[$user->uid[0]] = $user;
            }
        }

        $output->writeln('1- Group & memberships update ');
        foreach ($organisationGroups as $group)
        {
            $output->writeln("- Group ".$group->getSlug());
            if(!in_array($group->getSlug(), array_keys($to_check_groups)))
            {
                $output->writeln("  creation on SIA");
                $to_check_groups[$group->getSlug()] = $ipa->getConnection()->group()->add($group->getSlug(), 'ETUSIA');
            }
            $etu_existing_groups[] = $group->getSlug();
            $ipa_group = $to_check_groups[$group->getSlug()];

            $ipa_group_users = [];
            if(isset($ipa_group->member_user))
                $ipa_group_users = array_merge($ipa_group_users, array_values($ipa_group->member_user));

            $group_user_to_check = array_intersect($ipa_group_users, array_keys($ipa_users));
            $ipa_group_etu_ids = [];
            foreach ($group_user_to_check as $user)
                $ipa_group_etu_ids[] = $ipa_users[$user]->etu_id;

            $etu_group_etu_ids = [];
            foreach ($group->getMembers() as $m)
                $etu_group_etu_ids[] = $m->getUser()->getId();

            $ipa_ids_to_delete = array_diff($ipa_group_etu_ids, $etu_group_etu_ids);
            $ipa_ids_to_add = array_diff($etu_group_etu_ids, $ipa_group_etu_ids);

            $ipa_account_to_delete = [];
            $ipa_account_to_add = [];

            foreach ($ipa_ids_to_delete as $id)
            {
                foreach ($ipa_users as $m)
                    if($m->etu_id == $id)
                    {
                        $ipa_account_to_delete[] = $m->uid[0];
                        break;
                    }
            }

            $output->writeln(count($ipa_account_to_delete)." account membership to delete: ".implode(',', $ipa_account_to_delete));

            foreach ($ipa_ids_to_add as $id)
            {
                foreach ($ipa_users as $m)
                    if($m->etu_id == $id)
                    {
                        $ipa_account_to_add[] = $m->uid[0];
                        break;
                    }
            }
            $output->writeln(count($ipa_account_to_add)." account membership to add: ".implode(',', $ipa_account_to_add));

            if(count($ipa_account_to_add) > 0)
                $ipa->getConnection()->group()->addMember($group->getSlug(), ['user' => $ipa_account_to_add]);
            if(count($ipa_account_to_delete) > 0)
                $ipa->getConnection()->group()->removeMember($group->getSlug(), ['user' => $ipa_account_to_delete]);

        }
        $output->writeln('2- Groups to delete');
        $group_to_delete = array_diff(array_keys($to_check_groups), $etu_existing_groups);
        foreach ($group_to_delete as $group)
        {
            $output->writeln("      You should delete group: $group");
            //$ipa->getConnection()->group()->del($group);
        }

        $em->flush();

    }
}
