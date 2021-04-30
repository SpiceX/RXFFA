<?php
declare(strict_types=1);
namespace rxffa\spicex\rxffa\model;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use rxffa\spicex\rxffa\RXFFA;
use rxffa\spicex\rxffa\util\Messages;

/**
 * Class TopsModel
 * @package rxffa\spicex\rxffa\model
 */
class TopsModel extends Human
{

	/**
	 * TopsModel constructor.
	 * @param Location $location
	 * @param Skin $skin
	 * @param CompoundTag|null $nbt
	 */
	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null){
		parent::__construct($location, $skin, $nbt);
		$this->skin = new Skin('Standard_Custom', str_repeat("\x00", 8192), '', 'geometry.humanoid.custom');
		$this->sendSkin();
		$this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
		$this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
	}

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
		$subtitle = "";
		$tops = RXFFA::getInstance()->getYamlProvider()->getTops();
		if (count($tops) > 0) {
			arsort($tops);
			$i = 1;
			foreach ($tops as $name => $wins) {
				$subtitle .= " §7[§b# " . $i . "§7]. §f" . $name . "§7: §f" . $wins . "§e Kills\n";
				if ($i == 1) {
					$top1 = $name;
				}
				if ($i == 2) {
					$top2 = $name;
				}
				if ($i == 3) {
					$top3 = $name;
				}
				if ($i >= 10) {
					break;
				}
				++$i;
			}
		}

		$this->setNameTag(Messages::TOPS_NAMETAG . $subtitle);
		$this->setNameTagAlwaysVisible(true);
		return parent::onUpdate($currentTick);
	}
}