<?php

/**
 * This is the basic entry point for the agent API.
 * It should be entered as URL for the agent.
 *
 * The input is sent as JSON encoded data and the response will also be in JSON
 */

require_once(dirname(__FILE__) . "/../inc/load.php");
set_time_limit(0);

header("Content-Type: application/json");
$QUERY = json_decode(file_get_contents('php://input'), true);

$api = null;
switch ($QUERY[PQuery::ACTION]) {
    /**
     * Used to create new task and upload a hashlist
     */
    case PActions::CREATE_TASK:
        $api = new APICreateTask();
        break;
}

if ($api == null) {
    $api = new APITestConnection();
    $api->sendErrorResponse("INV", "Invalid query!");
}
else {
    $api->execute($QUERY);
}

