<?php
declare(strict_types=1);

namespace Wizxrdx\DamageIndicator;

use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class Main extends PluginBase {

    public array $sessionManagers = [];

    public static PluginBase $instance;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 1);
        $this->saveResource("config.yml");
    }

    public static function dataProperty(int|float $damage): EntityMetadataCollection {
        $message = TextFormat::colorize(str_replace("{DAMAGE}", strval($damage), self::$instance->getConfig()->get("message")));

        $data = new EntityMetadataCollection();
        $data->setLong(EntityMetadataProperties::FLAGS, 0);
        $data->setString(EntityMetadataProperties::NAMETAG, $message);
        $data->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.01);
        $data->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.01);
        $data->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
        $data->setFloat(EntityMetadataProperties::SCALE, 0.01);
        $data->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);

        return $data;
    }

    public function createSession(Player $attacker): void {
        $this->sessionManagers[$attacker->getName()] = new SessionManager($attacker);
    }

    public function removeSession(Player $player): void {
        unset($this->sessionManagers[$player->getName()]);
    }

    public function sendIndicator(Player $attacker, Position $victimPos, int|float $damage): void {
        ($this->sessionManagers[$attacker->getName()])->spawn($victimPos, $damage);
    }
}