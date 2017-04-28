<?php




function add_newsletter_member($api_key, $list_id, $user_data){

    $result = '';

    if(isset($user_data['e_mail'] && isset($api_key) && isset($list_id)){
        $fname = $user_data['f_name'];
        $lname = $user_data['l_name'];
        $email = $user_data['e_mail'];
        if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){
            // MailChimp API credentials
            $apiKey = $api_key;
            $listID = $list_id;

            // MailChimp API URL
            $memberID = md5(strtolower($email));
            $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberID;

            // member information
            $json = json_encode([
                'email_address' => $email,
                'status'        => 'subscribed',
                'merge_fields'  => [
                    'FNAME'     => $fname,
                    'LNAME'     => $lname
                ]
            ]);

            // send a HTTP POST request with curl
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $results = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // store the status message based on response code
            if ($httpCode == 200) {
                $result = '<p style="color: #34A853">You have successfully subscribed to our Newsletter.</p>';
            } else {
                switch ($httpCode) {
                    case 214:
                        $result = 'You are already subscribed.';
                        break;
                    default:
                        $result = 'Some problem occurred, please try again.';
                        break;
                }
                $result = '<p style="color: #EA4335">'.$result.'</p>';
            }
        }else{
            $result = '<p style="color: #EA4335">Please enter valid email address.</p>';
        }
    }

    return $result;

}