#!/usr/bin/env php
<?php
include 'vendor/autoload.php';

use Seat\Eseye\Eseye;

$webhookurl = '';
$chars = [];
$corps = [];
$alliances = [];
$postkills = true;
$postlosses = true;
$terminate_after_post = true;

is_file('config.php') AND include 'config.php';

$webhookurl OR die('You need to configure this script before you run it.');

$esi = new Eseye();

while (1) {
    $postkill=false;
    $json = file_get_contents('http://redisq.zkillboard.com/listen.php?queueID=reaper1');
    if (!$json) {
        sleep(5);
        continue;
    }
    $km = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
    if ($postlosses && (in_array(@$km->package->killmail->victim->character_id, $chars) || in_array(@$km->package->killmail->victim->corporation_id, $corps) || in_array(@$km->package->killmail->victim->alliance_id, $alliances)))
        $postkill=true;
    if (!$postkill && $postkills) {
        foreach (@$km->package->killmail->attackers ?? array() as $a) {
            if (in_array(@$a->character_id, $chars) || in_array(@$a->corporation_id, $corps) || in_array(@$a->alliance_id, $alliances)) {
                $postkill=true;
                break;
            }
        }
    }
    if (!$postkill)
        continue;
    $killID = $km->package->killID;
    try {
        $systemName = $esi->invoke('get', '/universe/systems/{system_id}/', [ 'system_id' => @$km->package->killmail->solar_system_id ])->name;
        $killTime = $km->package->killmail->killmail_time;
        $victimName = $esi->invoke('get', '/characters/{character_id}/', [ 'character_id' => @$km->package->killmail->victim->character_id ])->name;
        $victimCorpName = $esi->invoke('get', '/corporations/{corporation_id}/', [ 'corporation_id' => @$km->package->killmail->victim->corporation_id ])->name;
        $victimAllianceName = $esi->invoke('get', '/alliances/{alliance_id}/', [ 'alliance_id' => @$km->package->killmail->victim->alliance_id ])->name;
        $shipName = $esi->invoke('get', '/universe/types/{type_id}/', [ 'type_id' => @$km->package->killmail->victim->ship_type_id ])->name;
        $totalValue = number_format($km->package->zkb->totalValue);
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        continue;
    }
    $msg = "**{$killTime}**\n\n**{$shipName}** worth **{$totalValue} ISK** flown by **{$victimName}** of (***{$victimCorpName}|{$victimAllianceName}***) killed in {$systemName}\nhttps://zkillboard.com/kill/{$killID}/";

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode(array('content' => $msg)),
        )
    ));
    file_get_contents($webhookurl, FALSE, $context);
    $terminate_after_post AND die();
}
