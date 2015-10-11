<?php

namespace Etu\Core\CoreBundle\Util\Server;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class Status
{
	protected $name;
	protected $load;
	protected $memory = array();
	protected $swap = array();
	protected $processor = array();
	protected $processes = array();
	protected $versions = array();
	protected $upTime;
	protected $mountPoints = array();

	public function __construct()
	{
		$this->initName();
		$this->initLoad();
		$this->initProcessor();
		$this->initRAM();
		$this->initVersions();
		$this->initUpTime();
		$this->initMountPoints();
		$this->initTop();
	}

	public function getLoad()
	{
		return $this->load;
	}

	public function getMemory()
	{
		return $this->memory;
	}

	public function getMountPoints()
	{
		return $this->mountPoints;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getProcesses()
	{
		return $this->processes;
	}

	public function getProcessor()
	{
		return $this->processor;
	}

	public function getSwap()
	{
		return $this->swap;
	}

	public function getUpTime()
	{
		return $this->upTime;
	}

	public function getVersions()
	{
		return $this->versions;
	}

	protected function initName()
	{
		exec('uname -a', $output);

		$this->name = $output[0];
	}

	protected function initLoad()
	{
		exec('cat /proc/loadavg', $load);

		$this->load = $load[0];
	}

	protected function initProcessor()
	{
		exec('cat /proc/cpuinfo | grep "model name\\|processor"', $output);

		$procModel = explode(':', $output[1]);
		$this->processor['name'] = trim($procModel[1]);

		$procUsage = explode(':', $output[0]);
		$this->processor['usage'] = trim($procUsage[1]);
	}

	protected function initRAM()
	{
		exec('free -m', $output);

		$memoryUsage = preg_split('/\s+/', $output[1]);

		$this->memory = array(
			'total' => $memoryUsage[1],
			'used' => $memoryUsage[2],
			'free' => $memoryUsage[3],
			'shared' => $memoryUsage[4],
			'buffers' => $memoryUsage[5],
			'cached' => $memoryUsage[6],
		);

		$swapUsage = preg_split('/\s+/', $output[3]);

		$this->swap = array(
			'total' => $swapUsage[1],
			'used' => $swapUsage[2],
			'free' => $swapUsage[3],
		);
	}

	protected function initVersions()
	{
		$this->versions = array(
			'php' => phpversion(),
			'mysql' => mysqli_get_client_info(),
			'zend' => zend_version(),
		);
	}

	protected function initUpTime()
	{
		$ut = strtok(exec('cat /proc/uptime'), '.');
		$days = sprintf('%2d', ($ut/(3600*24)));
		$hours = sprintf('%2d', ( ($ut % (3600*24)) / 3600));
		$min = sprintf('%2d', ($ut % (3600*24) % 3600)/60);
		$sec = sprintf('%2d', ($ut % (3600*24) % 3600)%60);

		$this->upTime = array(
			'days' => trim($days),
			'hours' => trim($hours),
			'min' => trim($min),
			'sec' => trim($sec)
		);
	}

	protected function initMountPoints()
	{
		exec('df -h', $output);

		unset($output[0]);

		foreach ($output as $line) {
			$parts = preg_split('/\s+/', $line);

			$this->mountPoints[] = array(
				'name' => $parts[0],
				'size' => $parts[1],
				'used' => $parts[2],
				'available' => $parts[3],
				'usedPercentage' => $parts[4],
				'mountPoint' => $parts[5]
			);
		}
	}

	protected function initTop()
	{
		exec('top -b -n 1', $output);

		foreach ($output as $key => $line) {
			unset($output[$key]);

			if (empty($line)) {
				break;
			}
		}

		unset($output[$key + 1]);

		$i = 0;

		foreach ($output as $line) {
			if ($i == 10) {
				break;
			}

			$i++;

			$parts = preg_split('/\s+/', $line);

			$this->processes[] = array(
				'id' => $parts[1],
				'user' => $parts[2],
				'priority' => $parts[3],
				'cpu' => $parts[9],
				'memory' => $parts[10],
				'time' => $parts[11],
				'command' => (isset($parts[12])) ? $parts[12] : 'none',
			);
		}
	}
}
