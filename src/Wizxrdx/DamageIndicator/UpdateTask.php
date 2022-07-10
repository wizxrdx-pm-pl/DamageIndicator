<?php
declare(strict_types=1);

namespace Wizxrdx\DamageIndicator;

use pocketmine\scheduler\Task;

class UpdateTask extends Task {
    private Main $plugin;
    
    public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }

    public function onRun(): void {
        foreach ($this->plugin->sessionManagers as $session) {
            $session->onUpdate();
        }
    }
}