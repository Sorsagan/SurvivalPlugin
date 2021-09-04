<?php

namespace Survival;

use pocketmine\item\Item;

use pocketmine\Server;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

use pocketmine\network\mcpe\protocol\ActorEventPacket;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

class Main extends PluginBase implements Listener

{

    public function onLoad()

    {

        $this->getLogger()->info("onLoad() has been called!");

    }

    public function onEnable()

    {

        $this->getLogger()->info("onEnable() has been called!");

    }

    public function onDisable()

    {

        $this->getLogger()->info("onDisable() has been called!");

    }

    public function onCommand(

        CommandSender $sender,

        Command $cmd,

        string $label,

        array $args

    ): bool {

        switch ($cmd->getName()) {

            case "repair":

                if ($sender instanceof Player) {

                   $this->showTotemEffect($sender);

                    $item = $sender->getInventory()->getItemInHand();

                    if ($item->getDamage() > 0) {

                        $item->setDamage(0);

                        $sender->getInventory()->setItemInHand($item);

                        $sender->sendMessage(

                            TextFormat::BLUE . "Item succesfully repaired!"

                        );

                    } else {

                        $sender->sendMessage(

                            TextFormat::RED . "Item does not have any damage!"

                        );

                    }

                } else {

                    $sender->sendMessage("you arent hooman");

                }

        }

        return true;

    }

    public function showTotemEffect(Player $player)

    {

        $player->getInventory()->getItemInHand(Item::get(450, 0, 1));

        $player->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);

        $pk = new LevelEventPacket();

        $pk->evid = LevelEventPacket::EVENT_SOUND_TOTEM;

        $pk->position = $player->add(0, $player->eyeHeight, 0);

        $pk->data = 0;

        $player->dataPacket($pk);

        $player->getInventory()->getItemInHand(Item::get(0, 0, 1));

    }

}
