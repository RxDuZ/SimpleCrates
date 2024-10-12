<?php

namespace rxduz\crates\command\arguments;

use CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class PlayerArgument extends BaseArgument
{

	public function __construct(string $name, bool $optional)
	{
		parent::__construct($name, $optional);
	}

	public function getNetworkType(): int
	{
		return AvailableCommandsPacket::ARG_TYPE_TARGET;
	}

	public function getTypeName(): string
	{
		return 'player';
	}

	public function parse(string $argument, CommandSender $sender): Player
	{
		return Server::getInstance()->getPlayerByPrefix($argument);
	}

	public function canParse(string $testString, CommandSender $sender): bool
	{
		return !is_null(Server::getInstance()->getPlayerByPrefix($testString));
	}
}
