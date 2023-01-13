<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class reglementModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getReglementsClientsRecents() {


        $condmag = "";
        if ($_SESSION['userMag'] > 0)
            $condmag = " AND fv.caissier_fact in (SELECT id_user FROM t_user where mag_user=" . intval($_SESSION['userMag']) . ")";


        $query = "SELECT date(c.date_crce_clnt) as date_crce_clnt,time(c.date_crce_clnt) as heure_crce_clnt,
            c.code_caissier_crce,
            c.caissier_login_crce,
            c.mnt_paye_crce_clnt,
             c.id_crce_clnt,
            fv.code_fact,
             fv.id_fact,
            fv.date_fact,
            cl.code_clt,
            cl.nom_clt,
            (crdt_fact-som_verse_crdt) as reste
            FROM t_creance_client c 
            INNER JOIN t_facture_vente fv ON c.fact_crce_clnt=fv.id_fact
            INNER JOIN t_client cl ON fv.clnt_fact=cl.id_clt 
            where c.date_crce_clnt >= DATE_SUB(now(), INTERVAL 3 MONTH) $condmag  AND 
            fv.sup_fact=0 AND fv.bl_fact_grt=0 " . BTQ_REGC . " order by c.id_crce_clnt DESC,c.date_crce_clnt DESC";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " reglements Client");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            
             $totc = $this->getTotalReglementsClientsRecents();

            $response = array("recentsRc" => $result,
                                    "totcreance" => $totc
            );

            return $response;
        } else {
            return null;
        }
    }

    public function getTotalReglementsClientsRecents() {


        $condmag = "";
        if ($_SESSION['userMag'] > 0)
            $condmag = " AND fv.caissier_fact in (SELECT id_user FROM t_user where mag_user=" . intval($_SESSION['userMag']) . ")";


        $query = "SELECT  
            SUM(fv.crdt_fact-fv.som_verse_crdt) as reste
           FROM t_creance_client c 
            INNER JOIN t_facture_vente fv ON c.fact_crce_clnt=fv.id_fact
            INNER JOIN t_client cl ON fv.clnt_fact=cl.id_clt 
            where fv.sup_fact=0 $condmag AND  fv.bl_fact_grt=0 " . BTQ_REGC . "
            AND  c.date_crce_clnt >= DATE_SUB(now(), INTERVAL 3 MONTH)  ";
        
        

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $row = $r->fetch_assoc();
        $result["mntregtot"] = doubleval($row['reste']);

        return $result;
    }

    public function getComptantJour() {


        $query = "SELECT IFNULL(SUM(v.mnt_theo_vnt),0) as mntcpt,sum(f.tva_fact+f.bic_fact) as taxe,sum(f.remise_vnt_fact) as remise
                         FROM t_vente v 
                          INNER JOIN t_facture_vente f ON v.facture_vnt=f.id_fact
                          WHERE DATE(v.date_vnt)='" . date('Y-m-d') . "' " . BTQ_VNT . "
                          AND f.bl_fact_crdt=0 LIMIT 1";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $row = $r->fetch_assoc();
        $result["mntcpt"] = doubleval($row['mntcpt'] + $row['taxe'] - $row['remise']);

        return $result;
    }

}

?>