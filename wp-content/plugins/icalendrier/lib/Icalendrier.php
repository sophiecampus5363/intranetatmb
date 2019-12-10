<?php
/*  Copyright 2014  Baptiste Placé

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/**
 * Class: iCalendrier
 * Description: Un simple calendrier qui affiche des infos du jour, comme le numéro de semaine, la date, la fête du jour et la phase de lune.
 * Version: 1.0
 * Author: Baptiste Placé
 * Author URI: http://icalendrier.fr/
 * License: GNU General Public License, version 2
 */

require_once dirname(__FILE__) . "/FeteDuJour.php";


class Icalendrier
{

    protected $nameDay;

    protected $lang;

    protected $langs = array(
        "fr",
        "en",
        "es",
        "pt",
        "it",
        "de",
        "pt-BR"
    );

    protected $siteUrls = array(
        'fr'    => "http://icalendrier.fr/",
        'en'    => "http://icalendars.net/",
        'es'    => "http://icalendario.net/",
        'pt'    => "http://icalendario.pt/",
        'pt-BR' => "http://icalendario.br.com/",
        'it'    => "http://icalendario.it/",
        'de'    => "http://ikalender.org/",
    );

    protected $siteLabels = array(
        'fr'    => "iCalendrier.fr",
        'en'    => "iCalendars.net",
        'es'    => "iCalendario.net",
        'pt'    => "iCalendario.pt",
        'pt-BR' => "iCalendario.br.com",
        'it'    => "iCalendario.it",
        'de'    => "iKalender.org",
    );

    protected $todayLabels = array(
        'fr'    => "Aujourd'hui",
        'en'    => "Today",
        'es'    => "Hoy en día",
        'pt'    => "Hoje",
        'pt-BR' => "Hoje",
        'it'    => "Oggi",
        'de'    => "Heute",
    );

    protected $localDays = array(
        'fr'    => array(
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi',
            'Dimanche',
        ),
        'en'    => array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ),
        'es'    => array(
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado',
            'Domingo',
        ),
        'pt'    => array(
            'Segunda-feira',
            'Terca-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado',
            'Domingo',
        ),
        'pt-BR' => array(
            'Segunda-feira',
            'Terca-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado',
            'Domingo',
        ),
        'it'    => array(
            'Lunedí',
            'Martedí',
            'Mercoledí',
            'Geovedí',
            'Venerdí',
            'Sabato',
            'Domenica',
        ),
        'de'    => array(
            'Montag',
            'Dienstag',
            'Mittwoch',
            'Donnerstag',
            'Freitag',
            'Samstag',
            'Sonntag',
        ),
    );

    protected $shortLocalDays = array(
        'fr'    => array(
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi',
            'Dimanche',
        ),
        'en'    => array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ),
        'es'    => array(
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado',
            'Domingo',
        ),
        'pt'    => array(
            'Seg.-feira',
            'Terca-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado',
            'Domingo',
        ),
        'pt-BR' => array(
            'Seg.-feira',
            'Terca-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado',
            'Domingo',
        ),
        'it'    => array(
            'Lunedí',
            'Martedí',
            'Mercoledí',
            'Geovedí',
            'Venerdí',
            'Sabato',
            'Domenica',
        ),
        'de'    => array(
            'Montag',
            'Dienstag',
            'Mittwoch',
            'Donnerstag',
            'Freitag',
            'Samstag',
            'Sonntag',
        ),
    );

    protected $weekLabels = array(
        'fr'    => "Semaine",
        'en'    => "Week",
        'es'    => "Semana",
        'pt'    => "Semana",
        'pt-BR' => "Semana",
        'it'    => "Settimana",
        'de'    => "Woche",
    );

    protected $shortWeekLabels = array(
        'fr'    => "Sem.",
        'en'    => "Week",
        'es'    => "Sem.",
        'pt'    => "Sem.",
        'pt-BR' => "Sem.",
        'it'    => "Sett.",
        'de'    => "Wo.",
    );

    protected $shortMonthLabels = array(
        'fr'    => array(
            'Jan.',
            'Fév.',
            'Mars',
            'Avr.',
            'Mai',
            'Juin',
            'Juil.',
            'Août',
            'Sep.',
            'Oct.',
            'Nov.',
            'Déc.'
        ),
        'en'    => array(
            'Jan.',
            'Feb.',
            'Mar.',
            'Apr.',
            'May',
            'June',
            'July',
            'Aug.',
            'Sep.',
            'Oct.',
            'Nov.',
            'Dec.'
        ),
        'es'    => array(
            'Ene.',
            'Feb.',
            'Mar.',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Ago.',
            'Sep.',
            'Oct.',
            'Nov.',
            'Dic.'
        ),
        'pt'    => array(
            'Jan.',
            'Fev.',
            'Mar.',
            'Abr.',
            'Maio',
            'Jun.',
            'Jul.',
            'Ago.',
            'Set.',
            'Out.',
            'Nov.',
            'Dez.'
        ),
        'pt-BR' => array(
            'Jan.',
            'Fev.',
            'Mar.',
            'Abr.',
            'Maio',
            'Jun.',
            'Jul.',
            'Ago.',
            'Set.',
            'Out.',
            'Nov.',
            'Dez.'
        ),
        'it'    => array(
            'Gen.',
            'Feb.',
            'Mar.',
            'Apr.',
            'Mag.',
            'Giu.',
            'Lug.',
            'Ago.',
            'Set.',
            'Ott.',
            'Nov.',
            'Dic.'
        ),
        'de'    => array(
            'Jan.',
            'Feb.',
            'März',
            'Apr.',
            'Mai',
            'Juni',
            'Juli',
            'Aug.',
            'Sep.',
            'Okt.',
            'Nov.',
            'Dez.'
        ),
    );

    protected $monthLabels = array(
        'fr'    => array(
            'Janvier',
            'Février',
            'Mars',
            'Avril',
            'Mai',
            'Juin',
            'Juillet',
            'Août',
            'Septembre',
            'Octobre',
            'Novembre',
            'Décembre'
        ),
        'en'    => array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ),
        'es'    => array(
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ),
        'pt'    => array(
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        ),
        'pt-BR' => array(
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        ),
        'it'    => array(
            'Gennaio',
            'Febbraio',
            'Marzo',
            'Aprile',
            'Maggio',
            'Giugno',
            'Luglio',
            'Agosto',
            'Settembre',
            'Ottobre',
            'Novembre',
            'Dicembre'
        ),
        'de'    => array(
            'Januar',
            'Februar',
            'März',
            'April',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember'
        ),
    );

    protected $localMoonPhases = array(
        'fr'    => array(
            'New Moon'        => 'Nouvelle lune',
            'Waxing Crescent' => 'Premier croissant',
            'First Quarter'   => 'Premier quartier',
            'Waxing Gibbous'  => 'Gibbeuse croissante',
            'Full Moon'       => 'Pleine lune',
            'Waning Gibbous'  => 'Gibbeuse décroissante',
            'Third Quarter'   => 'Dernier quartier',
            'Waning Crescent' => 'Dernier croissant',
        ),
        'en'    => array(
            'New Moon'        => 'New Moon',
            'Waxing Crescent' => 'Waxing Crescent',
            'First Quarter'   => 'First Quarter',
            'Waxing Gibbous'  => 'Waxing Gibbous',
            'Full Moon'       => 'Full Moon',
            'Waning Gibbous'  => 'Waning Gibbous',
            'Third Quarter'   => 'Third Quarter',
            'Waning Crescent' => 'Waning Crescent',
        ),
        'es'    => array(
            'New Moon'        => 'Luna Nueva',
            'Waxing Crescent' => 'Luna Nueva Visible',
            'First Quarter'   => 'Cuarto Creciente',
            'Waxing Gibbous'  => 'Luna Gibosa Creciente',
            'Full Moon'       => 'Luna Llena',
            'Waning Gibbous'  => 'Luna Gibosa Menguante',
            'Third Quarter'   => 'Cuarto Menguante',
            'Waning Crescent' => 'Luna Menguante',
        ),
        'pt'    => array(
            'New Moon'        => 'Lua nova',
            'Waxing Crescent' => 'Lua crescente',
            'First Quarter'   => 'Quarto Crescente',
            'Waxing Gibbous'  => 'Lua crescente convexa',
            'Full Moon'       => 'Lua Cheia',
            'Waning Gibbous'  => 'Lua minguante convexa',
            'Third Quarter'   => 'Quarto Minguante',
            'Waning Crescent' => 'Lua minguante',
        ),
        'pt-BR' => array(
            'New Moon'        => 'Lua nova',
            'Waxing Crescent' => 'Lua crescente',
            'First Quarter'   => 'Quarto Crescente',
            'Waxing Gibbous'  => 'Lua crescente convexa',
            'Full Moon'       => 'Lua Cheia',
            'Waning Gibbous'  => 'Lua minguante convexa',
            'Third Quarter'   => 'Quarto Minguante',
            'Waning Crescent' => 'Lua minguante',
        ),
        'it'    => array(
            'New Moon'        => 'Luna nuova',
            'Waxing Crescent' => 'Luna crescente',
            'First Quarter'   => 'Primo quarto',
            'Waxing Gibbous'  => 'Gibbosa crescente',
            'Full Moon'       => 'Luna piena',
            'Waning Gibbous'  => 'Gibbosa calante',
            'Third Quarter'   => 'Ultimo quarto',
            'Waning Crescent' => 'Luna calante',
        ),
        'de'    => array(
            'New Moon'        => 'Neumond',
            'Waxing Crescent' => 'Erstes Viertel',
            'First Quarter'   => 'Zunehmender Halbmond',
            'Waxing Gibbous'  => 'Zweites Viertel',
            'Full Moon'       => 'Vollmond',
            'Waning Gibbous'  => 'Drittes Viertel',
            'Third Quarter'   => 'Abnehmender Halbmond',
            'Waning Crescent' => 'Letztes Viertel',
        ),
    );


    public function __construct($lang = 'en', $timezone = 0)
    {
        if ( ! in_array($lang, $this->langs)) {
            $this->lang = "en"; // Default En
        } else {
            $this->lang = $lang;
        }

        if (('0' != $timezone) and (function_exists('date_default_timezone_set'))) {
            date_default_timezone_set($timezone);
        }

        $this->nameDay = new NameDay();
    }


    public function iCalendrierComp($showLink = false, $bgColor = false)
    {


        $html = '';

        $html .= '<div class="icalComp">';
        $html .= '<div class="ccomp">';
        if ($bgColor) {
            $html .= '    <div class="cheight" style="background:' . $bgColor . ' !important;">';
        } else {
            $html .= '    <div class="cheight">';
        }

        $html .= '        <div class="ctitle">';
        if ($showLink) {
            $html .= '            <a href="' . $this->siteUrls[$this->lang] . '">' . $this->todayLabels[$this->lang] . '</a>';
        } else {
            $html .= '<a href="javascript:void(0)">' . $this->todayLabels[$this->lang] . '</a>';
        }
        $html .= '        </div>';

        $html .= '        <div class="cephem">';
        $html .= '            <div class="today">';
        $html .= '                <span class="daysem">';


        $html .= $this->shortLocalDays[$this->lang][date('N') - 1];

        $html .= " - ";

        $html .= $this->shortWeekLabels[$this->lang] . " " . ltrim(date('W'), '0');

        $html .= '                </span>';

        $day     = date('d');
        $daydig1 = substr($day, 0, 1);
        $daydig2 = substr($day, 1, 1);

        $html .= '                <span class="day">';
        $html .= '                    <span class="daydig1">';
        $html .= $daydig1;
        $html .= '                   </span>';
        $html .= '                    <span class="daydig1">';
        $html .= $daydig2;
        $html .= '                    </span>';
        $html .= '                </span>';


        $html .= '                <span class="month">';

        $html .= $this->shortMonthLabels[$this->lang][date('n') - 1];

        $html .= '                </span>';
        $html .= '                <span class="fete">';

        $html .= $this->nameDay->today($this->lang);

        $html .= '                </span>';
        $html .= '                <span class="moon">';

        $html .= $this->getMoonSide($this->lang);

        $html .= '                </span>';
        $html .= '            </div>';
        $html .= '        </div>';
        $html .= '    </div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Wide calendar
     */
    public function iCalendrierWide($showLink = false, $bgColor = false)
    {
        $html = "";

        if ($bgColor) {
            $html .= '<div class="icalWide" style="background:' . $bgColor . ' !important;">';
        } else {
            $html .= '<div class="icalWide">';
        }

        $html .= '<div class="today">';

        $html .= '<span class="ctitle">';
        if ($showLink) {
            $html .= '<a href="' . $this->siteUrls[$this->lang] . '">' . $this->todayLabels[$this->lang] . '</a>';
        } else {
            $html .= '<a href="javascript:void(0)">' . $this->todayLabels[$this->lang] . '</a>';
        }
        $html .= '</span>';


        $html .= '<span class="day">';

        $html .= $this->shortLocalDays[$this->lang][date('N') - 1];

        $html .= '</span>';

        $html .= '<span class="num">' . date('d') . '</span>';

        $html .= '<span class="month">';

        $html .= $this->monthLabels[$this->lang][date('n') - 1];
        $html .= '</span>';

        $html .= '<span class="more">';
        $html .= $this->weekLabels[$this->lang] . " " . ltrim(date('W'), '0');

        $html .= ' | ';

        $html .= $this->nameDay->today($this->lang);
        $html .= '</span>';

        $html .= '<span class="moon">';

        $html .= $this->getMoonSide($this->lang);

        $html .= '</span>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Select the moon character
     * @return string
     */
    protected function getMoonSide()
    {
        require_once(dirname(__FILE__) . '/Solaris/MoonPhase.php');

        $now = new DateTime();

        if (intval($now->format("G")) >= 7) {
            // 7 heure du matin ou +, on prend la prochaine nuit à minuit
            $now->setTime(0, 0); // Minuit
            $now->add(new DateInterval("P1D"));
            $moon = new Solaris_MoonPhase($now->format("U"));
        } else {
            // avant 7 heures du matin, on prend la nuit en cours ou presque finie
            $now->setTime(0, 0); // Minuit
            $moon = new Solaris_MoonPhase($now->format("U"));
        }

        $phase = $moon->phase();

        // Utilisation de la font

        // 29 "phases" pour correspondre à la font
        $moonCharacters = array(0, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', '@', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 0);

        $totalCharacters = count($moonCharacters);
        $step            = 1 / ($totalCharacters - 1);

        if ($phase < $step / 2) {
            $index = 0;
        } elseif ($phase > (1 - $step / 2)) {
            $index = 28;
        } else {
            for ($i = 1; $i <= 27; $i++) {
                // Passage dans l'interval qui entoure l'illustration
                if ($phase >= ($step / 2 + ($i - 1) * $step) && $phase < ($step / 2 + $i * $step)) {
                    $index = $i;
                }
            }
        }

        $html = '<span class="phase">' . $moonCharacters[$index] . '</span>';
        $html .= $this->localMoonPhases[$this->lang][$moon->phase_name()];

        return $html;
    }

}


