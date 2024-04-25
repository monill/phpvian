<?php

namespace PHPvian\Automation;

use PHPvian\Libs\Database;

class BuildComplete
{
    public function buildComplete()
    {
        $db = new Database();
        $time = time();
        $array = [];
        $q = "SELECT `id`,`wid`,`field`,`level`,`type` FROM bdata where timestamp <= :time";
        $params = [':time' => $time];
        $array = $db->select('bdata', '*', 'timestamp <= :time', $params);

        foreach ($array as $indi) {
            $q = "UPDATE fdata set f{$indi['field']} = :level, f{$indi['field']}t = :type where vref = :wid";
            $params = [
                ':level' => $indi['level'],
                ':type' => $indi['type'],
                ':wid' => $indi['wid']
            ];
            if ($indi['level'] == 100 && $indi['type'] == 40) {
                $cfg = $db->selectFirst('config', 'winmoment');
                if ($cfg['winmoment'] <= 0) {
                    $db->update('config', ['winmoment' => time()], '1');
                }
                $user = $db->selectFirst('vdata', 'owner', 'wref = :wid', [':wid' => $indi['wid']]);
                $user = $user['owner'];
                $email = $db->selectFirst('users', 'email', 'id = :user', [':user' => $user]);
                $email = $email['email'];
                $db->insert('medal', ['userid' => $user, 'categorie' => 'winner', 'status' => 'sv_', 'email' => $email, 'img' => 'tw']);
            }
            if ($db->executeQuery($q, $params)) {
                $resource = $this->resourceRequiredbcom($indi['wid'], $indi['field'], $indi['type']);
                $db->modifyPop($indi['wid'], $resource['pop'], 0);
                $db->addCP($indi['wid'], $resource['cp']);
                $level = $db->getFieldLevel($indi['wid'], $indi['field']);

                if ($indi['type'] == 18) {
                    $owner = $db->getVillageField($indi['wid'], "owner");
                    $max = $bid18[$level]['attri'];
                    $db->update('alidata', ['max' => $max], 'leader = :owner', [':owner' => $owner]);
                }

                if (in_array($indi['type'], [10, 38])) {
                    $field = ($indi['type'] == 10) ? 'maxstore' : 'maxcrop';
                    $max = $db->getVillageField($indi['wid'], $field);
                    if ($level == '1' && $max == STORAGE_BASE) {
                        $max -= STORAGE_BASE;
                    }
                    if ($level > 1) $max -= $this->bidLevels[$indi['type']][$level - 1]['attri'] * STORAGE_MULTIPLIER;
                    $max += $this->bidLevels[$indi['type']][$level]['attri'] * STORAGE_MULTIPLIER;
                    $db->setVillageField($indi['wid'], $field, $max);
                }

                $q4 = "UPDATE bdata set loopcon = 0 where loopcon = 1 and wid = :wid";
                $db->update('bdata', ['loopcon' => 0], 'loopcon = 1 and wid = :wid', [':wid' => $indi['wid']]);
                $db->delete('bdata', 'id = :id', [':id' => $indi['id']]);
            }
        }
    }

    public function resourceRequiredbcom($wid, $id, $tid)
    {
        $db = new Database();
        $name = "bid" . $tid;
        global $$name;
        $dataarray = $$name;
        $vilres = $db->getResourceLevel($wid);
        if (isset($dataarray[$vilres['f' . $id] + 1])) {
            $pop = $dataarray[$vilres['f' . $id] + 1]['pop'];
            $cp = $dataarray[$vilres['f' . $id] + 1]['cp'];
            return array("pop" => $pop, "cp" => $cp);
        }
    }
}
