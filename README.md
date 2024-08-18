# SimpleCrates

**SimpleCrates is a PocketMine-MP plugin to add Crates on your server easy to edit in the game with /cr editor.**

<p align="center"><img src="img/crate.png"></p>

<p align="center"><img src="img/crate_editor.png"></p>

## Prerequisites

- <a href="https://github.com/Muqsit/InvMenu">InvMenu virion</a>
- PMMP 5.17.0+

### Implementations

- [x] Easy Edit
- [x] Configure the representative crate block
- [x] Decorative particles by id or RGB
- [x] Floating items preview
- [x] Custom messages
- [x] Animations when opening the crate (can be disabled in config.yml)
- [x] Configurable key

---

### 💾 Config

```yml
#  ███████╗██╗███╗   ███╗██████╗ ██╗     ███████╗ ██████╗██████╗  █████╗ ████████╗███████╗███████╗
#  ██╔════╝██║████╗ ████║██╔══██╗██║     ██╔════╝██╔════╝██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔════╝
#  ███████╗██║██╔████╔██║██████╔╝██║     █████╗  ██║     ██████╔╝███████║   ██║   █████╗  ███████╗
#  ╚════██║██║██║╚██╔╝██║██╔═══╝ ██║     ██╔══╝  ██║     ██╔══██╗██╔══██║   ██║   ██╔══╝  ╚════██║
#  ███████║██║██║ ╚═╝ ██║██║     ███████╗███████╗╚██████╗██║  ██║██║  ██║   ██║   ███████╗███████║
#  ╚══════╝╚═╝╚═╝     ╚═╝╚═╝     ╚══════╝╚══════╝ ╚═════╝╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚══════╝╚══════╝
#
# SimpleCrates config by iRxDuZ ツ

# Do not touch :)
CONFIG_VERSION: 1

# Version migrator (true = yes false = no)
# Migrate data from version 1.0 of SimpleCrates.
# Instructions: Rename the crates.yml file to crates_old.yml from the version 1.0 and turn on the server.
# When the data has been migrated, disable this option with: false
version-migrator: false

# Keys configuration
keys:
  id: "tripwire_hook"
  name: "§d{CRATE} Key"
  lore: "§eClaim rewards from a {CRATE} Crate"

# Crates configuration default
# Default blocks (chest, enchantment table, end portal frame, lime shulker box)
crates:
  blocks: ["chest", "enchant_table", "end_portal_frame", "lime_shulker_box"]
  animation: true
  duration: 5
  preview-items: true
  drop-item-time: 5
  particle: true
```

## Permissions

| Permissions                   | Description                  | Default |
| ----------------------------- | ---------------------------- | ------- |
| `simplecrates.command.crate`  | Allow to use /crate command  | `op`    |
| `simplecrates.command.key`    | Allow to use /key command    | `op`    |
| `simplecrates.command.keyall` | Allow to use /keyall command | `op`    |

### ✔ Credits

| Authors        | Github                                         | Lib                                           |
| -------------- | ---------------------------------------------- | --------------------------------------------- |
| Muqsit         | [Muqsit](https://github.com/Muqsit)            | [InvMenu](https://github.com/Muqsit/InvMenu)  |
| yuko fuyutsuki | [yuko fuyutsuki](https://github.com/fuyutsuki) | [Texter](https://github.com/fuyutsuki/Texter) |
