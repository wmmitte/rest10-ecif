<?php
 
        function text_reduit($str, $nb_caract_maxi=200)
{
	if(strlen($str)>=$nb_caract_maxi)
	{
		$str = strip_tags($str);  
		$str = substr($str, 0, $nb_caract_maxi);  
		if(strrpos($str," ") > 1) { $str = substr($str, 0, strrpos($str," ")); } 
		$str = $str."";
	}
	return $str;
}

 
function remove_special_chars($text, $etendue, $carac_remplace="_")
{
	 
	$text = str_replace(array("à","â","ä"), "a", $text);
	$text = str_replace(array("é","è","ê","ë"), "e", $text);
	$text = str_replace(array("ï","ì"), "i", $text);
	$text = str_replace(array("ô","ö"), "o", $text);
	$text = str_replace(array("ù","û","ü"), "u", $text);
	$text = str_replace("ç", "c", $text);
 	if($etendue=="fichier_win")
	{
		$text = str_replace(array('\\','/',':','*','?','"','<','>','|','&'), $carac_remplace, $text);
	}
 	elseif($etendue=="normal" || $etendue=="max")
	{
		$carac_ok = ($etendue=="normale")  ?  array("-",".","_","'","(",")","[","]")  :  array("-",".","_");
		for($i=0; $i<strlen($text); $i++){
			if(!preg_match("/[0-9a-z]/i",$text[$i]) && !in_array($text[$i],$carac_ok))	$text[$i] = $carac_remplace;
		}
		$text = str_replace($carac_remplace.$carac_remplace, $carac_remplace, $text);
	}
	return trim($text,$carac_remplace);
}

  
function num2carac($valeur)
{
	return (strlen($valeur)<2) ? "0".$valeur : $valeur;
}

 
function unix_timestamp($date)
{
  $date = str_replace(array(' ', ':'), '-', $date);
  $c    = explode('-', $date);
  $c    = array_pad($c, 6, 0);
  array_walk($c, 'intval');
 
  return mktime($c[3], $c[4], $c[5], $c[1], $c[2], $c[0]);
}
 
function db_insert_date()
	{
		return strftime("%Y-%m-%d %H:%M:%S");
	}
        

        function isoToMysqldate($date){
            
            $dt =explode("/",$date);
            
            return $dt[2]."-".$dt[1]."-".$dt[0];
            
        } 
        
        function isDate($date){
            
            $dt =explode("/",$date);
            
            return count($dt)==3;
            
        } 
        
        
function alert($text_alerte, $onload=true)
{
	echo "<script type='text/javascript'>  ".($onload==true?"window.onload=":"")." alert(\"".$text_alerte."\");  </script>";
}


 
function redir($adresse)
{
	echo "<script type='text/javascript'>  window.location.replace(\"".$adresse."\");  </script>";
	exit();
}

 
function php_self()
{
	return htmlentities($_SERVER["PHP_SELF"]);
}

            
 function ConvLetter($Nombre, $Devise, $Langue) {
        $dblEnt=''; $byDec='';
        $bNegatif='';
        $strDev = '';
            $strCentimes = '';

        if( $Nombre < 0 ) {
            $bNegatif = true;
            $Nombre = abs($Nombre);

        }
        $dblEnt = intval($Nombre) ;
            $byDec = round(($Nombre - $dblEnt) * 100) ;
        if( $byDec == 0 ) {
            if ($dblEnt > 999999999999999) {
                return "#TropGrand" ;
            }
            }
        else {
            if ($dblEnt > 9999999999999.99) {
                return "#TropGrand" ;
            }
            }
            switch($Devise) {
            case 0 :
                if ($byDec > 0) $strDev = " virgule" ;
                            break;
            case 1 :
                $strDev = " Euro" ;
                if ($byDec > 0) $strCentimes = $strCentimes . " Cents" ;
                            break;
            case 2 :
                $strDev = " Dollar" ;
                if ($byDec > 0) $strCentimes = $strCentimes . " Cent" ;
                            break;
            }
        if (($dblEnt > 1) && ($Devise != 0)) $strDev = $strDev . "s" ;

            $NumberLetter = ConvNumEnt(floatval($dblEnt), $Langue) . $strDev . " " . ConvNumDizaine($byDec, $Langue) . $strCentimes ;
            return $NumberLetter;
    }

  function ConvNumEnt($Nombre, $Langue) {
       $byNum=$iTmp=$dblReste='' ;
       $StrTmp = '';
       $NumEnt='' ;
        $iTmp = $Nombre - (intval($Nombre / 1000) * 1000) ;
        $NumEnt = ConvNumCent(intval($iTmp), $Langue) ;
        $dblReste = intval($Nombre / 1000) ;
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000) ;
        $StrTmp = ConvNumCent(intval($iTmp), $Langue) ;
        switch($iTmp) {
            case 0 :
                            break;
            case 1 :
                $StrTmp = "mille " ;
                            break;
            default :
                $StrTmp = $StrTmp . " mille " ;
        }
        $NumEnt = $StrTmp . $NumEnt ;
        $dblReste = intval($dblReste / 1000) ;
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000) ;
        $StrTmp = ConvNumCent(intval($iTmp), $Langue) ;
        switch($iTmp) {
            case 0 :
                            break;
            case 1 :
                $StrTmp = $StrTmp . " million " ;
                            break;
            default :
                $StrTmp = $StrTmp . " millions " ;
        }
        $NumEnt = $StrTmp . $NumEnt ;
        $dblReste = intval($dblReste / 1000) ;
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000) ;
        $StrTmp = ConvNumCent(intval($iTmp), $Langue) ;
            switch($iTmp) {
            case 0 :
                            break;
            case 1 :
                $StrTmp = $StrTmp . " milliard " ;
                            break;
            default :
                $StrTmp = $StrTmp . " milliards " ;
        }
        $NumEnt = $StrTmp . $NumEnt ;
        $dblReste = intval($dblReste / 1000) ;
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000) ;
        $StrTmp = ConvNumCent(intval($iTmp), $Langue) ;
            switch($iTmp) {
            case 0 :
                            break;
            case 1 :
                $StrTmp = $StrTmp . " billion " ;
                            break;
            default :
                $StrTmp = $StrTmp . " billions " ;
        }
        $NumEnt = $StrTmp . $NumEnt ;
        return $NumEnt;
    }

   function ConvNumDizaine($Nombre, $Langue) {
        $TabUnit=$TabDiz='';
        $byUnit=$byDiz='' ;
        $strLiaison = '' ;

        $TabUnit = array("", "un", "deux", "trois", "quatre", "cinq", "six", "sept",
            "huit", "neuf", "dix", "onze", "douze", "treize", "quatorze", "quinze",
            "seize", "dix-sept", "dix-huit", "dix-neuf") ;
        $TabDiz = array("", "", "vingt", "trente", "quarante", "cinquante",
            "soixante", "soixante", "quatre-vingt", "quatre-vingt") ;
        if ($Langue == 1) {
            $TabDiz[7] = "septante" ;
            $TabDiz[9] = "nonante" ;
            }
        else if ($Langue == 2) {
            $TabDiz[7] = "septante" ;
            $TabDiz[8] = "huitante" ;
            $TabDiz[9] = "nonante" ;
        }
        $byDiz = intval($Nombre / 10) ;
        $byUnit = $Nombre - ($byDiz * 10) ;
        $strLiaison = "-" ;
        if ($byUnit == 1) $strLiaison = " et " ;
        switch($byDiz) {
            case 0 :
                $strLiaison = "" ;
                            break;
            case 1 :
                $byUnit = $byUnit + 10 ;
                $strLiaison = "" ;
                            break;
            case 7 :
                if ($Langue == 0) $byUnit = $byUnit + 10 ;
                            break;
            case 8 :
                if ($Langue != 2) $strLiaison = "-" ;
                            break;
            case 9 :
                if ($Langue == 0) {
                    $byUnit = $byUnit + 10 ;
                    $strLiaison = "-" ;
                }
                            break;
        }
        $NumDizaine = $TabDiz[$byDiz] ;
        if ($byDiz == 8 && $Langue != 2 && $byUnit == 0) $NumDizaine = $NumDizaine . "s" ;
        if ($TabUnit[$byUnit] != "") {
            $NumDizaine = $NumDizaine . $strLiaison . $TabUnit[$byUnit] ;
            }
        else {
            $NumDizaine = $NumDizaine ;
        }
        return $NumDizaine;
    }

   function ConvNumCent($Nombre, $Langue) {
        $TabUnit='' ;
        $byCent=$byReste='' ;
        $strReste = '' ;
        $NumCent='';
        $TabUnit = array("", "un", "deux", "trois", "quatre", "cinq", "six", "sept","huit", "neuf", "dix") ;

        $byCent = intval($Nombre / 100) ;
        $byReste = $Nombre - ($byCent * 100) ;
        $strReste = ConvNumDizaine($byReste, $Langue);
        switch($byCent) {
            case 0 :
                $NumCent = $strReste ;
                            break;
            case 1 :
                if ($byReste == 0)
                    $NumCent = "cent" ;
                else
                    $NumCent = "cent " . $strReste ;
                break;
            default :
                if ($byReste == 0)
                    $NumCent = $TabUnit[$byCent] . " cents" ;
                else
                    $NumCent = $TabUnit[$byCent] . " cent " . $strReste ;
            }
            return $NumCent;
    }
            



?>
