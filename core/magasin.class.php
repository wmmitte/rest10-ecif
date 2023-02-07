<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class magasinModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    
    public function getaSorties($search) {
         $response = array();
        $qry = $this->esc($search['search']);
        $_SESSION['userMag'] = intval($this->esc($search['userMag']));
        $query = "";

         if($qry!=""){
        if ($_SESSION['userMag'] == 0)
            $query = "SELECT m.id_mag, m.code_mag, m.nom_mag  FROM t_magasin m where actifmag=1 AND m.nom_mag like '%".$qry."%' order by m.nom_mag";
        else
            $query = "SELECT m.id_mag, m.code_mag, m.nom_mag  FROM t_magasin m WHERE  m.nom_mag like '%".$qry."%' AND actifmag=1 AND id_mag!=" . intval($_SESSION['userMag']) . "  order by m.nom_mag";
         }else{
            if ($_SESSION['userMag'] == 0)
            $query = "SELECT m.id_mag, m.code_mag, m.nom_mag  FROM t_magasin m where actifmag=1 order by m.nom_mag";
        else
            $query = "SELECT m.id_mag, m.code_mag, m.nom_mag  FROM t_magasin m WHERE actifmag=1 AND id_mag!=" . intval($_SESSION['userMag']) . " order by m.nom_mag";
         }

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $response =  $result;
            return $response;
        }else{
            return $response;
        }
    }
 
}
?>