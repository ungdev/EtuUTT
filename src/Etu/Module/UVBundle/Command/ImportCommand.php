<?php

namespace Etu\Module\UVBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

use Guzzle\Http;

use Etu\Core\UserBundle\Command\Util\ProgressBar;
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
			->addArgument('file', InputArgument::OPTIONAL, 'The file URL to download')
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
		if ($input->hasArgument('file')) {
			$url = $input->getArgument('file');
			$local = false;
			$output->writeln("\nDownloading ...");
		} elseif (file_exists(__DIR__.'/../Resources/objects/current-guide.pdf')) {
			$url = __DIR__.'/../Resources/objects/current-guide.pdf';
			$local = true;
			$output->writeln("\nUsing cached file");
		} else {
			throw new \RuntimeException('An URL where to download the guide is required');
		}

		$file = __DIR__.'/../Resources/objects/current-guide.pdf';
		$html = __DIR__.'/../Resources/objects/current-guide.html';
		$registry = __DIR__.'/../Resources/objects/registry.json';

		if (! $local) {
			$client = new Http\Client();

			// Create a request
			$response = $client->get($url)->send();

			file_put_contents($file, $response->getBody(true));
		}

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
		$list = explode('<b>F/ MASTER</b><br/>', $list[1]);
		$list = $list[0];

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
				'name' => $match[2][$key],
				'category' => 'other',
				'data' => $this->analyseHtml($desc[1])
			);
		}

		$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($uvs));
		$bar->update(0);
		$i = 1;

		// Then find category
		foreach ($uvs as $key => $uv) {
			$category = 'other';

			preg_match('/(CS|TM|CT|ME|EC|ST)<br\/>
'.preg_quote($uv['code']).'/iU', $list, $match);

			if (! isset($match[1])) {
				preg_match('/(CS|TM|CT|ME|EC|ST)&#160;'.preg_quote($uv['code']).'/iU', $list, $match);

				if (! isset($match[1])) {
					if (substr($uv['code'], 0, 2) == 'TN') {
						$category = 'ST';
					}
				} else {
					$category = $match[1];
				}
			} else {
				$category = $match[1];
			}

			$uvs[$key]['category'] = strtolower($category);

			$bar->update($i);
			$i++;
		}

		$output->writeln("\nImporting ...");

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
				->setCm($uv['data']['hours']['cm']);

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
			'objectifs' => '',
			'programme' => '',
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

			if (strpos($line, 'Automne') !== false) {
				$uv['automne'] = true;
				continue;
			}

			if (strpos($line, 'Printemps') !== false) {
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

			$line = strip_tags(str_replace('<br/>', ' ', $line));

			if (! empty($line) && ! empty($currentList) && preg_match('/[a-z]/i', $line)) {
				$uv[$currentList] .= $line.', ';
			}
		}

		$sanitizer = function($string) {
			$string = str_replace(' ,', ',', $string);
			return trim($string, ' ,');
		};

		$uv['programme'] = $sanitizer($this->ucfirst($uv['programme']));
		$uv['objectifs'] = $sanitizer($this->ucfirst($uv['objectifs']));

		return $uv;
	}

	/**
	 * Smart ucfirst that understand multibits letters
	 *
	 * @param $string
	 * @return mixed
	 */
	protected function ucfirst($string)
	{
		if (! preg_match('/^[a-z0-9]$/i', substr($string, 0, 1))) {
			$i = 2;
		} else {
			$i = 1;
		}

		$firstLetter = substr($string, 0, $i);
		$wordRest = substr($string, $i);
		$word = strtoupper(StringManipulationExtension::unaccent($firstLetter)).$wordRest;

		return htmlspecialchars(trim(html_entity_decode($word)));
	}
}
