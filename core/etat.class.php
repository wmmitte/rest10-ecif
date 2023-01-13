<?php

require_once ("api-class/model.php");
require_once ("api-class/helpers.php");

class etatModel extends model {

    public $data = "";

    public function __construct() {
        parent::__construct();
    }

    public function getEtatCreances() {

        $query = "SELECT  
            fv.code_caissier_fact,
            fv.bl_fact_grt,
            fv.login_caissier_fact,
            (fv.crdt_fact-fv.som_verse_crdt-remise_vnt_fact) as mnt_crce,
            fv.code_fact,fv.id_fact,
            date(fv.date_fact) as date_fact,
            time(fv.date_fact) as heure_fact,
            cl.id_clt,
            cl.code_clt,
            cl.nom_clt ,
            m.code_mag
            FROM t_facture_vente fv 
             INNER JOIN t_client cl ON fv.clnt_fact=cl.id_clt
             inner join t_magasin m on fv.mag_fact=m.id_mag
            WHERE fv.bl_fact_crdt=1 AND fv.sup_fact=0 AND fv.bl_fact_grt=0 AND fv.bl_crdt_regle=0  ";

        $query.="  order by fv.id_fact ASC";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $tot_crce = $this->getTotalCreances();

            $response = array("crce" => $result,
                "totcrce" => $tot_crce
            );

            return $response;
        }
    }

    public function getEtatCreancesofs($offset) {

        $query = "SELECT  
            fv.code_caissier_fact,
            fv.bl_fact_grt,
            fv.login_caissier_fact,
            (fv.crdt_fact-fv.som_verse_crdt-remise_vnt_fact) as mnt_crce,
            fv.code_fact,fv.id_fact,
            date(fv.date_fact) as date_fact,
            time(fv.date_fact) as heure_fact,
            cl.id_clt,
            cl.code_clt,
            cl.nom_clt ,
            m.code_mag
            FROM t_facture_vente fv 
             INNER JOIN t_client cl ON fv.clnt_fact=cl.id_clt
             inner join t_magasin m on fv.mag_fact=m.id_mag
            WHERE fv.bl_fact_crdt=1 AND fv.sup_fact=0 AND fv.bl_fact_grt=0 AND fv.bl_crdt_regle=0 AND fv.crdt_fact>0 AND (fv.crdt_fact-fv.som_verse_crdt-fv.remise_vnt_fact)>0 " . BTQ_CRED . " ";


        $query.="  order by fv.date_fact ASC limit  " . QLM . " offset $offset";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $tot_crce = $this->getTotalCreances();

            $response = array("crce" => $result,
                "totcrce" => $tot_crce
            );

            return $response;
        }
    }

    public function getSearchEtatCreances($search) {


        $query = "SELECT  
            fv.code_caissier_fact,
            fv.bl_fact_grt,
            fv.login_caissier_fact,
            (fv.crdt_fact-fv.som_verse_crdt-remise_vnt_fact) as mnt_crce,
            fv.code_fact,fv.id_fact,
            date(fv.date_fact) as date_fact,
            time(fv.date_fact) as heure_fact,
            cl.id_clt,
            cl.code_clt,
            cl.nom_clt ,
            m.code_mag
            FROM t_facture_vente fv 
             INNER JOIN t_client cl ON fv.clnt_fact=cl.id_clt
             inner join t_magasin m on fv.mag_fact=m.id_mag
            WHERE fv.bl_fact_crdt=1 AND fv.bl_fact_grt=0 AND fv.sup_fact=0
            AND fv.bl_crdt_regle=0 AND fv.crdt_fact>0 
            AND (fv.crdt_fact-fv.som_verse_crdt-fv.remise_vnt_fact)>0 " . BTQ_CRED . " ";


        if (!empty($search['user']))
            $query.=" AND fv.code_caissier_fact='" . $this->esc($search['user']) . "'";

        if (!empty($search['magasin']))
            $query.=" AND fv.mag_fact=" . intval($search['magasin']);


        if (!empty($search['client']))
            $query.=" AND fv.clnt_fact=" . intval($search['client']);


        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $query.=" AND date(fv.date_fact)='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $query.=" AND date(fv.date_fact) between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query.="  order by fv.id_fact ASC";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $tot_crce = $this->getTotalCreances();

            $response = array("crce" => $result,
                "totcrce" => $tot_crce
            );

            return $response;
        }
    }

    public function getFactureDetails($paramId) {

        $id = doubleval($paramId);

        $query = "SELECT a.nom_art,
                         v.qte_vnt,v.pu_theo_vnt
                          FROM t_vente v
                         INNER JOIN t_article a ON v.article_vnt=a.id_art
                          INNER JOIN t_facture_vente f ON v.facture_vnt=f.id_fact
                          WHERE f.id_fact=$id ORDER BY a.nom_art ASC";
 
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $result = array();
        if ($r->num_rows > 0) {
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
        }

        $item = $this->getFacture($id);

        $response = array("factureDetails" => $result,
            "facture" => $item
        );

        return $response;
    }

    public function getfacture($paramId) {

        $query = "SELECT
            f.code_fact,
            f.crdt_fact,
            f.code_caissier_fact, 
            m.nom_mag,
            c.nom_clt,
            c.code_clt, 
             date(f.date_fact) as date_fact,
            time(f.date_fact) as heure_fact
             FROM t_facture_vente f  
            INNER JOIN t_magasin m ON f.mag_fact=m.id_mag
            INNER JOIN t_client c ON f.clnt_fact=c.id_clt
            WHERE f.id_fact=$paramId LIMIT 1";



        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        $res = $r->fetch_assoc();
        return $res;
    }

    public function getSearchEtatDettes($search) {

        $query = "SELECT 
            ap.code_user_appro,
            ap.login_appro,
            (ap.mnt_revient_appro-ap.som_verse_dette) as mnt_dette ,
            ap.bon_liv_appro,ap.id_appro,
            date(ap.date_appro) as date_appro,
            time(ap.date_appro) as heure_appro,
            f.code_frns,
            f.nom_frns 
            FROM t_approvisionnement ap  
            INNER JOIN t_fournisseur f ON ap.frns_appro=f.id_frns
            WHERE ap.bl_bon_dette=1 AND ap.bl_dette_regle=0 " . BTQ_DET . " ";

        if (!empty($search['fournisseur']))
            $query.=" AND f.id_frns=" . intval($search['fournisseur']);

        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $query.=" AND date(ap.date_appro)='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $query.=" AND date(ap.date_appro) between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query.=" ORDER BY ap.date_appro ASC, f.nom_frns ASC";



        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $tot_crce = $this->getTotalCreances();

            $response = array("dette" => $result,
                "totdette" => $tot_crce
            );

            return $response;
        }
    }

    public function getTotalCreances() {


        $query = "SELECT  
            SUM(fv.crdt_fact-fv.som_verse_crdt-remise_vnt_fact) as mnt_crce
            FROM t_facture_vente fv 
             WHERE fv.bl_fact_crdt=1 AND fv.sup_fact=0 AND fv.bl_fact_grt=0 AND fv.bl_crdt_regle=0 AND fv.crdt_fact>0 AND (fv.crdt_fact-fv.som_verse_crdt-fv.remise_vnt_fact)>0 " . BTQ_CRED . "  
                           Limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vente");

        $row = $r->fetch_assoc();

        return $row["mnt_crce"];
    }

    public function getEtatDettes() {

        $query = "SELECT 
            ap.code_user_appro,
            ap.login_appro,
            (ap.mnt_revient_appro-ap.som_verse_dette) as mnt_dette,
            ap.bon_liv_appro,ap.id_appro,
            date(ap.date_appro) as date_appro,
            time(ap.date_appro) as heure_appro,
            f.code_frns,
            f.nom_frns 
            FROM t_approvisionnement ap  
            INNER JOIN t_fournisseur f ON ap.frns_appro=f.id_frns
            WHERE ap.bl_bon_dette=1 AND ap.bl_dette_regle=0 ";

        $query.="  order by ap.date_appro DESC";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $tot_dette = $this->getTotalDettes();

            $response = array("dette" => $result,
                "totdette" => $tot_dette
            );

            return $response;
        }
    }

    public function getEtatDettesofs($offset) {

        $query = "SELECT 
            ap.code_user_appro,
            ap.login_appro,
            (ap.mnt_revient_appro-ap.som_verse_dette) as mnt_dette,
            ap.bon_liv_appro,ap.id_appro,
            date(ap.date_appro) as date_appro,
            time(ap.date_appro) as heure_appro,
            f.code_frns,
            f.nom_frns 
            FROM t_approvisionnement ap  
            INNER JOIN t_fournisseur f ON ap.frns_appro=f.id_frns
            WHERE ap.bl_bon_dette=1 AND ap.bl_dette_regle=0 " . BTQ_DET . " ";

        $query.="  order by ap.date_appro ASC limit  " . QLM . " offset $offset";


        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);

        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $tot_dette = $this->getTotalDettes();

            $response = array("dette" => $result,
                "totdette" => $tot_dette
            );

            return $response;
        }
    }

    public function getTotalDettes() {


        $query = "SELECT  
            SUM(ap.mnt_revient_appro-ap.som_verse_dette) as mnt_dette FROM t_approvisionnement ap
            WHERE ap.bl_bon_dette=1 AND ap.bl_dette_regle=0 " . BTQ_DET . "  
                           Limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vente");

        $row = $r->fetch_assoc();

        return $row["mnt_dette"];
    }

    public function getEtatCaisseJour($search) {
        $comptant = $this->getComptantsJour($search);
        $provisions = $this->getProvisionsJour($search);
        $creances = $this->getReglementsClientsJour($search);
        $garantiesencaiss = $this->getGarantiesEncaissJour($search);
        $depenses = $this->getDepensesJour($search);
        $dettes = $this->getReglementsFournisseursJour($search);
        $remises = $this->getRemisesJour($search);
        $versements = $this->getVersementsJour($search);

        $response = array("comptant" => $comptant,
            "provision" => $provisions,
            "creance" => $creances,
            "grtencaiss" => $garantiesencaiss,
            "depense" => $depenses,
            "dette" => $dettes,
            "remise" => $remises,
            "versement" => $versements);

        return $response;
    }

    public function getEtatCaisseDate($search) {
        $comptant = $this->getComptantsDate($search);
        $provisions = $this->getProvisionsDate($search);
        $creances = $this->getReglementsClientsDate($search);
        $garantiesencaiss = $this->getGarantiesEncaissDate($search);
        $depenses = $this->getDepensesDate($search);
        $dettes = $this->getReglementsFournisseursDate($search);
        $remises = $this->getRemisesDate($search);
        $versements = $this->getVersementsDate($search);

        $response = array("comptant" => $comptant,
            "provision" => $provisions,
            "creance" => $creances,
            "grtencaiss" => $garantiesencaiss,
            "depense" => $depenses,
            "dette" => $dettes,
            "remise" => $remises,
            "versement" => $versements);

        return $response;
    }

    public function getRemisesJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND f.mag_fact=" . $search['magasin'];
        }

        $query = "SELECT sum(f.remise_vnt_fact) as remise
                           FROM 
                            t_facture_vente f   
                           WHERE  date(f.date_fact)=date(now()) AND f.sup_fact=0 " . BTQ_CAIS_REM . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " remise jour");


        $res = $r->fetch_assoc();
        return intval($res['remise']);
    }

    public function getReglementsClientsJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND cr.caissier_crce_clnt in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $query = "SELECT sum(cr.mnt_paye_crce_clnt) as creance
                           FROM 
                           t_creance_client cr
                           inner join t_facture_vente f ON cr.fact_crce_clnt=f.id_fact
                            WHERE f.bl_fact_grt=0 AND f.sup_fact=0 AND f.bl_fact_crdt=1 AND date(cr.date_crce_clnt)=date(now()) " . BTQ_CAIS_REGC . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " reglem jour");


        $res = $r->fetch_assoc();
        return intval($res['creance']);
    }

    public function getGarantiesEncaissJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND fv.caissier_fact in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $query = "SELECT sum(fv.crdt_fact) as grtencaiss
                           FROM t_facture_vente fv 
                           WHERE fv.bl_fact_grt=1 AND fv.bl_encaiss_grt=1 
                           AND fv.sup_fact=0 AND fv.bl_crdt_regle=1 
                           AND date(fv.date_encaiss_grt)=date(now()) " . BTQ_REGC . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " garencaiss jour");


        $res = $r->fetch_assoc();
        return intval($res['grtencaiss']);
    }

    public function getProvisionsJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND cais.user_cais in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $query = "SELECT IFNULL(sum(cais.mnt_cais),0) as provision
                           FROM 
                           t_caisse cais
                            WHERE date(cais.date_cais)=date(now()) " . BTQ_CAIS_PROV . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " provis jour");


        $res = $r->fetch_assoc();
        return intval($res['provision']);
    }

    public function getReglementsFournisseursJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND df.caissier_dette_frns in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $query = "SELECT sum(df.mnt_paye_dette_frns) as dette
                           FROM 
                           t_dette_fournisseur df
                            WHERE date(df.date_dette_frns)=date(now()) " . BTQ_CAIS_REGF . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " regfjou jour");

        $res = $r->fetch_assoc();
        return intval($res['dette']);
    }

    public function getVersementsJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND vrs.caissier_vrsmnt in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $query = "SELECT IFNULL(sum(vrs.mnt_vrsmnt),0) as versement
                           FROM 
                           t_versement vrs
                            WHERE date(vrs.date_vrsmnt)=date(now()) " . BTQ_CAIS_VERS . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vets jour");

        $res = $r->fetch_assoc();
        return intval($res['versement']);
    }

    public function getDepensesJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND d.user_dep in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $query = "SELECT IFNULL(sum(d.mnt_dep),0) as depense
                           FROM 
                           t_depense d
                            WHERE date(d.date_dep)=date(now()) " . BTQ_CAIS_DEP . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " dep jour");

        $res = $r->fetch_assoc();
        return intval($res['depense']);
    }

    public function getComptantsJour($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND f.mag_fact=" . $search['magasin'];
        }

        $query = "SELECT sum(v.mnt_theo_vnt) as comptant,sum(f.tva_fact+f.bic_fact) as taxe
                           FROM 
                           t_vente v
                           INNER JOIN t_facture_vente f ON v.facture_vnt=f.id_fact 
                           WHERE f.bl_fact_crdt=0 AND f.sup_fact=0
                           AND date(v.date_vnt)=date(now()) " . BTQ_CAIS_CPT . " $cond";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " compt jour");


        $res = $r->fetch_assoc();
        return doubleval($res['comptant'] + $res['taxe']);
    }

    /*     * ********************************** */
    /*     * ***********PAR DATE*************** */
    /*     * ********************************** */

    public function getRemisesDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND f.mag_fact=" . $search['magasin'];
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT sum(f.remise_vnt_fact) as remise
                           FROM 
                            t_facture_vente f   
                           WHERE  f.sup_fact=0 AND date(f.date_fact) $dateq " . BTQ_CAIS_REM . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " remise jour");


        $res = $r->fetch_assoc();
        return intval($res['remise']);
    }

    public function getReglementsClientsDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND cr.caissier_crce_clnt in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT sum(cr.mnt_paye_crce_clnt) as creance
                           FROM 
                           t_creance_client cr
                           inner join t_facture_vente f ON cr.fact_crce_clnt=f.id_fact
                            WHERE f.sup_fact=0 AND f.bl_fact_grt=0 AND f.bl_fact_crdt=1 AND date(cr.date_crce_clnt) $dateq " . BTQ_CAIS_REGC . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " reglem jour");


        $res = $r->fetch_assoc();
        return intval($res['creance']);
    }

    public function getGarantiesEncaissDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND fv.caissier_fact in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT sum(fv.crdt_fact) as grtencaiss
                           FROM t_facture_vente fv 
                           WHERE fv.bl_fact_grt=1 AND fv.bl_encaiss_grt=1 
                          AND fv.sup_fact=0 AND fv.bl_crdt_regle=1 
                           AND date(fv.date_encaiss_grt) $dateq " . BTQ_REGC . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " garencaiss jour");


        $res = $r->fetch_assoc();
        return intval($res['grtencaiss']);
    }

    public function getProvisionsDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND cais.user_cais in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT IFNULL(sum(cais.mnt_cais),0) as provision
                           FROM 
                           t_caisse cais
                            WHERE date(cais.date_cais) $dateq " . BTQ_CAIS_PROV . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " provis jour");


        $res = $r->fetch_assoc();
        return intval($res['provision']);
    }

    public function getReglementsFournisseursDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND df.caissier_dette_frns in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT sum(df.mnt_paye_dette_frns) as dette
                           FROM 
                           t_dette_fournisseur df
                            WHERE date(df.date_dette_frns) $dateq " . BTQ_CAIS_REGF . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " regfjou jour");

        $res = $r->fetch_assoc();
        return intval($res['dette']);
    }

    public function getVersementsDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND vrs.caissier_vrsmnt in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT IFNULL(sum(vrs.mnt_vrsmnt),0) as versement
                           FROM 
                           t_versement vrs
                            WHERE date(vrs.date_vrsmnt) $dateq " . BTQ_CAIS_VERS . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " vets jour");

        $res = $r->fetch_assoc();
        return intval($res['versement']);
    }

    public function getDepensesDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND d.user_dep in (SELECT id_user FROM t_user WHERE mag_user=" . $search['magasin'] . ")";
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT IFNULL(sum(d.mnt_dep),0) as depense
                           FROM 
                           t_depense d
                            WHERE date(d.date_dep) $dateq " . BTQ_CAIS_DEP . " $cond limit 1";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " dep jour");

        $res = $r->fetch_assoc();
        return intval($res['depense']);
    }

    public function getComptantsDate($search) {

        $cond = "";
        if (!empty($search['magasin'])) {
            $cond = " AND f.mag_fact=" . $search['magasin'];
        }

        $dateq = "";
        if (!empty($search['date_deb']) && empty($search['date_fin']))
            $dateq = "='" . isoToMysqldate($search['date_deb']) . "'";

        if (!empty($search['date_fin']))
            $dateq = " between '" . isoToMysqldate($search['date_deb']) . "' 
                AND '" . isoToMysqldate($search['date_fin']) . "'";

        $query = "SELECT sum(v.mnt_theo_vnt) as comptant,sum(f.tva_fact+f.bic_fact) as taxe
                           FROM 
                           t_vente v
                           INNER JOIN t_facture_vente f ON v.facture_vnt=f.id_fact 
                           WHERE f.bl_fact_crdt=0 AND f.sup_fact=0
                           AND date(v.date_vnt) $dateq " . BTQ_CAIS_CPT . " $cond";

        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__ . " compt jour");


        $res = $r->fetch_assoc();
        return doubleval($res['comptant'] + $res['taxe']);
    }

}

?>