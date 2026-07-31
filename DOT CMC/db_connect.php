<?php 

$conn= new mysqli('sql308.infinityfree.com','if0_42479903','92ZE90LCJMwYFp','if0_42479903_devsfps')or die("Could not connect to mysql".mysqli_error($con));
$conn->query("SET time_zone = '+05:30'");

if(!function_exists('is_feature_enabled')){
    function is_feature_enabled($flag_key){
        // Code-driven feature flags config
        $feature_flags = [
            'whatsapp_automation' => true,
            'dynamic_fee_dropdown' => true,
            // Add more features here as needed
        ];
        
        return isset($feature_flags[$flag_key]) ? $feature_flags[$flag_key] : false;
    }
}
