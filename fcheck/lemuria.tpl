; -------------------------------------
; - fcheck-Template für Lemuria Alpha -
; -------------------------------------

^(\/\/)|(komme|kommen|komment|kommenta|kommentar).*$
^;.*$
^@ +[a-z].+$
^@ +(\*[0-9]*|[0-9]*\*) +[a-z].+$
^@ +[0-9]+ +[a-z].+$
^@ +[0-9]+/+[0-9]+ +[a-z].+$
^=[0-9]+( +\+[0-9]+)? +[a-z].+$
^\+[0-9]+( +=[0-9]+)? +[a-z].+$

^(locale) +[a-zA-Z0-9]+$
^(region) +.+$

^@?(a|an|ang|angr|angre|angrei|angreif|angreife|angreifen|angri|angrif|angriff|at|att|atta|attac|attack|attacke|attacki|attackie|attackier|attackiere|attackieren)( +[a-z0-9]{1,6})+$

^(ba|ban|bann|banne|banner) +.*$

^@?(benu|benut|benutz|benutze|benutzen)( +[0-9]+)? +(bauernlieb|berserkerblut|elixier der macht|gehirnschmalz|goliathwasser|heiltrank|pferdeglück|pferdeglueck|schaffenstrunk|siebenmeilentee|trank der wahrheit|wasser des lebens|wundsalbe)$
^@?(benu|benut|benutz|benutze|benutzen)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(beschreibu|beschreibun|beschreibung|beschreibe|beschreiben|te|tex|text)( +(einheit|region|gebaeude|gebäude|burg|schiff|partei))? .+$

^@?(bestei|besteig|besteige|besteigen) +[a-z0-9]{1,6}$

^@?(bet|betr|betre|betret|betrete|betreten)( +(burg|gebäude|gebaeude|schiff))? +[a-z0-9]{1,6}$

^@?(bew|bewa|bewac|bewach|bewache|bewachen|bewachu|bewachun|bewachung)( +nicht)?$

^@?(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +(einheit +)?[a-z0-9]{1,6} +.+$
^@?(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +region +.+$
^@?(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +(burg|gebäude|gebaeude|partei|schiff) +[a-z0-9]{1,6} +.+$

^(einh|einhe|einheit) +[a-z0-9]{1,6}$

^(end|ende)$

^@?(ent|entl|entla|entlas|entlass|entlasse|entlassen) +.*$

^@?(erf|erfor|erfors|erforsc|erforsch|erforsche|erforschen|for|fors|forsc|forsch|forsche|forschen)$
^@?(erf|erfor|erfors|erforsc|erforsch|erforsche|erforschen|for|fors|forsc|forsch|forsche|forschen) +(kraut|kräuter|kraeuter)$

^@?(fol|folg|folge|folgen) +[a-z0-9]{1,6}$

^@?(ers|ersc|ersch|erscha|erschaf|erschaff|erschaffe|erschaffen) +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch)( +[a-z0-9]{1,6})?$

^@?(gi|gib|ge|geb|gebe|geben) +[a-z0-9]{1,6}( +.*)?$
^@?(gi|gib|ge|geb|gebe|geben) +[a-z0-9]{1,6}( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$
^@?(ü|ue|üb|ueb|übe|uebe|über|ueber|überg|ueberg|überge|ueberge|übergeb|uebergeb|übergebe|uebergebe|übergeben|uebergeben) +[a-z0-9]{1,6}( +.*)?$
^@?(ü|ue|üb|ueb|übe|uebe|über|ueber|überg|ueberg|überge|ueberge|übergeb|uebergeb|übergebe|uebergebe|übergeben|uebergeben) +[a-z0-9]{1,6}( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(h|he|hel|helf|helfe|helfen|hi|hil|hilf|hilfe) +[a-z0-9]{1,6} +[a-z]+( +(region|nicht|region +nicht|nicht +region))?$

^@?(kam|kamp|kampf|kampfz|kampfza|kampfzau|kampfzaub|kampfzaube|kampfzauber) +(astrales chaos|beschleunigung|blick des basilisken|elementarwesen|feuerball|friedenslied|geisterk(ae|ä)mpfer|rosthauch|schockwelle|steinhaut)( +(([0-9]+)|aus|nicht))?$
^@?(kam|kamp|kampf|kampfz|kampfza|kampfzau|kampfzaub|kampfzaube|kampfzauber) +(aus|kein|keine|keiner|keinen|nicht)$

^@?(kau|kauf|kaufe|kaufen)( +[0-9]+)? +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch)$

^@?(kä|käm|kämp|kämpf|kämpfe|kämpfen|kae|kaem|kaemp|kaempf|kaempfe|kaempfen|ka|kam|kamp|kampf) +(aggressiv|defensiv|fliehe|fliehen|flucht|hinten|nicht|vorn|vorne|vorsichtig)?$

^@?(komma|komman|kommand|kommando)( +[a-z0-9]{1,6})?$

^@?(kon|kont|konta|kontak|kontakt|kontakti|kontaktie|kontaktier|kontaktiere|kontaktieren)( +[a-z0-9]{1,6})+$

^@?(leh|lehr|lehre|lehren|lehrer)( +[a-z0-9]{1,6})+$

^@?(ler|lern|lerne|lernen) +(alchemie|armbrustschießen|armbrustschiessen|ausdauer|bergbau|bogenbau|bogenschießen|bogenschiessen|burgenbau|handel|handeln|hiebwaffen|holzfaellen|holzfällen|juwelierkunst|juwelierskunst|katapultbedienung|katapultschießen|katapultschiessen|kräuterkunde|kraeuterkunde|magie|navigation|navigieren|pferdedressur|reiten|ruestungsbau|rüstungsbau|schiffbau|segeln|speerkämpfen|speerkaempfen|speerkampf|spionage|spionieren|stangenwaffen|steinbau|steuereintreiben|steuereintreibung|strassenbau|straßenbau|taktik|tarnen|tarnung|unterhalten|unterhaltung|waffenbauen|waffenbau|wagenbau|wahrnehmen|wahrnehmung)$

^@?(les|lese|lesen|li|lie|lies)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$
^@?(unters|untersu|untersuc|untersuch|untersuche|untersuchen)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(m|ma|mac|mach|mache|machen) +(temp) +[a-z0-9]{1,6}$
^@?(m|ma|mac|mach|mache|machen) +[a-zäöüß]+$
^@?(m|ma|mac|mach|mache|machen) +[0-9]+ +[a-zäöüß]+$
^@?(m|ma|mac|mach|mache|machen) +(schiff|boot|drachenschiff|galeone|karavelle|langboot|trireme)( +[0-9]+)?$
^@?(m|ma|mac|mach|mache|machen) +(gebäude|gebaeude|burg|akropolis|alchemistenküche|bergwerk|hafen|holzfällerhütte|holzfaellerhütte|holzfällerhuette|holzfaellerhuette|kamelzucht|kanal|leuchtturm|leuchttürme|magierturm|minen|mine|monument|monumente|pferdezucht|ruinen|ruine|saegewerk|sägewerke|sägewerk|saegewerke|sattlerei|schiffswerft|schmiede|seehafen|steg|steinbruch|steingrube|steuerturm|taverne|wegweiser|werkstatt)(( +[0-9]+)|( +[a-z0-9]{1,6}))?$
^@?(m|ma|mac|mach|mache|machen) +straße +(no|o|so|sw|w|nw)$
^@?(m|ma|mac|mach|mache|machen) +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch)( +[a-z0-9]{1,6})?$

^@?(nam|name|bene|benen|benenn|benenne|benennen)( +(einheit|region|gebäude|gebaeude|burg|schiff|partei))? +.+$

^(nä|näc|näch|nächs|nächst|nächste|nächster|nae|naec|naech|naechs|naechst|naechste|naechster)$

^@?(ne|neh|nehm|nehme|nehmen)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$
^@?(ni|nim|nimm)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(nu|num|numm|numme|nummer|i|id)( +(einheit|gebaeude|gebäude|burg|schiff|partei))? +[a-z0-9]{1,6}$

^(p|pa|par|part|parte|partei) +[a-z0-9]{1,6}( +.*)?$
^(ere|eres|eress|eresse|eressea) +[a-z0-9]{1,6}( +.*)?$
^(f|fa|fan|fant|fanta|fantas|fantasy|fantasya) +[a-z0-9]{1,6}( +.*)?$
^(lem|lemu|lemur|lemuri|lemuria) +[a-z0-9]{1,6}( +.*)?$

^@?(rei|reis|reise|reisen|nac|nach)( +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west))+$

^@?(rek|rekr|rekru|rekrut|rekruti|rekrutie|rekrutier|rekrutiere|rekrutieren|rekrute|rekruten) +[0-9]+$

^@?(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +[0-9]+ +[a-z äöüß]+$
^@?(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +[a-z äöüß]+$
^@?(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +alles( +[a-z äöüß]+)?$

^@?(ro|rou|rout|route)( +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west|pause))+$

^@?(sc|sch|schr|schre|schrei|schreib|schreibe|schreiben)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}( +[a-zäöüß]+( [a-zäöüß]+)*)?$

^@?(s|so|sor|sort|sorti|sortie|sortier|sortiere|sortieren|sortieru|sortierun|sortierung) +(anfang|erste|erster|zuerst|ende|letzte|letzter|zuletzt)$
^@?(s|so|sor|sort|sorti|sortie|sortier|sortiere|sortieren|sortieru|sortierun|sortierung) +(vor|hinter|nach|austausch|austauschen|auswechseln|mit|tausch|tausche|tauschen|wechsel|wechseln)( +temp)? +[a-z0-9]{1,6}$
^@?(tau|taus|tausc|tausch|tausche|tauschen) +(anfang|erste|erster|zuerst|ende|letzte|letzter|zuletzt)$
^@?(tau|taus|tausc|tausch|tausche|tauschen) +(vor|hinter|nach|austausch|austauschen|auswechseln|mit|tausch|tausche|tauschen|wechsel|wechseln)( +temp)? +[a-z0-9]{1,6}$

^@?(sp|spi|spio|spion|spiona|spionag|spionage|spioni|spionie|spionier|spioniere|spionieren) +[a-z0-9]{1,6}$

^@?(st|ste|steh|stehl|stehle|stehlen) +[a-z0-9]{1,6}$
^@?(bek|bekl|bekla|beklau|beklaue|beklauen) +[a-z0-9]{1,6}$
^@?(besteh|bestehl|bestehle|bestehlen) +[a-z0-9]{1,6}$
^@?(di|die|dieb|diebs|diebst|diebsta|diebstah|diebstahl) +[a-z0-9]{1,6}$

^@?(tar|tarn|tarne|tarnen|tarnu|tarnun|tarnung)( +([0-9]+|nein|nicht|partei( +[a-z0-9]{1,6})?))?$

^@?(tr|tre|trei|treib|treibe|treiben)( +[0-9]+)?$
^@?(besteu|besteue|besteuer|besteuern|besteueru|besteuerun|besteuerung)( +[0-9]+)?$
^@?(eint|eintr|eintre|eintrei|eintreib|eintreibe|eintreiben)( +[0-9]+)?$

^@?(unterh|unterha|unterhal|unterhalt|unterhalte|unterhalten|unterhaltu|unterhaltun|unterhaltung)( +[0-9]+)?$

^@?(ur|urs|ursp|urspr|urspru|ursprun|ursprung)( +(partei|region) +[a-z0-9]{1,6})?$

^@?(verk|verkauf|verkaufe|verkaufen)( +[0-9]+)? +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch)$

^@?(verla|verlas|verlass|verlasse|verlassen)$

^@?(verli|verlie|verlier|verliere|verlieren) +.*$

^@?(vern|verni|vernic|vernich|vernicht|vernichte|vernichten)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^(vorg|vorga|vorgab|vorgabe) +kämpfen +(aggressiv|defensiv|fliehe|fliehen|flucht|hinten|nicht|vorn|vorne|vorsichtig)?$
^(vorg|vorga|vorgab|vorgabe) +tarnen( +nicht)?$
^(vorg|vorga|vorgab|vorgabe) +tarnen +partei( +(nicht|[a-z0-9]{1,6}))?$
^(vorg|vorga|vorgab|vorgabe) +sammeln( +nicht)?$

^(vorl|vorla|vorlag|vorlage) +[a-z].+$
^(vorl|vorla|vorlag|vorlage) +(\*[0-9]*|[0-9]*\*) +[a-z].+$
^(vorl|vorla|vorlag|vorlage) +[0-9]+ +[a-z].+$
^(vorl|vorla|vorlag|vorlage) +[0-9]+/+[0-9]+ +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +(\*[0-9]*|[0-9]*\*) +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +[0-9]+ +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +[0-9]+/+[0-9]+ +[a-z].+$

^@?(za|zau|zaub|zaube|zauber|zaubere|zaubern) +(adlerauge|astraler weg|aufruhr verursachen|auratransfer|blick des greifen|erdbeben|erwecke baumhirten|fernsicht|g(ue|ü)nstige winde|lautloser schatten|luftschiff|monster aufhetzen|ring der unsichtbarkeit|tagtraum|teleportation|wunderdoktor)( +[0-9]+)?$

^@?(zerstö|zerstör|zerstöre|zerstören) +(burg|gebäude|gebaeude|schiff)( +[a-z0-9]{1,6})?$
^@?(zerstoe|zerstoer|zerstoere|zerstoeren) +(burg|gebäude|gebaeude|schiff)( +[a-z0-9]{1,6})?$
