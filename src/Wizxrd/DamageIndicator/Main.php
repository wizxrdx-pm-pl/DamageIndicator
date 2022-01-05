<?php
declare(strict_types=1);

namespace Wizxrd\DamageIndicator;

use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class Main extends PluginBase
{
    public array $sessionManagers = [];
    
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 1);
    }

    public static function dataProperty(int|float $damage): EntityMetadataCollection
    {
        $data = new EntityMetadataCollection();

        $data->setLong(EntityMetadataProperties::FLAGS, 0);
        $data->setString(EntityMetadataProperties::NAMETAG, TextFormat::RED."- ".$damage);
        $data->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.01);
        $data->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.01);
        $data->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
        $data->setFloat(EntityMetadataProperties::SCALE, 0.01);
        $data->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);

        return $data;
    }

    public function createSession(Player $attacker): void
    {
        $this->sessionManagers[$attacker->getName()] = new SessionManager($attacker);
    }

    public function removeSession(Player $player): void
    {
        ($this->sessionManagers[$player->getName()])->despawnAll();
    }

    public function sendIndicator(Player $attacker, Position $victimPos, int|float $damage): void
    {
        ($this->sessionManagers[$attacker->getName()])->spawn($victimPos, $damage);
    }
}