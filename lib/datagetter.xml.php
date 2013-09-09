<?PHP
header('Content-type: text/xml');
require_once("XML.funcs.lib.php");
if(isset($_GET) && count($_GET) > 0 && $_GET['loc'] != "")
{
	$location = $_GET['loc'];
	//$test_acct = "rew9";
	//$test_pass = "social37";
	$auth = (($_POST['auth'] == "")?false:(($_POST['auth'] == "false")?"nocookies":$_POST['auth']));
	$output = curl_request(urldecode($location), $auth);
	//$output = urldecode($location);
	//$output = urlencode("http://www.wowarmory.com/item-tooltip.xml?i=3602&r=Scarlet%2520Crusade&n=Lereana&s=5");
	//$output = preg_replace('/^(\<\?[^\?]+\?\>)*/', '', $output);
	preg_match_all('/[\"\>]([\w\d]{3,10}_[\d\w]{3,}(_[^\"]+)*)[\"\<]/', $output, $images);
	foreach($images[1] as $image_name)
	{
		if($image_name != "en_us" && $image_name != "global_nav_lang" && preg_match('/[\'\=\s]/', $image_name) == 0)
		{
			$x43 = file_exists("http://www.wowarmory.com/wow-icons/_images/43x43/".$image_name.".png");
			$x53 = file_exists("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg");
			$x21 = file_exists("http://www.wowarmory.com/wow-icons/_images/21x21/".$image_name.".png");
			$localx43 = file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".png");
			$localx53 = file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".jpg");
			$localx21 = file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".png");
			if(($x43 != "" || $x53 != "" || $x21 != "" || 1 == 1) && ($localx43 == "" || $localx53 == "" || $localx21 == ""))
			{
				//echo "<image>".$image_name."</image>";
				/*
				if(!file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".png") && strlen($image_name) > 0)			// && file_exists("http://www.wowarmory.com/wow-icons/_images/43x43/".$image_name.".png")
					$filecopy = copy("http://www.wowarmory.com/wow-icons/_images/43x43/".$image_name.".png", "/home/web/thedissident.us/images/new_icons/".$image_name.".png");
				else if(!file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".jpg") && strlen($image_name) > 0)		// && file_exists("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg")
					$filecopy = copy("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg", "/home/web/thedissident.us/images/new_icons/".$image_name.".jpg");
				else if(!file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".png") && strlen($image_name) > 0)		// && file_exists("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg")
					$filecopy = copy("http://www.wowarmory.com/wow-icons/_images/21x21/".$image_name.".png", "/home/web/thedissident.us/images/new_icons/".$image_name.".png");
				else if(!file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".jpg") && !file_exists("/home/web/thedissident.us/images/new_icons/".$image_name.".png"))
					echo "<other>local and remote image not found</other>";
				*/
				if(copy("http://www.wowarmory.com/wow-icons/_images/43x43/".$image_name.".png", "/home/web/thedissident.us/images/new_icons/".$image_name.".png"))			// && file_exists("http://www.wowarmory.com/wow-icons/_images/43x43/".$image_name.".png")
					$output = preg_replace('/(\<\/page\>)/i', "  <imagefound><!--43x43/".$image_name.".png--></imagefound>\n$1", $output);
				else if(copy("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg", "/home/web/thedissident.us/images/new_icons/".$image_name.".jpg"))		// && file_exists("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg")
					$output = preg_replace('/(\<\/page\>)/i', "  <imagefound><!--53x53/".$image_name.".jpg--></imagefound>\n$1", $output);
				else if(copy("http://www.wowarmory.com/wow-icons/_images/21x21/".$image_name.".png", "/home/web/thedissident.us/images/new_icons/".$image_name.".png"))		// && file_exists("http://www.wowarmory.com/wow-icons/_images/53x53/".$image_name.".jpg")
					$output = preg_replace('/(\<\/page\>)/i', "  <imagefound><!--21x21/".$image_name.".png--></imagefound>\n$1", $output);
				else
					$output = preg_replace('/(\<\/page\>)/i', "  <imagenotfound><!--All Failed--></imagenotfound>\n$1", $output);
			}else
				$output = preg_replace('/(\<\/page\>)/i', "  <imagealreadyloadedornotonremote ".$x43." ".$x53." ".$x21." ".file_exists("http://www.wowarmory.com")."><!--".$image_name."--></imagealreadyloadedornotonremote>\n$1", $output);
		}
	}
	echo $output;
	//print_r($images);
	exit;
	
	
	
	
}    
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><?xml-stylesheet type=\"text/xsl\" href=\"/layout/item-tooltip.xsl\"?>";
?><page globalSearch="1" lang="en_us" requestUrl="/character-sheet.xml">
  <characterInfo>
	<character battleGroup="Reckoning" charUrl="r=Scarlet+Crusade&amp;n=Lereana" class="Warlock" classId="9" faction="Alliance" factionId="0" gender="Female" genderId="1" guildName="Aegis of the Dissident" guildUrl="r=Scarlet+Crusade&amp;n=Aegis+of+the+Dissident&amp;p=1" lastModified="February 19, 2009" level="12" name="Lereana" points="50" prefix="" race="Gnome" raceId="7" realm="Scarlet Crusade" suffix=""/>
	<characterTab>
	  <talentSpec treeOne="0" treeThree="0" treeTwo="3"/>
	  <buffs/>
	  <debuffs/>
	  <pvp>
		<lifetimehonorablekills value="0"/>

		<arenacurrency value="0"/>
	  </pvp>
	  <professions>
		<skill key="enchanting" max="75" name="Enchanting" value="53"/>
		<skill key="mining" max="75" name="Mining" value="20"/>
	  </professions>
	  <title value=""/>
	  <knownTitles/>
	  <characterBars>

		<health effective="233"/>
		<secondBar casting="0" effective="538" notCasting="32" type="m"/>
	  </characterBars>
	  <baseStats>
		<strength attack="8" base="18" block="-1" effective="18"/>
		<agility armor="56" attack="-1" base="27" critHitPercent="5.40" effective="28"/>
		<stamina base="26" effective="28" health="100" petBonus="8"/>
		<intellect base="38" critHitPercent="5.27" effective="40" mana="320" petBonus="12"/>
		<spirit base="34" effective="39" healthRegen="4" manaRegen="32"/>

		<armor base="156" effective="156" percent="9.90" petBonus="55"/>
	  </baseStats>
	  <resistances>
		<arcane petBonus="0" value="0"/>
		<fire petBonus="0" value="0"/>
		<frost petBonus="0" value="0"/>
		<holy petBonus="0" value="0"/>
		<nature petBonus="0" value="0"/>
		<shadow petBonus="0" value="0"/>

	  </resistances>
	  <melee>
		<mainHandDamage dps="6.9" max="25" min="17" percent="0" speed="3.10"/>
		<offHandDamage dps="0.0" max="0" min="0" percent="0" speed="2.00"/>
		<mainHandSpeed hastePercent="0.00" hasteRating="0" value="3.10"/>
		<offHandSpeed hastePercent="0.00" hasteRating="0" value="2.00"/>
		<power base="8" effective="8" increasedDps="0.0"/>
		<hitRating increasedHitPercent="0.00" penetration="0" reducedArmorPercent="0.00" value="0"/>
		<critChance percent="5.32" plusPercent="0.00" rating="0"/>

		<expertise additional="0" percent="0.00" rating="0" value="0"/>
	  </melee>
	  <ranged>
		<weaponSkill rating="0" value="0"/>
		<damage dps="8.7" max="17" min="9" percent="0" speed="1.50"/>
		<speed hastePercent="0.00" hasteRating="0" value="1.50"/>
		<power base="18" effective="18" increasedDps="1.0" petAttack="-1.00" petSpell="-1.00"/>
		<hitRating increasedHitPercent="0.00" penetration="0" reducedArmorPercent="0.00" value="0"/>
		<critChance percent="3.04" plusPercent="0.00" rating="0"/>

	  </ranged>
	  <spell>
		<bonusDamage>
		  <arcane value="0"/>
		  <fire value="0"/>
		  <frost value="0"/>
		  <holy value="0"/>
		  <nature value="0"/>
		  <shadow value="0"/>

		  <petBonus attack="0" damage="0" fromType="fire"/>
		</bonusDamage>
		<bonusHealing value="0"/>
		<hitRating increasedHitPercent="0.00" penetration="0" reducedResist="0" value="0"/>
		<critChance rating="0">
		  <arcane percent="5.27"/>
		  <fire percent="5.27"/>
		  <frost percent="5.27"/>
		  <holy percent="5.27"/>

		  <nature percent="5.27"/>
		  <shadow percent="5.27"/>
		</critChance>
		<penetration value="0"/>
		<manaRegen casting="0.00" notCasting="32.00"/>
		<hasteRating hastePercent="0.00" hasteRating="0"/>
	  </spell>
	  <defenses>
		<armor base="156" effective="156" percent="9.90" petBonus="55"/>

		<defense decreasePercent="0.00" increasePercent="0.00" plusDefense="0" rating="0" value="60.00"/>
		<dodge increasePercent="0.00" percent="5.50" rating="0"/>
		<parry increasePercent="0.00" percent="0.00" rating="0"/>
		<block increasePercent="0.00" percent="0.00" rating="0"/>
		<resilience damagePercent="0.00" hitPercent="0.00" value="0.00"/>
	  </defenses>
	  <items>
		<item durability="0" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_shirt_red_01" id="2575" maxDurability="0" permanentenchant="0" randomPropertiesId="0" seed="1990247776" slot="3"/>
		<item durability="50" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_chest_fur" id="2578" maxDurability="50" permanentenchant="0" randomPropertiesId="0" seed="1791649048" slot="4"/>

		<item durability="16" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_belt_06" id="3602" maxDurability="16" permanentenchant="0" randomPropertiesId="0" seed="0" slot="5"/>
		<item durability="40" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_pants_07" id="4309" maxDurability="40" permanentenchant="0" randomPropertiesId="0" seed="1215909398" slot="6"/>
		<item durability="25" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_boots_fabric_01" id="792" maxDurability="25" permanentenchant="0" randomPropertiesId="0" seed="0" slot="7"/>
		<item durability="16" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_bracer_03" id="3603" maxDurability="16" permanentenchant="41" randomPropertiesId="0" seed="0" slot="8"/>
		<item durability="16" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_gauntlets_18" id="793" maxDurability="16" permanentenchant="0" randomPropertiesId="0" seed="0" slot="9"/>
		<item durability="0" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_jewelry_ring_13" id="20906" maxDurability="0" permanentenchant="0" randomPropertiesId="0" seed="389836322" slot="10"/>
		<item durability="0" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_misc_cape_11" id="2905" maxDurability="0" permanentenchant="247" randomPropertiesId="0" seed="1063872064" slot="14"/>
		<item durability="45" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_staff_11" id="2257" maxDurability="45" permanentenchant="241" randomPropertiesId="0" seed="0" slot="15"/>
		<item durability="30" gem0Id="0" gem1Id="0" gem2Id="0" icon="inv_staff_02" id="5069" maxDurability="30" permanentenchant="0" randomPropertiesId="0" seed="0" slot="17"/>

	  </items>
	</characterTab>
  </characterInfo>
</page>
