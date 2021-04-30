<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\model;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use rxffa\spicex\rxffa\RXFFA;
use rxffa\spicex\rxffa\session\SessionFactory;
use rxffa\spicex\rxffa\util\Messages;

/**
 * Class GameModel
 * @package rxffa\spicex\rxffa\model
 */
class GameModel extends Human
{
	/**
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source): void
	{
		if ($source instanceof EntityDamageByEntityEvent) {
			if ($source->getDamager() instanceof Player) {
				RXFFA::getInstance()->joinGame($source->getDamager());
				return;
			}
		}
	}

	/**
	 * @param int $currentTick
	 * @return bool
	 */
	public function onUpdate(int $currentTick): bool
	{
		$this->setNameTagVisible(true);
		$this->setNameTagAlwaysVisible(true);
		if ($this->getScale() != 1.2) $this->setScale(1.2);
		$this->setNameTag(Messages::GAME_NPC_NAMETAG . SessionFactory::getSessionCount());
		return parent::onUpdate($currentTick);
	}


	/**
	 * @param float $distanceThisTick
	 * @param bool $onGround
	 */
	protected function updateFallState(float $distanceThisTick, bool $onGround): void
	{
		$this->resetFallDistance();
	}
}