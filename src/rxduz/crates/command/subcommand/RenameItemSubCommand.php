<?php

namespace rxduz\crates\command\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class RenameItemSubCommand extends BaseSubCommand
{

    private const ARGUMENT_TYPE = 'type';

    private const ARGUMENT_NAME = 'name';

    protected function prepare(): void
    {
        $this->setPermission('simplecrates.command.renameitem');
        $this->registerArgument(0, new RawStringArgument(self::ARGUMENT_TYPE));
        $this->registerArgument(1, new TextArgument(self::ARGUMENT_NAME));
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        assert($sender instanceof Player);

        /** @var string $type */
        $type = $args[self::ARGUMENT_TYPE];

        /** @var string $name */
        $name = $args[self::ARGUMENT_NAME];

        $item = $sender->getInventory()->getItemInHand();

        if ($item->isNull()) {
            $sender->sendMessage(TextFormat::RED . 'Please, use a valid item.');

            return;
        }

        switch (strtolower($type)) {
            case 'name':
                $colorize = TextFormat::colorize($name);

                $item->setCustomName($colorize);

                $sender->getInventory()->setItemInHand($item);

                $sender->sendMessage(TextFormat::GREEN . 'Item successfully renamed to ' . TextFormat::RESET . $colorize);
                break;
            case 'lore':
                $colorize = TextFormat::colorize($name);

                $lore = explode('{LINE}', $colorize);

                $item->setLore($lore);

                $sender->getInventory()->setItemInHand($item);

                $sender->sendMessage(TextFormat::GREEN . 'Item successfully change lore to ' . TextFormat::RESET . $colorize);
                break;
            case 'help':
            default:
                $sender->sendMessage(TextFormat::RED . 'Use /crate renameitem <name|lore> <text>' . TextFormat::EOL . TextFormat::GRAY . 'Note: To skip a line in the lore use {LINE}');
                break;
        }
    }
}
