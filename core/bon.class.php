<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");
require_once ("api-class/audit_log.class.php");

class bonModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getBonsOf($search) {
        $response = array();
        $code = $this->esc($search);
        $dt = "";  

        if (isDate($code))
            $dt = " OR date(app.date_appro)='" . isoToMysqldate($code) . "'";
         $condmag = "";
       /* if ($_SESSION['userMag'] > 0)
            $condmag = " AND m.id_mag=" . intval($_SESSION['userMag']); */
        
            $query = "SELECT distinct(app.id_appro),app.bon_liv_appro,app.bl_bon_dette,app.bl_dette_regle,app.dette_appro,app.actif,
            mnt_revient_appro,app.date_appro,app.login_appro,app.code_user_appro,app.user_appro,f.nom_frns  
            FROM t_approvisionnement app 
            left JOIN (select id_frns,nom_frns from t_fournisseur) f on app.frns_appro=f.id_frns 
            INNER JOIN t_approvisionnement_article apa ON app.id_appro=apa.appro_appro_art
            WHERE  1=1 AND (app.bon_liv_appro LIKE '%$code%' $dt) $condmag LIMIT ".QLM;


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " bons");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            } 
            $response = $result;

            return $response;
        } else {
            return $response;
        }
    }
    
    
    
     public function undoBon($id_fact) {
        
        
        $id_fact = intval($id_fact);


        $r = $this->fctUndoDette($id_fact);


        $r = $this->fctUndoApproArt($id_fact);


        $r = $this->fctUndoAppro($id_fact);

 
       return $response = array("status" => 0,
            "datas" => "",
            "msg" => "Annulation du bon effectuee avec success!");
 
     }

    
    
     public function fctUndoDette($id_fact) {
        $id = intval($id_fact);


       $query = "DELETE FROM t_dette_fournisseur WHERE bon_dette_frns=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        
        return $r;
    }
    
    
     public function fctUndoApproArt($id_crce) {
        $id = intval($id_crce);


        $query = "DELETE FROM t_approvisionnement_article WHERE appro_appro_art=$id "; 

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__); 
        return $r;
    }
    
    
    
    public function fctUndoAppro($fact) {
        $id = intval($fact);

         $query = "DELETE FROM t_approvisionnement WHERE id_appro=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        
        return $r;
    } 

}

?>