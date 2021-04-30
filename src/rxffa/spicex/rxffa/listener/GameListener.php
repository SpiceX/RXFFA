<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\listener;

use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use rxffa\spicex\rxffa\event\PlayerGameDeathEvent;
use rxffa\spicex\rxffa\event\PlayerGameKillEvent;
use rxffa\spicex\rxffa\RXFFA;
use rxffa\spicex\rxffa\session\SessionFactory;
use rxffa\spicex\rxffa\util\Messages;

/**
 * Class GameListener
 * @package rxffa\spicex\rxffa\listener
 */
class GameListener implements Listener
{

	/**
	 * YamlDataProvider constructor.
	 */
	public function __construct(public RXFFA $plugin)
	{

	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onEntityDamageEvent(EntityDamageEvent $event): void {
		$victim = $event->getEntity();
		if (!$victim instanceof Player) return;
		$victimSession = SessionFactory::getSession($victim);
		if ($victimSession === null) return;
		if ($victim->getLocation()->equals($this->plugin->getYamlProvider()->getWorldArena()->getSafeSpawn())){
			$event->cancel();
		}
		if ($event instanceof EntityDamageByEntityEvent){
			$killer = $event->getDamager();
			if ($event->getFinalDamage() > $victim->getHealth()){
				$event->cancel();
				$victim->teleport($victim->getWorld()->getSafeSpawn());
				if ($killer instanceof Player && $killer->isOnline()) {
					$this->broadcastKill($killer, $victim);
				}
			}
		}
		if ($event instanceof EntityDamageByChildEntityEvent){
			$projectile = $event->getDamager();
			if($projectile instanceof Projectile){
				$projectileShooter = $projectile->getOwningEntity();
				if ($event->getFinalDamage() > $victim->getHealth() && $victim !== $projectileShooter){
					$event->cancel();
					$victim->teleport($victim->getWorld()->getSafeSpawn());
					$this->broadcastKill($projectileShooter, $victim);
				}
			}
		}
		if ($event->getFinalDamage() > $victim->getHealth()){
			$event->cancel();
			$victim->teleport($victim->getWorld()->getSafeSpawn());
			$ev = new PlayerGameDeathEvent($victim, $this->plugin);
			$ev->call();
		}

	}

	/**
	 * @param Player $killer
	 * @param Player $victim
	 */
	private function broadcastKill(Player $killer, Player $victim): void {
		$victimSession = SessionFactory::getSession($victim);
		$killerSession = SessionFactory::getSession($killer);
		$victimSession->addDeath();
		$killerSession->addKill();
		$killer->sendMessage(str_replace('{victim}', $victim->getName(), Messages::KILL_MESSAGE));
		foreach ($killer->getWorld()->getPlayers() as $player) {
			$player->sendMessage(str_replace(
				['{victim}', '{killer}'], [$victim->getName(), $killer->getName()], Messages::KILL_MESSAGE)
			);
		}
		$ev = new PlayerGameKillEvent($killer, $this->plugin);
		$ev->call();

		$ev = new PlayerGameDeathEvent($victim, $this->plugin);
		$ev->call();
	}

}