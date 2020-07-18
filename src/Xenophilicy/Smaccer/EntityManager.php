<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\Entity;
use slapper\entities\other\SlapperBoat;
use slapper\entities\other\SlapperFallingSand;
use slapper\entities\other\SlapperMinecart;
use slapper\entities\other\SlapperPrimedTNT;
use slapper\entities\SlapperBat;
use slapper\entities\SlapperBlaze;
use slapper\entities\SlapperCaveSpider;
use slapper\entities\SlapperChicken;
use slapper\entities\SlapperCow;
use slapper\entities\SlapperCreeper;
use slapper\entities\SlapperDonkey;
use slapper\entities\SlapperElderGuardian;
use slapper\entities\SlapperEnderman;
use slapper\entities\SlapperEndermite;
use slapper\entities\SlapperEvoker;
use slapper\entities\SlapperGhast;
use slapper\entities\SlapperGuardian;
use slapper\entities\SlapperHorse;
use slapper\entities\SlapperHuman;
use slapper\entities\SlapperHusk;
use slapper\entities\SlapperIronGolem;
use slapper\entities\SlapperLavaSlime;
use slapper\entities\SlapperLlama;
use slapper\entities\SlapperMule;
use slapper\entities\SlapperMushroomCow;
use slapper\entities\SlapperOcelot;
use slapper\entities\SlapperPig;
use slapper\entities\SlapperPigZombie;
use slapper\entities\SlapperPolarBear;
use slapper\entities\SlapperRabbit;
use slapper\entities\SlapperSheep;
use slapper\entities\SlapperShulker;
use slapper\entities\SlapperSilverfish;
use slapper\entities\SlapperSkeleton;
use slapper\entities\SlapperSkeletonHorse;
use slapper\entities\SlapperSlime;
use slapper\entities\SlapperSnowman;
use slapper\entities\SlapperSpider;
use slapper\entities\SlapperSquid;
use slapper\entities\SlapperStray;
use slapper\entities\SlapperVex;
use slapper\entities\SlapperVillager;
use slapper\entities\SlapperVindicator;
use slapper\entities\SlapperWitch;
use slapper\entities\SlapperWither;
use slapper\entities\SlapperWitherSkeleton;
use slapper\entities\SlapperWolf;
use slapper\entities\SlapperZombie;
use slapper\entities\SlapperZombieHorse;
use slapper\entities\SlapperZombieVillager;
use Xenophilicy\Smaccer\entities\{SmaccerBat, SmaccerBlaze, SmaccerCat, SmaccerCaveSpider, SmaccerChicken, SmaccerCod, SmaccerCow, SmaccerCreeper, SmaccerDolphin,
  SmaccerDonkey, SmaccerDrowned, SmaccerElderGuardian, SmaccerEnderDragon, SmaccerEnderman, SmaccerEndermite, SmaccerEvoker, SmaccerGhast, SmaccerGuardian, SmaccerHorse,
  SmaccerHuman, SmaccerHusk, SmaccerIronGolem, SmaccerLavaSlime, SmaccerLlama, SmaccerMule, SmaccerMushroomCow, SmaccerOcelot, SmaccerPanda, SmaccerParrot,
  SmaccerPhantom, SmaccerPig, SmaccerPigZombie, SmaccerPolarBear, SmaccerPufferfish, SmaccerRabbit, SmaccerSalmon, SmaccerSheep, SmaccerShulker, SmaccerSilverfish,
  SmaccerSkeleton, SmaccerSkeletonHorse, SmaccerSlime, SmaccerSnowman, SmaccerSpider, SmaccerSquid, SmaccerStray, SmaccerTropicalFish, SmaccerTurtle, SmaccerVex,
  SmaccerVillager, SmaccerVindicator, SmaccerWitch, SmaccerWither, SmaccerWitherSkeleton, SmaccerWolf, SmaccerZombie, SmaccerZombieHorse, SmaccerZombieVillager};
use Xenophilicy\Smaccer\entities\other\{SmaccerBoat, SmaccerFallingSand, SmaccerMinecart, SmaccerPrimedTNT};

/**
 * Class EntityManager
 * @package Xenophilicy\Smaccer
 */
class EntityManager {
    const ENTITY_TYPES = ["Chicken", "Pig", "Sheep", "Cow", "MushroomCow", "Wolf", "Enderman", "Spider", "Skeleton", "PigZombie", "Creeper", "Slime", "Silverfish", "Villager", "Zombie", "Human", "Bat", "CaveSpider", "LavaSlime", "Ghast", "Ocelot", "Blaze", "ZombieVillager", "Snowman", "Minecart", "FallingSand", "Boat", "PrimedTNT", "Horse", "Donkey", "Mule", "SkeletonHorse", "ZombieHorse", "Witch", "Rabbit", "Stray", "Husk", "WitherSkeleton", "IronGolem", "Snowman", "LavaSlime", "Squid", "ElderGuardian", "Endermite", "Evoker", "Guardian", "PolarBear", "Shulker", "Vex", "Vindicator", "Wither", "Llama", "Cat", "Cod", "Salmon", "TropicalFish", "Dolphin", "Panda", "Pufferfish", "Drowned", "Turtle", "Parrot", "EnderDragon", "Entermite", "Phantom"];
    
    const ENTITY_ALIASES = ["Dragon" => "EnderDragon", "EDragon" => "EnderDragon", "MagmaCube" => "LavaSlime", "ZombiePigman" => "PigZombie", "Mooshroom" => "MushroomCow", "Player" => "Human", "VillagerZombie" => "ZombieVillager", "SnowGolem" => "Snowman", "FallingBlock" => "FallingSand", "FakeBlock" => "FallingSand", "VillagerGolem" => "IronGolem", "EGuardian" => "ElderGuardian", "Emite" => "Endermite"];
    
    public static function init(){
        foreach([SlapperCreeper::class, SlapperBat::class, SlapperSheep::class, SlapperPigZombie::class, SlapperGhast::class, SlapperBlaze::class, SlapperIronGolem::class, SlapperSnowman::class, SlapperOcelot::class, SlapperZombieVillager::class, SlapperHuman::class, SlapperCow::class, SlapperZombie::class, SlapperSquid::class, SlapperVillager::class, SlapperSpider::class, SlapperPig::class, SlapperMushroomCow::class, SlapperWolf::class, SlapperLavaSlime::class, SlapperSilverfish::class, SlapperSkeleton::class, SlapperSlime::class, SlapperChicken::class, SlapperEnderman::class, SlapperCaveSpider::class, SlapperBoat::class, SlapperMinecart::class, SlapperMule::class, SlapperWitch::class, SlapperPrimedTNT::class, SlapperHorse::class, SlapperDonkey::class, SlapperSkeletonHorse::class, SlapperZombieHorse::class, SlapperRabbit::class, SlapperStray::class, SlapperHusk::class, SlapperWitherSkeleton::class, SlapperFallingSand::class, SlapperElderGuardian::class, SlapperEndermite::class, SlapperEvoker::class, SlapperGuardian::class, SlapperLlama::class, SlapperPolarBear::class, SlapperShulker::class, SlapperVex::class, SlapperVindicator::class, SlapperWither::class] as $className){
            Entity::registerEntity($className, true);
        }
        foreach([SmaccerCreeper::class, SmaccerBat::class, SmaccerSheep::class, SmaccerPigZombie::class, SmaccerGhast::class, SmaccerBlaze::class, SmaccerIronGolem::class, SmaccerSnowman::class, SmaccerOcelot::class, SmaccerZombieVillager::class, SmaccerHuman::class, SmaccerCow::class, SmaccerZombie::class, SmaccerSquid::class, SmaccerVillager::class, SmaccerSpider::class, SmaccerPig::class, SmaccerMushroomCow::class, SmaccerWolf::class, SmaccerLavaSlime::class, SmaccerSilverfish::class, SmaccerSkeleton::class, SmaccerSlime::class, SmaccerChicken::class, SmaccerEnderman::class, SmaccerCaveSpider::class, SmaccerBoat::class, SmaccerMinecart::class, SmaccerMule::class, SmaccerWitch::class, SmaccerPrimedTNT::class, SmaccerHorse::class, SmaccerDonkey::class, SmaccerSkeletonHorse::class, SmaccerZombieHorse::class, SmaccerRabbit::class, SmaccerStray::class, SmaccerHusk::class, SmaccerWitherSkeleton::class, SmaccerFallingSand::class, SmaccerElderGuardian::class, SmaccerEndermite::class, SmaccerEvoker::class, SmaccerGuardian::class, SmaccerLlama::class, SmaccerPolarBear::class, SmaccerShulker::class, SmaccerVex::class, SmaccerVindicator::class, SmaccerWither::class, SmaccerEnderDragon::class, SmaccerParrot::class, SmaccerTurtle::class, SmaccerDrowned::class, SmaccerCat::class, SmaccerCod::class, SmaccerSalmon::class, SmaccerTropicalFish::class, SmaccerDolphin::class, SmaccerPanda::class, SmaccerPufferfish::class, SmaccerPhantom::class] as $className){
            Entity::registerEntity($className, true);
        }
    }
}