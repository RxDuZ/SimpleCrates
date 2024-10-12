<?php

namespace rxduz\crates\command\arguments;

use CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;

class CrateArgument extends StringEnumArgument
{

    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    public function getTypeName(): string
    {
        return 'crate';
    }

    public function getEnumName(): string
    {
        return 'string';
    }

    public function canParse(string $testString, CommandSender $sender): bool
    {
        return $this->getValue($testString) instanceof Crate;
    }

    public function parse(string $argument, CommandSender $sender): ?Crate
    {
        return $this->getValue($argument);
    }

    public function getValue(string $string): ?Crate
    {
        return Main::getInstance()->getCrateManager()->getCrateByName($string);
    }

    public function getEnumValues(): array
    {
        return array_keys(Main::getInstance()->getCrateManager()->getCrates());
    }
}
