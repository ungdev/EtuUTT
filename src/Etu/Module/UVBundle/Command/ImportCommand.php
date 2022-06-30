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

        ini_set("allow_url_fopen", 1);
        $arrContextOptions=array(
            "ssl"=>array(
                'ciphers' => 'DEFAULT:!DH'
            ),
        );
        $output->writeln('Getting data from API');
        $json = file_get_contents('https://api-guideue.utt.fr/uvs/fr/2022?q=', false, stream_context_create($arrContextOptions));
        $obj = json_decode($json, true);

        $ues = [];

        $convertCategorie = [
            "HUMANITES" => "CT",
            "MANAGEMENT DE L'ENTREPRISE" => "ME",
            "CONNAISSANCES SCIENTIFIQUES" => "CS",
            "TECHNIQUES ET METHODES" => "TM",
            "EXPRESSION ET COMMUNICATION" => "EC",
            "STAGE" => "ST"
        ];

        $formationsING = ["TC", "MTE", "RT", "GI", "MM", "GM", "ISI"];

        $codes = [];

        foreach ($obj as $ue) {
            $json = file_get_contents('https://api-guideue.utt.fr/uv/fr/2022/'.$ue["code"].'/1', false, stream_context_create($arrContextOptions));
            $ueAPI = json_decode($json, true);
            $ueToStore = [];
            if(!in_array($ueAPI["code"], $codes)) {
                $codes[] = $ueAPI["code"];
            }
            else {
                continue;
            }
            $ueToStore["UV"] = $ueAPI["code"];
            $periode = "";
            if ($ueAPI["automne"] && $ueAPI["automne"]["ouvert"] && $ueAPI["automne"] && $ueAPI["automne"]["ouvert"]) {
                $ueToStore["periode1"] = "Automne";
                $ueToStore["periode2"] = "Printemps";
                $periode = "automne";
            }
            elseif ($ueAPI["automne"] && $ueAPI["automne"]["ouvert"]) {
                $ueToStore["periode1"] = "Automne";
                $ueToStore["periode2"] = "";
                $periode = "automne";
            }
            elseif ($ueAPI["printemps"] && $ueAPI["printemps"]["ouvert"]) {
                $ueToStore["periode1"] = "Printemps";
                $ueToStore["periode2"] = "";
                $periode = "printemps";
            }
            else {
                continue;
            }
            $ueToStore["catégorie"] = $convertCategorie[$ueAPI[$periode]["profils"][0]["categorie"]];
            $ueToStore["titre"] = $ueAPI["libelle"];

            $isMaster = false;
            $isIng = false;
            foreach ($ueAPI[$periode]["profils"] as $profil) {
                if (in_array($profil["libelleCourtFormation"], $formationsING)) {
                    $isIng = true;
                }
                else {
                    $isMaster = true;
                }
            }
            if ($isIng && $isMaster) {
                $ueToStore["diplome"] = "UV ing. ou UV mast.";
            }
            elseif ($isIng) {
                $ueToStore["diplome"] = "UV ing.";
            }
            else {
                $ueToStore["diplome"] = "UV mast.";
            }
            if(strpos($ueAPI["acquisitionNotions"], "Mineur") !== false) {
                $ueToStore["mineur"] = substr($ueAPI["acquisitionNotions"], strlen("Mineur : "));
            }
            else {
                $ueToStore["mineur"] = "";
            }

            $ueToStore["antécédent"] = $ueAPI["prerequis"] ?: "";

            $types = ["THE", "C", "STG", "ENT", "TD", "PRJ", "TP"];
            foreach ($types as $type) {
                $ueToStore[$type] = "";
                $ueToStore[$type."volume"] = "";
            }

            foreach ($ueAPI["activites"] as $activite) {
                $ueToStore[$activite["libelle"]] = $activite["libelle"];
                $ueToStore[$activite["libelle"]."volume"] = $activite["nbVal"].$activite["nbType"];
            }
            $ueToStore["commentaires"] = $ueAPI["acquisitionNotions"] ?: "";
            $objectifs = explode("\n", $ueAPI["objectifs"]);
            foreach (range(1, 8) as $number) {
                $ueToStore["objectif".$number] = $number - 1 < count($objectifs) ? $objectifs[$number - 1] : "";
            }
            $programmes = explode("\n", $ueAPI["programme"]);
            foreach (range(1, 12) as $number) {
                $ueToStore["programme".$number] = $number - 1 < count($programmes) ? $programmes[$number - 1] : "";
            }
            $ueToStore["langues"] = "FRA";
            $ueToStore["credits"] = $ueAPI["creditsEcts"]." crédits";
            $ues[] = $ueToStore;
        }

        $output->writeln('Processing data');

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
        foreach ($uesInDBBeforeImport as $ue) {
            $codesUEsInDBBeforeImport[] = $ue->getCode();
            $codesUEsInDBAfterImport[] = $ue->getCode();
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
        foreach ($codesUEsInDBAfterImport as $code) {
            $ue = $em->getRepository('EtuModuleUVBundle:UV')->findOneBy(["code"=>$code]);
            if(!empty($ue)) {
                $ue->setIsOld(false);
                $em->persist($ue);
            }
        }

        // We ensure that all codes which are not in csv but in the db after import are old
        $oldCodes = array_diff($codesUEsInDBAfterImport, $codesUEsInCSV);
        foreach ($oldCodes as $oldCode) {
            $ue = $em->getRepository('EtuModuleUVBundle:UV')->findOneBy(["code"=>$oldCode]);
            if(!empty($ue)) {
                $ue->setIsOld(true);
                $em->persist($ue);
            }
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
