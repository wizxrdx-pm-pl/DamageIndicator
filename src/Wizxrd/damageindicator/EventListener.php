<?php
declare(strict_types=1);

namespace Wizxrd\damageindicator;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\types\GameMode;

class EventListener implements Listener
{
    private $plugin;
    
    public function __construct($plugin)
    {
    	$this->plugin = $plugin;
    }
    
    /**
     *	@priority MONITOR
     */
    public function onHit(EntityDamageByEntityEvent $ev)
    {
        $entity = $ev->getEntity();
        $damager = $ev->getDamager();
        if ($entity instanceof Player && $damager instanceof Player)
        {
            $damage = $ev->getFinalDamage();
            if ($damage > 0 && ($entity->getGamemode() == GameMode::SURVIVAL || $entity->getGamemode() == GameMode::ADVENTURE))
            {
            	$this->plugin->removeItemActor($damager);
            	$this->plugin->addItemActor($damager, $entity->asVector3(), $damage);
                // $this->plugin->moveItemActor($damager);
            }
        }
    }
}
?>