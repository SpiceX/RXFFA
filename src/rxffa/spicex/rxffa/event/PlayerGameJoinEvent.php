<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\event;

use JetBrains\PhpStorm\Pure;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

/**
 * Class PlayerGameJoinEvent
 * @package rxffa\spicex\rxffa\event
 */
class PlayerGameJoinEvent extends PluginEvent implements Cancellable
{
	use CancellableTrait;

	/**
	 * PlayerGameJoinEvent constructor.
	 */
	#[Pure] public function __construct(private Player $player, Plugin $plugin)
	{
		parent::__construct($plugin);
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->player;
	}
}