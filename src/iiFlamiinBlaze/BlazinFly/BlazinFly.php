<?php
/**
 *  ____  _            _______ _          _____
 * |  _ \| |          |__   __| |        |  __ \
 * | |_) | | __ _ _______| |  | |__   ___| |  | | _____   __
 * |  _ <| |/ _` |_  / _ \ |  | '_ \ / _ \ |  | |/ _ \ \ / /
 * | |_) | | (_| |/ /  __/ |  | | | |  __/ |__| |  __/\ V /
 * |____/|_|\__,_/___\___|_|  |_| |_|\___|_____/ \___| \_/
 *
 * Copyright (C) 2018 iiFlamiinBlaze
 *
 * iiFlamiinBlaze's plugins are licensed under MIT license!
 * Made by iiFlamiinBlaze for the PocketMine-MP Community!
 *
 * @author iiFlamiinBlaze
 * Twitter: https://twitter.com/iiFlamiinBlaze
 * GitHub: https://github.com/iiFlamiinBlaze
 * Discord: https://discord.gg/znEsFsG
 */
declare(strict_types=1);

namespace iiFlamiinBlaze\BlazinFly;

use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;

class BlazinFly extends PluginBase implements Listener{

    private const PREFIX = TextFormat::AQUA . "BlazinFly" . TextFormat::GOLD . " > ";
    private const VERSION = "v1.8.5";

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getLogger()->info("BlazinFly " . self::VERSION . " by iiFlamiinBlaze enabled");
    }

    private function multiWorldCheck(Entity $entity) : bool{
        if(!$entity instanceof Player) return false;
        if($this->getConfig()->get("multi-world") === "on"){
            if(!in_array($entity->getLevel()->getName(), $this->getConfig()->get("worlds"))){
                $entity->sendMessage(self::PREFIX . TextFormat::RED . "You are not in the right world to be able to use the fly command");
                return false;
            }
        }elseif($this->getConfig()->get("multi-world") === "off") return true;
        return true;
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        if($this->getConfig()->get("onJoin-FlyReset") === true){
            if($player->isCreative()) return;
            $player->setAllowFlight(false);
            $player->sendMessage($this->getConfig()->get("fly_disabled"));
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if($command->getName() === "fly"){
            if(!$sender instanceof Player){
                $sender->sendMessage(self::PREFIX . TextFormat::RED . "Use this command in-game");
                return false;
            }
            if(!$sender->hasPermission("fly.command")){
                $sender->sendMessage(self::PREFIX . TextFormat::RED . "You do not have permission to use this command");
                return false;
            }
            if(empty($args[0])){
                if(!$sender->isCreative()){
                    if($this->multiWorldCheck($sender) === false) return false;
                    $sender->setAllowFlight($sender->getAllowFlight() === false ? true : false);
                    $message = [
                        true => $this->getConfig()->get("fly-enabled"),
                        false => $this->getConfig()->get("fly-disabled")
                    ];
                    $sender->sendMessage($message[$sender->getAllowFlight()]);
                }else{
                    $sender->sendMessage(self::PREFIX . TextFormat::RED . "You can only use this command in survival mode");
                    return false;
                }
                return false;
            }
            if($this->getServer()->getPlayer($args[0])){
                $player = $this->getServer()->getPlayer($args[0]);
                if(!$player->isCreative()){
                    if($this->multiWorldCheck($player) === false) return false;
                    $player->setAllowFlight($player->getAllowFlight() === false ? true : false);
                    $message = [
                        true => $this->getConfig()->get("fly-enabled"),
                        false => $this->getConfig()->get("fly-disabled")
                    ];
                    $player->sendMessage($message[$sender->getAllowFlight()]);
                    $message = [
                        true => self::PREFIX . TextFormat::GREEN . "You have enabled fly for " . $player->getName(),
                        false => self::PREFIX . TextFormat::RED . "You have disabled fly for " . $player->getName()
                    ];
                    $sender->sendMessage($message[$player->getAllowFlight()]);
                }else{
                    $sender->sendMessage(self::PREFIX . TextFormat::RED . $player->getName() . " is in creative mode");
                    return false;
                }
            }else{
                $sender->sendMessage(self::PREFIX . TextFormat::RED . "Player not found");
                return false;
            }
        }
        return true;
    }

    public function onDamage(EntityDamageEvent $event) : void{
        $entity = $event->getEntity();
        if($this->getConfig()->get("onDamage-FlyReset") === true){
            if($event instanceof EntityDamageByEntityEvent){
                if($entity instanceof Player){
                    $damager = $event->getDamager();
                    if(!$damager instanceof Player) return;
                    if($damager->isCreative()) return;
                    if($damager->getAllowFlight() === true){
                        $damager->sendMessage(self::PREFIX . TextFormat::DARK_RED . "Flight mode disabled due to combat");
                        $damager->setAllowFlight(false);
                        $damager->setFlying(false);
                    }
                }
            }
        }
    }
}