<?php

namespace Etu\Module\UVBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Module\UVBundle\Entity\UV;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:ue:import')
            ->setDescription('Import UE informations from csv (ask admin UTT for it each semester)');
        //->addArgument('url', InputArgument::REQUIRED, 'The file URL to download');
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('
	Welcome to the EtuUTT UE manager

This command helps you to import the official UTT UE guide from CSV file.');

        $registry = __DIR__.'/../Resources/objects/registry.json';
        $csv = __DIR__.'/../Resources/objects/ues.csv';
        $reader = Reader::createFromPath($csv);
        // Download the guide
        // $url = $input->getArgument('url');
        // file_put_contents($file, fopen($url, 'r'));
        // $output->writeln("\nDownloading ...");

        /*
         * Convert te file to xml to parse it
         */
        //$output->writeln('Converting to XML ...');
        //shell_exec('pdftohtml -i -noframes -xml "'.$file.'" "'.$xml.'"');
        //$xml = file_get_contents($xml);

        $output->writeln('YOLO');
        $ues = $reader->fetchAssoc();
        $output->writeln('YOLO');
        $count = 0;
        foreach ($ues as $ue) {
            $output->writeln($ue['UV']);
            ++$count;
        }
        $output->writeln("\n\n".$count.' UV found');

        $output->writeln("\nImporting ...");

        $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $count);
        $bar->update(0);
        $i = 1;

        $entities = [];

        $container = $this->getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        // We list UEs before import and prepare the arrays
        $uesInDBBeforeImport = $em->getRepository('EtuModuleUVBundle:UV')->findAll();
        $codesUEsInDBBeforeImport = [];
        $codesUEsInDBAfterImport = [];
        for ($uesInDBBeforeImport as $ue) {
            $codesUEsInDBBeforeImport[] = $ue["code"];
            $codesUEsInDBAfterImport[] = $ue["code"];
        }

        $codesUEsInCSV = [];


        foreach ($ues as $uv) {
            $entity = new UV();

            // We import data from csv line
            $j = 1;
            $programme = $uv['programme1'];
            while ($j < 11 && '' != $uv['programme'.$j]) {
                ++$j;
                $programme = $programme."\n".$uv['programme'.$j];
            }
            $j = 1;
            $objectif = $uv['objectif1'];
            while ($j < 7 && '' != $uv['objectif'.$j]) {
                ++$j;
                $objectif = $objectif."\n".$uv['objectif'.$j];
            }
            $automne = 'Automne' == $uv['periode1'] || 'Automne' == $uv['periode2'];
            $printemps = 'Printemps' == $uv['periode1'] || 'Printemps' == $uv['periode2'];
            $commentaire = implode("\n", explode('|', $uv['commentaires']));
            $commentaire = implode('P', explode('Picto p', $commentaire));
            $commentaire = implode('UE en Anglais et en Français', explode('Drapeau anglais/français', $commentaire));


            // We add the UE to the array of UE in CSV
            $codesUEsInCSV[] = $uv['UV'];

            // We prepare the entity
            $entity->setCode($uv['UV'])
                ->setName($uv['titre'])
                ->setCategory($this->parseCategory($uv['catégorie']))
                ->setAutomne($automne)
                ->setPrintemps($printemps)
                ->setDiplomes($uv['diplome'])
                ->setMineurs($uv['mineur'])
                ->setDiplomes($uv['diplome'])
                ->setAntecedents($uv['antécédent'])
                ->setLanguages($uv['langues'])
                ->setCommentaire($commentaire)
                ->setObjectifs($objectif)
                ->setProgramme($programme)
                ->setIsOld(false)
                ->setCredits($this->parseCredits($uv['credits']))
                ->setCm($this->parseHour($uv['Cvolume']))
                ->setTd($this->parseHour($uv['TDvolume']))
                ->setTp($this->parseHour($uv['TPvolume']))
                ->setThe($this->parseHour($uv['THEvolume']))
                ->setProjet($this->parseHour($uv['PRJvolume']))
                ->setStage($this->parseHour($uv['STGvolume']));

            // If the UV is not currently in DB, we add it
            if(!in_array($uv['UV'], $codesUEsInDBBeforeImport)) {
                if ('dev' === $this->getContainer()->getParameter('kernel.environment')) {
                    $em->persist($entity);
                    $codesUEsInDBAfterImport[] = $uv['UV'];
                }
            }
            $entities[] = $entity;

            $bar->update($i);
            ++$i;
        }

        // We ensure all UEs imported are not old (in cas of : UE is not old -> UE is old -> UE is no longer old)
        for ($codesUEsInDBAfterImport as $code) {
            $ue = $em->getRepository('EtuModuleUVBundle:UV')->findOneBy(["code"=>$code]);
            $ue->setIsOld(false);
            $em->persist($ue);
        }

        // We ensure that all codes which are not in csv but in the db after import are old
        $oldCodes = array_diff($codesUEsInDBAfterImport, $codesUEsInCSV);
        for ($oldCodes as $oldCode) {
            $ue = $em->getRepository('EtuModuleUVBundle:UV')->findOneBy(["code"=>$code]);
            $ue->setIsOld(true);
            $em->persist($ue);
        }

        $em->flush();

        $output->writeln("\nWriting registry ...");

        file_put_contents($registry, serialize($entities));

        $output->writeln("Done.\n");
    }

    protected function parseCredits(string $credit)
    {
        str_replace(' crédits', '', $credit);

        return (int) $credit;
    }

    protected function parseCategory(string $category)
    {
        $category = mb_strtolower($category);
        if ('tc' == mb_substr($category, 0, 2)) {
            return mb_substr($category, 3);
        }

        return $category;
    }

    protected function parseHour(string $hour)
    {
        return (int) mb_substr($hour, 0, mb_strlen($hour) - 2);
    }
}
