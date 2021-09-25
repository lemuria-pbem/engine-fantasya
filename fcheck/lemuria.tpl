; -------------------------------------
; - fcheck-Template für Lemuria Alpha -
; -------------------------------------

^(\/\/ +)|(komme|kommen|komment|kommenta|kommentar) +.*$
^;.*$

^(locale) +[a-zA-Z0-9]+$
^(region) +.+$

^(ba|ban|bann|banne|banner) +.*$

^(benu|benut|benutz|benutze|benutzen)( +[0-9]+)? +(bauernlieb|berserkerblut|elixier der macht|gehirnschmalz|goliathwasser|heiltrank|pferdeglück|pferdeglueck|schaffenstrunk|siebenmeilentee|trank der wahrheit|wasser des lebens|wundsalbe)$

^(beschreibu|beschreibun|beschreibung|beschreibe|beschreiben|te|tex|text)( +(einheit|region|gebaeude|gebäude|burg|schiff|partei))? .+$

^(bestei|besteig|besteige|besteigen) +[a-z0-9]{1,6}$

^(bet|betr|betre|betret|betrete|betreten)( +(burg|gebäude|gebaeude|schiff))? +[a-z0-9]{1,6}$

^(bew|bewa|bewac|bewach|bewache|bewachen|bewachu|bewachun|bewachung)( +nicht)?$

^(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +(einheit +)?[a-z0-9]{1,6} +.+$
^(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +region +.+$
^(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +(burg|gebäude|gebaeude|partei|schiff) +[a-z0-9]{1,6} +.+$

^(einh|einhe|einheit) +[a-z0-9]{1,6}$

^(end|ende)$

^(ent|entl|entla|entlas|entlass|entlasse|entlassen) +.*$

^(fol|folg|folge|folgen) +[a-z0-9]{1,6}$

^(erf|erfor|erfors|erforsc|erforsch|erforsche|erforschen|for|fors|forsc|forsch|forsche|forschen)$
^(erf|erfor|erfors|erforsc|erforsch|erforsche|erforschen|for|fors|forsc|forsch|forsche|forschen) +(kraut|kräuter|kraeuter)$

^(gi|gib|ge|geb|gebe|geben) +[a-z0-9]{1,6}( +.*)?$
^(ü|ue|üb|ueb|übe|uebe|über|ueber|überg|ueberg|überge|ueberge|übergeb|uebergeb|übergebe|uebergebe|übergeben|uebergeben) +[a-z0-9]{1,6}( +.*)?$

^(h|he|hel|helf|helfe|helfen|hi|hil|hilf|hilfe) +[a-z0-9]{1,6} +[a-z]+( +(region|nicht|region +nicht|nicht +region))?$

^(kau|kauf|kaufe|kaufen)( +[0-9]+)? +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch)$

^(kä|käm|kämp|kämpf|kämpfe|kämpfen|kae|kaem|kaemp|kaempf|kaempfe|kaempfen|ka|kam|kamp|kampf) +(aggressiv|defensiv|fliehe|fliehen|flucht|hinten|nicht|vorn|vorne)?$

^(komma|komman|kommand|kommando)( +[a-z0-9]{1,6})?$

^(kon|kont|konta|kontak|kontakt|kontakti|kontaktie|kontaktier|kontaktiere|kontaktieren) +[a-z0-9]{1,6}$

^(leh|lehr|lehre|lehren|lehrer)( +[a-z0-9]{1,6})+$

^(ler|lern|lerne|lernen) +(alchemie|armbrustschießen|armbrustschiessen|ausdauer|bergbau|bogenbau|bogenschießen|bogenschiessen|burgenbau|handel|handeln|hiebwaffen|holzfaellen|holzfällen|katapultbedienung|katapultschießen|katapultschiessen|kräuterkunde|kraeuterkunde|magie|navigation|navigieren|pferdedressur|reiten|ruestungsbau|rüstungsbau|schiffbau|segeln|speerkämpfen|speerkaempfen|speerkampf|spionage|spionieren|steinbau|steuereintreiben|steuereintreibung|strassenbau|straßenbau|taktik|tarnen|tarnung|unterhalten|unterhaltung|waffenbauen|waffenbau|wagenbau|wahrnehmen|wahrnehmung)$

^(m|ma|mac|mach|mache|machen) +(temp) +[a-z0-9]{1,6}$
^(m|ma|mac|mach|mache|machen) +[a-zäöüß]+$
^(m|ma|mac|mach|mache|machen) +[0-9]+ +[a-zäöüß]+$
^(m|ma|mac|mach|mache|machen) +(schiff|boot|drachenschiff|galeone|karavelle|langboot|trireme)( +[0-9]+)?$
^(m|ma|mac|mach|mache|machen) +(gebäude|gebaeude|burg|bergwerk|hafen|holzfällerhütte|holzfaellerhütte|holzfällerhuette|holzfaellerhuette|leuchtturm|leuchttürme|minen|mine|monument|monumente|ruinen|ruine|saegewerk|sägewerke|sägewerk|saegewerke|sattlerei|schiffswerft|schmiede|seehafen|steg|steinbruch|steuerturm|werkstatt)( +[0-9]+)$

^(nam|name|bene|benen|benenn|benenne|benennen)( +(einheit|region|gebäude|gebaeude|burg|schiff|partei))? +.+$

^(nä|näc|näch|nächs|nächst|nächste|nächster|nae|naec|naech|naechs|naechst|naechste|naechster)$

^(nu|num|numm|numme|nummer|i|id)( +(einheit|gebaeude|gebäude|burg|schiff|partei))? +[a-z0-9]{1,6}$

^(p|pa|par|part|parte|partei) +[a-z0-9]{1,6}( +.*)?$
^(ere|eres|eress|eresse|eressea) +[a-z0-9]{1,6}( +.*)?$
^(f|fa|fan|fant|fanta|fantas|fantasy|fantasya) +[a-z0-9]{1,6}( +.*)?$
^(lem|lemu|lemur|lemuri|lemuria) +[a-z0-9]{1,6}( +.*)?$

^(rei|reis|reise|reisen|nac|nach)( +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west))+$

^(rek|rekr|rekru|rekrut|rekruti|rekrutie|rekrutier|rekrutiere|rekrutieren|rekrute|rekruten) +[0-9]+$

^(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +[0-9]+ +[a-zäöüß]+$
^(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +[a-zäöüß]+$
^(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +alles( +[a-zäöüß]+)?$

^(ro|rou|rout|route)( +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west|pause))+$

^(s|so|sor|sort|sorti|sortie|sortier|sortiere|sortieren|sortieru|sortierun|sortierung) +(anfang|erste|erster|zuerst|ende|letzte|letzter|zuletzt)$
^(s|so|sor|sort|sorti|sortie|sortier|sortiere|sortieren|sortieru|sortierun|sortierung) +(vor|hinter|nach|austausch|austauschen|auswechseln|mit|tausch|tausche|tauschen|wechsel|wechseln)( +temp)? +[a-z0-9]{1,6}$
^(tau|taus|tausc|tausch|tausche|tauschen) +(anfang|erste|erster|zuerst|ende|letzte|letzter|zuletzt)$
^(tau|taus|tausc|tausch|tausche|tauschen) +(vor|hinter|nach|austausch|austauschen|auswechseln|mit|tausch|tausche|tauschen|wechsel|wechseln)( +temp)? +[a-z0-9]{1,6}$

^(sp|spi|spio|spion|spiona|spionag|spionage|spioni|spionie|spionier|spioniere|spionieren) +[a-z0-9]{1,6}$

^(st|ste|steh|stehl|stehle|stehlen) +[a-z0-9]{1,6}$
^(bek|bekl|bekla|beklau|beklaue|beklauen) +[a-z0-9]{1,6}$
^(besteh|bestehl|bestehle|bestehlen) +[a-z0-9]{1,6}$
^(di|die|dieb|diebs|diebst|diebsta|diebstah|diebstahl) +[a-z0-9]{1,6}$

^(tar|tarn|tarne|tarnen|tarnu|tarnun|tarnung)( +([0-9]+|nein|nicht|partei( +[a-z0-9]{1,6})?))?$

^(tr|tre|trei|treib|treibe|treiben)( +[0-9]+)?$
^(besteu|besteue|besteuer|besteuern|besteueru|besteuerun|besteuerung)( +[0-9]+)?$
^(eint|eintr|eintre|eintrei|eintreib|eintreibe|eintreiben)( +[0-9]+)?$

^(un|unt|unte|unter|unterh|unterha|unterhal|unterhalt|unterhalte|unterhalten|unterhaltu|unterhaltun|unterhaltung)( +[0-9]+)?$

^(ur|urs|ursp|urspr|urspru|ursprun|ursprung)( +(partei|region) +[a-z0-9]{1,6})?$

^(verk|verkauf|verkaufe|verkaufen)( +[0-9]+)? +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch)$

^(verla|verlas|verlass|verlasse|verlassen)$

^(verli|verlie|verlier|verliere|verlieren) +.*$

^(vo|vor|vorl|vorla|vorlag|vorlage) +.+$
^(d|de|def|defa|defau|defaul|default) +.+$

^(zerstö|zerstör|zerstöre|zerstören) +(burg|gebäude|gebaeude|schiff)$
^(zerstoe|zerstoer|zerstoere|zerstoeren) +(burg|gebäude|gebaeude|schiff)$
