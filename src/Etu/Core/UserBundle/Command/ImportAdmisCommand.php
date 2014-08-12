<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Sync\Synchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportAdmisCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:import-admis')
            ->setDescription('Import new temp users')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\nConnecting to database ...");
        $output->writeln('----------------------------------------');

        $dialog = $this->getHelperSet()->get('dialog');

        $host = $dialog->ask($output, 'Host: ');
        $name = $dialog->ask($output, 'Name: ');
        $user = $dialog->ask($output, 'User: ');
        $pass = $dialog->ask($output, 'Pass: ');

        $pdo = new \PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $pass);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $output->writeln("\nFinding users differences ...");
        $output->writeln('----------------------------------------');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /*
         * Imported
         */
        $imported = $pdo->query('SELECT * FROM admis')->fetchAll(\PDO::FETCH_OBJ);

        $importedLogins = [];
        $importedRegistry = [];

        foreach ($imported as $import) {
            $importedRegistry[$import->login] = $import;
            $importedLogins[] = $import->login;
        }


        /*
         * Database
         */
        $qb = $em->createQueryBuilder();

        $dbUsers = $qb->select('u.login')
            ->from('EtuUserBundle:User', 'u')
            ->where($qb->expr()->in('u.login', $importedLogins))
            ->getQuery()
            ->getArrayResult();

        $dbLogins = [];

        foreach ($dbUsers as $dbUser) {
            $dbLogins[] = $dbUser['login'];
        }

        $toImport = array_diff($importedLogins, $dbLogins);

        $output->writeln(sprintf('%s users to import', count($toImport)));
        $output->writeln("\nImporting users ...");

        $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($toImport));
        $bar->update(0);
        $i = 1;

        foreach($toImport as $loginToImport) {
            $import = $importedRegistry[$loginToImport];

            $user = new User();
            $user->setLogin($import->login);
            $user->setPassword($this->getContainer()->get('etu.user.crypting')->encrypt($import->password));
            $user->setFullName($import->prenom . ' ' . $import->nom);
            $user->setSex(($import->sexe == 'M') ? User::SEX_MALE : User::SEX_FEMALE);
            $user->setSexPrivacy(User::PRIVACY_PRIVATE);
            $user->setCity(ucfirst(strtolower($import->ville)));
            $user->setCityPrivacy(User::PRIVACY_PRIVATE);
            $user->setPostalCode($import->codePostal);
            $user->setPostalCodePrivacy(User::PRIVACY_PRIVATE);
            $user->setCountry(ucfirst(strtolower($import->pays)));
            $user->setCountryPrivacy(User::PRIVACY_PRIVATE);
            $user->setPersonnalMail($import->email);
            $user->setPersonnalMailPrivacy(User::PRIVACY_PRIVATE);
            $user->setBranch($import->branche);
            $user->setFiliere($import->specialite);
            $user->setNiveau(intval($import->niveau));
            $user->setFormation('Inconnue');
            $user->setLanguage('fr');
            $user->setKeepActive(false);

            $em->persist($user);
            $em->flush();

            $bar->update($i);
            $i++;
        }

        $output->write("\nDone");
    }
}