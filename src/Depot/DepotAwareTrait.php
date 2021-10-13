<?php

namespace Smarty2\Depot;

use Smarty2\Depot\DepotInterface;

trait DepotAwareTrait
{
	protected DepotInterface $compiledDepot;

	function getCompiledDepot() : DepotInterface
	{
		return $this->compiledDepot;
	}

	function setCompiledDepot(DepotInterface $compiledDepot)
	{
		$this->compiledDepot = $compiledDepot;
	}
}
