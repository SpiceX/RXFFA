<?php
/**
 * Copyright 2021-2022 SpiceX
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);
namespace rxffa\spicex\rxffa;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use rxffa\spicex\rxffa\command\FFACommand;
use rxffa\spicex\rxffa\event\PlayerGameJoinEvent;
use rxffa\spicex\rxffa\event\PlayerGameQuitEvent;
use rxffa\spicex\rxffa\listener\GameListener;
use rxffa\spicex\rxffa\model\GameModel;
use rxffa\spicex\rxffa\model\TopsModel;
use rxffa\spicex\rxffa\provider\YamlDataProvider;
use rxffa\spicex\rxffa\scoreboard\ScoreboardFactory;
use rxffa\spicex\rxffa\session\SessionFactory;
use rxffa\spicex\rxffa\util\Messages;

/**
 * Class RXFFA
 * @package rxffa\spicex\rxffa
 */
class RXFFA extends PluginBase implements Listener
{
	/** @var RXFFA */
	private static RXFFA $instance;
	/** @var YamlDataProvider */
	private YamlDataProvider $yamlProvider;

	public function onEnable(): void
	{
		self::$instance = $this;
		$this->yamlProvider = new YamlDataProvider($this);
		$this->getServer()->getPluginManager()->registerEvents(new GameListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register('ffa', new FFACommand($this), 'ffa');
		EntityFactory::getInstance()->register(GameModel::class, function (World $world, CompoundTag $nbt): GameModel {
			return new GameModel(EntityDataHelper::parseLocation($nbt, $world), GameModel::parseSkinNBT($nbt), $nbt);
		}, ['GameModel']);
		EntityFactory::getInstance()->register(TopsModel::class, function (World $world, CompoundTag $nbt): TopsModel {
			return new TopsModel(EntityDataHelper::parseLocation($nbt, $world), TopsModel::parseSkinNBT($nbt), $nbt);
		}, ['TopsModel']);
		ScoreboardFactory::init();
	}

	public function onDisable(): void
	{
		$this->saveSessions();
	}

	/**
	 * @param Player $player
	 */
	public function joinGame(Player $player): void
	{
		SessionFactory::createSession($player);
		ScoreboardFactory::createScoreboard($player);
		$arena = $this->getServer()->getWorldManager()->getWorldByName($this->yamlProvider->getArena());
		$items = [
			ItemFactory::getInstance()->get(ItemIds::DIAMOND_SWORD)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS())),
			ItemFactory::getInstance()->get(ItemIds::STEAK, 0, 32),
			ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3),
			ItemFactory::getInstance()->get(ItemIds::BOW, 0, 1),
			ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 16),
		];
		$helmet = ItemFactory::getInstance()->get(ItemIds::DIAMOND_HELMET)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
		$chestplate = ItemFactory::getInstance()->get(ItemIds::DIAMOND_CHESTPLATE)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
		$leggings = ItemFactory::getInstance()->get(ItemIds::DIAMOND_LEGGINGS)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
		$boots = ItemFactory::getInstance()->get(ItemIds::DIAMOND_BOOTS)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));

		// clearly teleport
		$player->getEffects()->clear();
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getHungerManager()->setEnabled(true);
		$player->getHungerManager()->setFood(20.0);
		$player->setHealth(20.0);
		$player->teleport($arena->getSafeSpawn());

		// items equiment
		foreach ($items as $item) {
			$player->getInventory()->addItem($item);
		}

		// armor equipment
		$player->getArmorInventory()->setHelmet($helmet);
		$player->getArmorInventory()->setChestplate($chestplate);
		$player->getArmorInventory()->setLeggings($leggings);
		$player->getArmorInventory()->setBoots($boots);

		// extras
		$player->sendTitle(Messages::JOIN_GAME_TITLE, Messages::JOIN_GAME_SUBTITLE);
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function exitGame(Player $player): bool
	{
		ScoreboardFactory::removeSecoreboard($player);
		$player->getEffects()->clear();
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getHungerManager()->setEnabled(false);
		$player->getHungerManager()->setFood(20.0);
		$player->setHealth(20.0);
		$player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
		$player->sendMessage(Messages::EXIT_MESSAGE);
		return true;
	}

	final protected function saveSessions()
	{
		foreach (SessionFactory::getSessions() as $session) {
			$session->save();
		}
	}

	/**
	 * @param EntityTeleportEvent $event
	 */
	public function onEntityTeleportEvent(EntityTeleportEvent $event): void
	{
		$to = $event->getTo()->getWorld()->getFolderName();
		$from = $event->getFrom()->getWorld()->getFolderName();
		$entity = $event->getEntity();
		$arena = $this->yamlProvider->getArena();
		if (!$entity instanceof Player) return;
		if ($from === $arena){
			SessionFactory::removeSession($entity);
		}
		$event = match ($to) {
			$arena => new PlayerGameJoinEvent($entity, $this),
			default => ($from === $arena) ? new PlayerGameQuitEvent($entity, $this) : null,
		};
		if (!$event?->isCancelled()) {
			$event?->call();
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onPlayerQuitEvent(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		$session = SessionFactory::getSession($player);
		$event = $session != null ? new PlayerGameQuitEvent($player, $this) : null;
		if (!$event?->isCancelled()) {
			SessionFactory::removeSession($player);
			ScoreboardFactory::removeSecoreboard($player);
			$event?->call();
			$session?->save();
		}
	}

	/**
	 * @return RXFFA
	 */
	public static function getInstance(): RXFFA
	{
		return self::$instance;
	}

	/**
	 * @return YamlDataProvider
	 */
	public function getYamlProvider(): YamlDataProvider
	{
		return $this->yamlProvider;
	}

}