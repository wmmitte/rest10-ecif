<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class decaissementModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getDepensesJour() {


        $query = "SELECT dep.id_dep, dep.mnt_dep,dep.details_dep,dep.code_user_dep,
            td.lib_type_dep,time(dep.date_dep) as heure
                           FROM 
                           t_depense dep
                           INNER JOIN t_type_depense td ON dep.type_dep=td.id_type_dep
                           WHERE date(dep.date_dep)='" . date("Y-m-d") . "'" . BTQ_DEP . "
                           ORDER BY dep.date_dep DESC";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " depense jour");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $tot_dep = $this->getTotalDepensesJour();

            $response = array("dep" => $result,
                "totdep" => $tot_dep
            );

            return $response;
        } else {
            return null;
        }
    }

    public function getDepensesofs($offset) {/*ici les depenses du trimestre*/


        $query = "SELECT dep.id_dep, dep.mnt_dep,dep.details_dep,dep.code_user_dep,
            td.lib_type_dep,date(dep.date_dep) as date_dep,time(dep.date_dep) as heure
                           FROM 
                           t_depense dep
                           INNER JOIN t_type_depense td ON dep.type_dep=td.id_type_dep
                           WHERE dep.date_dep >= DATE_SUB(now(), INTERVAL 3 MONTH) " . BTQ_DEP . "
                           ORDER BY dep.date_dep DESC limit  " . QLM . " offset $offset";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vente");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $tot_dep = $this->getTotalDepenses();

            $response = array("dep" => $result,
                "totdep" => $tot_dep
            );

            return $response;
        } else {
            return null;
        }
    }

    public function saveDepense($depense) {


        $mnt_dep = intval($depense['mnt_dep']);
        $type_dep = intval($depense['type_dep']);
        $details_dep = !empty($depense['details_dep']) ? $this->esc($depense['details_dep']) : "Ras";
        $date_dep = (!empty($depense['date_dep'])) ? isoToMysqldate($depense['date_dep']) : date("Y-m-d");



        $response = array();

        if (!empty($type_dep) && !empty($mnt_dep) && $mnt_dep > 0) {
            try {
                $this->mysqli->autocommit(FALSE);
                $heure_vnt = date("H:i:s");
                $query = "INSERT INTO  t_depense (
                     	type_dep,
                     mnt_dep,
                     date_dep,
                     user_dep,
                     login_dep,
                     code_user_dep,
                     details_dep) 
                     VALUES(" . $type_dep . ",
                          " . $mnt_dep . ", 
                              '$date_dep $heure_vnt',
                              
                          " . $_SESSION['userId'] . ",
                         '" . $_SESSION['userLogin'] . "',
                         '" . $_SESSION['userCode'] . "',
                             '" . $details_dep . "')";


                if (!$r = $this->mysqli->query($query))
                    throw new Exception($this->mysqli->error . __LINE__);

                $this->mysqli->commit();
                $this->mysqli->autocommit(TRUE);

                return $response = array("status" => 0,
                    "datas" => "",
                    "msg" => " Depense  effectue avec success!");
            } catch (Exception $exc) {
                $this->mysqli->rollback();
                $this->mysqli->autocommit(TRUE);

                return $response = array("status" => 1,
                    "datas" => "",
                    "msg" => $exc->getMessage());
            }
        } else {
            return $response = array("status" => 0,
                "datas" => "-1",
                "msg" => "Attention donnees incorrectes!");
        }
    }

    public function getVersementsofs($offset) {


        $query = "SELECT vrs.id_vrsmnt, vrs.mnt_vrsmnt,vrs.obj_vrsmnt,date(vrs.date_vrsmnt) as date,time(vrs.date_vrsmnt) as heure,vrs.code_caissier_vrsmnt,
            bnk.nom_bank
                           FROM 
                           t_versement vrs
                           INNER JOIN t_banque bnk ON vrs.bank_vrsmnt=bnk.id_bank
                           WHERE vrs.date_vrsmnt >= DATE_SUB(now(), INTERVAL 3 MONTH) " . BTQ_VERS . "
                           ORDER BY vrs.date_vrsmnt DESC limit  " . QLM . " offset $offset";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " versementofs");

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }

            $tot_vers = $this->getTotalVersements();

            $response = array("vers" => $result,
                "totvers" => $tot_vers
            );

            return $response;
        } else {
            return null;
        }
    }

    public function getTotalDepensesJour() {


        $query = "SELECT SUM(dep.mnt_dep) as mnt_dep 
                           FROM 
                           t_depense dep
                            WHERE date(dep.date_dep)='" . date("Y-m-d") . "'" . BTQ_DEP . "
                           Limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vente");

        $row = $r->fetch_assoc();

        return $row["mnt_dep"];
    }

    public function getTotalDepenses() {


        $query = "SELECT SUM(dep.mnt_dep) as mnt_dep 
                           FROM 
                           t_depense dep
                            WHERE dep.date_dep >= DATE_SUB(now(), INTERVAL 3 MONTH) " . BTQ_DEP . "
                           Limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vente");

        $row = $r->fetch_assoc();

        return $row["mnt_dep"];
    }

    public function getTotalVersements() {


        $query = "SELECT SUM(vrs.mnt_vrsmnt) as mnt_vers 
                           FROM 
                           t_versement vrs
                            WHERE vrs.date_vrsmnt >= DATE_SUB(now(), INTERVAL 3 MONTH) " . BTQ_VERS . "
                           Limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " versement tot");

        $row = $r->fetch_assoc();

        return $row["mnt_vers"];
    }

}

?>