<?php
declare(strict_types=1);

namespace Wizxrd\damageindicator;

use pocketmine\scheduler\Task;

class RemoveItemTask extends Task
{
	/** @var DamageIndicator */
    private $plugin;
    
    public function __construct(DamageIndicator $plugin)
    {
		$this->plugin = $plugin;
    }

	public function onRun(int $tick): void
    {
        $timePassed = time() - $this->plugin->startup;
        $this->plugin->packets;
        foreach ($this->plugin->packets as $playerName => $time)
        {
            if ($time <= $timePassed)
            {
                unset($this->plugin->packets[$playerName]);
                $player = $this->plugin->getServer()->getPlayer($playerName);
                $this->plugin->removeItemActor($player);
            }
        }
    }
}
