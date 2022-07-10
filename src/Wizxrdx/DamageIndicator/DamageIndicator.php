<?php

namespace Wizxrdx\DamageIndicator;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;

class DamageIndicator {

    private SessionManager $manager;

    private Vector3 $location;
    private Vector3 $motion;
    private int $despawnAfter = 20;
    private int $id;

    public function __construct(SessionManager $manager, Vector3 $location, Vector3 $motion, int $id) {
        $this->manager = $manager;
        $this->location = $location;
        $this->motion = $motion;
        $this->id = $id;
    }

    private function getOffsetPosition(): Vector3 {
        return $this->location->add(0, 0.125, 0);
    }

    public function onUpdate(): void{
        $this->move();
        $this->sendMovement();
        $this->sendMotion();
        $this->despawnAfter--;
        if ($this->despawnAfter <= 0) {
            $this->despawn();
        }
    }

    private function move(): void{
        $this->location = $this->location->addVector($this->motion);
        $this->motion = $this->motion->divide(2);
    }

    public function sendMovement(): void {
        $pk = MoveActorAbsolutePacket::create(
            $this->id,
            $this->getOffsetPosition(),
            0,
            180,
            0,
            MoveActorAbsolutePacket::FLAG_GROUND
        );
        $this->sendPacket($pk);
    }

    public function sendMotion(): void {
        $pk = SetActorMotionPacket::create($this->id, $this->motion);
        $this->sendPacket($pk);
    }

    public function despawn(): void {
        $pk = RemoveActorPacket::create($this->id);
        $this->sendPacket($pk);
        unset($this->manager->indicators[$this->id]);
    }

    private function sendPacket(ClientboundPacket $pk): void {
        $player = $this->manager->getAttacker();
        if ($player->isOnline()) {
            $player->getNetworkSession()->sendDataPacket($pk);
        }

    }
}