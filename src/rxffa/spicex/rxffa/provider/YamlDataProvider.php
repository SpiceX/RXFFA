<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\provider;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\World;
use rxffa\spicex\rxffa\RXFFA;
use rxffa\spicex\rxffa\session\Session;
use rxffa\spicex\rxffa\util\Messages;

/**
 * Class YamlDataProvider
 * @package rxffa\spicex\rxffa\provider
 */
class YamlDataProvider
{
	public const DEFAULT_WORLD = 'default';

	/** @var Config */
	private Config $killsConfig;
	/** @var Config */
	private Config $deathsConfig;
	/** @var string */
	private string $arena;

	/**
	 * YamlDataProvider constructor.
	 */
	public function __construct(public RXFFA $plugin)
	{
		$this->init();
	}

	final protected function init(): void
	{
		$this->plugin->saveDefaultConfig();
		$this->plugin->saveResource('kills.yml');
		$this->plugin->saveResource('deaths.yml');
		$this->arena = $this->plugin->getConfig()->get('arena', self::DEFAULT_WORLD);
		if ($this->arena === self::DEFAULT_WORLD) {
			$this->plugin->getLogger()->warning(Messages::ARENA_NOT_CONFIGURED);
		} else {
			$this->plugin->getServer()->getWorldManager()->loadWorld($this->arena);
		}
		$this->killsConfig = new Config($this->plugin->getDataFolder() . 'kills.yml', Config::YAML);
		$this->deathsConfig = new Config($this->plugin->getDataFolder() . 'deaths.yml', Config::YAML);
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function playerExists(Player $player): bool
	{
		return $this->killsConfig->exists($player->getName()) || $this->deathsConfig->exists($player->getName());
	}

	/**
	 * @param Player $player
	 * @return int[]
	 */
	#[ArrayShape(['kills' => "int", 'deaths' => "int"])]
	public function getPlayerData(Player $player): array
	{
		return [
			'kills' => (int)$this->killsConfig->get($player->getName(), 0),
			'deaths' => (int)$this->deathsConfig->get($player->getName(), 0)
		];
	}

	/**
	 * @param Session|null $session
	 */
	public function saveSession(Session|null $session)
	{
		$this->killsConfig->set($session?->getPlayer()?->getName(), $session?->getKills());
		$this->deathsConfig->set($session?->getPlayer()?->getName(), $session?->getDeaths());
		$this->killsConfig->save();
		$this->deathsConfig->save();
	}

	/**
	 * @return array
	 */
	#[Pure] public function getTops(): array
	{
		return $this->killsConfig->getAll() ?? [];
	}

	/**
	 * @return Config
	 */
	public function getKillsConfig(): Config
	{
		return $this->killsConfig;
	}

	/**
	 * @return Config
	 */
	public function getDeathsConfig(): Config
	{
		return $this->deathsConfig;
	}

	/**
	 * @param string $arena
	 */
	public function setArena(string $arena)
	{
		$config = $this->plugin->getConfig();
		$config->set('arena', $arena);
		$config->save();
	}

	/**
	 * @return string
	 */
	public function getArena(): string
	{
		return $this->arena;
	}

	/**
	 * @return World|null
	 */
	public function getWorldArena(): World|null
	{
		return $this->plugin->getServer()->getWorldManager()->getWorldByName($this->arena);
	}
}