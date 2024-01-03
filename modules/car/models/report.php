<?php
/**
 * @filesource modules/car/models/report.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Report;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=car-report
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params)
    {
        $where = [];
        if ($params['status'] > -1) {
            $where[] = array('V.status', $params['status']);
        }
        if ($params['vehicle_id'] > 0) {
            $where[] = array('V.vehicle_id', $params['vehicle_id']);
        }
        if ($params['chauffeur'] > -2) {
            $where[] = array('V.chauffeur', $params['chauffeur']);
        }
        if (!empty($params['from'])) {
            $where[] = array('V.begin', '>=', $params['from'].' 00:00:00');
        }
        if (!empty($params['to'])) {
            $where[] = array('V.begin', '<=', $params['to'].' 23:59:59');
        }
        if (!empty($params['department'])) {
            $where[] = array('V.department', $params['department']);
        }
        $today = date('Y-m-d H:i:s');
        return static::createQuery()
            ->select(
                'V.detail',
                'V.id',
                'V.vehicle_id',
                'R.color',
                'U.name contact',
                'U.phone',
                'V.department',
                'V.begin',
                'V.end',
                'V.chauffeur',
                'V.create_date',
                'V.status',
                'V.approve',
                'V.closed',
                'V.reason',
                Sql::create('(CASE WHEN "'.$today.'" BETWEEN V.`begin` AND V.`end` THEN 1 WHEN "'.$today.'" > V.`end` THEN 2 ELSE 0 END) AS `today`'),
                Sql::create('TIMESTAMPDIFF(MINUTE,"'.$today.'",V.`begin`) AS `remain`')
            )
            ->from('car_reservation V')
            ->join('vehicles R', 'LEFT', array('R.id', 'V.vehicle_id'))
            ->join('user U', 'LEFT', array('U.id', 'V.member_id'))
            ->where($where);
    }

    /**
     * รับค่าจาก action (report.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = [];
        // session, referer, member
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            // ตรวจสอบสิทธิ์ผู้อนุมัติ
            $reportApprove = \Car\Base\Controller::reportApprove($login);
            if ($reportApprove != 0) {
                // ค่าที่ส่งมา
                $action = $request->post('action')->toString();
                // id ที่ส่งมา
                if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
                    if ($action === 'delete') {
                        $where = array(
                            array('id', $match[1])
                        );
                        if ($login['status'] != 1) {
                            // แอดมินลบได้ทั้งหมด, สถานะอื่นๆไม่สามารถลบรายการที่อนุมัติแล้วได้
                            $where[] = Sql::create('(NOW()<`begin` OR `status`!=1)');
                        }
                        $query = static::createQuery()
                            ->select('id')
                            ->from('car_reservation')
                            ->where($where);
                        $ids = [];
                        foreach ($query->execute() as $item) {
                            $ids[] = $item->id;
                        }
                        if (!empty($ids)) {
                            // ลบ
                            $this->db()->delete($this->getTableName('car_reservation'), array('id', $ids), 0);
                            $this->db()->delete($this->getTableName('car_reservation_data'), array('reservation_id', $ids), 0);
                            // log
                            \Index\Log\Model::add(0, 'car', 'Delete', '{LNG_Delete} {LNG_Vehicle booking report} ID : '.implode(', ', $ids), $login['id']);
                        }
                        // reload
                        $ret['location'] = 'reload';
                    } elseif ($action === 'approve') {
                        // ปรับสถานะ
                        $index = $this->createQuery()
                            ->from('car_reservation')
                            ->where(array('id', $request->post('id')->toInt()))
                            ->toArray()
                            ->first();
                        if ($index) {
                            $status = $request->post('status')->toInt();
                            // ฟอร์มอนุมัติ
                            $ret['modal'] = \Car\Approved\View::create()->render($index, $status, $login);
                        }
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
