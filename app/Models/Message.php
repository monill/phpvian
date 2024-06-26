<?php

namespace PHPvian\Models;

use PHPvian\Libs\Database;
use PHPvian\Libs\Session;

class Message
{
    public $unread, $nunread = FALSE;
    public $note;
    public $inbox, $sent, $reading, $reply, $archived, $noticearray, $readingNotice = array();
    public $allNotice = array();
    private $totalMessage, $totalNotice;
    private $database;

    public function __construct()
    {
        $this->database = new Database();

//        $this->getMessages();
//        $this->getNotice();
//        if ($this->totalMessage > 0) {
//            $this->unread = $this->checkUnread();
//        }
//        if ($this->totalNotice > 0) {
//            $this->nunread = $this->checkNUnread();
//        }
//        if (isset($_SESSION['reply'])) {
//            $this->reply = $_SESSION['reply'];
//            unset($_SESSION['reply']);
//        }
    }

    private function getMessages()
    {
        $this->inbox = $this->database->getMessage(Session::get('uid'), 1);
        $this->sent = $this->database->getMessage(Session::get('uid'), 2);
        //$this->inbox1 = $this->database->getMessage($session->uid, 9);
        //$this->sent1 = $this->database->getMessage($session->uid, 10);
        if (Session::get('goldclub')) {
            $this->archived = $this->database->getMessage(Session::get('uid'), 6);
            $this->archived1 = $this->database->getMessage(Session::get('uid'), 11);
        }
        $this->totalMessage = count($this->inbox) + count($this->sent);
    }

    private function getNotice()
    {
        $this->allNotice = $this->database->getNotice($session->uid);
        $this->noticearray = $this->filter_by_value_except($this->allNotice, "ntype", 9);
        $this->totalNotice = count($this->allNotice);
    }

    private function filter_by_value_except($array, $index, $value)
    {
        $newarray = array();
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];

                if ($temp[$key] != $value) {
                    array_push($newarray, $array[$key]);
                    //$newarray[$key] = $array[$key];
                }
            }
        }
        return $newarray;
    }

    private function checkUnread()
    {
        foreach ($this->inbox as $message) {
            if ($message['viewed'] == 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function checkNUnread()
    {
        foreach ($this->allNotice as $notice) {
            if ($notice['viewed'] == 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function procMessage($post)
    {
        if (isset($post['ft'])) {
            switch ($post['ft']) {
                case "m1":
                    $this->quoteMessage($post['id']);
                    break;
                case "m2":
                    if ($session->is_sitter == 1) {
                        $setting = $this->database->getUsersetting($session->uid);
                        $who = $this->database->whoissitter($session->uid);
                        if ($who['whosit_sit'] == 1) {
                            $settings = $setting['sitter1_set_5'];
                        } elseif ($who['whosit_sit'] == 2) {
                            $settings = $setting['sitter2_set_5'];
                        }
                    }
                    if ((isset($settings) && $settings == 1) || $session->is_sitter == 0) {
                        global $form;
                        $loadtime = $_POST['loadtime'];
                        $totaltime = time() - $loadtime;
                        if ($totaltime > 7) {
                            if (isset($_SESSION['last_msg'])) {
                                if (time() < $_SESSION['last_msg']) {
                                    $form->addError("message", MS_SPAMMSG);
                                } elseif (isset($_POST['ses']) and trim(md5(md5($_POST['ses']))) != $_COOKIE['USERC']) {
                                    $form->addError("message", MS_WRONGECAPTCHA);
                                } elseif ($_POST['robots'] != '') {
                                    $form->addError("message", MS_SPAMMSG2);
                                }
                            }
                        } else {
                            $form->addError("message", MS_SPAMMSG2);
                        }
                        if ($form->returnErrors() > 0) {
                            $_SESSION['errorarray'] = $form->getErrors();
                            $_SESSION['valuearray'] = $_POST;
                            header("Location: nachrichten.php?t=1");
                            exit;
                        } else {
                            $_SESSION['last_msg'] = time() + 10;
                            if ($post['an'] == "[ally]") {
                                $this->sendAMessage($post['be'], $post['message']);
                            } else {
                                $this->sendMessage($post['an'], $post['be'], $post['message']);
                            }
                        }
                        header("Location: nachrichten.php?t=2");
                        exit;
                    }
                    break;
                case "m3":
                case "m4":
                case "m5":
                    if ($session->is_sitter == 1) {
                        $setting = $this->database->getUsersetting($session->uid);
                        $who = $this->database->whoissitter($session->uid);
                        if ($who['whosit_sit'] == 1) {
                            $settings = $setting['sitter1_set_6'];
                        } else if ($who['whosit_sit'] == 2) {
                            $settings = $setting['sitter2_set_6'];
                        }
                    }
                    if ((isset($settings) && $settings == 1) || $session->is_sitter != 1) {
                        if (isset($post['delmsg'])) {
                            $this->removeMessage($post);
                        }
                        if (isset($post['archive'])) {
                            if ($session->goldclub) {
                                $this->archiveMessage($post);
                            }
                        }
                        if (isset($post['unarchive'])) {
                            if ($session->goldclub) {
                                $this->unarchiveMessage($post);
                            }
                        }
                    }
                    break;
                case "m6":
                    $this->createNote($post);
                    break;
                case 'm8':
                    if ($session->is_sitter == 1) {
                        $setting = $this->database->getUsersetting($session->uid);
                        $who = $this->database->whoissitter($session->uid);
                        if ($who['whosit_sit'] == 1) {
                            $settings = $setting['sitter1_set_6'];
                        } else if ($who['whosit_sit'] == 2) {
                            $settings = $setting['sitter2_set_6'];
                        }
                    }
                    if ((isset($settings) && $settings == 1) || $session->is_sitter != 1) {
                        $this->removeMessageReport($post);
                        break;
                    }
            }
        }
    }

    public function quoteMessage($id)
    {
        foreach ($this->inbox as $message) {
            if ($message['id'] == $id) {
                $this->reply = $_SESSION['reply'] = $message;
                header("Location: nachrichten.php?t=1&id=" . $message['owner']);
                exit;
            }
        }
    }

    private function sendAMessage($topic, $text)
    {
        $allmembersQ = mysql_query("SELECT id,`username` FROM users WHERE alliance='" . $session->alliance . "'");
        $userally = $this->database->getUserField($session->uid, "alliance", 0);
        $permission = mysql_fetch_array(mysql_query("SELECT opt7 FROM ali_permission WHERE uid='" . $session->uid . "'"));

        if (WORD_CENSOR) {
            $topic = $this->wordCensor($topic);
            $text = $this->wordCensor($text);
        }
        if ($topic == "") {
            $topic = MS_NOSUBJECT;
        }
        if ($permission['opt7'] == 1) {
            if ($userally != 0) {
                while ($allmembers = mysql_fetch_array($allmembersQ)) {
                    // echo $allmembers[id];
                    $this->sendMessage($allmembers['username'], $topic, $text);
                }
            }
        }
    }

    private function wordCensor($text)
    {
        $censorarray = explode(",", CENSORED);
        foreach ($censorarray as $key => $value) {
            $censorarray[$key] = "/" . $value . "/i";
        }
        return preg_replace($censorarray, "****", $text);
    }

    private function sendMessage($recieve, $topic, $text)
    {
        global $form;
        $user = $this->database->getUserField($recieve, "id", 1);
        if ($user == 1) $user = 4;
        if (WORD_CENSOR) {
            $topic = $this->wordCensor($topic);
            $text = $this->wordCensor($text);
        }
        // $user = $this->database->getUserField($recieve, 'id', 1);
        $usern = $this->database->getUserField($recieve, 'username', 1);
        $z = mysql_query("SELECT `ignore_msg` FROM users WHERE id = '" . $user . "'") or die(mysql_error());
        $z = mysql_fetch_assoc($z);
        $dataarray = explode(',', $z['ignore_msg']);
        foreach ($dataarray as $param) {
            if ($param == $session->uid) {
                $exist = 1;
                break;
            }
        }
        if ($exist == 1) {
            $form->addError("message", sprintf(MS_BLACKEDYOU, "<b>" . $usern . "</b>"));
            $_SESSION['errorarray'] = $form->getErrors();
            $_SESSION['valuearray'] = $_POST;

            return;
        }
        $alliance = $player = $coor = $report = 0;
        for ($i = 0; $i <= $alliance; $i++) {
            if (preg_match('/\[alliance' . $i . '\]/', $text) && preg_match('/\[\/alliance' . $i . '\]/', $text)) {

                $alliance1 = preg_replace('/\[message\](.*?)\[\/alliance' . $i . '\]/is', '', $text);
                if (preg_match('/\[alliance' . $i . '\]/', $alliance1) && preg_match('/\[\/alliance' . $i . '\]/', $alliance1)) {
                    $j = $i + 1;
                    $alliance2 = preg_replace('/\[\/alliance' . $i . '\](.*?)\[\/message\]/is', '', $text);
                    $alliance1 = preg_replace('/\[alliance' . $i . '\]/', '[alliance' . $j . ']', $alliance1);
                    $alliance1 = preg_replace('/\[\/alliance' . $i . '\]/', '[/alliance' . $j . ']', $alliance1);
                    $text = $alliance2 . '[/alliance' . $i . ']' . $alliance1;
                    $alliance += 1;
                }
            }
        }
        for ($i = 0; $i <= $player; $i++) {
            if (preg_match('/\[player' . $i . '\]/', $text) && preg_match('/\[\/player' . $i . '\]/', $text)) {
                $player1 = preg_replace('/\[message\](.*?)\[\/player' . $i . '\]/is', '', $text);
                if (preg_match('/\[player' . $i . '\]/', $player1) && preg_match('/\[\/player' . $i . '\]/', $player1)) {
                    $j = $i + 1;
                    $player2 = preg_replace('/\[\/player' . $i . '\](.*?)\[\/message\]/is', '', $text);
                    $player1 = preg_replace('/\[player' . $i . '\]/', '[player' . $j . ']', $player1);
                    $player1 = preg_replace('/\[\/player' . $i . '\]/', '[/player' . $j . ']', $player1);
                    $text = $player2 . '[/player' . $i . ']' . $player1;
                    $player += 1;
                }
            }
        }
        for ($i = 0; $i <= $coor; $i++) {
            if (preg_match('/\[coor' . $i . '\]/', $text) && preg_match('/\[\/coor' . $i . '\]/', $text)) {
                $coor1 = preg_replace('/\[message\](.*?)\[\/coor' . $i . '\]/is', '', $text);
                if (preg_match('/\[coor' . $i . '\]/', $coor1) && preg_match('/\[\/coor' . $i . '\]/', $coor1)) {
                    $j = $i + 1;
                    $coor2 = preg_replace('/\[\/coor' . $i . '\](.*?)\[\/message\]/is', '', $text);
                    $coor1 = preg_replace('/\[coor' . $i . '\]/', '[coor' . $j . ']', $coor1);
                    $coor1 = preg_replace('/\[\/coor' . $i . '\]/', '[/coor' . $j . ']', $coor1);
                    $text = $coor2 . '[/coor' . $i . ']' . $coor1;
                    $coor += 1;
                }
            }
        }
        for ($i = 0; $i <= $report; $i++) {
            if (preg_match('/\[report' . $i . '\]/', $text) && preg_match('/\[\/report' . $i . '\]/', $text)) {
                $report1 = preg_replace('/\[message\](.*?)\[\/report' . $i . '\]/is', '', $text);
                if (preg_match('/\[report' . $i . '\]/', $report1) && preg_match('/\[\/report' . $i . '\]/', $report1)) {
                    $j = $i + 1;
                    $report2 = preg_replace('/\[\/report' . $i . '\](.*?)\[\/message\]/is', '', $text);
                    $report1 = preg_replace('/\[report' . $i . '\]/', '[report' . $j . ']', $report1);
                    $report1 = preg_replace('/\[\/report' . $i . '\]/', '[/report' . $j . ']', $report1);
                    $text = $report2 . '[/report' . $i . ']' . $report1;
                    $report += 1;
                }
            }
        }
        if ($topic == "") {
            $topic = MS_NOSUBJECT;
        }
        $topic = htmlspecialchars(addslashes($topic));
        $text = htmlspecialchars(addslashes($text));

        $this->database->sendMessage($user, $session->uid, htmlspecialchars(addslashes($topic)), htmlspecialchars(addslashes($text)), 0, $alliance, $player, $coor, $report);
    }

    private function removeMessage($post)
    {
        for ($i = 1; $i <= 10; $i++) {
            if (isset($post['n' . $i])) {
                $message1 = mysql_query("SELECT `target`,`owner` FROM mdata where id = " . $post['n' . $i] . " LIMIT 1");
                $message = mysql_fetch_array($message1);


                if ($message['target'] == $session->uid && $message['owner'] == $session->uid) {
                    $this->database->getMessage($post['n' . $i], 8);
                } else if ($message['target'] == $session->uid) {
                    $this->database->getMessage($post['n' . $i], 5);
                } else if ($message['owner'] == $session->uid) {
                    $this->database->getMessage($post['n' . $i], 7);
                }
            }
        }
        if (isset($post['t'])) {
            header('Location: nachrichten.php?t=' . $post['t']);
        } else {
            header('Location: nachrichten.php');
        }
        exit;
    }

    private function archiveMessage($post)
    {
        for ($i = 1; $i <= 10; $i++) {
            if (isset($post['n' . $i]) and is_numeric($post['n' . $i])) {
                $this->database->setArchived($post['n' . $i]);
            }
        }
        header("Location: nachrichten.php" . (isset($post['t']) ? '?t=' . $post['t'] : ''));
        exit;
    }

    private function unarchiveMessage($post)
    {
        for ($i = 1; $i <= 10; $i++) {
            if (isset($post['n' . $i])) {
                $this->database->setNorm($post['n' . $i]);
            }
        }
        header("Location: nachrichten.php" . (isset($post['t']) ? '?t=' . $post['t'] : ''));
        exit;
    }

    private function createNote($post)
    {
        // if($session->plus) {
        // $ourFileHandle = fopen("GameEngine/Notes/" . md5($session->username) . ".txt", 'w');
        // fwrite($ourFileHandle, $post['notizen']);
        // fclose($ourFileHandle);
        // }
        // header("Location: nachrichten.php".(isset($post['t'])?'?t='.$post['t']:''));exit;

        $str = $post['notizen'];
        $str = filter_var($str, FILTER_SANITIZE_STRING);
        $file_name = md5(md5($session->username));
        $file_name = md5(md5($file_name));
        $ourFileHandle = fopen('GameEngine/Notes/' . $file_name . '.txt', 'w');
        if (strlen($str) > 400) $str = substr($str, 0, 200) . '...' . '(Reached Limit character)';
        fwrite($ourFileHandle, $str);
        fclose($ourFileHandle);
    }

    private function removeMessageReport($post)
    {
        if ($session->uid > 2) {
            for ($i = 1; $i <= 10; $i++) {
                if (isset($post['n' . $i])) {
                    $message1 = mysql_query("SELECT `target`,`owner` FROM msg_report where id = " . $post['n' . $i] . " LIMIT 1");
                    $message = mysql_fetch_array($message1);
                    $this->database->removeMReport($post['n' . $i]);
                }
            }
        }
        header('Location: nachrichten.php?t=6');
        exit;
    }

    public function noticeType($get)
    {
        if (isset($get['t'])) {
            if ($get['t'] == 4 && !$session->plus) {
                header("Location: berichte.php");
                exit;
            } elseif ($get['t'] == 3) {
                $atttype = array(1, 2, 3, 4, 5, 6, 7);
                $this->noticearray = $this->filter_by_value($this->allNotice, "ntype", $atttype);
            } elseif ($get['t'] == 1) {
                $type = 8;
                $this->noticearray = $this->filter_by_value($this->allNotice, "ntype", array($type));
            } elseif ($get['t'] == 2) {
                $type = 9;
                $this->noticearray = $this->filter_by_value($this->allNotice, "ntype", array($type));
            }
        }
        if (isset($get['id'])) {
            $this->readingNotice = $this->getReadNotice($get['id']);
        }
    }

    private function filter_by_value($array, $index, $value)
    {
        $newarray = array();
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];

                if (in_array($temp[$key], $value)) {
                    array_push($newarray, $array[$key]);
                    //$newarray[$key] = $array[$key];
                }
            }
        }
        return $newarray;
    }

    private function getReadNotice($id)
    {
        foreach ($this->allNotice as $notice) {
            if ($notice['id'] == $id) {
                $this->database->noticeViewed($notice['id']);
                return $notice;
            }
        }
    }

    public function procNotice($post)
    {
        if ($session->is_sitter == 1) {
            $setting = $this->database->getUsersetting($session->uid);
            $who = $this->database->whoissitter($session->uid);
            if ($who['whosit_sit'] == 1) {
                $settings = $setting['sitter1_set_6'];
            } else if ($who['whosit_sit'] == 2) {
                $settings = $setting['sitter2_set_6'];
            }
        }
        if ($settings == 1 || $session->is_sitter == 0) {
            if (isset($post["delntc"])) {
                $this->removeNotice($post);
            }
            if (isset($post['archive'])) {
                $this->archiveNotice($post);
            }
            if (isset($post['start'])) {
                $this->unarchiveNotice($post);
            }
        }
    }

    private function removeNotice($post)
    {
        for ($i = 1; $i <= 10; $i++) {
            if (isset($post['n' . $i])) {
                $this->database->removeNotice($post["n{$i}"]);
            }
        }
        if (isset($post['t'])) {
            header("Location: berichte.php?t=" . $post['t'] . "");
            exit;
        } else {
            header("Location: berichte.php");
            exit;
        }
    }

    //7 = village, attacker, att tribe, u1 - u10, lost %, w, c, i, c, cap
    //8 = village, attacker, att tribe, enforcement
    private function archiveNotice($post)
    {
        for ($i = 1; $i <= 10; $i++) {
            if (isset($post['n' . $i])) {
                $this->database->archiveNotice($post['n' . $i]);
            }
        }
        header("Location: berichte.php" . (isset($post['t']) ? '?t=' . $post['t'] : ''));
        exit;
    }

    private function unarchiveNotice($post)
    {
        for ($i = 1; $i <= 10; $i++) {
            if (isset($post['n' . $i])) {
                $this->database->unarchiveNotice($post['n' . $i]);
            }
        }
        header("Location: berichte.php" . (isset($post['t']) ? '?t=' . $post['t'] : ''));
        exit;
    }

    public function loadMessage($id)
    {
        if ($session->is_sitter == 1) {
            $setting = $this->database->getUsersetting($session->uid);
            $who = $this->database->whoissitter($session->uid);
            if ($who['whosit_sit'] == 1) {
                $settings = $setting['sitter1_set_5'];
            } else if ($who['whosit_sit'] == 2) {
                $settings = $setting['sitter2_set_5'];
            }
        }
        if ($settings == 1 || $session->is_sitter != 1) {
            if ($this->findInbox($id)) {
                foreach ($this->inbox as $message) {
                    if ($message['id'] == $id) {
                        if ($message['delowner'] == 0 && $message['deltarget'] == $session->uid) {
                            break;
                        }
                        $this->reading = $message;
                    }
                }
            }
            if ($this->findSent($id)) {
                foreach ($this->sent as $message) {
                    if ($message['id'] == $id) {
                        if ($message['delowner'] == $session->uid && $message['deltarget'] == 0) {
                            break;
                        }
                        $this->reading = $message;
                    }
                }
            }
            if ($session->goldclub && $this->findArchive($id)) {
                foreach ($this->archived as $message) {
                    if ($message['id'] == $id) {
                        $this->reading = $message;
                    }
                }
            }
            if ($this->reading == null) {
                header("Location: nachrichten.php");
                exit;
            }
            if ($this->reading['viewed'] == 0) {
                $this->database->getMessage($id, 4);
            }
        } else {
            echo "<font color=red>can't access!! You Don't Have Permision To read Messages</font>";
        }
    }

    private function findInbox($id)
    {
        foreach ($this->inbox as $message) {
            if ($message['id'] == $id) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function findSent($id)
    {
        foreach ($this->sent as $message) {
            if ($message['id'] == $id) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function findArchive($id)
    {
        if ($this->archived['id'] == $id) {
            return TRUE;
        }

        return FALSE;
    }

    public function loadNotes()
    {
        $file_name = md5(md5($session->username));
        $file_name = md5(md5($file_name));
        if (file_exists('GameEngine/Notes/' . $file_name . '.txt')) {
            $this->note = file_get_contents('GameEngine/Notes/' . $file_name . '.txt');
        } else {
            $this->note = '';
        }
    }

    public function sendWelcome($userID, $username)
    {
        $file = dirname(__DIR__) . '/../resources/views/templates/messages/welcome.php';
        $welcomemsg = file_get_contents($file);
        $welcomemsg = preg_replace("'%USER%'", $username, $welcomemsg);
        $welcomemsg = preg_replace("'%START%'", date("Y/m/d", setting('commence')), $welcomemsg);
        $welcomemsg = preg_replace("'%TIME%'", date("H:i", setting('commence')), $welcomemsg);
        $welcomemsg = preg_replace("'%PLAYERS%'", $this->database->countUser(), $welcomemsg);
        $welcomemsg = preg_replace("'%ALLI%'", $this->database->countAlli(), $welcomemsg);

        $this->database->sendwlcMessage($userID, 0, 'welcome', $welcomemsg, 0);
    }
}