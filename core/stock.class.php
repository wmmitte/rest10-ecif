<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class stockModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getApprovisionnementArticles($id_appro) {

        $id = intval($id_appro);

        $query = "SELECT a.code_art,a.nom_art,
                         ta.id_appro_art,ta.qte_appro_art,m.nom_mag,m.code_mag,ap.user_appro,ta.code_user_appro_art
                          FROM t_approvisionnement_article ta 
                         INNER JOIN t_article a ON ta.art_appro_art=a.id_art
                         INNER JOIN t_magasin m ON ta.mag_appro_art=m.id_mag
                         INNER JOIN t_approvisionnement ap ON ta.appro_appro_art=ap.id_appro
                          WHERE ap.id_appro=$id ORDER BY m.nom_mag,a.nom_art ASC";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $result = array();
        if ($r->num_rows > 0) {
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
        }

        $approv = $this->getApprovisionnement($id);

        $response = array("appdetails" => $result,
            "approv" => $approv
        );

        return $response;
    }

    public function getApprovisionnement($id_appro) {

        $query = "SELECT 
            ap.code_user_appro,
            ap.mnt_revient_appro,
            ap.bon_liv_appro,ap.id_appro,
            date(ap.date_appro) as date_appro,
            time(ap.date_appro) as heure_appro,
            f.code_frns,
            f.nom_frns 
            FROM t_approvisionnement ap  
            INNER JOIN t_fournisseur f ON ap.frns_appro=f.id_frns
            WHERE ap.id_appro=$id_appro LIMIT 1";



        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $res = $r->fetch_assoc();
        return $res;
    }

    public function etatStocklm($offset) {


        if ($_SESSION['userMag'] != 0)
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,a.unite_art,a.cat_art,m.nom_mag,
p.prix_mini_art,p.prix_gros_art                 
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE s.mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,a.unite_art,a.cat_art,m.nom_mag,
   p.prix_mini_art,p.prix_gros_art              
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE 1=1";

        $query .= " Order by m.nom_mag,ca.nom_cat,a.nom_art limit ". QLM." offset $offset ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $response = array("stock" => $result
            );

            return $response;
        }
    }

   
    
    public function etatStock($search) {


        if ($_SESSION['userMag'] != 0)
        /* $query = "SELECT *,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
          left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
          WHERE mag_stk=" . intval($_SESSION['userMag']); */
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag,
p.prix_mini_art,p.prix_gros_art                
from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE s.mag_stk=" . intval($_SESSION['userMag']);
        else
            $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag,
                p.prix_mini_art,p.prix_gros_art
                from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
  left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on s.art_stk=p.art_prix_art
  WHERE 1=1";

        if (!empty($search['magasin']))
            $query.=" AND s.mag_stk=" . intval($search['magasin']);

        if (!empty($search['article']))
            $query.=" AND s.art_stk=" . intval($search['article']);

        if (!empty($search['categorie']))
            $query.=" AND ca.id_cat=" . intval($search['categorie']);

        $query .= " Order by m.nom_mag,ca.nom_cat,a.nom_art";
        
       

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);


        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $response = array("stock" => $result
            );

            return $response;
        }
    }
    
    
    
    
    public function queryEtatStock($search) {
        $response = array();
        $qry = $this->esc($search['search']);
        $_SESSION['userMag'] = intval($this->esc($search['userMag']));
        $query = "";

         if($qry!=""){
        if ($_SESSION['userMag'] != 0)
            $query = "SELECT nom_mag,nom_cat,nom_art,qte_stk,p.seuil_art,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE (nom_art like '%$qry%' OR nom_cat like '%$qry%') AND  mag_stk=" . intval($_SESSION['userMag']) ." Order by nom_mag,nom_cat,nom_art";
        else
            $query = "SELECT nom_mag,nom_cat,nom_art,qte_stk,p.seuil_art,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE nom_art like '%$qry%' OR nom_cat like '%$qry%' Order by nom_mag,nom_cat,nom_art ";
         }else{

            if ($_SESSION['userMag'] != 0)
            $query = "SELECT nom_mag,nom_cat,nom_art,qte_stk,p.seuil_art,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE  mag_stk=" . intval($_SESSION['userMag']). " Order by nom_mag,nom_cat,nom_art limit 100 ";
        else
            $query = "SELECT nom_mag,nom_cat,nom_art,qte_stk,p.seuil_art,p.prix_mini_art,p.prix_gros_art FROM v_etat_stock 
                left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on v_etat_stock.art_stk=p.art_prix_art
                WHERE 1=1 Order by nom_mag,nom_cat,nom_art limit 100 ";

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
    
    
      public function etatAlertelm($offset) {


        if ($_SESSION['userMag'] != 0)
             $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag  from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
WHERE s.qte_stk <= a.seuil_art
AND s.mag_stk=" . intval($_SESSION['userMag']);
        else
             $query = "select s.*,a.nom_art,a.code_art,ca.id_cat,ca.nom_cat,a.seuil_art,m.nom_mag  from t_stock s
   inner join t_magasin m on s.mag_stk=m.id_mag
  inner join t_article a on s.art_stk=a.id_art
  inner join t_categorie_article ca on a.cat_art=ca.id_cat
WHERE s.qte_stk <= a.seuil_art";

        $query .= " Order by m.nom_mag,ca.nom_cat,a.nom_art limit ". QLM." offset $offset ";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $response = array("stock" => $result
            );

            return $response;
        }
    }

}

?>