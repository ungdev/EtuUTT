<?php

namespace Etu\Module\UVBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Guzzle\Http;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\UVBundle\Entity\UV;

class ImportCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:uv:import')
			->setDescription('Import UV informations from the PDF official guide')
			->addArgument('file', InputArgument::REQUIRED, 'The file URL to download')
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
		/*
		 * Is pdftohtml installed?
		 */
		$out = shell_exec('dpkg -s poppler-utils');
		$popplerInstalled = strpos($out, 'Status') !== false;

		if (! $popplerInstalled) {
			throw new \RuntimeException(
				"poppler-utils is required to import UV from the official guide.\n".
				"Install it using : sudo apt-get install poppler-utils"
			);
		}

		$output->writeln('
	Welcome to the EtuUTT UV manager

This command helps you to import the official UV guide.');

		/*
		 * Start the process
		 */
		// Download the guide
		$url = $input->getArgument('file');

		$output->writeln("\nDownloading ...");

		$file = __DIR__.'/../Resources/objects/current-guide.pdf';
		$html = __DIR__.'/../Resources/objects/current-guide.html';
		$registry = __DIR__.'/../Resources/objects/registry.json';

		$client = new Http\Client();

		// Create a request with basic Auth
		$response = $client->get($url)->send();

		file_put_contents($file, $response->getBody(true));

		$output->writeln("Converting to HTML ...");

		/*
		 * Analyse it
		 */
		// Convert
		shell_exec('pdftohtml -i -noframes "'.$file.'" "'.$html.'"');

		// Explode in two parts : the list in which we are going to find the UV categories
		// (CS, TM, CT, ...) and the details, in which we are going to find all the other informations
		$html = file_get_contents($html);

		$output->writeln("Analyzing ...");

		$parts = explode('Tronc Commun<br/>
Connaissances<br/>
Scientifiques<br/>', $html);

		$list = explode('<b>B/ TECHNOLOGIE&#160;<br/>&amp; SCIENCES DE L’HOMME&#160;</b><br/>', $parts[0]);
		$list = $list[1];

		$details = explode('Index alphabétique des UV', $parts[1]);
		$details = $details[0];

		preg_match_all('/<b>(.+)&#160;<\/b>(.+)<br\/>/i', $details, $match);

		// First find the basic informations
		$uvs = array();

		foreach ($match[1] as $key => $name) {
			if (isset($match[1][$key + 1])) {
				preg_match('/'.preg_quote($match[1][$key]).'&#160;<\/b>.+<br\/>(.+)'.preg_quote($match[1][$key + 1]).'/isU', $details, $desc);
			} else {
				preg_match('/'.preg_quote($match[1][$key]).'&#160;<\/b>.+<br\/>(.+)$/isU', $details, $desc);
			}

			$code = str_replace('&#160;', '', $name);

			if (strlen($code) > 8) {
				continue;
			}

			$uvs[] = array(
				'code' => str_replace('&#160;', '', $name),
				'name' => $this->ufirst($match[2][$key]),
				'category' => 'other',
				'data' => $this->analyseHtml($desc[1])
			);
		}

		// Then find category
		foreach ($uvs as $key => $uv) {
			$pos = strpos($list, $uv['code']);

			if ($pos !== false) {
				$pos -= 2;
				$string = substr($list, $pos, 2);

				while (! in_array($string, array('CS', 'TM', 'CT', 'ME', 'EC', 'ST'))) {
					$pos--;
					$string = substr($list, $pos, 2);
				}

				$uvs[$key]['category'] = strtolower($string);
			}
		}

		$output->writeln("Importing ...");

		$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($uvs));
		$bar->update(0);
		$i = 1;

		$entities = array();

		foreach ($uvs as $uv) {
			$entity = new UV();

			$entity->setCode($uv['code'])
				->setName($uv['name'])
				->setCategory($uv['category'])
				->setAutomne($uv['data']['automne'])
				->setPrintemps($uv['data']['printemps'])
				->setObjectifs($uv['data']['objectifs'])
				->setProgramme($uv['data']['programme'])
				->setCredits($uv['data']['credits'])
				->setCm($uv['data']['hours']['cm'])
				->setTd($uv['data']['hours']['td'])
				->setTp($uv['data']['hours']['tp'])
				->setThe($uv['data']['hours']['the'])
				->setCm($uv['data']['hours']['cm'])
				->setTarget($uv['data']['target']);

			$entities[] = $entity;

			$bar->update($i);
			$i++;
		}

		$output->writeln("\nWriting registry ...");

		file_put_contents($registry, serialize($entities));

		$output->writeln("Done.\n");
	}

	protected function analyseHtml($html)
	{
		$lines = preg_split('/\r\n|\n|\r/', $html);

		$currentCourseType = null;
		$currentList = null;

		$uv = array(
			'automne' => false,
			'printemps' => false,
			'objectifs' => array(),
			'programme' => array(),
			'credits' => 0,
			'target' => 'ing',
			'hours' => array(
				'cm' => 0,
				'td' => 0,
				'tp' => 0,
				'the' => 0,
			),
		);

		foreach ($lines as $line) {
			if (empty($line)) {
				continue;
			}

			if (strpos($line, 'Objectifs :') !== false) {
				$currentList = 'objectifs';
				continue;
			}

			if (strpos($line, 'Programme :') !== false) {
				$currentList = 'programme';
				continue;
			}

			if (strpos($line, '<b>C</b>') !== false) {
				$currentCourseType = 'cm';
				continue;
			}

			if (strpos($line, '<b>TD</b>') !== false) {
				$currentCourseType = 'td';
				continue;
			}

			if (strpos($line, '<b>TP</b>') !== false) {
				$currentCourseType = 'tp';
				continue;
			}

			if (strpos($line, '<b>THE</b>') !== false) {
				$currentCourseType = 'the';
				continue;
			}

			if (strpos($line, '<b>Automne</b>') !== false) {
				$uv['automne'] = true;
				continue;
			}

			if (strpos($line, '<b>Printemps</b>') !== false) {
				$uv['printemps'] = true;
				continue;
			}

			if (strpos($line, 'UV mast. et ing.') !== false) {
				$uv['target'] = 'both';
				continue;
			}

			if (strpos($line, 'UV mast.') !== false) {
				$uv['target'] = 'mast';
				continue;
			}

			if (strpos($line, 'UV ing.') !== false) {
				$uv['target'] = 'ing';
				continue;
			}

			if (preg_match('/<b>([0-9]+) crédits<\/b>/i', $line, $match)) {
				$uv['credits'] = (int) $match[1];
				continue;
			}

			if (preg_match('/<b>([0-9]+)h<\/b>/i', $line, $match)) {
				$uv['hours'][$currentCourseType] = (int) $match[1];
				continue;
			}

			$line = html_entity_decode(strip_tags($line));

			if (! empty($line)) {
				$uv[$currentList][] = $this->ufirst($line);
			}
		}

		return $uv;
	}

	protected function ufirst($string)
	{
		$string = utf8_decode($string);
		$string[0] = StringManipulationExtension::unaccent($string[0]);
		return utf8_encode(ucfirst($string));
	}
}
