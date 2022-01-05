<?php
declare(strict_types=1);

namespace Wizxrd\DamageIndicator;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

class EventListener implements Listener {

    private Main $plugin;
    
    public function __construct(Main $plugin)
    {
    	$this->plugin = $plugin;
    }

    /**
     * @priority MONITOR
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $this->plugin->createSession($event->getPlayer());
    }

    /**
     * @priority MONITOR
     */
    public function onLeave(PlayerQuitEvent $event): void
    {
        $this->plugin->removeSession($event->getPlayer());
    }
    
    /**
     *	@priority MONITOR
     */
    public function onHit(EntityDamageByEntityEvent $ev): void
    {
        $entity = $ev->getEntity();
        $attacker = $ev->getDamager();
        if($entity instanceof Player && $attacker instanceof Player) {
            $damage = $ev->getFinalDamage();
            if($damage > 0 && ($entity->isSurvival()|| $entity->isAdventure())) {
            	$this->plugin->sendIndicator($attacker, $entity->getPosition(), $damage);
            }
        }
    }
}