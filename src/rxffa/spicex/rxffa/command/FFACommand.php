<?php
declare(strict_types=1);

namespace rxffa\spicex\rxffa\command;

use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\entity\EntityDataHelper;
use pocketmine\player\Player;
use rxffa\spicex\rxffa\model\GameModel;
use rxffa\spicex\rxffa\model\TopsModel;
use rxffa\spicex\rxffa\RXFFA;
use rxffa\spicex\rxffa\util\Messages;

/**
 * Class FFACommand
 * @package rxffa\spicex\rxffa\command
 */
class FFACommand extends VanillaCommand
{

	/**
	 * FFACommand constructor.
	 */
	public function __construct(public RXFFA $plugin)
	{
		parent::__construct(
			'ffa', Messages::FFA_COMMAND_DESCRIPTION,
			'use /ffa help', ['ffa']
		);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool
	{
		if (!$sender instanceof Player || !isset($args[0])) {
			$sender->sendMessage("/ffa help");
			return false;
		}
		return match ($args[0]) {
			'arena' => isset($args[1]) ? $this->setArena($sender, arena: $args[1]) : $this->getHelp(player: $sender),
			'npc' => $this->setModel(player: $sender, modelType: 'npc'),
			'tops' => $this->setModel(player: $sender, modelType: 'tops'),
			'join' => $this->joinGame(player: $sender),
			'exit' => $this->exitGame(player: $sender),
			default => $this->getHelp(player: $sender),
		};
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	private function getHelp(Player $player): bool
	{
		if ($this->plugin->getServer()->isOp($player->getName())) {
			$player->sendMessage(Messages::HELP_COMMAND_OP);
		} else {
			$player->sendMessage(Messages::HELP_COMMAND_USER);
		}
		return true;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	private function joinGame(Player $player): bool
	{
		$this->plugin->joinGame($player);
		return true;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	private function exitGame(Player $player): bool
	{
		$this->plugin->exitGame($player);
		return true;
	}

	/**
	 * @param Player $player
	 * @param string $arena
	 * @return bool
	 */
	private function setArena(Player $player, string $arena): bool
	{
		if (!$this->plugin->getServer()->getWorldManager()->isWorldGenerated($arena)) {
			$player->sendMessage(Messages::WORLD_NOT_FOUND);
			return false;
		}
		if (!$this->plugin->getServer()->getWorldManager()->isWorldLoaded($arena)) {
			$this->plugin->getServer()->getWorldManager()->loadWorld($arena);
		}
		$this->plugin->getYamlProvider()->setArena($arena);
		return true;
	}

	/**
	 * @param Player $player
	 * @param string $modelType
	 * @return bool
	 */
	private function setModel(Player $player, string $modelType): bool
	{
		$nbt = EntityDataHelper::createBaseNBT(
			$player->getLocation(), $player->getMotion(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch()
		);
		$nbt->setTag('Skin', clone $player->getSaveData()->getCompoundTag('Skin'));
		$npc = match ($modelType) {
			'npc' => new GameModel($player->getLocation(), $player->getSkin(), $nbt),
			'tops' => new TopsModel($player->getLocation(), $player->getSkin(), $nbt),
			default => null,
		};
		$npc?->spawnToAll();
		return true;
	}

}