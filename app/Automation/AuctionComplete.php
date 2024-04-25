<?php

namespace PHPvian\Automation;

use PHPvian\Libs\Database;

class AuctionComplete
{
    public function auctionComplete()
    {
        try {
            $db = new Database();
            $time = time();
            $q = "SELECT `owner`,`uid`,`silver`,`btype`,`type`,`maxsilver`,`silver`,`num`,`id` FROM auction where finish = 0 and time <= $time LIMIT 100";
            $dataarray = $db->executeQuery($q)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($dataarray as $data) {
                $ownerID = $data['owner'];
                $biderID = $data['uid'];
                $silver = $data['silver'];
                $btype = $data['btype'];
                $type = $data['type'];
                $silverdiff = $data['maxsilver'] - $data['silver'];
                if ($silverdiff < 0) $silverdiff = 0;
                if ($biderID != 0) {
                    $id = $db->checkHeroItem($biderID, $btype, $type);
                    if ($id) {
                        $db->modifyHeroItem($id, 'num', $data['num'], 1);
                        $db->modifyHeroItem($id, 'proc', 0, 0);
                    } else {
                        $db->addHeroItem($biderID, $data['btype'], $data['type'], $data['num']);
                    }
                    $db->setSilver($biderID, $silverdiff, 1);
                    $db->update('users', ['bidsilver' => $db->quote('bidsilver') . '-' . $silverdiff], 'id = ' . $biderID);
                }
                $db->setSilver($ownerID, $silver, 1);
                $db->update('users', ['ausilver' => $db->quote('ausilver') . '+' . $silver], 'id = ' . $ownerID);
                $db->update('auction', ['finish' => 1], 'id = ' . $data['id']);
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error executing database query: " . $e->getMessage());
        }
    }

}