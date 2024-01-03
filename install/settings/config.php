<?php
/* config.php */
return array(
    'version' => '6.0.2',
    'web_title' => 'E-Office',
    'web_description' => 'ระบบจองห้องประชุม จองรถ แจ้งซ่อม งานสารบรรณ',
    'timezone' => 'Asia/Bangkok',
    'member_status' => array(
        0 => 'สมาชิก',
        1 => 'ผู้ดูแลระบบ',
        2 => 'บุคลากร',
        3 => 'พนักงานขับรถ'
    ),
    'color_status' => array(
        0 => '#259B24',
        1 => '#FF0000',
        2 => '#0000FF',
        3 => '#FFD600'
    ),
    'user_forgot' => 0,
    'user_register' => 0,
    'welcome_email' => 0,
    'booking_w' => 500,
    'inventory_w' => 500,
    'repair_first_status' => 1,
    'repair_send_mail' => 0,
    'edocument_format_no' => 'DOC-%04d',
    'edocument_send_mail' => 1,
    'edocument_file_typies' => array(
        0 => 'doc',
        1 => 'ppt',
        2 => 'pptx',
        3 => 'docx',
        4 => 'rar',
        5 => 'zip',
        6 => 'jpg',
        7 => 'jpeg',
        8 => 'pdf'
    ),
    'edocument_upload_size' => 2097152,
    'edocument_download_action' => 0,
    'booking_line_id' => 0,
    'booking_send_mail' => 0,
    'personnel_w' => 500,
    'personnel_h' => 500,
    'personnel_status' => array(
        0 => 1,
        1 => 2
    ),
    'member_only' => 1,
    'facebook_appId' => '',
    'google_client_id' => '',
    'bg_color' => '#769E51',
    'color' => '#FFFFFF',
    'noreply_email' => '',
    'email_charset' => 'utf-8',
    'email_Host' => 'localhost',
    'email_Port' => 25,
    'email_SMTPSecure' => '',
    'email_Username' => '',
    'email_use_phpMailer' => 0,
    'email_SMTPAuth' => 0,
    'booking_status' => 0,
    'booking_notifications' => 0,
    'edocument_line_id' => array(
        3 => 0,
        1 => 0,
        2 => 0
    ),
    'repair_line_id' => 0,
    'repair_status_success' => 7,
    'chauffeur_status' => 3,
    'car_w' => 600,
    'car_approving' => 0,
    'car_notifications' => 0,
    'car_email' => 1,
    'car_line_id' => 0,
    'booking_approving' => 0,
    'booking_delete' => 1,
    'car_delete' => 1,
    'car_status' => 0,
    'skin' => 'skin/booking',
    'header_bg_color' => '#769E51',
    'warpper_bg_color' => '#D2D2D2',
    'header_color' => '#FFFFFF',
    'footer_color' => '#7E7E7E',
    'logo_color' => '#000000',
    'login_header_color' => '#000000',
    'login_footer_color' => '#7E7E7E',
    'login_color' => '#000000',
    'login_bg_color' => '#D2D2D2',
    'theme_width' => 'wide'
);
