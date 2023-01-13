<?php

require_once ("api-class/model.php"); 
require_once ("api-class/helpers.php");

class transfertController extends model {

    public $data = "";

    public function __construct() {
        parent::__construct(); 
    }
    
    

  public function etatTransf() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $search = $_POST;


        if ($_SESSION['userMag'] != 0)
            $query = "SELECT t.id_transf, time(t.date_transf) as heure_transf,t.user_transf,t.qte_transf,DATE(t.date_transf) as date_transf,m.nom_mag as nom_mag_src,
                m1.nom_mag as nom_mag_dst,
                a.nom_art,a.code_art,c.code_cat,c.nom_cat
                FROM t_transfert t
                 INNER JOIN t_magasin m  ON t.mag_src_transf=m.id_mag
                 INNER JOIN t_magasin m1  ON t.mag_dst_transf=m1.id_mag
                 INNER JOIN t_article a ON t.art_transf=a.id_art
                 INNER JOIN t_categorie_article c
                 ON a.cat_art=c.id_cat WHERE t.mag_src_transf=" . intval($_SESSION['userMag']);
        else
            $query = "SELECT t.*,time(t.date_transf) as heure_transf,m.nom_mag as nom_mag_src,
                m1.nom_mag as nom_mag_dst,a.code_art,a.nom_art,c.code_cat,c.nom_cat
                FROM t_transfert t
                 INNER JOIN t_magasin m  ON t.mag_src_transf=m.id_mag
                 INNER JOIN t_magasin m1  ON t.mag_dst_transf=m1.id_mag
                 INNER JOIN t_article a ON t.art_transf=a.id_art
                 INNER JOIN t_categorie_article c
                 ON a.cat_art=c.id_cat
                 WHERE 1=1 ";
        
         if (!empty($search['date_deb']) && empty($search['date_fin']))
            $query.=" AND date(t.date_transf)='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $query.=" AND date(t.date_transf) between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";
        
        if (!empty($search['magasin']))
            $query.=" AND t.mag_src_transf=" . intval($search['magasin']);
        
         if (!empty($search['magasind']))
            $query.=" AND t.mag_dst_transf=" . intval($search['magasind']);

        if (!empty($search['article']))
            $query.=" AND t.art_transf=" . intval($search['article']);

        if (!empty($search['categorie']))
            $query.=" AND c.id_cat=" . intval($search['categorie']);

        $query .= " Order by t.date_transf DESC,m.nom_mag,c.nom_cat,a.nom_art";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $row['supok'] = false;
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
    
    
    

    public function transfStock() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $appstock = $_POST;

  
        $qtet = intval($appstock['qte_transf']);
        $ms = intval($appstock['mag_src_transf']);
        $md = intval($appstock['mag_dst_transf']);
        $art = intval($appstock['art_transf']);
        
        $mtf="Rupture de stock";
        if(!empty($appstock['motif_transf']))
        $mtf = $this->esc($appstock['motif_transf']); 

        $response = array();
        $query = " INSERT INTO  t_transfert 
            (mag_src_transf,mag_dst_transf,art_transf,qte_transf,date_transf,motif_transf,user_transf) 
             VALUES($ms,$md,$art,$qtet,now(),'$mtf'," .  $_SESSION['userId'] . ")";
        
        

        if (!empty($appstock)) {
            try {
                 $this->mysqli->autocommit(FALSE);
                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);
 

                $query = "SELECT id_stk FROM t_stock  WHERE art_stk =$art AND mag_stk=$md LIMIT 1";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

                if ($r->num_rows > 0) {
                    $query = "UPDATE t_stock SET qte_stk=qte_stk + $qtet WHERE art_stk =$art AND mag_stk=$md";
                    $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
                } else {
                    $query = "INSERT INTO t_stock (art_stk,mag_stk,qte_stk,date_stk) VALUES($art,$md,$qtet,now())";
                    $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
                }
                
                
                $query = "UPDATE t_stock SET qte_stk=qte_stk - $qtet WHERE art_stk =$art AND mag_stk=$ms";
                    $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

                 $this->mysqli->commit();
                $this->mysqli->autocommit(TRUE);
                
                $response = array("status" => 0,
                    "datas" => $appstock,
                    "msg" => "Transfert effectue avec success!");

                $this->response($this->json($response), 200);
            } catch (Exception $exc) {
                 $this->mysqli->rollback();
                $this->mysqli->autocommit(TRUE);
                $response = array("status" => 1,
                    "datas" => "",
                    "msg" => $exc->getMessage());

                $this->response($this->json($response), 200);
            }
        }
        else
            $this->response('', 204); 
    }
    
    
     public function undoTransf() {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }

        $fact = $_POST;

        $id = intval($fact['id_transf']);

 
        $query = "DELETE FROM t_transfert WHERE id_transf=$id ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $response = array("status" => 0,
            "datas" => $r,
            "msg" => "Transfert Supprime avec success!!!");
        $this->response($this->json($response), 200);

        $this->response('', 204);
    }

} 

session_name('SessSngS');
session_start(); 
if(isset($_SESSION['userId'])){
$app = new transfertController;
$app->processApp();
}

?>