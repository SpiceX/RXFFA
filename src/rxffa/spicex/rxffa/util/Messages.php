<?php
declare(strict_types=1);


namespace rxffa\spicex\rxffa\util;


class Messages
{
	public const JOIN_GAME_TITLE = "§6FFA";
	public const JOIN_GAME_SUBTITLE = "§7Kill the all!";
	public const EXIT_MESSAGE = "§7You have left FFA Game";
	public const HELP_COMMAND_OP =
		"§7-====(§eFFA Help§7)====-\n" .
		"§e/ffa help: §7Shows ffa help\n" .
		"§e/ffa join: §7Join ffa game\n" .
		"§e/ffa arena <arena>: §7Sets ffa arena\n" .
		"§e/ffa npc: §7Sets the game npc\n" .
		"§e/ffa tops: §7Sets tops npc\n" .
		"§e/ffa exit: §7Exit from ffa\n";
	public const HELP_COMMAND_USER =
	"§7-====(§eFFA Help§7)====-\n" .
	"§e/ffa help: §7Shows ffa help\n" .
	"§e/ffa join: §7Join ffa game\n";
	public const KILL_MESSAGE = "§eYou have killed §7{victim}";
	public const GLOBAL_KILL_MESSAGE = "§7{killer} §ehas killed §7{victim}";
	public const FFA_COMMAND_DESCRIPTION = "ffa command help";
	public const WORLD_NOT_FOUND = "§cThat world does not exists";
	public const GAME_NPC_NAMETAG = '§6FFA' . "\n" . '§ePlaying ';
	public const TOPS_NAMETAG = "§l§9LeaderBoard\n§r§6FFA Kills\n";
	public const ARENA_NOT_CONFIGURED = "§cThe FFA arena is not ready!";

}