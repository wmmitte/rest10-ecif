<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class rechercheDataModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getaClients() {

        if($_SESSION['userMag']>0)
        $query = "SELECT c.*  FROM t_client c 
             WHERE c.user_clt ".BTQ_USERS." order by c.nom_clt";
        else 
             $query = "SELECT c.*  FROM t_client c";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("clnts" => $result 
            );

            return $response;
        }
    }
    
    
    public function getFournisseurs() {

      
             $query = "SELECT f.id_frns,f.code_frns,
            f.nom_frns,(f.dette_frns-f.dette_en_cours_frns) as dette_frns,
            f.sexe_frns,  f.type_frns,f.adr_frns,
            f.bp_frns,f.pays_frns,f.ville_frns,
            f.tel_frns,f.mob_frns,f.mail_frns,
            f.siteweb_frns FROM t_fournisseur f WHERE f.actif=1 
            AND (f.user_frns ".BTQ_USERS." OR f.id_frns=1)
    order by f.nom_frns";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("frns" => $result 
            );

            return $response;
        }
    }
    
    
    public function getUsers() {

        if($_SESSION['userMag']==0 && $_SESSION['userProfil']<2)
         $query = "SELECT login_user,code_user
              FROM t_user WHERE login_user not in('super','brou','root') AND veille=0 order by login_user";
        else
          $query = "SELECT login_user,code_user
              FROM t_user WHERE mag_user=".intval($_SESSION['userMag'])." AND login_user not in('super','brou','root') AND veille=0 order by login_user";
        


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("usrs" => $result 
            );

            return $response;
        }
    } 
    
    
    public function getMagasins() {

        if ($_SESSION['userMag'] == 0)
            $query = "SELECT m.*  FROM t_magasin m order by m.nom_mag";
        else
            $query = "SELECT m.*  FROM t_magasin m WHERE id_mag=" . intval($_SESSION['userMag']) . " order by m.nom_mag";

   


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("mgs" => $result 
            );

            return $response;
        }
    } 
    
    
    public function getCategories() {

       $query = "SELECT c.id_cat,c.nom_cat,c.code_cat  FROM t_categorie_article c 
           order by
           CASE WHEN c.activite=" . $_SESSION['userMagAct'] . " THEN 0 ELSE 1 END ASC,c.nom_cat";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("categories" => $result 
            );

            return $response;
        }
    } 
    
     public function getArticles() {

        $query = "SELECT a.id_art,a.code_art,a.nom_art,a.seuil_art,a.marq_art,a.model_art,a.caract_art,a.unite_art,a.cat_art,c.nom_cat,u.nom_unite,p.prix_mini_art,p.prix_gros_art  FROM t_article a 
                      left join (select * from t_prix_article GROUP BY art_prix_art DESC) p on a.id_art=p.art_prix_art 
                      inner join t_categorie_article c on a.cat_art=c.id_cat
                      inner join t_unite_article u on a.unite_art=u.id_unite
                      ORDER BY CASE WHEN c.activite=" . $_SESSION['userMagAct'] . " THEN 0 ELSE 1 END ASC,a.nom_art";
   


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("articles" => $result 
            );

            return $response;
        }
    } 
    
    
    
         public function getTypeDepenses() {

        $query = "SELECT td.*  FROM t_type_depense td order by td.lib_type_dep";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
           
            $response = array("typedepenses" => $result 
            );

            return $response;
        }
    } 
    
    
     

}

?>