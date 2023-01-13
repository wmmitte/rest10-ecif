<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");
require_once ("api-class/audit_log.class.php");

class factureModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getFacturesOf($search) {
        $response = array();
        $code = $this->esc($search);
        $dt = "";

        $qc = "";
        if (strtolower($code) == "annulee")
            $qc = " OR f.sup_fact=1";

        if (strtolower($code) == "remise")
            $qc = " OR f.remise_vnt_fact>0";

        if (isDate($code))
            $dt = " OR date(f.date_fact)='" . isoToMysqldate($code) . "'";

        $condmag = "";
        if ($_SESSION['userMag'] > 0)
            $condmag = " AND m.id_mag=" . intval($_SESSION['userMag']);

        $query = "SELECT f.motif_sup_fact,f.sup_by_fact,f.id_fact,f.sup_fact,f.date_fact,f.date_sup_fact,f.bl_fact_crdt,f.bl_crdt_regle,f.crdt_fact,f.bl_fact_grt,f.remise_vnt_fact,f.som_verse_crdt,f.code_fact,f.bl_fact_crdt,f.bl_bic,f.bl_tva,f.date_fact,
            c.code_clt,COALESCE(c.nom_clt,'-') as nom_clt,COALESCE(c.exo_tva_clt,0) as exo_tva_clt,
            COALESCE(c.id_clt,0) as id_clt,
            m.nom_mag,m.code_mag,f.code_caissier_fact,f.caissier_fact,u.mag_user
                           FROM 
                           t_facture_vente f
                            INNER JOIN t_user u ON f.caissier_fact=u.id_user 
                           LEFT JOIN t_client c ON f.clnt_fact=c.id_clt 
                           INNER JOIN t_magasin m ON f.mag_fact=m.id_mag 
                           WHERE f.crdt_fact>0 AND (f.code_fact LIKE '%$code%' $qc OR c.nom_clt LIKE '%$code%' $dt) $condmag
                           ORDER BY f.id_fact DESC";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " factures");

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
    
    
    
     public function undoFacture($id_fact) {
        
        //$motif_fact = $fact['motif'];
        $id_fact = intval($id_fact);


        $r = $this->fctUndoRegClntByFact($id_fact);


        $r = $this->fctSupFactVnt($id_fact);
       

	   $r = $this->fctSupVntByFact($id_fact);



        $r = $this->fctDefMotifSupFactVnt($id_fact,"Par Mobile");

       return $response = array("status" => 0,
            "datas" => "",
            "msg" => "Annulation Facture effectuee avec success!");
 
     }

    
    
     public function fctUndoRegClntByFact($id_fact) {
        $id_fact = intval($id_fact);


        $query = "SELECT id_crce_clnt
            FROM t_creance_client 
                WHERE fact_crce_clnt = $id_fact";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $re = "";
        if ($r->num_rows > 0) {
            while ($row = $r->fetch_assoc()) {
                $re = $this->fctUndoRegClnt(intval($row['id_crce_clnt']));
            }
        }
        return $re;
    }
    
    
     public function fctUndoRegClnt($id_crce) {
        $id_crce = intval($id_crce);


        $kry = "SELECT f.code_fact,f.id_fact,
            c.code_clt,c.nom_clt,
            rc.mnt_paye_crce_clnt,rc.date_crce_clnt,rc.caissier_login_crce
                FROM t_creance_client rc
                inner join t_facture_vente f on rc.fact_crce_clnt=f.id_fact
                inner join t_client c on rc.clnt_crce=c.id_clt
                  WHERE rc.id_crce_clnt =$id_crce LIMIT 1";

        $rez = $this->mysqli->query($kry);

        $rezult = $rez->fetch_assoc();
        $id_fact = $rezult['id_fact'];
        $dat = $rezult['date_crce_clnt'];
        $det = "";
        $anc = $rezult['mnt_paye_crce_clnt'];
        $nouv = 0;
        $comm = "Facture : " . $rezult['code_fact'] . " Client : " . $rezult['code_clt'] . "-" . $rezult['nom_clt'] . "  Auteur : " . $rezult['caissier_login_crce'];
        $log = $_SESSION['userLogin'];
        $cod = $_SESSION['userCode'];
        $ide = $_SESSION['userId'];

        $query = "DELETE FROM t_creance_client WHERE id_crce_clnt=$id_crce ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $query = "update t_facture_vente SET bl_crdt_regle=0 WHERE id_fact=$id_fact ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        /*$aud = new aditlogController;
        $aud->auditlog("SUPPRESSION", "Reglements", "Reglement Client", $anc, $nouv, $dat, $ide, $log, $cod, $comm
        );*/

        return $r;
    }
    
    
    
    public function fctSupVntByFact($fact) {
        $id_fact = intval($fact);

        $query = "SELECT f.id_fact,f.bl_tva,f.bl_bic,v.id_vnt,v.pu_theo_vnt,v.qte_vnt,v.mnt_theo_vnt
            FROM t_facture_vente f
            inner join t_vente v on v.facture_vnt=f.id_fact
                WHERE v.facture_vnt = $id_fact";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $re = "";
        if ($r->num_rows > 0) {
            while ($row = $r->fetch_assoc()) {
                $re = $this->fctSupVnt($row);
            }
        }
        return $re;
    }

    
     public function fctSupVnt($fact) {
        $fact = $fact;

        $id_vnt = doubleval($fact['id_vnt']);

        try {
            $this->mysqli->autocommit(FALSE);


            $kry = "SELECT   f.mag_fact,  a.id_art,  v.qte_vnt 
                FROM t_vente v
                inner join t_facture_vente f on v.facture_vnt=f.id_fact
                inner join t_article a on v.article_vnt=a.id_art
                   WHERE v.id_vnt =$id_vnt LIMIT 1";

            $rez = $this->mysqli->query($kry);

            $rezult = $rez->fetch_assoc();
            $article = $rezult['id_art'];
            $magasin = $rezult['mag_fact'];
            $quant = $rezult['qte_vnt'];

           // $qu = "UPDATE t_stock SET qte_stk = (qte_stk + $quant) WHERE art_stk = $article AND mag_stk = $magasin";
           // $r = $this->mysqli->query($qu) or die($this->mysqli->error . __LINE__);

            $qu = "UPDATE t_vente SET sup_vnt=1,date_sup_vnt=now() WHERE id_vnt = $id_vnt";
            $r = $this->mysqli->query($qu) or die($this->mysqli->error . __LINE__);

            $this->mysqli->commit();
            $this->mysqli->autocommit(TRUE);
            return $r;
        } catch (Exception $exc) {
            $this->mysqli->rollback();
            $this->mysqli->autocommit(TRUE);
            $this->response($exc, 204);
        }
    }

    
    
     public function fctSupFactVnt($id_fact) {
        $id_fact = intval($id_fact);

        $query = "update  t_facture_vente set sup_fact=1,date_sup_fact=now() WHERE id_fact=$id_fact ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        return $r;
    }

    public function fctDefMotifSupFactVnt($id_fact, $motif) {
        $id_fact = intval($id_fact);
        $sup_by = $_SESSION['userCode'] . "-" . $_SESSION['userLogin'];

        $query = "update  t_facture_vente set motif_sup_fact='$motif',sup_by_fact='$sup_by' WHERE id_fact=$id_fact ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        return $r;
    }



}

?>