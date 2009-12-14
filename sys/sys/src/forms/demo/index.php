<?php
/**
* Demonstracni kod na predstaveni formularovaeho frameworku.
*
* Pro demonstraci je zvolen formular na registraci .cz nebo
* ENUM domeny. Trida CzDomainRegistrationForm definuje formular,
* na konci stranky je pak nastinen zpusob jeho obsluhy.
*/

require_once('../load.inc');

/**
* Definice formulare pro registraci ceskych a ENUM domen.
*/
class CzDomainRegistrationForm extends Form
{
    /**
    * Definice formularovych poli.
    *
    * Poznamka: Definice poli je v tomto pripade pomerne dlouha, 
    * protoze vyuzivame pouze zakladnich typu poli (napr. obecny CharField
    * nebo specializovnejsi RegexField). V budoucnu ale bude vznikat
    * mnozina konkretnich poli pro potreby DomainMasteru, napr.
    * CzDomainField, EuContactField, IdaccField, apod. Pak uz nebude
    * treba u kazdeho pole definovat casto se opakujici parametry,
    * jako regexp, min/max velikost hodnoty, ale taky label, popr. help.
    */
    function set_up()
    {
        $this->add_field('domain_name', new CharField(array(
            'label' => 'Jméno domény',
            'help_text' => "Zadejte jméno cz (např. domainmaster.cz) nebo 
                            ENUM (9.8.7.6.5.4.3.2.1.0.2.4.e164.arpa) domény.",
            'min_length' => 1, 
            'max_length' => 64
        )));
        $this->add_field('period', new IntegerField(array(
            'label' => 'Délka registrace',
            'min_value' => 1, 
            'max_value' => 10
        )));
        $this->add_field('nsset', new RegexField(
            "/^NSSID:[.a-zA-Z0-9_-]{1,34}$/", 
            array(
                'required' => false,
                'label' => 'ID sady nameserverů (nsset)',
                'help_text' => "Pro samotnou registraci .cz domény není nutné 
                                nameservery uvádět. Nicméně, pokud chcete své 
                                doménové jméno skutečně využívat, budete si muset 
                                sadu nameserverů zaregistrovat, a do pole uvést
                                její ID.",
                'min_length' => 1, 
                'max_length' => 34,
                'error_message' => "Špatný formát ID NSSetu. Správná hodnota by 
                                    měla vypadat např. takto: NSSID:PLOVARNA.CZ"
            )
        ));
        $this->add_field('owner', new RegexField(
            "/^[-A-Za-z0-9_.:]{3,63}$/", 
            array(
                'label' => 'Vlastník',
                'help_text' => "ID osoby, na kterou bude doména zaregistrována 
                                (např. PURBAN).",
                'min_length' => 3, 
                'max_length' => 63,
                'error_message' => "Špatný formát ID kontaktu. Správná hodnota 
                                    by měla vypadat např. takto: PURBAN"
            )
        ));
        $this->add_field('admins', new CharField(array(
            'required' => false,
            'label' => 'Seznam administrátorů',
            'help_text' => "Administrátoři mají nad doménou stejnou moc, jako 
                            její vlastník. S jedinou vyjímkou -- administrátoři 
                            nemohou změnit vlastníka domény. Maximální počet 
                            administrativních kontaktů je 10.",
            'min_length' => 3, 
            'max_length' => 1000,
            'widget' => new Textarea(array('attrs'=>array('rows'=>5,'cols'=>40)))
        )));
        $this->add_field('idacc', new RegexField(
            "/^GR:[.a-zA-Z0-9_-]{1,64}$/", 
            array(
                'label' => 'ID Plátce (idacc)',
                'help_text' => 'Např. GR:DANA-RUZICKOVA',
                'min_length' => 1, 
                'max_length' => 64,
                'initial' => 'GR:',
                'error_message' => "Špatný formát ID plátce. Správná hodnota by 
                                    měla vypadat např. takto: GR:VALOUSEK-MICHAL"
            ))
        );
        $this->add_field('iddealer', new RegexField(
            "/^GR:[.a-zA-Z0-9_-]{1,64}$/", 
            array(
                'required' => false,
                'label' => 'ID Partnera (iddealer)',
                'help_text' => 'Např. GR:GOLDFLAM',
                'min_length' => 1, 
                'max_length' => 64,
                'error_message' => "Špatný formát ID plátce. Správná hodnota by 
                                    měla vypadat např. takto: GR:VALOUSEK-MICHAL"
            )
        ));
        $this->add_field('info_email', new EmailField(array(
            'label' => 'Informační email',
            'help_text' => "Na adresu informačního emailu obdržíte informace o 
                            průběhu zpracování vašeho požadavku.",
            'min_length' => 1, 
            'max_length' => 128
        )));
    }
        
    /**
    * Overeni formatu domenoveho jmena.
    *
    * Poznamka: zde se provadi pouze jednoducha kontrola formatu domeny.
    * V budoucnu vznikne sada specializovanych poli, napr. CzDomainField,
    * ktera podobnou kontrolu uz provede sama a zadanou hodnotu pole 
    * navic znormalizuje (napr. uzivatel zada www.DoMENa.Cz, vystupem 
    * bude "domena.cz").
    */
    function clean_domain_name()
    {
        // kontrola CZ
        if (isset($this->cleaned_data['domain_name'])) {
            $domain = $this->cleaned_data['domain_name'];
            $regPatt1 = '/^[a-z0-9-]{1,}\.cz$/';
            $regPatt2 = '/-\.cz$/';
            $regPatt3 = '/--/';
            if (preg_match($regPatt1, $domain, $parts) && 
                (!(preg_match($regPatt2, $domain, $parts))) && 
                (!(preg_match($regPatt3, $domain, $parts)))
                && ($domain[0] != '-')) {

                return array(null, $this->cleaned_data['domain_name']);
            }
        }
        // kontrola ENUM
        $regPatt = "/^([0-9]{1}\.){1,9}0\.2\.4\.e164\.arpa$/";
        if (preg_match($regPatt, $domain)) {
            return array(null, $this->cleaned_data['domain_name']);
        }
        return array(
            "Špatný formát doménového jména. Správná hodnota je 
             např. domainmaster.cz nebo 9.8.7.6.5.4.3.2.1.0.2.4.e164.arpa", 
             null
        );
    }

    /**
    * Metoda pro kontrolu pole admins.
    */
    function clean_admins()
    {
        if (isset($this->cleaned_data['admins'])) {
            $regPatt = "/^[-A-Za-z0-9_.:]{3,63}$/";
            $admins = explode("\n", trim($this->cleaned_data['admins']));
            if ((count($admins) > 0) && (strlen(trim($this->cleaned_data['admins'])) > 0)) {
                foreach ($admins as $admin) {
                    if (!preg_match($regPatt, trim($admin))) {
                        return array('Špatný formát ID kontaktu "'.trim($admin).'"', null);
                    }
                }
            }
        }
        return array(null, $this->cleaned_data['admins']);
    }
}


/**
* Pomocna funkce na vykresleni formulare.
*
* Poznamka: V budoucnu vznikne sada funkci pro Smartyho, ktery
* formulare snadno vykresli v jednotne podobe. Pro potreby
* prototypovani ale zatim zcela postacuje pouzivat
* metody $form->as_table(), $form->as_ul() a $form->as_p().
*/
function render_form($form)
{
    $content = '<h1>Registrace .cz nebo ENUM domény</h1>';
    $content .= '<p><strong>Demonstrace základního používání frameworku pro práci s formuláři.</strong></p>';
    $content .= '<form method="POST" action="">';
    if (count($form->get_errors())) {
        $content .= '<p class="error">Ve formuláři se objevily nějaké chyby. Opravte je prosím a formulář poté znovu odešlete.<p>';
    }
    $content .= '<table>';
    $content .= $form->as_table();
    $content .= '</table>';
    $content .= '<p><input type="submit" value="Odeslat formulář" /></p>';
    $content .= '</form>';
    return $content;
}


/**
* Hlavni kod pro obsluhu formulare.
*/
if (count($_POST) > 0) { // odeslal uzivatel formular?
    // ano, odeslal.
    $form = new CzDomainRegistrationForm(array('data'=>$_POST));
    if ($form->is_valid()) { // jsou odeslana formularova data spravna?
        // ano, jsou; muzeme s nimi provest nejake zpracovani
        $content = '<h1>Data ve formuláři jsou správna!</h1>';
        $content .= '<p>V této chvíli s nimi můžeme nějak naložit, např. vložit záznam do DB a redirectnout na nějaké URL.</p>';
        $content .= '<p>Data:</p><pre>';
        $content .= var_export($form->cleaned_data, true);
        $content .= '</pre>';
    }
    else {
        // formular byl odeslan s chybnymi daty
        $content = render_form($form);
    }
}
else {
    // formular nebyl odeslan; zobrazime ho v jeho vychozim stavu
    $form = new CzDomainRegistrationForm();
    $content = render_form($form);
}

// HTML vystup
header('Content-Type: text/html; charset=utf-8');

// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
<html>
    <head>
         <style type="text/css">
         /*<![CDATA[*/ 
            .error, ul.errorlist { margin:1em 0 0 0; padding:.5em 1em .5em 1.5em; color:red; font-weight:bold; }
            ul.errorlist li { margin-left:0; padding-left:0; }
            td, th { border-top: 6px solid #f0f0f0; padding:1em 0; }
            table { border-bottom: 6px solid #f0f0f0; }
         /*]]>*/
         </style>
    </head>
    <body>
        <?php echo $content;?>
    </body>
</html>
