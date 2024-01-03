<?php
/**
 * @filesource modules/repair/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\Email;

use Kotchasan\Date;
use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ส่งอีเมลและ LINE แจ้งการทำรายการ
     *
     * @param array $order
     *
     * @return string
     */
    public static function send($order)
    {
        $lines = [];
        $emails = [];
        $name = '';
        $mailto = '';
        $line_uid = '';
        // ตรวจสอบรายชื่อผู้รับ
        $query = \Kotchasan\Model::createQuery()
            ->select('id', 'username', 'name', 'line_uid')
            ->from('user')
            ->where(array('active', 1))
            ->andWhere(array(
                array('id', $order['customer_id']),
                array('status', 1),
                array('permission', 'LIKE', '%,can_repair,%')
            ), 'OR')
            ->cacheOn();
        if (self::$cfg->demo_mode) {
            $query->andWhere(array('social', 0));
        }
        foreach ($query->execute() as $item) {
            if ($item->id == $order['customer_id']) {
                // ผู้จอง
                $name = $item->name;
                $mailto = $item->username;
                $line_uid = $item->line_uid;
            } else {
                // เจ้าหน้าที่
                $emails[] = $item->name.'<'.$item->username.'>';
                if ($item->line_uid != '') {
                    $lines[] = $item->line_uid;
                }
            }
        }

        // อ่านข้อมูลพัสดุ
        $inventory = \Inventory\Write\Model::get($order['inventory_id']);
        // ข้อความ
        $msg = array(
            '{LNG_Repair jobs}',
            '{LNG_Informer} : '.$name,
            '{LNG_Equipment} : '.$inventory->topic,
            '{LNG_Serial/Registration No.} : '.$inventory->product_no,
            '{LNG_Date} : '.Date::format($order['create_date']),
            '{LNG_Problems and repairs details} : '.$order['job_description']
        );
        // ข้อความของ user
        $msg = Language::trans(implode("\n", $msg));
        // ข้อความของแอดมิน
        $admin_msg = $msg."\nURL : ".WEB_URL.'index.php?module=repair-setup';
        // ส่งข้อความ
        $ret = [];
        if (!empty(self::$cfg->repair_line_id)) {
            // อ่าน token
            $search = \Kotchasan\Model::createQuery()
                ->from('line')
                ->where(array('id', self::$cfg->repair_line_id))
                ->cacheOn()
                ->first('token');
            if ($search) {
                $err = \Gcms\Line::notify($admin_msg, $search->token);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
        }
        if (!empty(self::$cfg->line_channel_access_token)) {
            // LINE ส่วนตัว
            if (!empty($lines)) {
                $err = \Gcms\Line::sendTo($lines, $admin_msg);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
            if (!empty($line_uid)) {
                $err = \Gcms\Line::sendTo($line_uid, $msg);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
        }
        if (self::$cfg->noreply_email != '') {
            // หัวข้ออีเมล
            $subject = '['.self::$cfg->web_title.'] '.Language::get('Repair jobs');
            // ส่งอีเมลไปยังผู้ทำรายการเสมอ
            $err = \Kotchasan\Email::send($name.'<'.$mailto.'>', self::$cfg->noreply_email, $subject, nl2br($msg));
            if ($err->error()) {
                // คืนค่า error
                $ret[] = strip_tags($err->getErrorMessage());
            }
            // ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
            if (!empty(self::$cfg->repair_send_mail)) {
                // รายละเอียดในอีเมล (แอดมิน)
                $admin_msg = nl2br($admin_msg);
                foreach ($emails as $item) {
                    // ส่งอีเมล
                    $err = \Kotchasan\Email::send($item, self::$cfg->noreply_email, $subject, $admin_msg);
                    if ($err->error()) {
                        // คืนค่า error
                        $ret[] = strip_tags($err->getErrorMessage());
                    }
                }
            }
        }
        // คืนค่า
        return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", array_unique($ret));
    }
}
