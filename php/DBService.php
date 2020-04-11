<?php
class DBService
{
    private static $hDBlink = null;
    private static $iActiveSeasonId = null;
    private static $sActiveSeasonLabel = null;
    private static $sAARegistrationDate = null;
    private static $sPJRegistrationDate = null;
    private static $sMapURL = null;
    
    /**
     * Retourne l'identifiant de la saison active
     * @return int
     */
    public static function GetActiveSeasonId()
    {
        if (static::$iActiveSeasonId !== null) return static::$iActiveSeasonId;
        static::LoadActiveSeason();
        return static::$iActiveSeasonId;
    }
    
    /**
     * Retourne le label (p.ex. '2020') de la saison active  
     * @return string
     */
    public static function GetActiveSeasonLabel()
    {
        if (static::$sActiveSeasonLabel !== null) return static::$sActiveSeasonLabel;
        static::LoadActiveSeason();
        return static::$sActiveSeasonLabel;
    }
    
    /**
     * Retourne la date de début des inscriptions pour les accompagnateurs actifs
     * (Date au format MySQL = yyyy-mm-dd hh:ii:ss)
     * @return string
     */
    public static function GetAARegistrationDate()
    {
        if (static::$sAARegistrationDate !== null) return static::$sAARegistrationDate;
        static::LoadActiveSeason();
        return static::$sAARegistrationDate;
    }
    
    /**
     * Retourne la date de début des inscriptions pour les passagers joëlette
     * (Date au format MySQL = yyyy-mm-dd hh:ii:ss)
     * @return string
     */
    public static function GetPJRegistrationDate()
    {
        if (static::$sPJRegistrationDate !== null) return static::$sPJRegistrationDate;
        static::LoadActiveSeason();
        return static::$sPJRegistrationDate;
    }
    
    /**
     * Retourne l'identifiant de la saison active
     * @return int
     */
    public static function GetMapURL()
    {
        if (static::$sMapURL !== null) return static::$sMapURL;
        static::LoadActiveSeason();
        return static::$sMapURL;
    }
    
    /**
     * Charge les données (id, label, date d'ouverture des inscriptions...) pour la saison
     * active
     */
    private static function LoadActiveSeason()
    {
        $sPrefix = ITOP_DB_TABLES_PREFIX;
        $sSQL =
<<<SQL
SELECT id, nom, date_inscription_pj, date_inscription_acc, carte_sejours
FROM {$sPrefix}saisons
WHERE date_publication <= NOW()
ORDER BY date_publication DESC
LIMIT 0,1
SQL;
        $aSeason = static::QueryFirstRow($sSQL);
        static::$iActiveSeasonId = $aSeason['id'];
        static::$sActiveSeasonLabel = $aSeason['nom'];
        static::$sPJRegistrationDate = $aSeason['date_inscription_pj'];
        static::$sAARegistrationDate = $aSeason['date_inscription_acc'];
        static::$sMapURL = $aSeason['carte_sejours'];
    }
    
    /**
     * Retourne les informations sur les séjours de la saison active
     * @return string[]
     */
    public static function GetActiveSejours()
    {
        $sPrefix = ITOP_DB_TABLES_PREFIX;
        $idSeason = static::GetActiveSeasonId();
        $sSQL =
<<<SQL
SELECT Sj.id, Sj.nom, Sj.date_debut, Sj.date_fin, Sj.url_fiche_technique, Sa.nom AS annee, 'etoile' as type, 'B' as tarif
FROM {$sPrefix}sejours AS Sj
JOIN {$sPrefix}saisons AS Sa ON Sj.saison_id=Sa.id
WHERE Sa.id={$idSeason}
ORDER BY date_debut ASC, nom ASC
SQL;
        $aSejours = static::Query($sSQL);
        
        // Now get the availables seats
        $sSQL =
<<<SQL
SELECT `date`, json
FROM {$sPrefix}places_disponibles
WHERE saison_id={$idSeason}
ORDER BY `date` DESC
LIMIT 0,1
SQL;
        $aRow = static::QueryFirstRow($sSQL);
        $aData = json_decode($aRow['json'], true);
        
        $sToday = date('Y-m-d');
        foreach($aSejours as $idx => $aSejour)
        {
            $key = (int)$aSejour['id'];
            if (strcmp($aSejour['date_debut'], $sToday) < 0)
            {
                // In the past, no free seats
                $aSejours[$idx]['places_pj'] = '-';
                $aSejours[$idx]['places_aa'] = '-';
                $aSejours[$idx]['places_hm'] = '-';
            }
            else if(array_key_exists($key, $aData))
            {
                $aSejours[$idx]['places_pj'] = $aData[$key]['places_pj'];
                $aSejours[$idx]['places_aa'] = $aData[$key]['places_aa'];
                $aSejours[$idx]['places_hm'] = $aData[$key]['places_hm'];
            }
            else
            {
                $aSejours[$idx]['places_pj'] = '-';
                $aSejours[$idx]['places_aa'] = '-';
                $aSejours[$idx]['places_hm'] = '-';
            }
        }
        return $aSejours;
    }

    /**
     * Renvoie la date de dernière mise à jour des places disponibles (pour la saison active)
     * @throws Exception
     * @return string La date au format MySQL
     */
    public static function GetLatestOpenSeatsUpdate()
    {
        $sPrefix = ITOP_DB_TABLES_PREFIX;
        $idSeason = static::GetActiveSeasonId();
        
        // Now get the availables seats
        $sSQL =
<<<SQL
SELECT `date`
FROM {$sPrefix}places_disponibles
WHERE saison_id={$idSeason}
ORDER BY `date` DESC
LIMIT 0,1
SQL;
        $aRow = static::QueryFirstRow($sSQL);
        return $aRow['date'];
    }
    
    /**
     * Execute une requête SQL sur la base iTop
     * @param string $sSQL
     * @throws Exception
     * @return string[][]
     */
    private static function Query($sSQL)
    {
        $aData = array();
        static::Connect();
        $hRes = mysqli_query(static::$hDBlink, $sSQL);
        if ($hRes === false)
        {
            throw new Exception('MySQL Error: '.mysqli_error(static::$hDBlink));
        }
        while($aRow = mysqli_fetch_assoc($hRes))
        {
            $aData[] = $aRow;
        }
        return $aData;
    }
    
    /**
     * Execute une requête SQL sur la base iTop et ne retourne QUE la première ligne
     * @param string $sSQL
     * @throws Exception
     * @return string[]
     */
    private static function QueryFirstRow($sSQL)
    {
        static::Connect();
        $hRes = mysqli_query(static::$hDBlink, $sSQL);
        if ($hRes === false)
        {
            throw new Exception('MySQL Error: '.mysqli_error(static::$hDBlink));
        }
        $aRow = mysqli_fetch_assoc($hRes);
        return $aRow;
    }
    
    /**
     * Etablit (si pas déjà ouverte) la connexion à la base iTop
     * @throws Exception
     */
    private static function Connect()
    {
        if (static::$hDBlink !== null) return; // Already connected
        static::$hDBlink = mysqli_connect(ITOP_DB_HOST, ITOP_DB_USER, ITOP_DB_PWD, ITOP_DB_NAME);
        if (static::$hDBlink === false)
        {
            throw new Exception("Unable to connect to MySQL, ".mysqli_connect_error());
        }
        mysqli_set_charset(static::$hDBlink, 'utf8');
        
    }
    
    /**
     * Les icônes associées aux types de séjours
     * @return string[][]
     */
    public static function GetIcons()
    {
        $aIcons = array(
            'en_dur' => array(
                'title' => 'Hébergement en dur',
                'description' => 'Hébergement en dur (gîte ou refuge). Un bivouac est possible selon les conditions météo.',
            ),
            'etoile' => array(
                'title' => 'Séjour en étoile',
                'description' => 'Randonnées en étoile. Retour au même campement chaque soir (sauf pour le bivouac).',
            ),
            'campements' => array(
                'title' => 'Itinérance avec plusieurs camps de base',
                'description' => 'Séjour itinérant, avec plusieurs camps de base.',
            ),
            'itinerant' => array(
                'title' => 'Séjour itinérant',
                'description' => 'Séjour itinérant: un lieu de campement différent chaque soir.',
            ),
        );
        
        return $aIcons;
    }
    
    /**
     * Transforme un mois numérique 2 chiffres ('01', '02'...) en mois en français ('janvier', 'février'...)
     * @param string $m
     * @return string
     */
    public static function MonthInFrench($m)
    {
        $aMois["01"] = "janvier";
        $aMois["02"] = "f&eacute;vrier";
        $aMois["03"] = "mars";
        $aMois["04"] = "avril";
        $aMois["05"] = "mai";
        $aMois["06"] = "juin";
        $aMois["07"] = "juillet";
        $aMois["08"] = "ao&ucirc;t";
        $aMois["09"] = "septembre";
        $aMois["10"] = "octobre";
        $aMois["11"] = "novembre";
        $aMois["12"] = "d&eacute;cembre";
        
        return $aMois[$m];
    }
    
    /**
     * Formatte une date/heure (au format MySQL) en français
     * @param string $date Date au format MySQL (Y-m-d h:i:s)
     * @return string
     */
    public static function FormatDateTime($date)
    {
        $aDate = array();
        preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):/", $date, $aDate);
        $sYear = $aDate[1];
        $sMonth = $aDate[2];
        $sDay = $aDate[3];
        $sHour = $aDate[4];
        $sMinutes = $aDate[5];
        
        if ($sDay == '01')
        {
            $sDay = "1<sup>er</sup>";
        }
        return "$sDay ".static::MonthInFrench($sMonth)." $sYear à {$sHour}h $sMinutes";
    }
    
    /**
     * Formatte une date (au format MySQL) en français
     * @param string $date Date au format MySQL (Y-m-d h:i:s)
     * @return string
     */
    public static function FormatDate($date)
    {
        $aDate = array();
        preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $date, $aDate);
        $sYear = $aDate[1];
        $sMonth = $aDate[2];
        $sDay = $aDate[3];
        
        if ($sDay == '01')
        {
            $sDay = "1<sup>er</sup>";
        }
        return "$sDay ".static::MonthInFrench($sMonth)." $sYear";
    }
    
    /**
     * Formatte un intervalle en français (ex. 1er au 30 avril 2020)
     * @param string $sStartDate Date de début au format MySQL
     * @param string $sEndDate Date de fin au format MySQL
     * @return string
     */
    public static function FormatInterval($sStartDate, $sEndDate)
    {
        $aStart = array();
        $aEnd = array();
        preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $sStartDate, $aStart);
        preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $sEndDate, $aEnd);
        
        $sMonth1 = '';
        $sYear1 = '';
        $sYear2 = $aEnd[1];
        if ($aStart[1] != $aEnd[1])
        {
            // Start and end not in the same year
            $sYear1 = ' '.$aStart[1];
        }
        
        $sMonth2 = static::MonthInFrench($aEnd[2]);
        if ($aStart[2] != $aEnd[2])
        {
            // Start and end not the same month
            $sMonth1 = ' '.static::MonthInFrench($aStart[2]);
        }
        
        $sDay1 =  $aStart[3] == '01' ? "1<sup>er</sup>" : $aStart[3];
        $sDay2 =  $aEnd[3] == '01' ? "1<sup>er</sup>" : $aEnd[3];
        
        return "du $sDay1{$sMonth1}{$sYear1} au $sDay2 $sMonth2 $sYear2";
    }
    
}