<?php
/**
 * Runs batch upgrades
 */

$guid = get_input('guid');

$upgrade = get_entity($guid);

if (!$upgrade instanceof \ElggUpgrade) {
	register_error(elgg_echo('admin:upgrades:error:invalid_upgrade', array($entity->title, $guid)));
	exit;
}

$upgrader = _elgg_services()->batchUpgrader;
$upgrader->setUpgrade($upgrade);
$result = $upgrader->run();

echo json_encode($result);