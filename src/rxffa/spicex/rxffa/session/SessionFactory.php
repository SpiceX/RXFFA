<?php
declare(strict_types=1);

namespace rxffa\spicex\rxffa\session;

use JetBrains\PhpStorm\Pure;
use pocketmine\player\Player;
use rxffa\spicex\rxffa\RXFFA;

/**
 * Class SessionFactory
 * @package rxffa\spicex\rxffa\session
 */
class SessionFactory
{
	/** @var array */
	private static array $sessions = [];

	/**
	 * @param Player $player
	 */
	public static function createSession(Player $player): void {
		$plugin = RXFFA::getInstance();
		$playerExists = $plugin->getYamlProvider()->playerExists($player);
		if ($playerExists) {
			$data = $plugin->getYamlProvider()->getPlayerData($player);
			SessionFactory::$sessions[$player->getName()] = new Session($player, $data['kills'], $data['deaths']);
		} else {
			SessionFactory::$sessions[$player->getName()] = new Session($player);
		}
	}

	/**
	 * @return int
	 */
	public static function getSessionCount(): int {
		return count(self::$sessions);
	}

	/**
	 * @return Session[]
	 */
	public static function getSessions(): array
	{
		return self::$sessions;
	}

	/**
	 * @param Player $player
	 * @return Session|null
	 */
	#[Pure]
	public static function getSession(Player $player): Session|null{
		return self::$sessions[$player->getName()] ?? null;
	}

	/**
	 * @param Player $player
	 */
	public static function removeSession(Player $player){
		unset(self::$sessions[$player->getName()]);
	}

}