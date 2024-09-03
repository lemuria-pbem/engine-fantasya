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

^(al|alt|alte|alter|altern|alterna|alternat|alternati|ælternativ|alternative) +[a-zäöüß]+( .+)?$

^(ange|angeb|angebo|angebot) +([0-9-]+|\*) +[a-zäöüß ]+[0-9-]+ +[a-zäöüß ]+$

^@?(angr|angre|angrei|angreif|angreife|angreifen|angri|angrif|angriff|at|att|atta|attac|attack|attacke|attacki|attackie|attackier|attackiere|attackieren)( +[a-z0-9]{1,6})+$
^@?(angr|angre|angrei|angreif|angreife|angreifen|angri|angrif|angriff|at|att|atta|attac|attack|attacke|attacki|attackie|attackier|attackiere|attackieren) +partei( +[a-z0-9]{1,6})+$
^@?(angr|angre|angrei|angreif|angreife|angreifen|angri|angrif|angriff|at|att|atta|attac|attack|attacke|attacki|attackie|attackier|attackiere|attackieren) +(aquaner|baer|baeren|bär|bären|baumhirten?|elf|elfen|ghoule?|halblinge?|kobolde?|kraken?|mensch|menschen|monster|orks?|riesenfrosch|riesenfrösche|riesenfroesche|sandwurm|sandwürmer|sandwuermer|skelette?|trolle?|wolf|woelfe|wölfe|zombies?|zwerge?)$

^(auf|auft|auftr|auftra|auftrag) +[a-z0-9]{1,6}$

^@?(aus|ausr|ausra|ausrau|ausraub|ausraube|ausrauben)( +[a-z0-9]{1,6})+$
^@?(aus|ausr|ausra|ausrau|ausraub|ausraube|ausrauben) +partei( +[a-z0-9]{1,6})+$
^@?(aus|ausr|ausra|ausrau|ausraub|ausraube|ausrauben) +(aquaner|baer|baeren|bär|bären|baumhirten?|elf|elfen|ghoule?|halblinge?|kobolde?|kraken?|mensch|menschen|monster|orks?|riesenfrosch|riesenfrösche|riesenfroesche|sandwurm|sandwürmer|sandwuermer|skelette?|trolle?|wolf|woelfe|wölfe|zombies?|zwerge?)$
^@?(ü|üb|übe|über|überf|überfa|überfal|überfall|überfalle|überfallen)( +[a-z0-9]{1,6})+$
^@?(ü|üb|übe|über|überf|überfa|überfal|überfall|überfalle|überfallen) +partei( +[a-z0-9]{1,6})+$
^@?(ü|üb|übe|über|überf|überfa|überfal|überfall|überfalle|überfallen) +(aquaner|baer|baeren|bär|bären|baumhirten?|elf|elfen|ghoule?|halblinge?|kobolde?|kraken?|mensch|menschen|monster|orks?|riesenfrosch|riesenfrösche|riesenfroesche|sandwurm|sandwürmer|sandwuermer|skelette?|trolle?|wolf|woelfe|wölfe|zombies?|zwerge?)$
^@?(ue|ueb|uebe|ueber|ueberf|ueberfa|ueberfal|ueberfall|ueberfalle|ueberfallen)( +[a-z0-9]{1,6})+$
^@?(ue|ueb|uebe|ueber|ueberf|ueberfa|ueberfal|ueberfall|ueberfalle|ueberfallen) +partei( +[a-z0-9]{1,6})+$
^@?(ue|ueb|uebe|ueber|ueberf|ueberfa|ueberfal|ueberfall|ueberfalle|ueberfallen) +(aquaner|baer|baeren|bär|bären|baumhirten?|elf|elfen|ghoule?|halblinge?|kobolde?|kraken?|mensch|menschen|monster|riesenfrosch|riesenfrösche|riesenfroesche|sandwurm|sandwürmer|sandwuermer|orks?|skelette?|trolle?|wolf|woelfe|wölfe|zombies?|zwerge?)$

^(ba|ban|bann|banne|banner) +.*$

^(bee|been|beend|beende|beenden)( +[a-z0-9]{1,6})*$

^@?(benu|benut|benutz|benutze|benutzen)( +[0-9]+)? +(bauernlieb|berserkerblut|elixier der macht|gehirnschmalz|goliathwasser|heiltrank|pferdeglück|pferdeglueck|schaffenstrunk|siebenmeilentee|trank der wahrheit|wasser des lebens|wundsalbe)$
^@?(benu|benut|benutz|benutze|benutzen)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(besc|besch|beschr|beschre|beschrei|beschreib|beschreibu|beschreibun|beschreibung|beschreibe|beschreiben|te|tex|text)( +(einheit|region|reich|gebaeude|gebäude|burg|schiff|partei))? .+$

^@?(bestei|besteig|besteige|besteigen)( +schiff)? +[a-z0-9]{1,6}$

^@?(besu|besuc|besuch|besuche|besuchen) +[a-z0-9]{1,6}$

^@?(bet|betr|betre|betret|betrete|betreten)( +(burg|gebäude|gebaeude|schiff))? +[a-z0-9]{1,6}$

^(beu|beut|beute) +[a-zäöüß ]+$

^@?(bew|bewa|bewac|bewach|bewache|bewachen|bewachu|bewachun|bewachung)( +(nicht|region))?$
^@?(bew|bewa|bewac|bewach|bewache|bewachen|bewachu|bewachun|bewachung) +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west)$

^@?(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +(einheit +)?[a-z0-9]{1,6} +.+$
^@?(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +region +.+$
^@?(bo|bot|bots|botsc|botsch|botscha|botschaf|botschaft) +(burg|gebäude|gebaeude|partei|schiff) +[a-z0-9]{1,6} +.+$

^(einh|einhe|einheit) +[a-z0-9]{1,6}$

^(end|ende)$

^@?(ent|entl|entla|entlas|entlass|entlasse|entlassen) +.*$

^@?(erf|erfor|erfors|erforsc|erforsch|erforsche|erforschen|for|fors|forsc|forsch|forsche|forschen)$
^@?(erf|erfor|erfors|erforsc|erforsch|erforsche|erforschen|for|fors|forsc|forsch|forsche|forschen) +(kraut|kräuter|kraeuter)$

^(erl|erla|erlau|erlaub|erlaube|erlauben) +[a-z]+$

^@?(fol|folg|folge|folgen) +[a-z0-9]{1,6}$
^@?(fol|folg|folge|folgen) +einheit +[a-z0-9]{1,6}$

^@?(ers|ersc|ersch|erscha|erschaf|erschaff|erschaffe|erschaffen) +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch)( +[a-z0-9]{1,6})?$

^@?(ger|gerü|gerüc|gerüch|gerücht|geru|gerue|geruec|geruech|geruecht).+$

^@?(gi|gib|geb|gebe|geben) +[a-z0-9]{1,6}( +.*)?$
^@?(gi|gib|geb|gebe|geben) +[a-z0-9]{1,6}( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(gr|gre|gren|grenz|grenze) +[0-9]+%? +[a-zäöüß ]+$
^@?(gr|gre|gren|grenz|grenze) +[a-zäöüß ]+ +nicht$

^(ha|han|hand|hande|handel|handeln) +[a-z0-9]{1,6}$
^(ha|han|hand|hande|handel|handeln) +[a-z0-9]{1,6} +(\*|alle|alles)$
^(ha|han|hand|hande|handel|handeln) +[a-z0-9]{1,6} +[0-9-]+$
^(ha|han|hand|hande|handel|handeln) +[a-z0-9]{1,6} +[0-9-]+[a-zäöüß ]+$
^(ha|han|hand|hande|handel|handeln) +[a-z0-9]{1,6} +[0-9-]+[a-zäöüß ]+ [0-9]+[a-zäöüß ]+$
^(ha|han|hand|hande|handel|handeln) +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch) +[a-z0-9]{1,6}$
^(ha|han|hand|hande|handel|handeln) +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch) +[a-z0-9]{1,6} +[0-9]+[a-zäöüß ]+$

^@?(he|hel|helf|helfe|helfen|hi|hil|hilf|hilfe) +[a-z0-9]{1,6} +[a-z]+( +(region|nicht|region +nicht|nicht +region))?$

^@?(kam|kamp|kampf|kampfz|kampfza|kampfzau|kampfzaub|kampfzaube|kampfzauber) +(astrales chaos|beschleunigung|blick des basilisken|elementarwesen|feuerball|friedenslied|geisterk(ae|ä)mpfer|rosthauch|schockwelle|steinhaut)( +(([0-9]+)|aus|nicht))?$
^@?(kam|kamp|kampf|kampfz|kampfza|kampfzau|kampfzaub|kampfzaube|kampfzauber) +(aus|kein|keine|keiner|keinen|nicht)$

^@?(kau|kauf|kaufe|kaufen)( +[0-9]+)? +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch)$
^@?(kau|kauf|kaufe|kaufen) +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch) +[0-9]+$

^@?(kä|käm|kämp|kämpf|kämpfe|kämpfen|kae|kaem|kaemp|kaempf|kaempfe|kaempfen|ka|kam|kamp|kampf) +(aggressiv|defensiv|fliehe|fliehen|flucht|hinten|nicht|vorn|vorne|vorsichtig)?$

^@?(komma|komman|kommand|kommando)$
^@?(komma|komman|kommand|kommando) +(temp +)?[a-z0-9]{1,6}$

^@?(kon|kont|konta|kontak|kontakt|kontakti|kontaktie|kontaktier|kontaktiere|kontaktieren)( +[a-z0-9]{1,6})+$

^@?(leh|lehr|lehre|lehren|lehrer)( +[a-z0-9]{1,6})+$

^@?(ler|lern|lerne|lernen) +(alchemie|armbrustschießen|armbrustschiessen|ausdauer|bergbau|bogenbau|bogenschießen|bogenschiessen|burgenbau|handel|handeln|hiebwaffen|holzfaellen|holzfällen|juwelierkunst|juwelierskunst|katapultbedienung|katapultschießen|katapultschiessen|kräuterkunde|kraeuterkunde|magie|navigation|navigieren|pferdedressur|reiten|ruestungsbau|rüstungsbau|schiffbau|segeln|speerkämpfen|speerkaempfen|speerkampf|spionage|spionieren|stangenwaffen|steinbau|steuereintreiben|steuereintreibung|strassenbau|straßenbau|taktik|tarnen|tarnung|unterhalten|unterhaltung|waffenbauen|waffenbau|wagenbau|wahrnehmen|wahrnehmung)( +[0-9]+)?$

^@?(les|lese|lesen|li|lie|lies)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$
^@?(unters|untersu|untersuc|untersuch|untersuche|untersuchen)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^@?(ma|mac|mach|mache|machen) +(temp) +[a-z0-9]{1,6}$
^@?(ma|mac|mach|mache|machen) +[a-zäöüß]+$
^@?(ma|mac|mach|mache|machen) +-?[0-9]+ +[a-zäöüß]+$
^@?(ma|mac|mach|mache|machen) +-[0-9]+% +kräuter$
^@?(ma|mac|mach|mache|machen) +(schiff|boot|drachenschiff|galeone|karavelle|langboot|trireme)( +[0-9]+)?$
^@?(ma|mac|mach|mache|machen) +(gebäude|gebaeude|burg|akropolis|alchemistenküche|bergwerk|forsthaus|greifenzucht|hafen|holzfällerhütte|holzfaellerhütte|holzfällerhuette|holzfaellerhuette|hütte|huette|jagdhaus|kamelzucht|kanal|kräuterhütte|kraeuterhütte|kräuterhuette|kraeuterhuette|leuchtturm|leuchttürme|magierturm|markt|minen|mine|monument|monumente|oase|pferdezucht|pilzhöhle|pilzhoehle|plantage|ruinen|ruine|saegewerk|sägewerke|sägewerk|saegewerke|sattlerei|schiffswerft|schmiede|seehafen|steg|steinbruch|steingrube|steuerturm|taverne|wegweiser|werkstatt)( +[0-9]+)?$
^@?(ma|mac|mach|mache|machen) +(gebäude|gebaeude|burg|akropolis|alchemistenküche|bergwerk|forsthaus|greifenzucht|hafen|holzfällerhütte|holzfaellerhütte|holzfällerhuette|holzfaellerhuette|hütte|huette|jagdhaus|kamelzucht|kanal|kräuterhütte|kraeuterhütte|kräuterhuette|kraeuterhuette|leuchtturm|leuchttürme|magierturm|markt|minen|mine|monument|monumente|oase|pferdezucht|pilzhöhle|pilzhoehle|plantage|ruinen|ruine|saegewerk|sägewerke|sägewerk|saegewerke|sattlerei|schiffswerft|schmiede|seehafen|steg|steinbruch|steingrube|steuerturm|taverne|wegweiser|werkstatt) +[a-z0-9]{1,6}( +[0-9]+)?$
^@?(ma|mac|mach|mache|machen) +straße +(no|o|so|sw|w|nw)( +[0-9]+)?$
^@?(ma|mac|mach|mache|machen) +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch)( +[a-z0-9]{1,6})?$

^(me|men|meng|menge) +[a-z0-9]{1,6} +([0-9-]+|\*) +[a-zäöüß ]+$

^(nachf|nachfr|nachfra|nachfrag|nachfrage) +([0-9-]+|\*) +[a-zäöüß ]+[0-9-]+ +[a-zäöüß ]+$

^@?(nam|name|bene|benen|benenn|benenne|benennen)( +(einheit|region|reich|gebäude|gebaeude|burg|schiff|partei))? +.+$

^(nä|näc|näch|nächs|nächst|nächste|nächster|nae|naec|naech|naechs|naechst|naechste|naechster)$

^@?(ne|neh|nehm|nehme|nehmen)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}.*$
^@?(ne|neh|nehm|nehme|nehmen)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6} +[0-9]+[a-zäöüß ]+$
^@?(ni|nim|nimm)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}.*$
^@?(ni|nim|nimm)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6} +[0-9]+[a-zäöüß ]+$

^@?(nu|num|numm|numme|nummer|i|id)( +(einheit|gebaeude|gebäude|burg|reich|schiff|partei))? +[a-z0-9]{1,6}$

^(pa|par|part|parte|partei) +[a-z0-9]{1,6}( +.*)?$
^(ere|eres|eress|eresse|eressea) +[a-z0-9]{1,6}( +.*)?$
^(fa|fan|fant|fanta|fantas|fantasy|fantasya) +[a-z0-9]{1,6}( +.*)?$
^(lem|lemu|lemur|lemuri|lemuria) +[a-z0-9]{1,6}( +.*)?$

^(pr|pre|prei|preis) +[a-z0-9]{1,6} +([0-9-]+|\*) +[a-zäöüß ]+$

^(reic|reich) +[a-z0-9]{1,6}( +nicht)?$

^@?(reis|reise|reisen|nac|nach)( +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west))+$

^@?(rek|rekr|rekru|rekrut|rekruti|rekrutie|rekrutier|rekrutiere|rekrutieren|rekrute|rekruten) +-?[0-9]+$

^@?(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +[0-9]+ +[a-z äöüß]+$
^@?(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +[a-z äöüß]+$
^@?(res|rese|reser|reserv|reservi|reserve|reservie|reservier|reserviere|reservieren|reservieru|reservierun|reservierung) +alles( +[a-z äöüß]+)?$

^@?(ro|rou|rout|route)( +(e|ne|no|nw|o|so|sw|w|east|nordosten|nordwesten|northeast|northwest|osten|suedosten|suedwesten|southeast|southwest|westen|west|pause))+$

^(sa|sam|samm|samme|sammel|sammeln)( +nicht)?$

^@?(sc|sch|schr|schre|schrei|schreib|schreibe|schreiben)( +(kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}( +[a-zäöüß]+( [a-zäöüß]+)*)?$

^@?(so|sor|sort|sorti|sortie|sortier|sortiere|sortieren|sortieru|sortierun|sortierung) +(anfang|erste|erster|zuerst|ende|letzte|letzter|zuletzt)$
^@?(so|sor|sort|sorti|sortie|sortier|sortiere|sortieren|sortieru|sortierun|sortierung) +(vor|hinter|nach|austausch|austauschen|auswechseln|mit|tausch|tausche|tauschen|wechsel|wechseln)( +temp)? +[a-z0-9]{1,6}$
^@?(tau|taus|tausc|tausch|tausche|tauschen) +(anfang|erste|erster|zuerst|ende|letzte|letzter|zuletzt)$
^@?(tau|taus|tausc|tausch|tausche|tauschen) +(vor|hinter|nach|austausch|austauschen|auswechseln|mit|tausch|tausche|tauschen|wechsel|wechseln)( +temp)? +[a-z0-9]{1,6}$

^@?(sp|spi|spio|spion|spiona|spionag|spionage|spioni|spionie|spionier|spioniere|spionieren) +[a-z0-9]{1,6}$

^@?(steh|stehl|stehle|stehlen) +[a-z0-9]{1,6}$
^@?(bek|bekl|bekla|beklau|beklaue|beklauen) +[a-z0-9]{1,6}$
^@?(besteh|bestehl|bestehle|bestehlen) +[a-z0-9]{1,6}$
^@?(di|die|dieb|diebs|diebst|diebsta|diebstah|diebstahl) +[a-z0-9]{1,6}$

^(steu|steue|steuer|steuern|steuers|steuersa|steuersat|steuersatz) +[0-9]+( +[a-zäöüß ]+)?$
^(steu|steue|steuer|steuern|steuers|steuersa|steuersat|steuersatz) +[0-9]+ *%$

^@?(tar|tarn|tarne|tarnen|tarnu|tarnun|tarnung)( +([0-9]+|nein|nicht|partei( +[a-z0-9]{1,6})?))?$

^(tra|tran|trans|transp|transpo|transpor|transport|transporti|transportie|transportier|transportiere|transportieren)( +nicht)?$

^@?(tre|trei|treib|treibe|treiben)( +-?[0-9]+)?$
^@?(besteu|besteue|besteuer|besteuern|besteueru|besteuerun|besteuerung)( +-?[0-9]+)?$
^@?(eint|eintr|eintre|eintrei|eintreib|eintreibe|eintreiben)( +-?[0-9]+)?$

^@?(unterh|unterha|unterhal|unterhalt|unterhalte|unterhalten|unterhaltu|unterhaltun|unterhaltung)( +-?[0-9]+)?$

^@?(ur|urs|ursp|urspr|urspru|ursprun|ursprung)( +(partei|region) +[a-z0-9]{1,6})?$

^(verb|verbi|verbie|verbiet|verbiete|verbieten) +[a-z]+$

^(verg|verge|verges|vergess|vergesse|vergessen) +[a-zäöüß]+( +[0-9]+)?$
^(vergi|vergis|vergiss) +[a-zäöüß]+( +[0-9]+)?$

^@?(verk|verkauf|verkaufe|verkaufen)( +[0-9]+)? +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch)$
^@?(verk|verkauf|verkaufe|verkaufen) +(balsam|balsame|gewürz|gewürze|gewuerz|gewuerze|juwel|juwelen|myrrhe|myrrhen|öl|öle|oel|oele|pelz|pelze|seide|seiden|weihrauch) +[0-9]+$

^@?(verla|verlas|verlass|verlasse|verlassen)$

^@?(verli|verlie|verlier|verliere|verlieren) +.*$

^@?(vern|verni|vernic|vernich|vernicht|vernichte|vernichten)( +(kadaver|kräuteralmanach|kraeuteralmanach|ring der unsichtbarkeit|schriftrolle|zauberbuch))? +[a-z0-9]{1,6}$

^(vorg|vorga|vorgab|vorgabe) +kämpfen +(aggressiv|defensiv|fliehe|fliehen|flucht|hinten|nicht|vorn|vorne|vorsichtig)?$
^(vorg|vorga|vorgab|vorgabe) +tarnen( +nicht)?$
^(vorg|vorga|vorgab|vorgabe) +tarnen +partei( +(nicht|[a-z0-9]{1,6}))?$
^(vorg|vorga|vorgab|vorgabe) +sammeln( +nicht)?$
^(vorg|vorga|vorgab|vorgabe) +(wiederhole|wiederholen|wiederholung)( +nicht)?$
^(vorg|vorga|vorgab|vorgabe) +erkunden( +(anlegen|ablegen|nicht))?$

^(vorl|vorla|vorlag|vorlage) +[a-z].+$
^(vorl|vorla|vorlag|vorlage) +(\*[0-9]*|[0-9]*\*) +[a-z].+$
^(vorl|vorla|vorlag|vorlage) +[0-9]+ +[a-z].+$
^(vorl|vorla|vorlag|vorlage) +[0-9]+/+[0-9]+ +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +(\*[0-9]*|[0-9]*\*) +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +[0-9]+ +[a-z].+$
^(d|de|def|defa|defau|defaul|default) +[0-9]+/+[0-9]+ +[a-z].+$

^(w|wi|wie|wied|wiede|wieder|wiederh|wiederho|wiederhol|wiederhole|wiederholen) +[a-z0-9]{1,6}( +nicht)?$
^(w|wi|wie|wied|wiede|wieder|wiederh|wiederho|wiederhol|wiederhole|wiederholen)( +(alle|alles|nicht|nichts)?$

^@?(za|zau|zaub|zaube|zauber|zaubere|zaubern) +(adlerauge|astraler weg|aufruhr verursachen|auratransfer|blick des greifen|erdbeben|erwecke baumhirten|fernsicht|g(ue|ü)nstige winde|lautloser schatten|luftschiff|monster aufhetzen|ring der unsichtbarkeit|tagtraum|teleportation|wunderdoktor)( +[0-9]+)?$

^@?(zerst|zerstö|zerstör|zerstöre|zerstören) +(burg|gebäude|gebaeude|schiff|straße|strasse) +[a-z0-9]{1,6}$
^@?(zerstoe|zerstoer|zerstoere|zerstoeren) +(burg|gebäude|gebaeude|schiff|straße|strasse) +[a-z0-9]{1,6}$
