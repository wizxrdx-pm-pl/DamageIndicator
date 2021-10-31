<?php
declare(strict_types=1);

namespace Wizxrd\damageindicator;

use pocketmine\scheduler\Task;

class RemoveItemTask extends Task
{
    private $plugin;
    
    public function __construct($plugin)
    {
		$this->plugin = $plugin;
    }

	public function onRun(int $tick)
    {
        $passedTime = time() - $this->plugin->startup;
        $this->plugin->packets;
        foreach ($this->plugin->packets as $playerName => $time)
        {
            if ($time <= $passedTime)
            {
                unset($this->plugin->packets[$playerName]);
                $player = $this->plugin->getServer()->getPlayer($playerName);
                $this->plugin->removeItemActor($player);
            }
        }
    }
}