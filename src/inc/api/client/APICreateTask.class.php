<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcinb
 * Date: 04/05/2018
 * Time: 13:49
 */
use DBA\QueryFilter;
use DBA\Hashlist;
use DBA\HashBinary;
use DBA\AccessGroupUser;
use DBA\Task;
use DBA\TaskWrapper;
use DBA\FileTask;
use DBA\CrackerBinary;

class APICreateTask extends APIBasic{

    /**
     * @param array $QUERY input query sent to the API
     */
    public function execute($QUERY = array())
    {
        /** @var DataSet $CONFIG */
        /** @var $LOGIN Login */
        global $FACTORIES, $CONFIG, $LOGIN;

        // new task creator
        $name = htmlentities(util::randomString(10));
        $cmdline = $QUERY["cmdline"];
        $chunk = intval(600);
        $status = intval(5);
        $useNewBench = intval(0);
        $isCpuTask = intval(0);
        $isSmall = intval(0);
        $skipKeyspace = intval(0);

        $QF = new QueryFilter(DBA\CrackerBinaryType::TYPE_NAME,"hashcat","=");
        $crackerBinaryType = $FACTORIES::getCrackerBinaryTypeFactory()->filter(array($FACTORIES::FILTER=>$QF), true);
        $crackerBinary = CrackerBinaryUtils::getNewestVersion($crackerBinaryType->getId())->getId();

        $hashlist = new Hashlist(0, htmlentities($QUERY["hashlistname"], ENT_QUOTES, "UTF-8"), DHashlistFormat::WPA, 2500, 0,":",0,0,0,0,AccessUtils::getOrCreateDefaultAccessGroup()->getId());
        $hashlist = $FACTORIES::getHashlistFactory()->save($hashlist);

        $fileInTmp = dirname(__FILE__)."/../../../tmp/".Util::randomString(10);
        file_put_contents($fileInTmp, base64_decode($QUERY["capdata"]));
        $file = fopen($fileInTmp, "rb");

        $added = 0;
        while (!feof($file)) {
            $data = fread($file, 393);
            if (strlen($data) == 0) {
                break;
            }
            if (strlen($data) != 393) {
                UI::printError("ERROR", "Data file only contains " . strlen($data) . " bytes!");
            }
            // get the SSID
            $network = "";
            for ($i = 10; $i < 42; $i++) {
                $byte = $data[$i];
                if ($byte != "\x00") {
                    $network .= $byte;
                }
                else {
                    break;
                }
            }
            // get the AP MAC
            $mac_ap = "";
            for ($i = 59; $i < 65; $i++) {
                $mac_ap .= $data[$i];
            }
            $mac_ap = Util::bintohex($mac_ap);
            // get the Client MAC
            $mac_cli = "";
            for ($i = 97; $i < 103; $i++) {
                $mac_cli .= $data[$i];
            }
            $mac_cli = Util::bintohex($mac_cli);
            // we cannot save the network name here, as on the submission we don't get this
            $hash = new HashBinary(0, $hashlist->getId(), $mac_ap . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $mac_cli . $CONFIG->getVal(DConfig::FIELD_SEPARATOR) . $network, Util::bintohex($data), null, 0, null, 0);
            $FACTORIES::getHashBinaryFactory()->save($hash);
            $added++;
        }
        fclose($file);
        unlink($fileInTmp);
        $hashlist->setHashCount($added);
        $FACTORIES::getHashlistFactory()->update($hashlist);

        $color = null;
        $accessGroup = $FACTORIES::getAccessGroupFactory()->get($hashlist->getAccessGroupId());

        if (strpos($cmdline, $CONFIG->getVal(DConfig::HASHLIST_ALIAS)) === false) {
            $this->sendErrorResponse("","Missing Hashlist alias in TaskCreation");
        }
        else if ($accessGroup == null) {
            $this->sendErrorResponse("", "Invalid access group!");
        }
        else if (Util::containsBlacklistedChars($cmdline)) {
            $this->sendErrorResponse("","The command must contain no blacklisted characters!");
        }
        else if ($crackerBinary == null || $crackerBinaryType == null) {
            $this->sendErrorResponse("", "Invalid cracker binary selection!");
        }
        else if ($hashlist == null) {
            $this->sendErrorResponse("", "Invalid hashlist selected!");
        }
        else if ($chunk < 0 || $status < 0 || $chunk < $status) {
            $this->sendErrorResponse("", "Chunk time must be higher than status timer!");
        }

        if ($skipKeyspace < 0) {
            $skipKeyspace = 0;
        }

        $hashlistId = $hashlist->getId();
        if (strlen($name) == 0) {
            $name = "HL" . $hashlistId . "_" . date("Ymd_Hi");
        }
        $forward = "tasks.php";
        if ($hashlistId != null && $hashlist->getHexSalt() == 1 && strpos($cmdline, "--hex-salt") === false) {
            $cmdline = "--hex-salt $cmdline"; // put the --hex-salt if the user was not clever enough to put it there :D
        }

        $FACTORIES::getAgentFactory()->getDB()->beginTransaction();
        $taskWrapper = new TaskWrapper(0, 0, DTaskTypes::NORMAL, $hashlistId, $accessGroup->getId(), "");
        $taskWrapper = $FACTORIES::getTaskWrapperFactory()->save($taskWrapper);
        $task = new Task(0, $name, $cmdline, $chunk, $status, 0, 0, 0, $color, $isSmall, $isCpuTask, $useNewBench, $skipKeyspace, CrackerBinaryUtils::getNewestVersion($crackerBinaryType->getId())->getId(), $crackerBinaryType->getId(), $taskWrapper->getId());
        $task = $FACTORIES::getTaskFactory()->save($task);
        if (isset($QUERY["adfile"])) {
            foreach ($QUERY["adfile"] as $fileId) {
                $taskFile = new FileTask(0, $fileId, $task->getId());
                $FACTORIES::getFileTaskFactory()->save($taskFile);
            }
        }
        $FACTORIES::getAgentFactory()->getDB()->commit();

        $payload = new DataSet(array(DPayloadKeys::TASK => $task));
        NotificationHandler::checkNotifications(DNotificationType::NEW_TASK, $payload);

        $stringName = (string)$name;
        $this->sendResponse("","Successfully created task named {$stringName} and new hashlist");
        die();
    }
}