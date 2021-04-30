<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\session;

use pocketmine\player\Player;
use rxffa\spicex\rxffa\RXFFA;

/**
 * Class Session
 * @package rxffa\spicex\rxffa\session
 */
class Session
{

	/**
	 * Session constructor.
	 */
	public function __construct(private Player $player, private int $kills = 0, private int $deaths = 0)
	{
	}

	/**
	 * @return int
	 */
	public function getKills(): int
	{
		return $this->kills;
	}

	public function addKill(): void
	{
		$this->kills++;
	}

	/**
	 * @return int
	 */
	public function getDeaths(): int
	{
		return $this->deaths;
	}

	public function addDeath(): void
	{
		$this->deaths++;
	}

	/**
	 * @return Player|null
	 */
	public function getPlayer(): Player|null
	{
		return $this->player;
	}


	public function save(): void
	{
		$config = RXFFA::getInstance()->getYamlProvider();
		$config->saveSession($this);
	}

}