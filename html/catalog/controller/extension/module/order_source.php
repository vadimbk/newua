<?php
class ControllerExtensionModuleOrderSource extends Controller {

    public function ajaxOrderSource() {

    	if(!isset($_COOKIE['referer_marker']) or $_COOKIE['referer_marker'] != 1){

	        if(isset($_POST['referer'])){
	            $referer = $_POST['referer'];
	        } else {
	            $referer = '';
	        }

	        if(isset($_POST['utm_source'])){
	            $utm_source = $_POST['utm_source'];
	        } else {
	            $utm_source = '';
	        }      
	        if(isset($_POST['utm_medium'])){
	            $utm_medium = $_POST['utm_medium'];
	        } else {
	            $utm_medium = '';
	        }     
	        if(isset($_POST['utm_campaign'])){
	            $utm_campaign = $_POST['utm_campaign'];
	        } else {
	            $utm_campaign = '';
	        }     
	        if(isset($_POST['utm_content'])){
	            $utm_content = $_POST['utm_content'];
	        } else {
	            $utm_content = '';
	        }     
	        if(isset($_POST['utm_term'])){
	            $utm_term = $_POST['utm_term'];
	        } else {
	            $utm_term = '';
	        }     

            $crawler = '';

            if(isset($utm_source) and $utm_source != ''){
                setcookie( 'utm_source', $utm_source, time()+36000, "/");
            }
            if(isset($utm_medium) and $utm_medium != ''){
                setcookie( 'utm_medium', $utm_medium, time()+36000, "/");
            }
            if(isset($utm_campaign) and $utm_campaign != ''){
                setcookie( 'utm_campaign', $utm_campaign, time()+36000, "/");
            }
            if(isset($utm_content) and $utm_content != ''){
                setcookie( 'utm_content', $utm_content, time()+36000, "/");
            }            
            if(isset($utm_term) and $utm_term != ''){
                setcookie( 'utm_term', $utm_term, time()+36000, "/");
            }

            setcookie( 'referer', $referer, time()+36000, "/");

            $referermarker = 1;
            setcookie( 'referer_marker', $referermarker, time()+36000, "/");
            $referermarker = 1;
            setcookie( 'referer_marker', $referermarker, time()+36000, "/");

        }  
    }
}