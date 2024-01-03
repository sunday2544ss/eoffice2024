<?php
/**
 * @filesource modules/repair/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\Settings;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=repair-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตั้งค่าโมดูล
     *
     * @return string
     */
    public function render()
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/repair/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-config',
            'title' => '{LNG_Module settings}'
        ));
        // สถานะการซ่อม
        $statuses = \Repair\Status\Model::create();
        // repair_first_status
        $fieldset->add('select', array(
            'id' => 'repair_first_status',
            'labelClass' => 'g-input icon-tools',
            'itemClass' => 'item',
            'label' => '{LNG_Initial repair status}',
            'options' => $statuses->toSelect(),
            'value' => isset(self::$cfg->repair_first_status) ? self::$cfg->repair_first_status : 1
        ));
        // repair_status_success
        $fieldset->add('select', array(
            'id' => 'repair_status_success',
            'labelClass' => 'g-input icon-star0',
            'itemClass' => 'item',
            'label' => '{LNG_Repair status successful}',
            'options' => $statuses->toSelect(),
            'value' => isset(self::$cfg->repair_status_success) ? self::$cfg->repair_status_success : 7
        ));
        $groups = $fieldset->add('groups', array(
            'comment' => '{LNG_The document number prefix, such as %Y%M, is replaced with the year and month. When the prefix changes (New month starts) The number will count to 1 again.}'
        ));
        if (isset(self::$cfg->repair_job_no) && preg_match('/^(.*?)%0([0-9]+)d$/', self::$cfg->repair_job_no, $match)) {
            $prefix = $match[1];
            $digits = $match[2];
        } else {
            $prefix = 'JOB%Y%M-';
            $digits = 4;
        }
        // repair_prefix
        $groups->add('text', array(
            'id' => 'repair_prefix',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Job No.}',
            'value' => isset(self::$cfg->repair_prefix) ? self::$cfg->repair_prefix : $prefix
        ));
        // repair_digits
        $groups->add('select', array(
            'id' => 'repair_digits',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Number of digits}',
            'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8),
            'value' => $digits
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-comments',
            'title' => '{LNG_Notification}'
        ));
        // repair_send_mail
        $booleans = Language::get('BOOLEANS');
        $fieldset->add('select', array(
            'id' => 'repair_send_mail',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Emailing}',
            'comment' => '{LNG_Send notification messages When making a transaction}',
            'options' => $booleans,
            'value' => isset(self::$cfg->repair_send_mail) ? self::$cfg->repair_send_mail : 1
        ));
        // repair_line_id
        $fieldset->add('select', array(
            'id' => 'repair_line_id',
            'itemClass' => 'item',
            'label' => '{LNG_LINE group account}',
            'labelClass' => 'g-input icon-line',
            'comment' => '{LNG_Send notification to LINE group when making a transaction}',
            'options' => array(0 => $booleans[0])+\Index\Linegroup\Model::create()->toSelect(),
            'value' => isset(self::$cfg->repair_line_id) ? self::$cfg->repair_line_id : 0
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
