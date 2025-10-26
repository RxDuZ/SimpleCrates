<?php

namespace rxduz\crates\command\subcommand;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\CrateCommand;

class HelpSubCommand extends BaseSubCommand
{

    private const ARGUMENT_PAGE = 'page';

    private const COMMANDS_PER_PAGE = 7;

    public function __construct(
        private readonly CrateCommand $parentCommand,
        string $name,
        string $description = "",
        array $aliases = []
    ) {
        parent::__construct($parentCommand->plugin, $name, $description, $aliases);
    }

    protected function prepare(): void
    {
        $this->setPermission('simplecrates.command.help');
        $this->registerArgument(0, new IntegerArgument(self::ARGUMENT_PAGE, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {

        $subcommands = array_filter($this->parentCommand->getSubCommands(), function (BaseSubCommand $subCommand, string $alias) use ($sender): bool {
            return $subCommand->getName() === $alias && count(array_filter($subCommand->getPermissions(), $sender->hasPermission(...))) > 0;
        }, ARRAY_FILTER_USE_BOTH);

        $helpCommands = [];

        foreach ($subcommands as $name => $subCommand) {
            $desc = $subCommand->getDescription();
            $arguments = [];

            foreach ($subCommand->getArgumentList() as $argumentList) {
                foreach ($argumentList as $baseArgument) {
                    $arguments[] = '<' . $baseArgument->getName() . ':' . $baseArgument->getTypeName() . '>';
                }
            }

            $argsString = count($arguments) > 0 ? implode(' ', $arguments) : '';
            $helpCommands[] = '' . TextFormat::GREEN . '/' . $this->parent->getName() . ' ' . $name . ' ' . $argsString . ': ' . TextFormat::WHITE . $desc;
        }

        $commandsPerPage = $sender instanceof Player ? self::COMMANDS_PER_PAGE : count($subcommands);
        $page = $args[self::ARGUMENT_PAGE] ?? 1;

        if ($page < 1) $page = 1;

        $totalCommands = count($helpCommands);
        $totalPages = (int)ceil($totalCommands / $commandsPerPage);

        if ($page > $totalPages) $page = $totalPages;

        $start = ($page - 1) * $commandsPerPage;
        $end = min($start + $commandsPerPage, $totalCommands);

        $sender->sendMessage(TextFormat::BLUE . '[SimpleCrates] Help Page ' . TextFormat::DARK_GRAY . '[' . $page . '/' . $totalPages . ']');

        for ($i = $start; $i < $end; $i++) {
            $sender->sendMessage($helpCommands[$i]);
        }
    }
}
