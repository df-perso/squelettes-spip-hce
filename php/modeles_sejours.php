<?php
require_once(__DIR__.'/DBService.php');

function _H($s)
{
    return htmlentities($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Modèle <tableau_des_sejours_legende|>
 */
function TableauDesSejoursLegende()
{
    $aIcons = DBService::GetIcons();
    echo
<<<HTML
<table class="table table-striped table-bordered table-sm" id="tableau-sejours-legende">
<tbody>
HTML;
    
    foreach($aIcons as $sCode => $aIcon)
    {
        echo
<<<HTML
<tr>
   <td class="text-center align-middle">
        <img alt="{$aIcon['title']}" src="./squelettes/images/$sCode.png">
    </td>
    <td>
		{$aIcon['description']}
    </td>
</tr>
HTML;
        }
        echo
<<<HTML
</tbody>
</table>
HTML;
}

/**
 * Modèle <tableau_des_sejours|>
 */
function TableauDesSejours()
{
    try
    {
        $aSejours = DBService::GetActiveSejours();
        $aIcons = DBService::GetIcons();
        echo
<<<HTML
<table class="table table-striped table-hover" id="tableau-sejours">
<thead class="thead-dark">
<tr>
    <th title="Type et nom du séjour" colspan="2">Nom du séjour</th>
    <th title="Places restantes pour les passagers joëlette" class="text-center">Joël.</th>
    <th title="Places restantes pour les accompagnateurs actifs" class="text-center">Acc.</th>
    <th title="Places restante pour les handicapés marchants" class="text-center">H.M.</th>
    <th title="Dates du séjour">Dates</th>
    <th title="Tarif du séjour" class="text-center">Tarif</th>
</tr>
<thead>
<tbody>
HTML;
        
        foreach($aSejours as $aSejour)
        {
            $tarif = 'A';
            $dates = DBService::FormatInterval($aSejour['date_debut'], $aSejour['date_fin']);
            
            $sIcontAlt = _H($aIcons[$aSejour['type']]['title']);
            $sIconTitle = _H($aIcons[$aSejour['type']]['description']);
            
            echo
<<<HTML
<tr>
    <td class="text-center align-center"><img alt="$sIcontAlt" title="$sIconTitle" src="./squelettes/images/{$aSejour['type']}.png"></td>
    <td><a title="Cliquez pour télécharger la fiche technique" href="{$aSejour['url_fiche_technique']}" target="_blank">{$aSejour['nom']}</a></td>
    <td class="text-center">{$aSejour['places_pj']}</td>
    <td class="text-center">{$aSejour['places_aa']}</td>
    <td class="text-center">{$aSejour['places_hm']}</td>
    <td>$dates</td>
    <td class="text-center">{$aSejour['tarif']}</td>
</tr>
HTML;
        }
        echo
<<<HTML
</tbody>
</table>
HTML;
    }
    catch (Exception $e)
    {
        $sMessage = htmlentities($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo
<<<HTML
<div class="alert alert-danger" role="alert">
<h4 class="alert-heading">Oups!</h4>
<p>Aïe, aïe, aïe, pas de chance une erreur s'est produite lors de la construction de cette page.</p>
<hr>
<p>$sMessage</p>
</div>
HTML;
    }
}

/**
 * Modèle <tableau_des_sejours_mise_a_jour|>
 */
function TableauDesSejoursMiseAJour()
{
    try
    {
        $sDate = DBService::GetLatestOpenSeatsUpdate();
        echo DBService::FormatDateTime($sDate);
    }
    catch (Exception $e)
    {
        echo '<span class="badge badge-danger" title="'._H($e->getMessage()).'">Erreur</span>';
    }
}

/**
 * Modèle <ouverture_des_inscriptions|>
 */
function OuvertureDesInscription()
{
    try
    {
        $sNow = date('Y-m-d');
        $sAARegistrationDate = DBService::GetAARegistrationDate();
        $sPJRegistrationDate = DBService::GetPJRegistrationDate();
        if (($sAARegistrationDate > $sNow) || ($sPJRegistrationDate > $sNow))
        {
            echo
<<<HTML
<div class="alert alert-primary" role="alert">
    <h4 class="alert-heading text-left">Ouverture des inscriptions aux séjours</h4>
    <hr>
HTML;
            if ($sPJRegistrationDate > $sNow)
            {
                $sNiceDate = DBService::FormatDate($sPJRegistrationDate);
                echo "<p><strong>Pour les passagers joëlette</strong> et <strong>pour les accompagnateurs actifs des séjours à l'étranger</strong> les inscriptions seront traitées à partir du $sNiceDate (mais vous pouvez envoyer votre inscription un peu avant).</p>";
            }
            if ($sAARegistrationDate > $sNow)
            {
                $sNiceDate = DBService::FormatDate($sAARegistrationDate);
                echo "<p><strong>Pour les accompagnateurs actifs</strong> des autres séjours les inscriptions seront traitées à partir du $sNiceDate (mais vous pouvez envoyer votre inscription un peu avant).</p>";
            }
            echo
<<<HTML
</div>
HTML;
        }
    }
    catch (Exception $e)
    {
        $sMessage = htmlentities($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo
<<<HTML
<div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">Oups!</h4>
    <p>Aïe, aïe, aïe, pas de chance une erreur s'est produite lors de la construction de cette page.</p>
    <hr>
    <p>$sMessage</p>
</div>
HTML;
    }
}

function CarteDesSejours()
{
    $sMapURL = DBService::GetMapURL();
    if ($sMapURL != '')
    {
        echo
<<<HTML
<div class="embed-responsive embed-responsive-16by9">
    <iframe src="$sMapURL"></iframe>
</div>
HTML;
    }
}
