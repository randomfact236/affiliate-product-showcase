<?php
add_action('phpmailer_init', function($phpmailer){
    $phpmailer->isSMTP();
    $phpmailer->Host = 'mailhog';
    $phpmailer->Port = 1025;
    $phpmailer->SMTPAutoTLS = false;
    $phpmailer->SMTPSecure = false;
    $phpmailer->SMTPAuth = false;
});

add_action('rest_api_init', function(){
    register_rest_route('test-mail/v1', '/send', [
        'methods' => 'POST',
        'callback' => function($req){
            $subject = 'Test from WP - '.uniqid();
            $to = 'devnull@example.test';
            $body = 'This is a REST-triggered test message sent by wp_mail()';
            $sent = wp_mail($to, $subject, $body);
            return ['sent'=>$sent,'subject'=>$subject];
        },
        'permission_callback' => function(){ return true; }
    ]);
});
?>

