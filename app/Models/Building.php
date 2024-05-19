<?php

namespace PHPvian\Models;

use PHPvian\Libs\Connection;
use PHPvian\Libs\Session;

class Building
{
    public $newBuilding = false;
    public $buildArray = [];
    public $database, $village;
    private $maxConcurrent;
    private $allocated;
    private $basic, $inner, $plus = 0;

    public function __construct()
    {
        $this->maxConcurrent = setting('basic_max');
        if (setting('allow_all_tribe') || Session::get('tribe') == 1) {
            $this->maxConcurrent += setting('inner_max');
        }
        if (Session::get('plus')) {
            $this->maxConcurrent += setting('plus_max');
        }
        $this->loadBuilding();
        $this->database = new Connection();
        $this->village = new Village();
    }

    private function loadBuilding()
    {
        $this->buildArray = $this->database->getJobs($this->village->wid);
        $this->allocated = count($this->buildArray);
        if ($this->allocated > 0) {
            foreach ($this->buildArray as $build) {
                if ($build['loopcon'] == 1) {
                    $this->plus = 1;
                } else {
                    if ($build['field'] <= 18) {
                        $this->basic += 1;
                    } else {
                        if (Session::get('tribe') == 1 || setting('allow_all_tribe')) {
                            $this->inner += 1;
                        } else {
                            $this->basic += 1;
                        }
                    }
                }
            }
            $this->newBuilding = true;
        }
    }

    public function procBuild($get)
    {
        if (isset($get['a']) && $get['c'] == Session::get('checker') && !isset($get['id'])) {
            if ($get['a'] == 0) {
                $this->removeBuilding($get['d']);
            } elseif (isset($get['ins']) && Session::get('userinfo')['gold'] >= g_level20) {
                Session::changeChecker();
                $FIELD_ID = intval($get['a']);
                $this->database->query("DELETE FROM bdata WHERE wid=" . $this->village->wid . " AND field=" . $FIELD_ID);
                $RPA_LEVEL = $this->village->resarray['f' . $FIELD_ID];
                $FIELD_BID = $this->village->resarray['f' . $FIELD_ID . 't'];
                $maxLvL = count($GLOBALS['bid' . $FIELD_BID]);
                if ($FIELD_BID <= 4) {
                    $maxLvL--;
                    if (!$this->village->capital) {
                        $maxLvL = 10;
                    }
                }
                if ($maxLvL > 20) {
                    return;
                }
                $bindicate = $this->canBuild($FIELD_ID, $this->village->resarray['f' . $FIELD_ID . 't']);
                if (!in_array($FIELD_BID, array(40, 25, 26)) && !in_array($bindicate, array(1, 10, 11))) {
                    if ($maxLvL - $RPA_LEVEL) {
                        $pop = $cp = 0;
                        for ($i = 1 + $RPA_LEVEL; $i <= $maxLvL; ++$i) {
                            $pop += $GLOBALS['bid' . $FIELD_BID][$i]['pop'];
                            $cp += $GLOBALS['bid' . $FIELD_BID][$i]['cp'];
                        }
                        $this->database->modifyPop($this->village->wid, $pop, 0);
                        $this->database->addCP($this->village->wid, $cp / SPEED);
                        $this->database->modifyFieldType($this->village->wid, $FIELD_ID, $FIELD_BID);
                        $this->database->modifyFieldLevel($this->village->wid, $FIELD_ID, $maxLvL - $RPA_LEVEL, true);
                        $this->database->modifyGold(Session::get('uid'), g_level20, 0);
                    }
                }
                if ($FIELD_ID >= 19) {
                    redirect('/dorf2');
                } else {
                    redirect('/dorf1');
                }
            } else {
                Session::changeChecker();
                $this->upgradeBuilding($get['a']);
            }
        }
        if (isset($get['a']) && $get['c'] == Session::get('checker') && isset($get['id'])) {
            Session::changeChecker();
            $this->constructBuilding($get['id'], $get['a']);
        }
        if (isset($get['cmd']) && $get['cmd'] == 'buildingFinish') {
            if (Session::get('gold') >= 2) {
                $this->finishAll();
            }
        }
    }

    private function removeBuilding($d)
    {
        foreach ($this->buildArray as $jobs) {
            if ($jobs['id'] == $d) {
                $uprequire = $this->resourceRequired($jobs['field'], $jobs['type']);
                if ($this->database->removeBuilding($d)) {
                    $this->database->modifyResource($this->village->wid, $uprequire['wood'], $uprequire['clay'], $uprequire['iron'], $uprequire['crop'], 1);
                    if ($jobs['field'] >= 19) {
                        redirect('/dorf2');
                    } else {
                        redirect('/dorf1');
                    }
                }
            }
        }
    }

    public function resourceRequired($id, $tid, $plus = 1)
    {
        $name = "bid" . $tid;
        $dataarray = $$name;
        if (isset($dataarray[$this->village->resarray['f' . $id] + $plus])) {
            $wood = $dataarray[$this->village->resarray['f' . $id] + $plus]['wood'];
            $clay = $dataarray[$this->village->resarray['f' . $id] + $plus]['clay'];
            $iron = $dataarray[$this->village->resarray['f' . $id] + $plus]['iron'];
            $crop = $dataarray[$this->village->resarray['f' . $id] + $plus]['crop'];
            $pop = $dataarray[$this->village->resarray['f' . $id] + $plus]['pop'];

            if ($tid == 15) {
                if ($this->getTypeLevel(15) == 0) {
                    $time = round($dataarray[$this->village->resarray['f' . $id] + $plus]['time'] / SPEED * 5);
                } else {
                    $time = round($dataarray[$this->village->resarray['f' . $id] + $plus]['time'] / SPEED);
                }
            } else {
                if ($this->getTypeLevel(15) != 0) {
                    $time = round($dataarray[$this->village->resarray['f' . $id] + $plus]['time'] * ($GLOBALS['bid15'][$this->getTypeLevel(15)]['attri'] / 100) / SPEED);
                } else {
                    $time = round($dataarray[$this->village->resarray['f' . $id] + $plus]['time'] * 5 / SPEED);
                }
            }
            $cp = $dataarray[$this->village->resarray['f' . $id] + $plus]['cp'];

            return array("wood" => $wood, "clay" => $clay, "iron" => $iron, "crop" => $crop, "pop" => $pop, "time" => $time, "cp" => $cp);
        }
    }

    public function getTypeLevel($tid)
    {
        $keyholder = [];
        if ($this->village === null) {
            $resourcearray = $this->village->resarray;
        } else {
            $resourcearray = $this->database->getResourceLevel($this->village->wid);
        }
        foreach (array_keys($resourcearray, $tid) as $key) {
            if (strpos($key, 't')) {
                $key = preg_replace("/[^0-9]/", '', $key);
                array_push($keyholder, $key);
            }
        }
        $element = count($keyholder);
        if ($element >= 2) {
            if ($tid <= 4) {
                $temparray = array();
                for ($i = 0; $i <= $element - 1; $i++) {
                    array_push($temparray, $resourcearray['f' . $keyholder[$i]]);
                }
                foreach ($temparray as $key => $val) {
                    if ($val == max($temparray))
                        $target = $key;
                }
            } else {
                $target = 0;
                for ($i = 1; $i <= $element - 1; $i++) {
                    if ($resourcearray['f' . $keyholder[$i]] > $resourcearray['f' . $keyholder[$target]]) {
                        $target = $i;
                    }
                }
            }
        } else if ($element == 1) {
            $target = 0;
        } else {
            return 0;
        }
        if (isset($keyholder[$target]) && $keyholder[$target] != "") {
            return $resourcearray['f' . $keyholder[$target]];
        } else {
            return 0;
        }
    }

    public function canBuild($id, $tid)
    {
        $demolition = $this->database->getDemolition($this->village->wid);
        $mur = $this->meetUpRequierments($id, $tid);
        if ($mur != 0) {
            return $mur;
        }
        if (!empty($demolition) && count($demolition) > 0 && $demolition['buildnumber'] == $id) {
            return 11;
        }
        if ($this->isMax($tid, $id)) {
            return 1;
        } elseif ($this->isMax($tid, $id, 1) && ($this->isLoop($id) || $this->isCurrent($id))) {
            return 10;
        } elseif ($this->isMax($tid, $id, 2) && $this->isLoop($id) && $this->isCurrent($id)) {
            return 10;
        } elseif ($this->isMax($tid, $id, 3) && $this->isLoop($id) && $this->isCurrent($id) && count($this->database->getMasterJobs($this->village->wid)) > 0) {
            return 10;
        } else {
            if ($this->allocated <= $this->maxConcurrent) {
                $resRequired = $this->resourceRequired($id, $this->village->resarray['f' . $id . 't']);
                $resRequiredPop = $resRequired['pop'];
                if ($resRequiredPop == "") {
                    $buildarray = $GLOBALS["bid" . $tid];
                    $resRequiredPop = $buildarray[1]['pop'];
                }
                $jobs = $this->database->getJobs($this->village->wid);
                if ($jobs > 0) {
                    $soonPop = 0;
                    foreach ($jobs as $j) {
                        $buildarray = $GLOBALS["bid" . $j['type']];
                        $soonPop += $buildarray[$this->database->getFieldLevel($this->village->wid, $j['field']) + 1]['pop'];
                    }
                }

                if (($this->village->getProd("crop") + $this->village->upkeep - $soonPop - $resRequiredPop) <= 0 && $this->village->resarray['f' . $id . 't'] <> 4) {
                    return 4;
                } else {
                    switch ($this->checkResource($tid, $id)) {
                        case 1:
                            return 5;
                        case 2:
                            return 6;
                        case 3:
                            return 7;
                        case 4:
                            if ($id >= 19) {
                                if (Session::get('tribe') == 1 || ALLOW_ALL_TRIBE) {
                                    if ($this->inner == 0) {
                                        return 8;
                                    } else {
                                        if (Session::get('plus')) {
                                            if ($this->plus == 0) {
                                                return 9;
                                            } else {
                                                return 3;
                                            }
                                        } else {
                                            return 2;
                                        }
                                    }
                                } else {
                                    if ($this->basic == 0) {
                                        return 8;
                                    } else {
                                        if (Session::get('plus')) {
                                            if ($this->plus == 0) {
                                                return 9;
                                            } else {
                                                return 3;
                                            }
                                        } else {
                                            return 2;
                                        }
                                    }
                                }
                            } else {
                                if ($this->basic == 1) {
                                    if (Session::get('plus') && $this->plus == 0) {
                                        return 9;
                                    } else {
                                        return 3;
                                    }
                                } else {
                                    return 8;
                                }
                            }
                    }
                }
            } else {
                return 2;
            }
        }
    }

    private function meetUpRequierments($id, $tid)
    {
        $wwlvl = $this->getTypeLevel(40);
        if (!(($this->village->resarray['f' . $id . 't'] != 0 && $tid == $this->village->resarray['f' . $id . 't']) || ($this->village->resarray['f' . $id . 't'] == 0 && $id != 99))) {
            return 1000;
        }

        switch ($tid) {
            case 38:
            case 39:
                foreach (Session::get('villages') as $vil) {
                    $artEffGrt = $this->database->getArtEffGrt($vil);

                    if ($artEffGrt > 0) break;
                }
                if ($artEffGrt <= 0 && $wwlvl <= 0) {
                    return 20;
                }
                break;
            case 40:
                $artEffBP = $this->database->getArtEffBP($this->village->wid);
                if ($artEffBP <= 0) {
                    return 21;
                } elseif ($wwlvl >= 50) {
                    $artEffAllyBP = $this->database->getArtEffAllyBP(Session::get('uid'));
                    if ($artEffAllyBP <= 0) {
                        return 22;
                    }
                }
                break;
        }

        return 0;
    }

    public function isMax($id, $field, $loop = 0)
    {
        $name = "bid" . $id;
        $dataarray = $$name;
        if ($this->village->resarray['f'.$field] > 20) {
            $this->database->query("UPDATE fdata SET `f".$field."` = 20 WHERE vref = '".$this->village->wid."'");
            return 1;
        }
        if ($id <= 4) {
            if ($this->village->capital == 1) {
                return ($this->village->resarray['f' . $field] == (count($dataarray) - 1 - $loop));
            } else {
                return ($this->village->resarray['f' . $field] == (count($dataarray) - 11 - $loop));
            }
        } else {
            return ($this->village->resarray['f' . $field] == count($dataarray) - $loop);
        }
    }

    public function isLoop($id = 0)
    {
        foreach ($this->buildArray as $build) {
            if (($build['field'] == $id && $build['loopcon']) || ($build['loopcon'] == 1 && $id == 0)) {
                return true;
            }
        }

        return false;
    }

    public function isCurrent($id)
    {
        foreach ($this->buildArray as $build) {
            if ($build['field'] == $id && $build['loopcon'] != 1) {
                return true;
            }
        }
        return false;
    }

    private function checkResource($tid, $id)
    {
        $name = "bid" . $tid;
        $plus = 1;
        foreach ($this->buildArray as $job) {
            if ($job['type'] == $tid && $job['field'] == $id) {
                $plus = 2;
            }
        }
        $dataarray = $$name;
        $wood = $dataarray[$this->village->resarray['f' . $id] + $plus]['wood'];
        $clay = $dataarray[$this->village->resarray['f' . $id] + $plus]['clay'];
        $iron = $dataarray[$this->village->resarray['f' . $id] + $plus]['iron'];
        $crop = $dataarray[$this->village->resarray['f' . $id] + $plus]['crop'];
        if ($wood > $this->village->maxstore || $clay > $this->village->maxstore || $iron > $this->village->maxstore) {
            return 1;
        } else {
            if ($crop > $this->village->maxcrop) {
                return 2;
            } else {
                if ($wood > $this->village->awood || $clay > $this->village->aclay || $iron > $this->village->airon || $crop > $this->village->acrop) {
                    return 3;
                } else {
                    if ($this->village->awood - $wood >= 0 && $this->village->aclay - $clay >= 0 && $this->village->airon - $iron >= 0 && $this->village->acrop - $crop >= 0) {
                        return 4;
                    } else {
                        return 3;
                    }
                }
            }
        }
    }

    private function upgradeBuilding($id)
    {
        $bindicate = $this->canBuild($id, $this->village->resarray['f' . $id . 't']);
        if (($this->allocated < $this->maxConcurrent) && ($bindicate == 8 || $bindicate == 9)) {
            $uprequire = $this->resourceRequired($id, $this->village->resarray['f' . $id . 't']);
            $time = time() + $uprequire['time'];
            $loop = ($bindicate == 9 ? 1 : 0);
            $loopsame = 0;
            if ($loop == 1) {
                foreach ($this->buildArray as $build) {
                    if ($build['field'] == $id) {
                        $loopsame += 1;
                        $uprequire = $this->resourceRequired($id, $this->village->resarray['f' . $id . 't'], ($loopsame > 0 ? 2 : 1));
                    }
                }
                if (Session::get('tribe') == 1 || ALLOW_ALL_TRIBE) {
                    if ($id >= 19) {
                        foreach ($this->buildArray as $build) {
                            if ($build['field'] >= 19) {
                                $time = $build['timestamp'] + $uprequire['time'];
                            }
                        }
                    } else {
                        foreach ($this->buildArray as $build) {
                            if ($build['field'] <= 18) {
                                $time = $build['timestamp'] + $uprequire['time'];
                            }
                        }
                    }
                } else {
                    $time = $this->buildArray['timestamp'] + $uprequire['time'];
                }
            }
            $level = $this->database->getResourceLevel($this->village->wid);
            $time = $time + ($loop == 1 ? ceil(60 / SPEED) : 0);
            $newlevel = $level['f' . $id] + 1 + count($this->database->getBuildingByField($this->village->wid, $id));
            if ($this->database->addBuilding($this->village->wid, $id, $this->village->resarray['f' . $id . 't'], $loop, $time, 0, $newlevel)) {
                $this->database->modifyResource($this->village->wid, $uprequire['wood'], $uprequire['clay'], $uprequire['iron'], $uprequire['crop'], 0);
                $this->logging->addBuildLog($this->village->wid, $this->procResType($this->village->resarray['f' . $id . 't']), ($this->village->resarray['f' . $id] + ($loopsame > 0 ? 2 : 1)), 0);
            }
        }
        if ($id >= 19) {
            redirect('/dorf2');
        } else {
            redirect('/dorf1');
        }
    }

    public function procResType($ref)
    {
        $resourceNames = [
            1 => "Wood",
            2 => "Clay",
            3 => "IronOre",
            4 => "GrainField",
            5 => "Sawmill Factory",
            6 => "Brick Making",
            7 => "Forging",
            8 => "Water Mill",
            9 => "Bakery",
            10 => "Storeroom",
            11 => "Warehouse",
            12 => "Smithy",
            13 => "Armoury",
            14 => "Practice field",
            15 => "The main building",
            16 => "Camp",
            17 => "Market",
            18 => "Embassy",
            19 => "Barrack",
            20 => "Stables",
            21 => "Workshop",
            22 => "Darfellon",
            23 => "Sanctuary",
            24 => "Chamber",
            25 => "Residence",
            26 => "Palace",
            27 => "Treasury",
            28 => "Factory",
            29 => "Great barracks",
            30 => "Great stables",
            31 => "City wall",
            32 => "Fence",
            33 => "Hedge",
            34 => "Masonry",
            35 => "Brewery",
            36 => "TrapMaker",
            37 => "HeroSMansion",
            38 => "Bunker",
            39 => "LargeGroceryStore",
            40 => "WondersOfTheWorld",
            41 => "Horse drinker",
            42 => "GreatWorkshop",
        ];

        return $resourceNames[$ref] ?? "Error";
    }

    private function constructBuilding($id, $tid)
    {
        if ($tid == 16) {
            $id = 39;
        } elseif (in_array($tid, [31, 32, 33])) {
            $id = 40;
        }

        $bindicate = $this->canBuild($id, $tid);

        if (($this->allocated < $this->maxConcurrent) && ($bindicate == 8 || $bindicate == 9)) {
            $uprequire = $this->resourceRequired($id, $tid);
            $time = time() + $uprequire['time'];
            $loop = ($bindicate == 9 ? 1 : 0);

            if ($this->meetRequirement($tid)) {
                $level = $this->database->getResourceLevel($this->village->wid);
                $time += ($loop == 1 ? ceil(60 / SPEED) : 0);
                $newlevel = $level['f' . $id] + 1 + count($this->database->getBuildingByField($this->village->wid, $id));

                if ($this->database->addBuilding($this->village->wid, $id, $tid, $loop, $time, 0, $newlevel)) {
                    $logging->addBuildLog($this->village->wid, $this->procResType($tid), ($this->village->resarray['f' . $id] + 1), 1);
                    $this->database->modifyResource($this->village->wid, $uprequire['wood'], $uprequire['clay'], $uprequire['iron'], $uprequire['crop'], 0);
                }
            }
        }
        redirect('/dorf2');
    }

    private function meetRequirement($id)
    {
        $lvl = $this->getTypeLevel($id);
        $bl = $this->database->getBuildList($id, $this->village->wid);
        switch ($id) {
            case 38:
            case 39:
                $artEffGrt = $this->database->getArtEffGrt($this->village->wid);
                if ($artEffGrt <= 0 && !$this->getTypeLevel(40)) return FALSE;
                $lvl10 = $this->getTypeLevel(10);
                $lvl11 = $this->getTypeLevel(11);
                $lvl38 = $this->getTypeLevel(38);
                $lvl39 = $this->getTypeLevel(39);
                if ($id == 38 && $lvl10 < 20 && $lvl38 == 0) return FALSE;
                if ($id == 39 && $lvl11 < 20 && $lvl39 == 0) return FALSE;
                break;
            case 10:
            case 11:
                if (($lvl > 0 && $lvl < 20) || ($lvl == 0 && count($bl) > 0)) return FALSE;
                break;
            case 23:
                if (($lvl > 0 && $lvl < 10) || ($lvl == 0 && count($bl) > 0)) return FALSE;
                break;
            case 36:
                if (($lvl > 0 && $lvl < 20) || ($lvl == 0 && count($bl) > 0)) return FALSE;
                break;
            default:
                if (count($bl) > 0 || ($lvl > 0)) return FALSE;
                break;
        }
        switch ($id) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 11:
            case 15:
            case 16:
            case 18:
            case 23:
            case 31:
            case 32:
            case 33:
                return TRUE;
                break;
            case 10:
            case 20:
                return ($this->getTypeLevel(15) >= 1) ? TRUE : FALSE;
                break;
            case 5:
                if ($this->getTypeLevel(1) >= 10 && $this->getTypeLevel(15) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 6:
                if ($this->getTypeLevel(2) >= 10 && $this->getTypeLevel(15) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 7:
                if ($this->getTypeLevel(3) >= 10 && $this->getTypeLevel(15) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 8:
                if ($this->getTypeLevel(4) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 9:
                if ($this->getTypeLevel(15) >= 5 && $this->getTypeLevel(4) >= 10 && $this->getTypeLevel(8) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 12:
                if ($this->getTypeLevel(22) >= 1 && $this->getTypeLevel(15) >= 3) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 13:
                if ($this->getTypeLevel(15) >= 3 && $this->getTypeLevel(22) >= 1) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 14:
                if ($this->getTypeLevel(16) >= 15) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 17:
                if ($this->getTypeLevel(15) >= 3 && $this->getTypeLevel(10) >= 1 && $this->getTypeLevel(11) >= 1) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 19:
                if ($this->getTypeLevel(15) >= 3 && $this->getTypeLevel(16) >= 1) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 20:
                if ($this->getTypeLevel(12) >= 3 && $this->getTypeLevel(22) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 21:
                if ($this->getTypeLevel(22) >= 10 && $this->getTypeLevel(15) >= 5) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 22:
                if ($this->getTypeLevel(15) >= 3 && $this->getTypeLevel(16) >= 1) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 24:
                if ($this->getTypeLevel(22) >= 10 && $this->getTypeLevel(15) >= 10) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 25:
                if ($this->getTypeLevel(15) >= 5 && $this->getTypeLevel(26) == 0) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 26:
                if ($this->getTypeLevel(18) >= 1 && $this->getTypeLevel(15) >= 5 && $this->getTypeLevel(25) == 0 && !$this->hasPalaceAnywhere()) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 27:
                if ($this->getTypeLevel(15) >= 10) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 28:
                if ($this->getTypeLevel(17) == 20 && $this->getTypeLevel(20) >= 10) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 29:
                if ($this->getTypeLevel(19) == 20 && $this->village->capital == 0) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 30:
                if ($this->getTypeLevel(20) == 20 && $this->village->capital == 0) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 34:
                if ($this->getTypeLevel(26) >= 3 && $this->getTypeLevel(15) >= 5 && $this->getTypeLevel(25) == 0) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 35:
                if ($this->getTypeLevel(16) >= 10 && $this->getTypeLevel(11) == 20) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 36:
                if ($this->getTypeLevel(16) >= 1 && Session::get('tribe') == 3) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 37:
                if ($this->getTypeLevel(15) >= 3 && $this->getTypeLevel(16) >= 1) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 38:
                if ($this->getTypeLevel(15) >= 10 && ($this->getTypeLevel(10) == 20 || $this->getTypeLevel(38))) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 39:
                if ($this->getTypeLevel(15) >= 10 && ($this->getTypeLevel(11) == 20 || $this->getTypeLevel(39))) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 40:
                $artEffBP = $this->database->getArtEffBP($this->village->wid);
                if ($artEffBP <= 0 || !$this->getTypeLevel(40)) {
                    return FALSE;
                } else {
                    return TRUE;
                }
                break;
            case 41:
                if ($this->getTypeLevel(16) >= 10 && $this->getTypeLevel(20) == 20) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case 42:
                if ($this->getTypeLevel(21) == 20 && $this->village->capital == 0) {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
        }
    }

    public function hasPalaceAnywhere()
    {
        $userVillages = $this->database->getArrayMemberVillage(Session::get('uid'));

        foreach ($userVillages as $v) {
            $lvl = $this->getTypeLevel(26, $v['wref']);
            $bl = $this->database->getBuildList(26, $v['wref']);

            if ($lvl || count($bl) > 0) {
                return true;
            }
        }

        return false;
    }

    private function finishAll()
    {
        foreach ($this->buildArray as $jobs) {
            $level = $this->database->getFieldLevel($jobs['wid'], $jobs['field']);
            $level = ($level == -1) ? 0 : $level;

            if ($jobs['type'] != 25 && $jobs['type'] != 26 && $jobs['type'] != 40) {
                $resource = $this->resourceRequired($jobs['field'], $jobs['type']);
                $q = "UPDATE fdata SET f" . $jobs['field'] . " = f" . $jobs['field'] . " + 1, f" . $jobs['field'] . "t = " . $jobs['type'] . " WHERE vref = " . $jobs['wid'];

                if ($this->database->query($q)) {
                    $this->database->modifyPop($jobs['wid'], $resource['pop'], 0);
                    $this->database->addCP($jobs['wid'], $resource['cp']);
                    $this->database->finishDemolition($this->village->wid);
                    $this->database->addCLP(Session::get('uid'), 7);

                    $q = "DELETE FROM bdata WHERE id = " . $jobs['id'];
                    $this->database->query($q);

                    switch ($jobs['type']) {
                        case 18:
                            $owner = $this->database->getVillageField($jobs['wid'], "owner");
                            $max = $bid18[$level]['attri'];
                            $q = "UPDATE alidata SET max = $max WHERE leader = $owner";
                            $this->database->query($q);
                            break;
                        case 10:
                        case 11:
                        case 38:
                        case 39:
                            $fieldName = ($jobs['type'] == 10 || $jobs['type'] == 38) ? "maxstore" : "maxcrop";
                            $max = $this->database->getVillageField($jobs['wid'], $fieldName);

                            if ($level == '0' && $this->getTypeLevel($jobs['type']) != 20) {
                                $max -= setting('storage_base');
                            }

                            $max -= ${"bid" . $jobs['type']}[$level]['attri'] * setting('storage_multiplier');
                            $max += ${"bid" . $jobs['type']}[$level + 1]['attri'] * setting('storage_multiplier');

                            $this->database->setVillageField($jobs['wid'], $fieldName, $max);
                            break;
                    }
                }
            }
        }

        $technology->finishTech();
        $logging->goldFinLog($this->village->wid);
        $this->database->modifyGold($session->uid, 2, 0);
        redirect('/' . $session->referrer);
    }

    public function walling()
    {
        $wall = [31, 32, 33];

        foreach ($this->buildArray as $job) {
            if (in_array($job['type'], $wall)) {
                return "3" . $session->tribe;
            }
        }
        return false;
    }

    public function rallying()
    {
        foreach ($this->buildArray as $job) {
            if ($job['type'] == 16) {
                return true;
            }
        }

        return false;
    }

    public function getTypeField($type)
    {
        for ($i = 19; $i <= 40; $i++) {
            if ($this->village->resarray['f' . $i . 't'] == $type) {
                return $i;
            }
        }
        return null;
    }

    public function calculateAvailable($id, $tid, $plus = 1)
    {
        $uprequire = $this->resourceRequired($id, $tid, $plus);

        $rwood = max(0, $uprequire['wood'] - $this->village->awood);
        $rclay = max(0, $uprequire['clay'] - $this->village->aclay);
        $rcrop = max(0, $uprequire['crop'] - $this->village->acrop);
        $riron = max(0, $uprequire['iron'] - $this->village->airon);

        $rwtime = ($rwood > 0) ? ($rwood / $this->village->getProd("wood") * 3600) : 0;
        $rcltime = ($rclay > 0) ? ($rclay / $this->village->getProd("clay") * 3600) : 0;
        $rctime = ($rcrop > 0) ? (($this->village->getProd("crop") <= 0) ? 172800 : ($rcrop / $this->village->getProd("crop") * 3600)) : 0;
        $ritime = ($riron > 0) ? ($riron / $this->village->getProd("iron") * 3600) : 0;

        $reqtime = time() + max($rwtime, $rctime, $rcltime, $ritime);

        return $generator->procMtime($reqtime);
    }

}