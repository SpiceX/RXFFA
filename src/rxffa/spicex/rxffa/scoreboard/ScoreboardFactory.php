<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\scoreboard;

use JetBrains\PhpStorm\Pure;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use rxffa\spicex\rxffa\RXFFA;

/**
 * Class ScoreboardFactory
 * @package rxffa\spicex\rxffa\scoreboard
 */
class ScoreboardFactory
{
	/** @var array Scoreboard[] */
	private static array $scoreboards = [];

	public static function init(){
		RXFFA::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			if (empty(self::$scoreboards)) return;
			/** @var Scoreboard $scoreboard */
			foreach (self::$scoreboards as $scoreboard) {
				if (!$scoreboard->isSpawned()){
					$scoreboard->spawn('§6RX§eFFA', Scoreboard::SORT_ASCENDING);
				}
				$scoreboard->removeLines();
				$scoreboard->setLines([
					1 => ' §f-------------- ',
					2 => ' §f§fKills: §e{KILLS} ',
					3 => ' §fDeaths: §e{DEATHS} ',
					4 => ' §fKDR: §e{KDR} ',
					5 => " ",
					6 => ' §f--§8(§6SpiceX§8)§f-- ',
				]);
			}
		}), 20);
	}

	/**
	 * @param Player $player
	 */
	public static function createScoreboard(Player $player): void {
		ScoreboardFactory::$scoreboards[$player->getName()] = new Scoreboard($player);
	}

	/**
	 * @return Scoreboard[]
	 */
	public static function getScoreboards(): array
	{
		return self::$scoreboards;
	}

	/**
	 * @param Player $player
	 * @return Scoreboard|null
	 */
	#[Pure]
	public static function getScoreboard(Player $player): Scoreboard|null{
		return self::$scoreboards[$player->getName()] ?? null;
	}

	/**
	 * @param Player $player
	 */
	public static function removeSecoreboard(Player $player){
		unset(self::$scoreboards[$player->getName()]);
	}

}