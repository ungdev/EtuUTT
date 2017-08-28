<?php

namespace Etu\Module\UVBundle\Command;

use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Module\UVBundle\Entity\UV;
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
            ->setName('etu:uv:import')
            ->setDescription("Import UV informations from the PDF official guide.\u{a0}Script made for the 2015-2016 version of the guide")
            ->addArgument('url', InputArgument::REQUIRED, 'The file URL to download');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
         * Is pdftohtml installed?
         */
        $returnVal = shell_exec('which pdftohtml');
        $popplerInstalled = (empty($returnVal) ? false : true);

        if (!$popplerInstalled) {
            throw new \RuntimeException(
                "poppler-utils is required to import UV from the official guide.\n".
                'Install it using : sudo apt-get install poppler-utils'
            );
        }

        $output->writeln('
	Welcome to the EtuUTT UV manager

This command helps you to import the official UTT\u{a0}UV guide.
This command has been updated for the 2015-2016 guide. If you want to use it
for another version, you will have to update this parser.');

        $file = __DIR__.'/../Resources/objects/current-guide.pdf';
        $xml = __DIR__.'/../Resources/objects/current-guide.xml';
        $registry = __DIR__.'/../Resources/objects/registry.json';

        // Download the guide
        $url = $input->getArgument('url');
        file_put_contents($file, fopen($url, 'r'));
        $output->writeln("\nDownloading ...");

        /*
         * Convert te file to xml to parse it
         */
        $output->writeln('Converting to XML ...');
        shell_exec('pdftohtml -i -noframes -xml "'.$file.'" "'.$xml.'"');
        $xml = file_get_contents($xml);

        /*
         * We use only the part of the file where UV are described.
         * We will split the file in category of UV (CS, TM, ..)
         */
        $part = [];

        // Cut everything before UV descriptions
        $tmp = explode('<text top="71" left="296" width="367" height="52" font="34">Connaissances</text>
<text top="139" left="373" width="290" height="52" font="34">scientiﬁ ques</text>
<text top="194" left="476" width="187" height="27" font="35">Tronc commun</text>', $xml);
        $xml = $tmp[1];

        // Get "CS of TC"
        $output->writeln('Isolate CS of TC');
        $tmp = explode('<text top="71" left="343" width="320" height="52" font="34">Techniques &amp;</text>
<text top="139" left="424" width="239" height="52" font="34">Méthodes</text>
<text top="194" left="476" width="187" height="27" font="35">Tronc commun</text>', $xml);
        $part['cs tc'] = $tmp[0];
        $xml = $tmp[1];

        // Get "TM of TC"
        $output->writeln('Isolate TM of TC');
        $tmp = explode('<text top="71" left="296" width="367" height="52" font="34">Connaissances</text>
<text top="139" left="373" width="290" height="52" font="34">scientiﬁ ques</text>
<text top="194" left="432" width="231" height="27" font="35">Branches - Master</text>', $xml);
        $part['tm tc'] = $tmp[0];
        $xml = $tmp[1];

        // Get "CS of BR"
        $output->writeln('Isolate CS of BR');
        $tmp = explode('<text top="71" left="343" width="320" height="52" font="34">Techniques &amp;</text>
<text top="139" left="424" width="239" height="52" font="34">Méthodes</text>
<text top="194" left="432" width="231" height="27" font="35">Branches - Master</text>', $xml);
        $part['cs br'] = $tmp[0];
        $xml = $tmp[1];

        // Get "TM of BR"
        $output->writeln('Isolate TM of BR');
        $tmp = explode('<text top="71" left="355" width="308" height="52" font="34">Expression &amp;</text>
<text top="139" left="290" width="373" height="52" font="34">Communication</text>', $xml);
        $part['tm br'] = $tmp[0];
        $xml = $tmp[1];

        // Get "EC"
        $output->writeln('Isolate EC');
        $tmp = explode('<text top="71" left="274" width="389" height="52" font="34">Management de</text>
<text top="139" left="406" width="257" height="52" font="34">l’Entreprise</text>', $xml);
        $part['ec'] = $tmp[0];
        $xml = $tmp[1];

        // Get "ME"
        $output->writeln('Isolate ME');
        $tmp = explode('<text top="71" left="411" width="252" height="52" font="34">Humanités</text>', $xml);
        $part['me'] = $tmp[0];
        $xml = $tmp[1];

        // Get "CT"
        $output->writeln('Isolate CT');
        $tmp = explode('<text top="71" left="551" width="112" height="52" font="34">Hors</text>
<text top="139" left="551" width="112" height="52" font="34">Proﬁ l</text>', $xml);
        $part['ct'] = $tmp[0];
        $xml = $tmp[1];

        // Get "HP"
        $output->writeln('Isolate HP');
        $tmp = explode('<text top="71" left="496" width="167" height="52" font="34">Stages</text>', $xml);
        $part['hp'] = $tmp[0];
        $xml = $tmp[1];

        // Get "ST"
        $output->writeln('Isolate ST');
        $tmp = explode('<text top="71" left="486" width="193" height="52" font="34">Travaux </text>
<text top="139" left="160" width="503" height="52" font="34">Personnels Encadrés</text>', $xml);
        $part['st'] = $tmp[0];
        $xml = $tmp[1];

        // Get "TPE"
        $output->writeln('Isolate TPE');
        $tmp = explode('<text top="92" left="117" width="75" height="34" font="4">Index </text>
<text top="137" left="71" width="166" height="34" font="4">alphabétique </text>', $xml);
        $part['tpe'] = $tmp[0];
        $xml = $tmp[1];

        $uv = [];

        // Parse UV header (not description)
        foreach ($part as $category => $text) {
            $output->writeln('Parse UV from '.$category);

            preg_match_all('/<text top="[0-9]+" left="[5678][0-9]" width="[0-9]+" height="[0-9]+" font="[0-9]+"><b>([A-Z][A-Z0-9]{1,5})<\/b><\/text>\n<text top="[0-9]+" left="152" width="[0-9]+" height="[0-9]+" font="38">([^<]+)<\/text>(?:\n<text top="[0-9]+" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="38">([^<]+)<\/text>){0,1}(?:\n<text top="[0-9]+" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">([^<]+)<\/text>){0,1}\n<text top="[0-9]+" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+"><b>UV (MAST.|ING.|ING. OU UV MAST.|ING. OU UV CS|ING. OU|ING. ET MAST|ING. OU UV TM)\s*<\/b><\/text>/', $text, $match, PREG_OFFSET_CAPTURE);

            $descBegin = -1;
            for ($i = 0; $i < count($match[1]); ++$i) {
                // Create raw description of the precedent UV
                if ($descBegin > 0) {
                    $uv[$this->sanitizer($match[1][$i - 1][0])] =
                        array_merge(
                        $uv[$this->sanitizer($match[1][$i - 1][0])],
                        $this->parseDesc(mb_substr($text, $descBegin, $match[0][$i][1] - $descBegin)));
                }

                // Find index : UV Code
                $code = $this->sanitizer($match[1][$i][0]);
                $uv[$code] = [];

                // Set the UV name
                $uv[$code]['name'] = $this->sanitizer($match[2][$i][0].' '.$match[3][$i][0].' '.$match[4][$i][0]);

                //  Set target (ing or master)
                switch ($this->sanitizer($match[5][$i][0])) {
                    case 'MAST.':
                        $uv[$code]['target'] = 'mast';
                        break;
                    case 'ING. OU':
                    case 'ING. OU UV CS':
                    case 'ING. OU UV MAST.':
                    case 'ING. ET MAST':
                    case 'ING. OU UV TM':
                        $uv[$code]['target'] = 'both';
                        break;
                    case 'ING.':
                        $uv[$code]['target'] = 'ing';
                        break;
                    default:
                        $uv[$code]['target'] = 'ing';
                }

                //  Set UV category
                switch ($category) {
                    case 'cs tc':
                    case 'cs br':
                        $uv[$code]['category'] = 'cs';
                        break;
                    case 'tm tc':
                    case 'tm br':
                        $uv[$code]['category'] = 'tm';
                        break;
                    case 'tpe':
                    case 'hp':
                        $uv[$code]['category'] = 'other';
                        break;
                    default:
                        $uv[$code]['category'] = $category;
                }

                $descBegin = $match[0][$i][1] + mb_strlen($match[0][$i][0]);
            }
            if ($descBegin > 0) {
                $uv[$this->sanitizer($match[1][$i - 1][0])] =
                    array_merge(
                    $uv[$this->sanitizer($match[1][$i - 1][0])],
                    $this->parseDesc(mb_substr($text, $descBegin)));
            }
        }

        // Debug usefull to check what's parsed
        /*
        $content = '';
        foreach ($uv as $key => $value) {
            $content .= $key.' : '.$value['name']."\n";
            $content .= "\tSemester: " . (($value['automne'])?'Automne':'').' '.(($value['printemps'])?'Printemps':'')."\n";
            $content .= "\tTarget: " . $value['target']."\n";
            $content .= "\tCredits: " . $value['credits']."\n";
            $content .= "\tCM: " . $value['hours']['cm']."h - TD: " . $value['hours']['td']."h - TP: " . $value['hours']['tp']."h - the: " . $value['hours']['the']."h - prj: " . $value['hours']['prj']."h - \n";
            $content .= "\tCadre: \n" . $value['cadre']."\n";
            $content .= "\tProgramme: \n" . $value['programme']."\n";
            $content .= "\n";
        }
        $content .= "\n\n".count($uv).' UV found';
        file_put_contents(__DIR__.'/../Resources/objects/debug.txt', $content);
        */

        $output->writeln("\n\n".count($uv).' UV found');

        $output->writeln("\nImporting ...");

        $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($uv));
        $bar->update(0);
        $i = 1;

        $entities = [];

        foreach ($uv as $code => $uv) {
            $entity = new UV();

            $entity->setCode($code)
                ->setName($uv['name'])
                ->setCategory($uv['category'])
                ->setAutomne($uv['automne'])
                ->setPrintemps($uv['printemps'])
                ->setObjectifs($uv['cadre'])
                ->setProgramme($uv['programme'].((!empty($uv['comment'])) ? "\n\n".$uv['comment'] : ''))
                ->setCredits($uv['credits'])
                ->setCm($uv['hours']['cm'])
                ->setTd($uv['hours']['td'])
                ->setTp($uv['hours']['tp'])
                ->setThe($uv['hours']['the'] + $uv['hours']['prj']);

            $entities[] = $entity;

            $bar->update($i);
            ++$i;
        }

        $output->writeln("\nWriting registry ...");

        file_put_contents($registry, serialize($entities));

        $output->writeln("Done.\n");
    }

    protected function parseDesc($raw)
    {
        $currentCourseType = null;
        $currentList = null;
        $tops = [];

        $uv = [
            'automne' => false,
            'printemps' => false,
            'cadre' => '',
            'programme' => '',
            'comment' => '',
            'credits' => 0,
            'hours' => [
                'cm' => 0,
                'td' => 0,
                'tp' => 0,
                'the' => 0,
                'prj' => 0,
            ],
        ];

        $lines = explode('</text>', $raw);

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if (mb_strpos($line, 'Automne') !== false) {
                $uv['automne'] = true;
                continue;
            }

            if (mb_strpos($line, 'Printemps') !== false) {
                $uv['printemps'] = true;
                continue;
            }

            if (mb_strpos($line, 'CADRE') !== false) {
                $currentList = 'cadre';
                continue;
            }

            if (mb_strpos($line, 'PROGRAMME') !== false) {
                $currentList = 'programme';
                continue;
            }

            if (preg_match('/top="([0-9]+)" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">\s*C\s*$/', $line, $match)) {
                $tops[$match[1]] = 'cm';
                continue;
            }
            if (preg_match('/top="([0-9]+)" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">\s*TD\s*$/', $line, $match)) {
                $tops[$match[1]] = 'td';
                continue;
            }
            if (preg_match('/top="([0-9]+)" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">\s*TP\s*$/', $line, $match)) {
                $tops[$match[1]] = 'tp';
                continue;
            }
            if (preg_match('/top="([0-9]+)" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">\s*PRJ\s*$/', $line, $match)) {
                $tops[$match[1]] = 'prj';
                continue;
            }
            if (preg_match('/top="([0-9]+)" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">\s*THE\s*$/', $line, $match)) {
                $tops[$match[1]] = 'the';
                continue;
            }

            if (preg_match('/([0-9]+) crédits\s*/i', $line, $match)) {
                $uv['credits'] = (int) $match[1];
                $currentList = '';
                continue;
            }

            if (preg_match('/top="([0-9]+)" left="[0-9]+" width="[0-9]+" height="[0-9]+" font="[0-9]+">\s*([0-9]{1,3}) h\s*$/', $line, $match)) {
                $uv['hours'][$tops[$match[1]]] = (int) $match[2];
                continue;
            }

            // append to cadre and programme
            if (!empty($line) && !empty($currentList) && preg_match('/[a-z]/i', $this->sanitizer($line))) {
                if (mb_strpos(strip_tags($line), "\n  ") === 0) {
                    $uv[$currentList] .= "\n• ".ucfirst($this->sanitizer($line));
                } else {
                    $uv[$currentList] .= ' '.$this->sanitizer($line);
                }
            }

            // append to comment
            if (preg_match('/top="[0-9]+" left="([0-9]+)" width="[0-9]+" height="[0-9]+" font="[0-9]+">(.*)$/', $line, $match) && $match[1] == 43) {
                if (mb_strpos($match[2], '  ') === 0) {
                    $uv['comment'] .= ucfirst($this->sanitizer($line));
                } else {
                    $uv['comment'] .= ' '.$this->sanitizer($line);
                }
            }
        }
        $uv['cadre'] = $this->sanitizer($uv['cadre']);
        $uv['programme'] = $this->sanitizer($uv['programme']);
        $uv['comment'] = $this->sanitizer($uv['comment']);

        return $uv;
    }

    /**
     * Smart ucfirst that understand multibits letters.
     *
     * @param $string
     *
     * @return mixed
     */
    protected function ucfirst($string)
    {
        if (!preg_match('/^[a-z0-9]$/i', mb_substr($string, 0, 1))) {
            $i = 2;
        } else {
            $i = 1;
        }

        $firstLetter = mb_substr($string, 0, $i);
        $wordRest = mb_substr($string, $i);
        $word = mb_strtoupper(StringManipulationExtension::unaccent($firstLetter)).$wordRest;

        return htmlspecialchars(trim(html_entity_decode($word)));
    }

    /**
     * Remove any html elements from text, Remove double spaces and trim.
     *
     * @param $string
     *
     * @return mixed
     */
    protected function sanitizer($string)
    {
        $string = str_replace('ﬁ ', 'fi', $string);
        $string = str_replace(' ,', ',', $string);
        $string = preg_replace('/([A-z])- ([A-z])/', '${1}${2}', $string);

        $string = html_entity_decode($string);
        $string = strip_tags($string);
        $string = preg_replace('/[ \t\r\x0B]+/', ' ', $string);
        $string = trim($string);

        return $string;
    }
}
