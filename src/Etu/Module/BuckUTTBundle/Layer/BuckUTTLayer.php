<?php

namespace Etu\Module\BuckUTTBundle\Layer;

use Etu\Module\BuckUTTBundle\Layer\History\HistoryItem;
use Etu\Module\BuckUTTBundle\Soap\SoapManager;
use Etu\Module\BuckUTTBundle\Soap\SoapManagerBuilder;

class BuckUTTLayer
{
	/**
	 * @var SoapManagerBuilder
	 */
	protected $builder;

	/**
	 * @param SoapManagerBuilder $builder
	 */
	public function __construct(SoapManagerBuilder $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * @param \DateTime $start
	 * @param \DateTime $end
	 * @return array
	 */
	public function getHistoryBetween(\DateTime $start, \DateTime $end)
	{
		$history = array();
		$dates = array();

		/** @var SoapManager $client */
		$client = $this->builder->createManager('SADMIN');

		$purchases = $client->getHistoriqueAchats($start->format('U'), $end->format('U'));

		if((int) $purchases == 400){
			return array();
		}

		foreach ($purchases as $purchase) {
			$item = new HistoryItem();
			$item->setType(HistoryItem::TYPE_BUY)
				->setDate(\DateTime::createFromFormat('U', $purchase[0]))
				->setObject($purchase[1])
				->setUser($purchase[2].' '.$purchase[3])
				->setPoint($purchase[4])
				->setFundation($purchase[5])
				->setAmount(number_format($purchase[6]/100, 2));

			$history[] = $item;
			$dates[] = $purchase[0];
		}

		$reloads = $client->getHistoriqueRecharge($start->format('U'), $end->format('U'));

		if((int) $reloads == 400){
			array_multisort($dates, SORT_DESC, $history);
			return $history;
		}

		foreach ($reloads as $reload) {
			$item = new HistoryItem();
			$item->setType(HistoryItem::TYPE_RELOAD)
				->setDate(\DateTime::createFromFormat('U', $reload[0]))
				->setObject($reload[1])
				->setPoint($reload[4])
				->setAmount(number_format($reload[6]/100, 2));

			$history[] = $item;
			$dates[] = $reload[0];
		}

		array_multisort($dates, SORT_DESC, $history);

		return $history;
	}
}
