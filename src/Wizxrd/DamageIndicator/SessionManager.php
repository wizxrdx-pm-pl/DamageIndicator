<?php

namespace Wizxrd\DamageIndicator;

use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;

class SessionManager {

    private Player $attacker;

    /** @var DamageIndicator[] */
    public array $indicators = [];

    public function __construct(Player $attacker)
    {
        $this->attacker = $attacker;
    }

    public function spawn(Vector3 $location, float|int $damage): void
    {
        $motion = new Vector3(0, 0.75, 0);
        $id = $this->getNewId();
        $this->indicators[$this->getNewId()] = new DamageIndicator($this, $location, $motion, $id);
        $pk = AddItemActorPacket::create(
            $id,
            $id,
            ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(ItemFactory::air())),
            $this->calculateTextInitialPosition($location),
            $motion,
            Main::dataProperty($damage)->getAll(),
            false
        );
        $this->attacker->getNetworkSession()->sendDataPacket($pk);
    }

    private function calculateTextInitialPosition(Vector3 $victimPos): Vector3
    {
        // TODO: Check the Entity height
        $attackerPos = $this->attacker->getPosition();
        $dist = ($attackerPos->distance($victimPos) / 0.25);
        if ($dist != 0) {
            return $victimPos->add(
                ($attackerPos->getX() - $victimPos->getX()) / $dist,
                0.80,
                ($attackerPos->getZ() - $victimPos->getZ()) / $dist
            );
        }
        return $victimPos->add(0, 0.80, 0);
    }

    public function onUpdate(): void
    {
        foreach ($this->indicators as $indicator)
        {
            $indicator->onUpdate();
        }
    }

    private function getNewId(): int
    {
        return mt_rand() * 69420; // Cuz y not :P
    }

    public function getAttacker(): Player
    {
        return $this->attacker;
    }

    public function despawnAll(): void
    {
        foreach ($this->indicators as $indicator)
        {
            $indicator->despawn();
        }
    }
}