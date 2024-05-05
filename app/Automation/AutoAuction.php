<?php

namespace PHPvian\Automation;

use PHPvian\Libs\Connection;

class AutoAuction
{
    public function autoAuction()
    {
        $db = new Connection();
        $currentTime = $_SERVER['REQUEST_TIME'];

        try {
            $stmt = $db->query('SELECT `time`,`lasttime`,`id`,`number` FROM autoauction WHERE active = 1');
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $endTime = $row['time'] * 60 + $row['lasttime'];

                if ($endTime - $currentTime < 0) {
                    $btype = $row['id'];
                    $itemQty = $row['number'];
                    $itemTime = $row['time'] * 180;

                    switch ($btype) {
                        case 1:
                            $type = rand(1, 15);
                            break;
                        case 2:
                            $type = rand(82, 93);
                            break;
                        case 3:
                            $type = rand(61, 81);
                            break;
                        case 4:
                            $type = rand(16, 60);
                            break;
                        case 5:
                            $type = rand(94, 102);
                            break;
                        case 6:
                            $type = rand(103, 105);
                            break;
                        case 7:
                            $type = 112;
                            break;
                        case 8:
                            $type = 113;
                            break;
                        case 9:
                            $type = 114;
                            break;
                        case 10:
                            $type = 107;
                            break;
                        case 11:
                            $type = 106;
                            break;
                        case 12:
                            $type = 108;
                            break;
                        case 13:
                            $type = 110;
                            break;
                        case 14:
                            $type = 109;
                            break;
                        case 15:
                            $type = 111;
                            break;
                    }

                    $this->addAuctionNew(4, $btype, $type, $itemQty, $itemTime);
                }
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error in autoauction: " . $e->getMessage());
        }
    }

    public function addAuctionNew($owner, $btype, $type, $amount, $mtime)
    {
        $db = new Connection();
        $time = time() + $mtime;
        $data = [
            'uid' => $owner,
            'btype' => $btype,
            'type' => $type,
            'num' => $amount,
            'proc' => 1
        ];
        $item_id = $db->insert('heroitems', $data);

        if ($btype == 7 || $btype == 8 || $btype == 9 || $btype == 10 || $btype == 11 || $btype == 13 || $btype == 14) {
            $silver = $amount;
        } else {
            $silver = 100;
        }

        $data = [
            'owner' => $owner,
            'itemid' => $item_id,
            'btype' => $btype,
            'type' => $type,
            'num' => $amount,
            'uid' => 0,
            'bids' => 0,
            'silver' => $silver,
            'time' => $time,
            'finish' => 0
        ];
        $db->insert('auction', $data);
        $db->query("UPDATE autoauction SET lasttime = " . time() . " WHERE id = $btype");
    }

}