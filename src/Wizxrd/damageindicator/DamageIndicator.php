<?php
declare(strict_types=1);

namespace Wizxrd\damageindicator;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\MotionPredictionHintsPacket;
use pocketmine\entity\Entity;
use pocketmine\entity\DataPropertyManager;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class DamageIndicator extends PluginBase
{
    public $packets = [];
    public $startup;
    
    public function onEnable() 
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->startup = time();
        $this->getScheduler()->scheduleRepeatingTask(new RemoveItemTask($this), 20);
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() === "drop")
        {
            $this->removeItemActor($sender);
            $this->addItemActor($sender, $sender->asVector3(), "Test");
        }
        return true;
    }
    
    public function addItemActor($player, $pos, $text)
    {
        $dist = ($player->distance($pos) / 0.25);
        $vector3 = $pos->add(($player->getX() - $pos->getX()) / $dist, 0.5, ($player->getZ() - $pos->getZ()) / $dist);
        $pk = new AddItemActorPacket();
        $pk->entityUniqueId = 1100;
        $pk->entityRuntimeId = 1100;
        $pk->position = $vector3;
        $pk->motion = new Vector3(0, 0.05, 0);
        $pk->item = ItemStackWrapper::legacy(Item::get(0, 0, 1));
        $pk->metadata = $this->dataProperty($text)->getAll();
        $player->dataPacket($pk);
        $this->packets[$player->getName()] = (time()-($this->startup))+2;
    }
    
    public function moveItemActor($player)
    {
        $pk = MotionPredictionHintsPacket::create(
        1100,
        new Vector3(0, 0.05, 0),
        false
        );
        $player->dataPacket($pk);
    }
    
    public function removeItemActor($player)
    {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = 1100;
        $player->dataPacket($pk);
    }
    
    private function dataProperty($text)
    {
        $data = new DataPropertyManager();
        
        $data->setLong(Entity::DATA_FLAGS, 0);
		$data->setShort(Entity::DATA_MAX_AIR, 400);
		$data->setString(Entity::DATA_NAMETAG, "§c- " . strval($text));
		$data->setLong(Entity::DATA_LEAD_HOLDER_EID, -1);
		$data->setFloat(Entity::DATA_SCALE, 1);
		$data->setFloat(Entity::DATA_BOUNDING_BOX_WIDTH, 0.25);
		$data->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, 0.25);
        $data->setByte(Entity::DATA_ALWAYS_SHOW_NAMETAG, 1);
        $data->setFloat(Entity::DATA_SCALE, 0.01);
        
        $flags = (int) $data->getPropertyValue(Entity::DATA_FLAGS, Entity::DATA_TYPE_LONG);
		$flags ^= 1 << (Entity::DATA_FLAG_AFFECTED_BY_GRAVITY % 64);
        $data->setPropertyValue(Entity::DATA_FLAGS, Entity::DATA_TYPE_LONG, $flags);
        
        return $data;
    }
}
?>