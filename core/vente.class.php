<?php
require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class venteModel extends model{

    public $data = "";

    public function __construct() {
          parent::__construct();
    }
    
    
    public function etatVente() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $search = $_POST;

        $query = "SELECT date(Date_vnt) as Date_vnt,time(Date_vnt) as heure_vnt,code_fact,bl_fact_grt,bl_fact_crdt,code_caissier_fact,code_clt,code_art,nom_art,nom_mag,code_mag,nom_clt,Qte_vnt,pu_theo_vnt,mnt_theo_vnt,marge_vnt FROM v_etat_ventes  WHERE 1=1";
        if ($_SESSION['userMag'] > 0)
            $query = "SELECT date(Date_vnt) as Date_vnt,time(Date_vnt) as heure_vnt,code_fact,bl_fact_grt,bl_fact_crdt,code_caissier_fact,code_clt,code_art,nom_art,nom_mag,code_mag,nom_clt,Qte_vnt,pu_theo_vnt,mnt_theo_vnt FROM v_etat_ventes WHERE id_mag=" . intval($_SESSION['userMag']);;

        if (!empty($search['magasin']))
            $query.=" AND id_mag=" . intval($search['magasin']);

        if (!empty($search['user']))
            $query.=" AND code_caissier_fact='" . $this->esc($search['user']) . "'";

        if (!empty($search['article']))
            $query.=" AND id_art=" . intval($search['article']);

        if (!empty($search['categorie']))
            $query.=" AND id_cat=" . intval($search['categorie']);

        if (!empty($search['client']))
            $query.=" AND id_clt=" . intval($search['client']);

        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $query.=" AND date(date_vnt)='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $query.=" AND date(date_vnt) between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        if (isset($search['bc']) && $search['bc']!="" && $search['bc']!=2 )
            $query.=" AND bl_fact_grt=0 AND bl_fact_crdt=" . intval($search['bc']);
      
        if (isset($search['bc']) && $search['bc']!="" && $search['bc']==2 )
            $query.=" AND bl_fact_crdt=1 AND bl_fact_grt=1";

        $query.=" ORDER BY id_vnt DESC  ";

  
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $response = array("status" => 0,
                "datas" => $result,
                "msg" => "");
            $this->response($this->json($response), 200);
        } else {
            $response = array("status" => 0,
                "datas" => "",
                "msg" => "");
            $this->response($this->json($response), 200);
        }
        $this->response('', 204);
    }

    public function getVentesJour() {
       
        $condMag = "";
        $sliceT = "";
        
       
        $query = "SELECT a.nom_art,f.code_fact,
                         m.nom_mag,m.code_mag,
                         v.id_vnt,v.qte_vnt,v.pu_theo_vnt,v.mnt_theo_vnt,v.date_vnt,v.marge_vnt,
                         COALESCE(c.nom_clt,'-') as clt,
                         time(f.date_fact) as heure_fact,
                         f.login_caissier_fact,
                         f.code_caissier_fact,
                         f.bl_crdt_regle,f.crdt_fact,f.som_verse_crdt,f.remise_vnt_fact,f.bl_bic,f.bl_tva,
                         f.bl_fact_crdt,f.bl_fact_grt,f.bl_encaiss_grt
                         FROM t_vente v 
                         INNER JOIN t_article a ON v.article_vnt=a.id_art
                         INNER JOIN t_facture_vente f ON v.facture_vnt=f.id_fact
                         INNER JOIN t_magasin m on f.mag_fact=m.id_mag
                         LEFT JOIN t_client c ON f.clnt_fact=c.id_clt 
                         WHERE f.sup_fact=0 AND DATE(v.date_vnt)='" . date('Y-m-d') . "' ".BTQ_VNT." $condMag $sliceT ORDER BY v.id_vnt DESC";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__." vente");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $cpj = $this->getComptantJour();
            $crj = $this->getCreditJour();


            $response = array("vj" => $result, 
                                 "cpj" => $cpj,
                                 "crj" => $crj
                              );
            
            return $response;
           
        } else { 
           return null;
        }
    }
    
    
    public function getVentesJourofs($offset) {
       
        $condMag = "";
        $sliceT = "";
        
       
        $query = "SELECT a.nom_art,f.code_fact,
                         m.nom_mag,m.code_mag,
                         v.id_vnt,v.qte_vnt,v.pu_theo_vnt,v.mnt_theo_vnt,v.date_vnt,v.marge_vnt,
                         COALESCE(c.nom_clt,'-') as clt,
                         time(f.date_fact) as heure_fact,
                         f.login_caissier_fact,
                         f.code_caissier_fact,
                         f.bl_crdt_regle,f.crdt_fact,f.som_verse_crdt,f.remise_vnt_fact,f.bl_bic,f.bl_tva,
                         f.bl_fact_crdt,f.bl_fact_grt,f.bl_encaiss_grt
                         FROM t_vente v 
                         INNER JOIN t_article a ON v.article_vnt=a.id_art
                         INNER JOIN t_facture_vente f ON v.facture_vnt=f.id_fact
                         INNER JOIN t_magasin m on f.mag_fact=m.id_mag
                         LEFT JOIN t_client c ON f.clnt_fact=c.id_clt 
                         WHERE DATE(v.date_vnt)='" . date('Y-m-d') . "' ".BTQ_VNT." $condMag $sliceT ORDER BY v.id_vnt DESC limit ". QLM." offset $offset";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__." vente");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $cpj = $this->getComptantJour();
            $crj = $this->getCreditJour();


            $response = array("vj" => $result, 
                                 "cpj" => $cpj,
                                 "crj" => $crj
                              );
            
            return $response;
           
        } else { 
           return null;
        }
    }
    
    
     public function getComptantJour() {

       $_SESSION['userMag'] = $_GET['userMag'];

        $condMag = "";
        $result = array();
        if ($_SESSION['userMag'] > 0)
            $condMag = "AND f.mag_fact=" . $_SESSION['userMag'] . "";

        $condImpot = "";
       /* if ($_SESSION['impots_cond'] == 1)
            $condImpot = "AND f.bl_tva=1";*/

        $queryt = "SELECT sum(f.tva_fact+f.bic_fact) as taxe,sum(f.remise_vnt_fact) as remise
                         FROM t_facture_vente f 
                         WHERE DATE(f.date_fact)='" . date('Y-m-d') . "'
                          AND f.bl_fact_crdt=0 $condImpot 
                             $condMag LIMIT 1";
        $rt = $this->mysqli->query($queryt) or die($this->mysqli->error . __LINE__);
        $rowt = $rt->fetch_assoc();

        $taxet = $rowt['taxe'];
        $remiset = $rowt['remise'];

        
        $query = "SELECT IFNULL(SUM(f.crdt_fact),0) as mntcpt 
                         FROM t_facture_vente f   WHERE DATE(f.date_fact)='" . date('Y-m-d') . "'
                          AND f.bl_fact_crdt=0 AND f.sup_fact=0 $condImpot
                             $condMag LIMIT 1";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $row = $r->fetch_assoc();

        $result["mntcpt"] = intval($row['mntcpt'] /* + $taxet - $remiset */);

        return $result;
    }
    
    
    public function getCreditJour() {
         $_SESSION['userMag'] = $_GET['userMag'];

        $condMag = "";
        $result = array();
        if ($_SESSION['userMag'] > 0)
            $condMag = "AND f.mag_fact=" . $_SESSION['userMag'] . "";

        $condImpot = "";
       /* if ($_SESSION['impots_cond'] == 1)
            $condImpot = "AND f.bl_tva=1";*/

             
        $queryt = "SELECT sum(f.remise_vnt_fact) as remise
                         FROM t_facture_vente f
                          WHERE DATE(f.date_fact)='" . date('Y-m-d') . "'
                          AND f.bl_fact_crdt=1 AND f.bl_fact_grt=0 AND f.sup_fact=0
                             $condMag $condImpot LIMIT 1";
        $rt = $this->mysqli->query($queryt) or die($this->mysqli->error . __LINE__);
        $rowt = $rt->fetch_assoc();

        $remiset = $rowt['remise'];

        $query = "SELECT IFNULL(SUM(f.crdt_fact),0) as mntcrdt
                         FROM t_facture_vente f 
                           WHERE DATE(f.date_fact)='" . date('Y-m-d') . "'
                          AND f.bl_fact_crdt=1 AND f.bl_fact_grt=0 AND f.sup_fact=0
                             $condMag $condImpot LIMIT 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $row = $r->fetch_assoc();

    $result["mntcrdt"] = intval($row['mntcrdt'] /* - $remiset */);

        return $result;
    } 
    
}

?>