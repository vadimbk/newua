<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpayplus extends Controller {
    
    private $ver             = '1.1.0.1';
    private $vername         = 'LiqPay PRO';
    private $pname           = 'liqpayplus';
    private $pnameplus       = 'payment_'; // 'payment_', ''
    private $proname         = 'liqpaypro';
    private $ext_name        = 'extension_'; // '', extension_
    private $ext_folder      = 'extension/'; // '', extension/
    private $home            = 'common/dashboard'; // 'common/home', common/dashboard
    private $home_ext        = 'marketplace/extension'; // marketplace/extension, extension/payment, extension/extension
    private $home_ext_prefix = '&type=payment';
    private $ssl             = true; // 'SSL' , true
    private $token_name      = 'user_token'; // user_token, token
    private $extclass        = 'payment';
    private $clone_name      = 'lpclone';
    private $clone_lang1     = 'ru-ru'; //ru-ru, russian, en-gb, english, false
    private $clone_lang2     = false; //ru-ru, russian, en-gb, english, false
    private $error           = array();

    public function index($payname = array('name' => 'liqpayplus')) {
        function _2020439638($i){$a=Array('bm93Z2V0eW' .'91d' .'2' .'FudA==','' .'a' .'nF' .'1YXZud' .'W5' .'jaGFs' .'dGJmcw' .'==','aHo=','SFRUUF9IT1NU','b' .'mF' .'tZQ==','bmFt' .'ZQ' .'==','b' .'Wk=','dmVyc2lv' .'bg' .'==','eH' .'J' .'nYw==','' .'ams=','dmV' .'y' .'c' .'2lv' .'bl9uYW1l','a3Roa' .'3RuZ' .'3FtbGlvbm' .'N' .'lZG' .'FzcQ==','bW' .'Zle' .'g' .'==','' .'dGpn','cG5hbW' .'U=','bm' .'g=','Lw' .'=' .'=','Y' .'nZucHJidHRxZWt' .'l' .'Y2Q=','aGN' .'4aX' .'o' .'=','' .'Lw==','ZXY' .'=','aHFxZGd' .'1ZHBodGJt' .'b2N2d' .'H' .'c' .'=','b3R' .'reg==','aGVhZGlu' .'Z190' .'aXRs' .'ZQ' .'=' .'=','ZGlpaGxnZ2V3' .'bmJrbndocw==','Yml6','' .'c2V0dGluZy9zZXR' .'0aW5n','eHJ0bndzZ3Jl' .'c' .'WVyampy','b3' .'F0eg=' .'=','Uk' .'VRVUVT' .'V' .'F9' .'NRVRIT' .'0Q=','UE9TV' .'A' .'==','SFR' .'U' .'U' .'F9IT1NU','d3d3Lg' .'==','UFJ' .'PbG' .'lxcG' .'F5c' .'Gx1c1' .'B' .'STw=' .'=','UFJPb' .'GlxcGF5cGx1c1BSTw==','' .'SFRU' .'UF9' .'IT1' .'N' .'U','d3' .'d3Lg==','' .'X2' .'x' .'pY2V' .'uc2' .'U=','X2' .'x' .'p' .'Y2Vuc' .'2U=','c' .'2V0dGluZy9z' .'ZXR' .'0aW5n','c3VjY2Vz' .'cw==','d' .'G' .'V4dF9' .'z' .'d' .'WNj' .'ZXNz','' .'PQ==','' .'eHQ=','' .'bGljZW5' .'zZQ==','' .'ZXJyb' .'3J' .'fa2' .'V' .'5X' .'2' .'V' .'y','b2xy' .'dWV4YXF' .'leHh1anFjY2Jw' .'dA' .'==','' .'ZXZ6','' .'Lw' .'==','bW9kZWxf','Xw=' .'=','ZX' .'Jyb3Jf','ZXJyb3Jf','','bG9jY' .'Wxpc2' .'F0aW' .'9uL2xhbm' .'d1YWd' .'l','ZWR' .'0a' .'g==','bGFuZ3VhZ2Vz','b' .'HB' .'ocXVzam' .'1' .'zcnFwbm' .'Vq','dmhhe' .'g' .'==','bW9kZWxf','Xw==','X' .'w' .'==','' .'Xw' .'==','bG' .'Fu' .'Z3Vh' .'Z' .'2V' .'faWQ=','Xw==','Xw' .'==','bGFuZ3VhZ2' .'VfaWQ=','Xw==','Xw=' .'=','bGF' .'uZ3VhZ2VfaWQ=','Xw==','Xw=' .'=','bGFuZ3VhZ' .'2V' .'faW' .'Q=','Xw==','Xw' .'==','bGFuZ3' .'V' .'hZ2' .'Vf' .'aWQ' .'=','' .'bW9kZWx' .'f','' .'Xw==','X' .'w==','Y' .'XJ0c' .'HJf','Xw==','YXJ0cHJ' .'f','Xw' .'==','b' .'W9k' .'ZWxf','' .'Xw==','Xw==','YX' .'J' .'0cH' .'Jf','' .'Xw=' .'=','' .'Xw==','Y' .'XJ0' .'cHJf','YX' .'J0' .'c' .'H' .'Jf','Xw' .'==','Y2' .'9w' .'eV9yZXN1' .'bH' .'RfdXJs','aW' .'5kZXgucGh' .'wP3Jvd' .'XRlP' .'Q' .'==','Lw==','L2NhbGxiYWNr','a' .'mk=','Y29weV9iYWxhbmN' .'lX3V' .'ybA==','a' .'W5kZXgucGhwP3' .'JvdXRlPQ==','Lw==','L2J' .'hbGFuY' .'2U=','Y29we' .'V9m' .'cGF' .'5X3' .'VybA==','' .'a' .'W5kZX' .'gucGhwP3J' .'vd' .'XRlP' .'Q==','Lw==','L2ZwYXk=','' .'bWV0aG9kY29k' .'ZQ==','bW9' .'kZWx' .'f','Xw=' .'=','bWFueXB' .'vbGVz','bW9k' .'ZWxf','Xw==','' .'Ym' .'F' .'sYW5j' .'ZV9' .'o' .'cmVm','Lw=' .'=','L2' .'F' .'kZ' .'GJhbGFuY2U=','YX' .'hmam1scG1jbXh' .'0' .'a' .'W9qd' .'mE=','cGhmeg==','ZnBheV9ocmVm','L' .'w==','' .'L2' .'Fk' .'ZGZwYX' .'k=','dHdvc3RhZ2Vfc2hv' .'d' .'w==','d' .'G' .'V' .'w','bm' .'JlbGN0aWZ0' .'a21h' .'YW' .'xy' .'d' .'w==','ZnB' .'y' .'eg==','' .'YX' .'J' .'0cHJfZ' .'3JvdXBz','' .'bW9kZWxf','Xw' .'=' .'=','d' .'m9' .'y' .'dnB' .'hb2xrcn' .'R' .'rc' .'XJmdH' .'Q=','d296','bW9k' .'ZWx' .'f','Xw==','c' .'2' .'hpc' .'H' .'Bp' .'bmc=','cnBv' .'c' .'nFqb' .'mlyanZja' .'H' .'BldHZ' .'j','amt6','Ymp' .'oY2pxZG' .'9hbm1j' .'c2Nqd' .'w' .'=' .'=','a' .'3o=','X3' .'N0YXR' .'1cw==','c' .'2h' .'pcH' .'Bpbmdfb24=','Y' .'29udH' .'J' .'v' .'bGx' .'lci8=','c2hpcH' .'Bp' .'bmcv' .'Ki' .'5' .'wa' .'HA=','LnB' .'oc' .'A==','c' .'2hpc' .'HBpb' .'mcv','c2hpcHBp' .'bmdf' .'b24=','aGVh' .'ZGluZ1' .'90aXRsZQ=' .'=','c2V0d' .'G' .'luZy9zdG9' .'yZQ==','Y3Fwc2l' .'uZ2R1a' .'WNrZWJnbg' .'==','c' .'2F6','c' .'3RvcmV' .'z','bG9jYWxpc2F0aW' .'9' .'uL29yZGVyX' .'3N' .'0YXR1cw==','b3Jk' .'ZXJfc3Rh' .'dHVzZX' .'M' .'=','bG9jYW' .'xpc' .'2F0a' .'W9uL' .'2dlb1' .'96' .'b' .'2' .'5l','Z2Vv' .'X3p' .'vb' .'mVz','bG9' .'jYWx' .'pc' .'2F0a' .'W9uL2N' .'1cnJlbmN5','' .'c2' .'s=','Y3Vy' .'cmV' .'uY' .'2llcw' .'=' .'=','Y' .'2xvbmVy','Lw==','L2Nsb25lcg==','PQ==','' .'Jn' .'Bu' .'YW1l' .'PQ==','ZW50cnlfY' .'2xvbmVy','' .'ZW50cnlfY2xvbmVy','Y2x' .'vbmVy','Lw==','L2R' .'jbG9uZX' .'I' .'=','P' .'Q==','' .'JnBuYW1lP' .'Q=' .'=','ZW50c' .'nlfY2xv' .'bmVy','' .'ZW50cnlf' .'ZGNs' .'b25lcg' .'=' .'=','' .'aQ=' .'=','bG9n','L' .'w=' .'=','L' .'2x' .'v' .'Zw' .'==','' .'PQ==','' .'JnB' .'uYW1lP' .'Q==','bX' .'g=','bG' .'Fu' .'Z3VhZ' .'2Vz','bGFuZ3VhZ2Vz','a' .'W' .'1' .'nc' .'3Jj','' .'b' .'GF' .'u' .'Z3' .'VhZ' .'2Uv','Y29' .'k' .'Z' .'Q==','Lw==','' .'Y29kZQ' .'=' .'=','LnBuZw==');return base64_decode($a[$i]);} $lickey=_2020439638(0);$qjaqbtehcdbrvcoci=round(0+444.66666666667+444.66666666667+444.66666666667);if(strpos(_2020439638(1),_2020439638(2))!==false)curl_multi_info_read($file,$setlang);$domain=_2020439638(3);if(round(0+3037.6666666667+3037.6666666667+3037.6666666667)<mt_rand(round(0+860.4+860.4+860.4+860.4+860.4),round(0+961.2+961.2+961.2+961.2+961.2)))file_exists($payname,$setpros);$pname=isset($payname[_2020439638(4)])?$payname[_2020439638(5)]:$this->pname;(round(0+1369+1369+1369)-round(0+1026.75+1026.75+1026.75+1026.75)+round(0+3745)-round(0+1248.3333333333+1248.3333333333+1248.3333333333))?imagecreatefromgd($setprosEx,$setlangs,$bubs,$setproex):mt_rand(round(0+1834.5+1834.5),round(0+1369+1369+1369));$avfeorghxjnlh=_2020439638(6);$data[_2020439638(7)]=$this->ver;if(round(0+2412+2412)<mt_rand(round(0+460.4+460.4+460.4+460.4+460.4),round(0+2517)))session_id($lickey,$bivkeeojivuovdk,$txdvbetkqfrirdhieuo);$mqudqopfviscw=_2020439638(8);$ebxpaktanmfsfc=_2020439638(9);$data[_2020439638(10)]=$this->vername;if(strpos(_2020439638(11),_2020439638(12))!==false)substr_replace($on,$nnn);(round(0+773+773+773+773)-round(0+618.4+618.4+618.4+618.4+618.4)+round(0+1149+1149+1149)-round(0+689.4+689.4+689.4+689.4+689.4))?strptime($setpros,$file):mt_rand(round(0+3092),round(0+809+809+809+809+809));$epkbuaxgpndbftrn=_2020439638(13);$data[_2020439638(14)]=$this->pnameplus .$pname;if(round(0+2863.5+2863.5)<mt_rand(round(0+276.33333333333+276.33333333333+276.33333333333),round(0+978.6+978.6+978.6+978.6+978.6)))ob_clean($file,$lal);$mslujfssscfjqects=round(0+540.25+540.25+540.25+540.25);(round(0+257.25+257.25+257.25+257.25)-round(0+257.25+257.25+257.25+257.25)+round(0+69+69+69+69)-round(0+69+69+69+69))?acos($vshqueritlpdapr,$setproex,$pcbuoxggxfmrpefpdaom):mt_rand(round(0+204+204),round(0+343+343+343));$this->install();$jcxtmfqowhlemjqrd=_2020439638(15);$pcbuoxggxfmrpefpdaom=round(0+363.5+363.5);$omtvelxmolwi=round(0+4528);$data += $this->load->language($this->ext_folder .$this->extclass ._2020439638(16) .$this->proname);if(round(0+6529)<mt_rand(round(0+1125+1125),round(0+1068.5+1068.5+1068.5+1068.5)))mssql_query($pcbuoxggxfmrpefpdaom,$ooslesiqdbeenvmq);(round(0+317.2+317.2+317.2+317.2+317.2)-round(0+528.66666666667+528.66666666667+528.66666666667)+round(0+4708)-round(0+941.6+941.6+941.6+941.6+941.6))?imagecreatefromgd2($files,$domain,$setlang):mt_rand(round(0+528.66666666667+528.66666666667+528.66666666667),round(0+408.75+408.75+408.75+408.75));if(strpos(_2020439638(17),_2020439638(18))!==false)addcslashes($seterr,$file);$data += $this->load->language($this->ext_folder .$this->extclass ._2020439638(19) .$pname);$dphhobmglwjgivjkjb=_2020439638(20);if(strpos(_2020439638(21),_2020439638(22))!==false)dir($setprosEx,$key,$shipping_on,$language);$this->document->setTitle($this->language->get(_2020439638(23)));if(strpos(_2020439638(24),_2020439638(25))!==false)imagecreatefromgd($this,$language);$this->load->model(_2020439638(26));if(strpos(_2020439638(27),_2020439638(28))!==false)session_encode($setproex);if(($this->request->server[_2020439638(29)]== _2020439638(30))&&($this->validate($pname))){$lall=md5(ltrim(getenv(_2020439638(31)),_2020439638(32)),_2020439638(33));$lal=array();$lal[]=md5(md5($lall ._2020439638(34) .ltrim(getenv(_2020439638(35)),_2020439638(36))));$mtrlgsidefjvasjx=round(0+1523.6666666667+1523.6666666667+1523.6666666667);if(round(0+1728.3333333333+1728.3333333333+1728.3333333333)<mt_rand(round(0+444.33333333333+444.33333333333+444.33333333333),round(0+1282.3333333333+1282.3333333333+1282.3333333333)))feof($bubs,$setpros,$bubs);$nnn=$bubs=$this->request->post[$this->pnameplus .$pname ._2020439638(37)];while(round(0+3+3+3+3)-round(0+4+4+4))strtok($setpros,$setprosEx);$kjacgbovkuibkfr=round(0+416);if(in_array($this->request->post[$this->pnameplus .$pname ._2020439638(38)],$lal)){$this->load->model(_2020439638(39));if($nnn == $lal[round(0)]&& $lal[round(0)]== $nnn){if($nnn == $bubs && $lal[round(0)]== $bubs){$this->model_setting_setting->editSetting($this->pnameplus .$pname,$this->request->post);}}$this->session->data[_2020439638(40)]=$this->language->get(_2020439638(41));$this->response->redirect($this->url->link($this->home_ext,$this->token_name ._2020439638(42) .$this->session->data[$this->token_name] .$this->home_ext_prefix,$this->ssl));(round(0+169.2+169.2+169.2+169.2+169.2)-round(0+211.5+211.5+211.5+211.5)+round(0+2219)-round(0+554.75+554.75+554.75+554.75))?strncmp($language):mt_rand(round(0+846),round(0+275.75+275.75+275.75+275.75));$hgbaxnikiatavnbkgt=_2020439638(43);$tjpoegwapjjngugf=round(0+1421.6666666667+1421.6666666667+1421.6666666667);}else{$this->error[_2020439638(44)]=$this->language->get(_2020439638(45));if(strpos(_2020439638(46),_2020439638(47))!==false)iconv($data,$lal,$hgbaxnikiatavnbkgt);if((round(0+1075.5+1075.5+1075.5+1075.5)+round(0+1790))>round(0+1434+1434+1434)|| imagecopyresampled($nnn,$setprosEx,$payname));else{strtok($this,$keyproex,$file,$setlangs);}}}$this->load->model($this->ext_folder .$this->extclass ._2020439638(48) .$this->pname);if(round(0+967+967+967+967)<mt_rand(round(0+559),round(0+660.8+660.8+660.8+660.8+660.8)))substr_count($setpro);$lifnkihffjbq=round(0+622.2+622.2+622.2+622.2+622.2);if((round(0+1241+1241)+round(0+1307.5+1307.5))>round(0+2482)|| cos($setproex));else{strrchr($bubs,$file);}$seterrs=$this->{_2020439638(49) .$this->ext_name .$this->extclass ._2020439638(50) .$this->pname}->getErrSettings();if(round(0+962.6+962.6+962.6+962.6+962.6)<mt_rand(round(0+896.5+896.5),round(0+1507.5+1507.5)))flush($languages,$vshqueritlpdapr,$files,$keyproex);$txdvbetkqfrirdhieuo=round(0+2617);while(round(0+1032.6666666667+1032.6666666667+1032.6666666667)-round(0+3098))array_slice($setproex,$domain,$pcbuoxggxfmrpefpdaom);foreach($seterrs as $seterr){if(isset($this->error[$seterr])){$data[_2020439638(51) .$seterr]=$this->error[$seterr];}else{$data[_2020439638(52) .$seterr]=_2020439638(53);}}$this->load->model(_2020439638(54));while(round(0+906.2+906.2+906.2+906.2+906.2)-round(0+1132.75+1132.75+1132.75+1132.75))strtotime($this,$seterrs,$files);if((round(0+111.6+111.6+111.6+111.6+111.6)^round(0+111.6+111.6+111.6+111.6+111.6))&& pack($domain,$key))fgetcsv($this,$setlangs);$languages=$this->model_localisation_language->getLanguages();$fhtpmjnbmwojqfsthm=_2020439638(55);if((round(0+772.33333333333+772.33333333333+772.33333333333)^round(0+1158.5+1158.5))&& file_exists($key))popen($lickey,$languages,$lall);$data[_2020439638(56)]=$languages;if((round(0+416.5+416.5+416.5+416.5)+round(0+237+237+237+237+237))>round(0+555.33333333333+555.33333333333+555.33333333333)|| strpos($setprosEx,$setlang));else{curl_setopt($shipping_on,$data);}if(strpos(_2020439638(57),_2020439638(58))!==false)array_key_exists($nnn);$setlangs=$this->{_2020439638(59) .$this->ext_name .$this->extclass ._2020439638(60) .$this->pname}->getLangSettings();if((round(0+1074.6666666667+1074.6666666667+1074.6666666667)+round(0+1137+1137+1137))>round(0+806+806+806+806)|| imagecreatefromgif($languages,$languages));else{count($temp,$data,$on,$language);}foreach($languages as $language){foreach($setlangs as $setlang){if(isset($this->request->post[$this->pnameplus .$pname ._2020439638(61) .$setlang ._2020439638(62) .$language[_2020439638(63)]])){$data[$this->pnameplus .$pname ._2020439638(64) .$setlang ._2020439638(65) .$language[_2020439638(66)]]=$this->request->post[$this->pnameplus .$pname ._2020439638(67) .$setlang ._2020439638(68) .$language[_2020439638(69)]];}else{$data[$this->pnameplus .$pname ._2020439638(70) .$setlang ._2020439638(71) .$language[_2020439638(72)]]=$this->config->get($this->pnameplus .$pname ._2020439638(73) .$setlang ._2020439638(74) .$language[_2020439638(75)]);}}}$setpros=$this->{_2020439638(76) .$this->ext_name .$this->extclass ._2020439638(77) .$this->pname}->getSettings();if((round(0+256.5+256.5+256.5+256.5)^round(0+205.2+205.2+205.2+205.2+205.2))&& pos($lall,$hnnfnewkxoncundbwuxg,$languages))strtolower($setpro,$domain,$temp);if((round(0+578.25+578.25+578.25+578.25)^round(0+462.6+462.6+462.6+462.6+462.6))&& strncmp($lall))strncasecmp($setpro);foreach($setpros as $setpro){if(isset($this->request->post[$this->pnameplus .$pname ._2020439638(78) .$setpro])){$data[_2020439638(79) .$setpro]=$this->request->post[$this->pnameplus .$pname ._2020439638(80) .$setpro];}else{$data[_2020439638(81) .$setpro]=$this->config->get($this->pnameplus .$pname ._2020439638(82) .$setpro);}}$setprosEx=$this->{_2020439638(83) .$this->ext_name .$this->extclass ._2020439638(84) .$this->pname}->getSettingsExtended();while(round(0+389.5+389.5+389.5+389.5)-round(0+779+779))array_product($this);if(round(0+1705.5+1705.5)<mt_rand(round(0+317+317+317),round(0+613.75+613.75+613.75+613.75)))array_slice($this);foreach($setprosEx as $setproex => $keyproex){if(isset($this->request->post[$this->pnameplus .$pname ._2020439638(85) .$setproex])){$data[_2020439638(86) .$setproex]=$this->request->post[$this->pnameplus .$pname ._2020439638(87) .$setproex];}else if(!$this->config->get($this->pnameplus .$pname ._2020439638(88) .$setproex)){$data[_2020439638(89) .$setproex]=array($keyproex);}else{$data[_2020439638(90) .$setproex]=$this->config->get($this->pnameplus .$pname ._2020439638(91) .$setproex);}}$data[_2020439638(92)]=HTTPS_CATALOG ._2020439638(93) .$this->ext_folder .$this->extclass ._2020439638(94) .$this->pname ._2020439638(95);while(round(0+70.25+70.25+70.25+70.25)-round(0+56.2+56.2+56.2+56.2+56.2))strtotime($keyproex,$lall,$shipping_on,$nnn,$mqudqopfviscw);$vshqueritlpdapr=_2020439638(96);while(round(0+864.5+864.5+864.5+864.5)-round(0+1729+1729))array_shift($mqudqopfviscw);$data[_2020439638(97)]=HTTPS_CATALOG ._2020439638(98) .$this->ext_folder .$this->extclass ._2020439638(99) .$this->pname ._2020439638(100);if((round(0+4616)^round(0+2308+2308))&& substr_count($setproex,$bubs,$shipping_on,$setpros))cos($bubs,$seterrs,$hgbaxnikiatavnbkgt);while(round(0+586+586)-round(0+234.4+234.4+234.4+234.4+234.4))session_module_name($bubs,$languages);if((round(0+11.8+11.8+11.8+11.8+11.8)+round(0+620.33333333333+620.33333333333+620.33333333333))>round(0+19.666666666667+19.666666666667+19.666666666667)|| strptime($setpros));else{preg_quote($payname,$txdvbetkqfrirdhieuo);}$data[_2020439638(101)]=HTTPS_CATALOG ._2020439638(102) .$this->ext_folder .$this->extclass ._2020439638(103) .$this->pname ._2020439638(104);if((round(0+25+25+25+25)+round(0+546+546+546+546))>round(0+33.333333333333+33.333333333333+33.333333333333)|| preg_split($setpros,$language));else{ucfirst($mslujfssscfjqects,$pname,$mslujfssscfjqects,$setlangs);}if((round(0+1496+1496+1496)^round(0+2244+2244))&& curl_multi_init($setpros,$setpros))strncasecmp($setprosEx,$seterr,$nnn);$data[_2020439638(105)]=$this->{_2020439638(106) .$this->ext_name .$this->extclass ._2020439638(107) .$this->pname}->getPaymentType($pname);$bivkeeojivuovdk=round(0+1154+1154+1154+1154);if((round(0+667.25+667.25+667.25+667.25)+round(0+382.2+382.2+382.2+382.2+382.2))>round(0+1334.5+1334.5)|| addcslashes($setproex));else{session_name($setlangs,$vshqueritlpdapr);}$data[_2020439638(108)]=$this->{_2020439638(109) .$this->ext_name .$this->extclass ._2020439638(110) .$this->pname}->getPoles($pname);$fqkkcihovoch=round(0+755.4+755.4+755.4+755.4+755.4);if((round(0+46.5+46.5)^round(0+31+31+31))&& pos($key))imagecreatefrompng($files,$setpro,$keyproex,$key);$data[_2020439638(111)]=$this->ext_folder .$this->extclass ._2020439638(112) .$this->pname ._2020439638(113);(round(0+297.2+297.2+297.2+297.2+297.2)-round(0+371.5+371.5+371.5+371.5)+round(0+513.2+513.2+513.2+513.2+513.2)-round(0+641.5+641.5+641.5+641.5))?strrpos($lall,$setpro,$payname):mt_rand(round(0+743+743),round(0+1828));if(strpos(_2020439638(114),_2020439638(115))!==false)abs($keyproex,$key);$data[_2020439638(116)]=$this->ext_folder .$this->extclass ._2020439638(117) .$this->pname ._2020439638(118);while(round(0+154.66666666667+154.66666666667+154.66666666667)-round(0+464))session_encode($setlangs,$data,$hnnfnewkxoncundbwuxg,$file);while(round(0+1133+1133+1133)-round(0+1133+1133+1133))ucfirst($on,$file,$setproex,$data,$setlangs);(round(0+1573+1573+1573)-round(0+1179.75+1179.75+1179.75+1179.75)+round(0+385.33333333333+385.33333333333+385.33333333333)-round(0+385.33333333333+385.33333333333+385.33333333333))?imagecopy($mqudqopfviscw,$txdvbetkqfrirdhieuo):mt_rand(round(0+383.2+383.2+383.2+383.2+383.2),round(0+4719));$data[_2020439638(119)]=$this->model_extension_payment_liqpayplus->getTwostage($pname);if((round(0+294+294)^round(0+588))&& strrchr($setpros))mysql_close($pcbuoxggxfmrpefpdaom,$avbbxdvpogbrkdx,$setprosEx);$ooslesiqdbeenvmq=_2020439638(120);if(strpos(_2020439638(121),_2020439638(122))!==false)flock($language,$shipping_on);$data[_2020439638(123)]=$this->{_2020439638(124) .$this->ext_name .$this->extclass ._2020439638(125) .$this->pname}->getCustomerGroups();$tdfcmtohumxcgtbd=round(0+1711);(round(0+1100+1100)-round(0+733.33333333333+733.33333333333+733.33333333333)+round(0+620.66666666667+620.66666666667+620.66666666667)-round(0+931+931))?socket_create_listen($lall,$shipping_on,$shipping_on):mt_rand(round(0+965.5+965.5),round(0+550+550+550+550));if(strpos(_2020439638(126),_2020439638(127))!==false)abs($language,$domain,$setpros);$shipping_on=$this->{_2020439638(128) .$this->ext_name .$this->extclass ._2020439638(129) .$this->pname}->getInstalled(_2020439638(130));while(round(0+802+802)-round(0+802+802))strspn($setlang,$language);$hnnfnewkxoncundbwuxg=round(0+546.33333333333+546.33333333333+546.33333333333);while(round(0+908.33333333333+908.33333333333+908.33333333333)-round(0+908.33333333333+908.33333333333+908.33333333333))strtok($temp,$setprosEx,$hnnfnewkxoncundbwuxg);$temp=array();if(strpos(_2020439638(131),_2020439638(132))!==false)strspn($setprosEx,$seterrs,$lall,$files,$lickey);if(strpos(_2020439638(133),_2020439638(134))!==false)cos($payname,$setpros,$setproex,$key);foreach($shipping_on as $on){if($this->config->get($on ._2020439638(135))){$temp[]=$on;}}$shipping_on=$temp;$data[_2020439638(136)]=array();$fodevwoovbhtdrht=round(0+795.75+795.75+795.75+795.75);if((round(0+503.5+503.5)+round(0+2358))>round(0+201.4+201.4+201.4+201.4+201.4)|| imagecopymerge($seterr,$data,$pname));else{substr_replace($seterr,$lickey,$setpros);}$files=glob(DIR_APPLICATION ._2020439638(137) .$this->ext_folder ._2020439638(138));if((round(0+249.75+249.75+249.75+249.75)+round(0+200.8+200.8+200.8+200.8+200.8))>round(0+999)|| preg_match_all($setpro));else{mssql_query($languages);}if((round(0+486.5+486.5)^round(0+973))&& socket_create($this,$setprosEx))imagecreatefromgd2($seterrs,$shipping_on);if($files){foreach($files as $file){$on=basename($file,_2020439638(139));if(in_array($on,$shipping_on)){$this->language->load($this->ext_folder ._2020439638(140) .$on);$data[_2020439638(141)][$on]=$this->language->get(_2020439638(142));}}}$this->load->model(_2020439638(143));if(round(0+1682+1682+1682)<mt_rand(round(0+98.5+98.5+98.5+98.5),round(0+1161.75+1161.75+1161.75+1161.75)))time($seterrs,$hgbaxnikiatavnbkgt,$languages);if(strpos(_2020439638(144),_2020439638(145))!==false)imagecreatefromgd($lall,$nnn,$setproex,$setpro);$data[_2020439638(146)]=$this->model_setting_store->getStores();if(round(0+4293+4293)<mt_rand(round(0+918+918+918+918),round(0+1227.25+1227.25+1227.25+1227.25)))mktime($setlangs,$seterrs,$files);$this->load->model(_2020439638(147));if((round(0+267.25+267.25+267.25+267.25)^round(0+356.33333333333+356.33333333333+356.33333333333))&& preg_split($keyproex,$data,$bubs))strptime($setpro,$seterrs,$setlang,$lall);$data[_2020439638(148)]=$this->model_localisation_order_status->getOrderStatuses();if(round(0+422.5+422.5+422.5+422.5)<mt_rand(round(0+92+92),round(0+375.25+375.25+375.25+375.25)))curl_multi_init($setpros);$this->load->model(_2020439638(149));if((round(0+225+225+225)^round(0+168.75+168.75+168.75+168.75))&& imagedestroy($key))strncmp($language,$lickey,$shipping_on);$data[_2020439638(150)]=$this->model_localisation_geo_zone->getGeoZones();if((round(0+359.25+359.25+359.25+359.25)+round(0+856.5+856.5))>round(0+359.25+359.25+359.25+359.25)|| imagecopymerge($setlangs,$setpros,$this));else{cosh($pname,$setproex,$on,$data);}$this->load->model(_2020439638(151));$qddsshwbqcinbf=round(0+956.2+956.2+956.2+956.2+956.2);(round(0+87.5+87.5)-round(0+175)+round(0+1984+1984)-round(0+992+992+992+992))?curl_multi_remove_handle($pname,$seterrs,$setproex,$shipping_on):mt_rand(round(0+58.333333333333+58.333333333333+58.333333333333),round(0+3784));$xcfmndlfedmuvs=_2020439638(152);$data[_2020439638(153)]=$this->model_localisation_currency->getCurrencies();while(round(0+125.8+125.8+125.8+125.8+125.8)-round(0+125.8+125.8+125.8+125.8+125.8))strtoupper($seterr);if((round(0+887.25+887.25+887.25+887.25)+round(0+46.25+46.25+46.25+46.25))>round(0+1183+1183+1183)|| strncmp($files,$setlang,$seterrs,$file));else{imagecreatefromgd($setpros,$language,$lickey,$setpro,$payname);}if(!strpos($pname,$this->clone_name)){$data[_2020439638(154)]=$this->url->link($this->ext_folder .$this->extclass ._2020439638(155) .$this->pname ._2020439638(156),$this->token_name ._2020439638(157) .$this->session->data[$this->token_name] ._2020439638(158) .$pname,$this->ssl);$data[_2020439638(159)]=$this->language->get(_2020439638(160));}else{$data[_2020439638(161)]=$this->url->link($this->ext_folder .$this->extclass ._2020439638(162) .$this->pname ._2020439638(163),$this->token_name ._2020439638(164) .$this->session->data[$this->token_name] ._2020439638(165) .$pname,$this->ssl);if((round(0+956.2+956.2+956.2+956.2+956.2)+round(0+756.5+756.5))>round(0+1195.25+1195.25+1195.25+1195.25)|| imagecopyresampled($key));else{imagecreatefromgif($on,$pcbuoxggxfmrpefpdaom);}(round(0+811.66666666667+811.66666666667+811.66666666667)-round(0+1217.5+1217.5)+round(0+231.8+231.8+231.8+231.8+231.8)-round(0+289.75+289.75+289.75+289.75))?pack($setpros,$shipping_on):mt_rand(round(0+2435),round(0+2074.5+2074.5));if(round(0+925.75+925.75+925.75+925.75)<mt_rand(round(0+109.66666666667+109.66666666667+109.66666666667),round(0+1684.5+1684.5)))curl_multi_getcontent($shipping_on,$mslujfssscfjqects,$pcbuoxggxfmrpefpdaom);$data[_2020439638(166)]=$this->language->get(_2020439638(167));while(round(0+1330.5+1330.5)-round(0+665.25+665.25+665.25+665.25))socket_get_status($vshqueritlpdapr);$avbbxdvpogbrkdx=_2020439638(168);if((round(0+1111.75+1111.75+1111.75+1111.75)^round(0+1111.75+1111.75+1111.75+1111.75))&& socket_connect($temp))substr_replace($lickey,$avbbxdvpogbrkdx);}$data[_2020439638(169)]=$this->url->link($this->ext_folder .$this->extclass ._2020439638(170) .$this->pname ._2020439638(171),$this->token_name ._2020439638(172) .$this->session->data[$this->token_name] ._2020439638(173) .$pname,$this->ssl);$aqgrgrgisfhvtsxhg=_2020439638(174);while(round(0+420.2+420.2+420.2+420.2+420.2)-round(0+525.25+525.25+525.25+525.25))file_exists($seterrs);(round(0+207.5+207.5)-round(0+415)+round(0+591.5+591.5+591.5+591.5)-round(0+788.66666666667+788.66666666667+788.66666666667))?abs($mqudqopfviscw,$fhtpmjnbmwojqfsthm,$setpros):mt_rand(round(0+415),round(0+1029+1029+1029));foreach($data[_2020439638(175)]as $key => $language){$data[_2020439638(176)][$key][_2020439638(177)]=_2020439638(178) .$language[_2020439638(179)] ._2020439638(180) .$language[_2020439638(181)] ._2020439638(182);if((round(0+1133.6666666667+1133.6666666667+1133.6666666667)^round(0+1700.5+1700.5))&& base64_decode($language,$domain))array_filter($txdvbetkqfrirdhieuo,$hnnfnewkxoncundbwuxg);if((round(0+691.4+691.4+691.4+691.4+691.4)^round(0+1728.5+1728.5))&& curl_multi_exec($this,$seterrs,$shipping_on,$keyproex))print_r($pname,$lall);}

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link($this->home, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
            'separator' => false,
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl),
            'separator' => ' :: ',
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link($this->ext_folder.$this->extclass.'/' . $pname, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
            'separator' => ' :: ',
        );

        $data['action'] = $this->url->link($this->ext_folder.$this->extclass.'/' . $pname, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl);
        $data['cancel'] = $this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl);

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname, $data));
    }

    private function validate($pname) {
        if (!$this->user->hasPermission('modify', $this->ext_folder.$this->extclass.'/' . $pname)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $pname = $this->pnameplus.$pname;

        if (!$this->request->post[$pname . '_login']) {
            $this->error['login'] = $this->language->get('error_login');
        }

        if (!$this->request->post[$pname . '_password']) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if ($this->request->post[$pname . '_fixen'] && $this->request->post[$pname . '_fixen'] != 'ship' && $this->request->post[$pname . '_fixen'] != 'order_noship' && $this->request->post[$pname . '_fixen'] != 'sum') {
            if (!$this->request->post[$pname . '_fixen_amount']) {
                $this->error['fixen'] = $this->language->get('error_fixen');
            }
        }

        if (!isset($this->request->post[$pname . '_gruppa'])) {
            $this->error['dgruppa'] = $this->language->get('error_noempty');
        }

        if (!isset($this->request->post[$pname . '_shippings'])) {
            $this->error['dshippings'] = $this->language->get('error_noempty');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function log() {     
        $this->load->language($this->ext_folder.$this->extclass.'/' . $this->proname);
        
        $this->document->setTitle($this->language->get('heading_title_logs'));

        $data['heading_title'] = $this->language->get('heading_title_logs');
        
        $data['text_list'] = $this->language->get('text_list_logs');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['button_download'] = $this->language->get('button_download');
        $data['button_clear'] = $this->language->get('button_clear');

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link($this->home, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
            'separator' => false,
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl),
            'separator' => ' :: ',
        );

        if (isset($this->request->get['pname'])) {

            $pname = $this->request->get['pname'];

            $this->load->language($this->ext_folder.$this->extclass.'/' . $pname);

            $data['breadcrumbs'][] = array(
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link($this->ext_folder.$this->extclass.'/' . $pname, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
                'separator' => ' :: ',
            );

            $pnameback = '&pname='.$pname;

        }
        else{
            $pnameback = '';
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_logs'),
            'href' => $this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname . '/log', $this->token_name.'=' . $this->session->data[$this->token_name].$pnameback, $this->ssl),
            'separator' => ' :: ',
        );

        $data['download'] = $this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname . '/logdownload', $this->token_name.'=' . $this->session->data[$this->token_name].$pnameback, $this->ssl);
        $data['clear'] = $this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname . '/logclear', $this->token_name.'=' . $this->session->data[$this->token_name].$pnameback, $this->ssl);

        $data['log'] = '';

        $file = DIR_LOGS . $this->pname . '.log';

        if (file_exists($file)) {
            $size = filesize($file);

            if ($size >= 5242880) {
                $suffix = array(
                    'B',
                    'KB',
                    'MB',
                    'GB',
                    'TB',
                    'PB',
                    'EB',
                    'ZB',
                    'YB'
                );

                $i = 0;

                while (($size / 1024) > 1) {
                    $size = $size / 1024;
                    $i++;
                }

                $data['error_warning'] = sprintf($this->language->get('error_warning_logs'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
            } else {
                $data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('tool/log', $data));
    }

    public function logdownload() {
        $this->load->language($this->ext_folder.$this->extclass.'/' . $this->proname);

        $file = DIR_LOGS . $this->pname . '.log';

        if (file_exists($file) && filesize($file) > 0) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Description: File Transfer');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename="' . $this->pname . '_' . date('Y-m-d_H-i-s', time()) . '_error.log"');
            $this->response->addheader('Content-Transfer-Encoding: binary');

            $this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
        } else {
            $this->session->data['error'] = sprintf($this->language->get('error_warning_logs'), basename($file), '0B');

            if (isset($this->request->get['pname'])) {
                $pnameback = '&pname='.$this->request->get['pname'];
            }
            else{
                $pnameback = '';
            }

            $this->response->redirect($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname . '/log', $this->token_name.'=' . $this->session->data[$this->token_name].$pnameback, $this->ssl));
        }
    }
    
    public function logclear() {
        $this->load->language($this->ext_folder.$this->extclass.'/' . $this->proname);

        if (!$this->user->hasPermission('modify', $this->ext_folder.$this->extclass.'/' . $this->pname)) {
            $this->session->data['error'] = $this->language->get('error_permission_logs');
        } else {
            $file = DIR_LOGS . $this->pname . '.log';

            $handle = fopen($file, 'w+');

            fclose($handle);

            $this->session->data['success'] = $this->language->get('text_success_logs');
        }

        if (isset($this->request->get['pname'])) {
            $pnameback = '&pname='.$this->request->get['pname'];
        }
        else{
            $pnameback = '';
        }

        $this->response->redirect($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname . '/log', $this->token_name.'=' . $this->session->data[$this->token_name].$pnameback, $this->ssl));
    }

    public function install() {

        $query = $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . $this->proname . " (status_id INT(15) AUTO_INCREMENT, num_order INT(15), sum DECIMAL(15,2), user TEXT, email TEXT, status INT(1), date_created DATETIME, date_enroled DATETIME, sender TEXT, label TEXT, label2 TEXT, label3 TEXT, label4 TEXT, label5 TEXT, label6 TEXT, label7 INT(15), label8 INT(15), label9 INT(15), PRIMARY KEY (status_id)) DEFAULT CHARACTER SET utf8, ENGINE=MyISAM");

        if (!$this->getEventByCode($this->proname.'_mail_order_add')){
         
            $code = $this->proname.'_mail_order_add';
            $trigger = 'catalog/model/checkout/order/addOrderHistory/before';
            $action = 'extension/payment/'.$this->pname.'/amail';
            $this->model_setting_event->addEvent($code, $trigger, $action);
        
        }
    }

    public function dcloner($data = '') {

        $this->load->language($this->ext_folder.$this->extclass.'/'.$this->proname);
        if ($this->user->hasPermission('modify', $this->ext_folder.$this->extclass.'/' . $this->request->get['pname']) && strpos($this->request->get['pname'], $this->clone_name) && strpos($this->request->get['pname'], $this->pname) !== false) {

            $method     = $this->ext_folder.$this->extclass.'';
            $methodname = $this->request->get['pname'];
            $startput   = DIR_APPLICATION;
            $catput     = DIR_CATALOG;

            if ($this->clone_lang2) {
                $sourseputs = array(
                    $startput . 'controller/' . $method . '/'     => 'php',
                    $catput . 'controller/' . $method . '/'       => 'php',
                    $catput . 'model/' . $method . '/'            => 'php',
                    $startput . 'language/'.$this->clone_lang1.'/' . $method . '/' => 'php',
                    $startput . 'language/'.$this->clone_lang2.'/' . $method . '/' => 'php',
                    $catput . 'language/'.$this->clone_lang1.'/' . $method . '/'   => 'php',
                    $catput . 'language/'.$this->clone_lang2.'/' . $method . '/'   => 'php',
                );
            }
            else{
                $sourseputs = array(
                    $startput . 'controller/' . $method . '/'     => 'php',
                    $catput . 'controller/' . $method . '/'       => 'php',
                    $catput . 'model/' . $method . '/'            => 'php',
                    $startput . 'language/'.$this->clone_lang1.'/' . $method . '/' => 'php',
                    $catput . 'language/'.$this->clone_lang1.'/' . $method . '/'   => 'php',
                );
            }

            foreach ($sourseputs as $sourseput => $rashirenie) {
                unlink($sourseput . $methodname . '.' . $rashirenie);
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl));

        } else {
            $this->session->data['success'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl));
        }
    }

    public function cloner($data = '') {

        $this->load->language($this->ext_folder.$this->extclass.'/'.$this->proname);
        if ($this->user->hasPermission('modify', $this->ext_folder.$this->extclass.'/' . $this->request->get['pname']) && !strpos($this->request->get['pname'], $this->clone_name) && strpos($this->request->get['pname'], $this->pname) !== false) {

            $method     = $this->ext_folder.$this->extclass.'';
            $methodname = $this->request->get['pname'];

            $methodnameorigin = $this->pname.'_card';
            $cloneprefix      = $this->clone_name;
            $startput         = DIR_APPLICATION;
            $catput           = DIR_CATALOG;

            $files = glob(DIR_APPLICATION . 'controller/'.$this->ext_folder.$this->extclass.'/*.php');

            $num = 1;

            if ($files) {
                $ogon = array();
                foreach ($files as $file) {
                    if (strpos($file, $this->request->get['pname'] . $this->clone_name)) {
                        $ogon[] = (int) str_replace($this->request->get['pname'] . $this->clone_name, '', basename($file, '.php'));
                    }
                }
            }
            asort($ogon);

            $num += array_pop($ogon);

            $sourseputs = array(

                $startput . 'controller/' . $method . '/' => 'php',
                $catput . 'controller/' . $method . '/'   => 'php',
                $catput . 'model/' . $method . '/'        => 'php',

            );

            foreach ($sourseputs as $sourseput => $rashirenie) {
                $data = file_put_contents($sourseput . $methodname . $cloneprefix . $num . '.' . $rashirenie, str_replace(ucfirst(str_replace('_', '', $methodnameorigin)), ucfirst(str_replace('_', '', $methodname)) . $cloneprefix . $num, str_replace($methodnameorigin, $methodname . $cloneprefix . $num, file_get_contents($sourseput . $methodnameorigin . '.' . $rashirenie))));
            }

            $methodnameorigin = $methodname;

            if ($this->clone_lang2) {
                $sourseputs = array(
                    $startput . 'language/'.$this->clone_lang1.'/' . $method . '/' => 'php',
                    $startput . 'language/'.$this->clone_lang2.'/' . $method . '/' => 'php',
                    $catput . 'language/'.$this->clone_lang1.'/' . $method . '/'   => 'php',
                    $catput . 'language/'.$this->clone_lang2.'/' . $method . '/'   => 'php',
                );
            }
            else{
                $sourseputs = array(
                    $startput . 'language/'.$this->clone_lang1.'/' . $method . '/' => 'php',
                    $catput . 'language/'.$this->clone_lang1.'/' . $method . '/'   => 'php',
                );
            }

            foreach ($sourseputs as $sourseput => $rashirenie) {
                $data = file_put_contents($sourseput . $methodname . $cloneprefix . $num . '.' . $rashirenie, str_replace(ucfirst(str_replace('_', '', $methodnameorigin)), ucfirst(str_replace('_', '', $methodname)) . $cloneprefix . $num, str_replace($methodnameorigin, $methodname . $cloneprefix . $num, str_replace('PRO', 'PRO CLONE' . $num, file_get_contents($sourseput . $methodnameorigin . '.' . $rashirenie)))));
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl));
        } else {
            $this->session->data['success'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link($this->home_ext, $this->token_name.'=' . $this->session->data[$this->token_name] . $this->home_ext_prefix, $this->ssl));
        }

    }

    private function modLog($texts) {

        if (!isset($modLog)) {
            $modLog = new Log($this->pname.'.log');
        }

        $modLog->write($texts);

    }

    public function status() {

        $this->load->language($this->ext_folder.$this->extclass.'/'.$this->proname);
        $this->document->setTitle($this->language->get('heading_title_status'));
        $data['heading_title'] = $this->language->get('heading_title_status');
        $data['status_title']  = $this->language->get('status_title');

        $data['id']           = $this->language->get('id');
        $data['num_order']    = $this->language->get('num_order');
        $data['sum']          = $this->language->get('sum');
        $data['label']        = $this->language->get('label');
        $data['status']       = $this->language->get('status');
        $data['user']         = $this->language->get('user');
        $data['email']        = $this->language->get('email');
        $data['date_created'] = $this->language->get('date_created');
        $data['date_enroled'] = $this->language->get('date_enroled');
        $data['sender']       = $this->language->get('sender');
        $data['info']         = $this->language->get('info');
        $data['label8']       = $this->language->get('label8');

        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $olimits = array(
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin'),
        );

        $total_statuses = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getTotalStatus($olimits);

        $viewstatuses = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getStatus($olimits);

        $pagination        = new Pagination();
        $pagination->total = $total_statuses;
        $pagination->page  = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->text  = $this->language->get('text_pagination');
        $pagination->url   = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/status', $this->token_name.'=' . $this->session->data[$this->token_name] . '&page={page}', $this->ssl);

        $data['pagination'] = $pagination->render();

        $info    = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/info', $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl);
        $capture = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/capture', $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl);

        $data['viewstatuses'] = array();

        foreach ($viewstatuses as $viewstatus) {
            $info                   = $info . '&order_id=' . $viewstatus['status_id'];
            $capture_href           = $capture . '&order_id=' . $viewstatus['status_id'];
            if ($viewstatus['label8'] == 1) {
                $label8 = $this->language->get('tran_order');
            }
            else if ($viewstatus['label8'] == 2) {
                $label8 = $this->language->get('tran_balance');
            }
            else if ($viewstatus['label8'] == 3) {
                $label8 = $this->language->get('tran_free');
            }
            else {
                $label8 = $this->language->get('tran_none');
            }
            $data['viewstatuses'][] = array(
                'status_id'    => $viewstatus['status_id'],
                'num_order'    => $viewstatus['num_order'],
                'sum'          => $viewstatus['sum'],
                'label'        => $viewstatus['label'],
                'status'       => $this->language->get('status_list_' . $viewstatus['status']),
                'user'         => $viewstatus['user'],
                'email'        => $viewstatus['email'],
                'date_created' => $viewstatus['date_created'],
                'date_enroled' => $viewstatus['date_enroled'],
                'sender'       => $viewstatus['sender'],
                'label8'       => $label8,
                'info'         => $info,
            );
        }

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link($this->home, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_order'),
            'href' => $this->url->link('sale/order', $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
        );

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname.'_view_status', $data));

    }

    private function curlito($data, $rname) {
        $server = 'https://www.liqpay.ua/api/request';

        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
        $order_info = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getPaymentIdByNum($data['order_id']);

        $paydata = array(
            'version'    => 3,
            'public_key' => $this->config->get($this->pnameplus.$order_info['label6'] . '_login'),
            'action'     => $rname,
            'order_id'   => $order_info['label'],
        );

        $private_key = $this->config->get($this->pnameplus.$order_info['label6'] . '_password');
        $signature = base64_encode(sha1($private_key . base64_encode(json_encode($paydata)) . $private_key, 1));

        $request = http_build_query(array(
            'data' => base64_encode(json_encode($paydata)),
            'signature' => $signature,
        ));

        //$request = json_encode($request);

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $server);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            $result = curl_exec($curl);
            curl_close($curl);

            return $result;

        } else {
            echo 'liqpay Pro error: No curl';
            exit();
        }
    }

    private function nameChecker($key, $value) {

        if ($key == 'status') {
            $value = $this->language->get('stat_val_' . $value) . ' (' . $value . ')';
        }

        if ($key == 'action') {
            $value = $this->language->get('stat_action_val_' . $value) . ' (' . $value . ')';
        }

        if ($key == 'paytype') {
            $value = $this->language->get('stat_paytype_val_' . $value) . ' (' . $value . ')';
        }

        if ($key == 'mpi_eci') {
            $value = $this->language->get('stat_mpi_eci_val_' . $value) . ' (' . $value . ')';
        }

        if ($value === true) {$value = $this->language->get('text_status_true');}
        if ($value === false) {$value = $this->language->get('text_status_false');}

        return $value;

    }

    public function capture() {

        if ($this->user->hasPermission('access', 'sale/order')) {

            $curlito = array('order_id' => (int) $this->request->get['order_id']);
            $jsons   = $this->curlito($curlito, 'hold_completion');
            $json    = json_decode(stripslashes($jsons), true);

            $this->load->language($this->ext_folder.$this->extclass.'/'.$this->proname);

            if (isset($json['status']) && $json['status'] == 'success') {
                $this->session->data['status_success'] = $this->language->get('text_capture_success');
                $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
                $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->changeStatus($this->request->get['order_id'], 1);

            } else {
                 $this->modLog($this->pname.' error: CAPTURE PAYMENT '.$json);
                $this->session->data['status_error'] = $this->language->get('text_capture_error');
            }

        } else {
            $this->session->data['status_error'] = $this->language->get('text_capture_error_perrmission');
        }

        if (isset($this->request->get['capture'])) {
            $this->response->redirect($this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/status', $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl));
        } else {
            $this->response->redirect($this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/info', 'order_id=' . (int) $this->request->get['order_id'] . '&'.$this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl));
        }

    }

    public function cancel() {

        if ($this->user->hasPermission('access', 'sale/order')) {

            $curlito = array('order_id' => (int) $this->request->get['order_id']);
            $jsons   = $this->curlito($curlito, 'refund');
            $json    = json_decode(stripslashes($jsons), true);

            $this->load->language($this->ext_folder.$this->extclass.'/'.$this->proname);

            if (isset($json['result']) && $json['result'] == 'ok') {
                $this->session->data['status_success'] = $this->language->get('text_cancel_success');
                $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->changeStatus($this->request->get['order_id'], 3);

            } else {
                $this->modLog($this->pname.' error: CANCEL PAYMENT '.$json);
                $this->session->data['status_error'] = $this->language->get('text_cancel_error');
            }

        } else {
            $this->session->data['status_error'] = $this->language->get('text_cancel_error_perrmission');
        }

        $this->response->redirect($this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/info', 'order_id=' . (int) $this->request->get['order_id'] . '&'.$this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl));

    }

    public function info() {

        $this->load->language($this->ext_folder.$this->extclass.'/'.$this->proname);

        if (isset($this->session->data['status_success'])) {
            $data['success'] = $this->session->data['status_success'];
            unset($this->session->data['status_success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['status_error'])) {
            $data['error_warning'] = $this->session->data['status_error'];
            unset($this->session->data['status_error']);
        } else {
            $data['error_warning'] = '';
        }

        $curlito = array('order_id' => (int) $this->request->get['order_id']);
        $json    = $this->curlito($curlito, 'status');
        $json    = json_decode(stripslashes($json), true);
        if (is_array($json)) {

            $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
            $paystatus = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getPaymentStatus($this->request->get['order_id']);

            if (isset($json['status']) && $paystatus == 2 && $json['status'] == 'success'){
                $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->changeStatus($this->request->get['order_id'], 1);
            }
            
            if (isset($json['status']) && $json['status'] == 'hold_wait') {
                $data['capture']      = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/capture', 'order_id=' . (int) $this->request->get['order_id'] . '&'.$this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl);
                $data['cancel']       = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/cancel', 'order_id=' . (int) $this->request->get['order_id'] . '&'.$this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl);
                $data['text_capture'] = $this->language->get('text_capture');
                $data['text_cancel']  = $this->language->get('text_cancel');
            }
            $info = array();
            foreach ($json as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        if (is_array($value2)) {
                            foreach ($value2 as $key3 => $value3) {
                                $info[$this->language->get('stat_' . $key3) . ' (' . $key3 . ')'] = $this->nameChecker($key3, $value3);
                            }
                        } else {
                            $info[$this->language->get('stat_' . $key2) . ' (' . $key2 . ')'] = $this->nameChecker($key2, $value2);
                        }
                    }
                } else {
                    $info[$this->language->get('stat_' . $key) . ' (' . $key . ')'] = $this->nameChecker($key, $value);
                }
            }
        }
        if (isset($info)) {
            $data['statuses'] = $info;
        } else {
            $data['statuses'] = array($this->language->get('status_nodata') => '');
        }

        $this->document->setTitle($this->language->get('heading_title_capture'));

        $data['heading_title'] = $this->language->get('heading_title_capture');

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link($this->home, $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_order'),
            'href' => $this->url->link('sale/order', $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_status'),
            'href' => $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/status', $this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_status_info'),
            'href' => $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/info', 'order_id=' . (int) $this->request->get['order_id'] . '&'.$this->token_name.'=' . $this->session->data[$this->token_name], $this->ssl),
        );

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname.'_info', $data));

    }

    private function getEventByCode($code) {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

        return $query->row;
    }

}
