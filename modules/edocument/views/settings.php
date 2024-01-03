<?php
/**
 * @filesource modules/edocument/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Settings;

use Kotchasan\Html;
use Kotchasan\Http\UploadedFile;
use Kotchasan\Language;
use Kotchasan\Text;

/**
 * module=edocument-settings
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
            'action' => 'index.php/edocument/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-config',
            'title' => '{LNG_Module settings}'
        ));
        $groups = $form->add('groups', array(
            'comment' => '{LNG_The document number prefix, such as %Y%M, is replaced with the year and month. When the prefix changes (New month starts) The number will count to 1 again.}'
        ));
        if (isset(self::$cfg->edocument_format_no) && preg_match('/^(.*?)%0([0-9]+)d$/', self::$cfg->edocument_format_no, $match)) {
            $prefix = $match[1];
            $digits = $match[2];
        } else {
            $prefix = 'ที่ ศธ%Y%M-';
            $digits = 4;
        }
        // edocument_prefix
        $groups->add('text', array(
            'id' => 'edocument_prefix',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Document No.}',
            'value' => isset(self::$cfg->edocument_prefix) ? self::$cfg->edocument_prefix : $prefix
        ));
        // edocument_digits
        $groups->add('select', array(
            'id' => 'edocument_digits',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Number of digits}',
            'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8),
            'value' => $digits
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-upload',
            'title' => '{LNG_Upload}'
        ));
        // edocument_file_typies
        $fieldset->add('text', array(
            'id' => 'edocument_file_typies',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Type of file uploads}',
            'comment' => '{LNG_Specify the file extension that allows uploading. English lowercase letters and numbers 2-4 characters to separate each type with a comma (,) and without spaces. eg zip,rar,doc,docx}',
            'value' => isset(self::$cfg->edocument_file_typies) ? implode(',', self::$cfg->edocument_file_typies) : 'doc,ppt,pptx,docx,rar,zip,jpg,pdf'
        ));
        // อ่านการตั้งค่าขนาดของไฟลอัปโหลด
        $upload_max = UploadedFile::getUploadSize(true);
        // dms_upload_size
        $sizes = [];
        foreach (array(1, 2, 4, 6, 8, 16, 32, 64, 128, 256, 512, 1024, 2048) as $i) {
            $a = $i * 1048576;
            if ($a <= $upload_max) {
                $sizes[$a] = Text::formatFileSize($a);
            }
        }
        if (!isset($sizes[$upload_max])) {
            $sizes[$upload_max] = Text::formatFileSize($upload_max);
        }
        // edocument_upload_size
        $fieldset->add('select', array(
            'id' => 'edocument_upload_size',
            'labelClass' => 'g-input icon-upload',
            'itemClass' => 'item',
            'label' => '{LNG_Size of the file upload}',
            'comment' => '{LNG_The size of the files can be uploaded. (Should not exceed the value of the Server :upload_max_filesize.)}',
            'options' => $sizes,
            'value' => isset(self::$cfg->edocument_upload_size) ? self::$cfg->edocument_upload_size : ':upload_max_filesize'
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-download',
            'title' => '{LNG_Download}'
        ));
        // edocument_download_action
        $fieldset->add('select', array(
            'id' => 'edocument_download_action',
            'labelClass' => 'g-input icon-download',
            'itemClass' => 'item',
            'label' => '{LNG_When download}',
            'options' => Language::get('DOWNLOAD_ACTIONS'),
            'value' => isset(self::$cfg->edocument_download_action) ? self::$cfg->edocument_download_action : 0
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-comments',
            'title' => '{LNG_Notification}'
        ));
        // edocument_send_mail
        $booleans = Language::get('BOOLEANS');
        $fieldset->add('select', array(
            'id' => 'edocument_send_mail',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Emailing}',
            'comment' => '{LNG_Send notification messages When making a transaction}',
            'options' => $booleans,
            'value' => isset(self::$cfg->edocument_send_mail) ? self::$cfg->edocument_send_mail : 1
        ));
        $linegroup = array(0 => $booleans[0])+\Index\Linegroup\Model::create()->toSelect();
        // กำหนดการส่งไลน์ตามแผนก
        foreach (\Index\Category\Model::init()->toSelect('department') as $i => $label) {
            // line_id
            $fieldset->add('select', array(
                'id' => 'edocument_line_id['.$i.']',
                'itemClass' => 'item',
                'label' => '{LNG_LINE group account} '.$label,
                'labelClass' => 'g-input icon-line',
                'comment' => '{LNG_Send notification to LINE group when making a transaction}',
                'options' => $linegroup,
                'value' => isset(self::$cfg->edocument_line_id[$i]) ? self::$cfg->edocument_line_id[$i] : 0
            ));
        }
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ));
        \Gcms\Controller::$view->setContentsAfter(array(
            '/:upload_max_filesize/' => Text::formatFileSize($upload_max)
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
