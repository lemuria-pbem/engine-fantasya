# To-Do

Hier werden Ideen gesammelt und zu behebende Fehler gelistet.

## Fehler in 0.4

    PHP Notice:  Object of class Lemuria\Model\Fantasya\Ability could not be converted to int in /home/sascha/Projekte/Lemuria/lemuria-alpha/vendor/lemuria-pbem/engine-fantasya/src/Command/Travel.php on line 105
    TypeError: Unsupported operand types: int * Lemuria\Model\Fantasya\Ability in /home/sascha/Projekte/Lemuria/lemuria-alpha/vendor/lemuria-pbem/engine-fantasya/src/Factory/NavigationTrait.php:29
    Stack trace:
    /home/sascha/Projekte/Lemuria/lemuria-alpha/vendor/lemuria-pbem/engine-fantasya/src/Command/Travel.php(109): Lemuria\Engine\Fantasya\Command\Travel->navigationTalent()

- Langer Default-Befehl muss aktuellen ersetzen
- Angebotspreis speichern
- Kriegshammer und Streitaxt anpassen
- Boot-Fertigstellung in Magellan falsch
- Simulation sollte keine Hinweise zu fremden Einheiten liefern

## Neue Funktionen

### Version 0.5

- Alchemie (Kräuter und Tränke, BENUTZEN, FORSCHEN, MACHEN)

### Version 0.6

- Magie (KAMPFZAUBER/ZAUBERN)

### Version 0.7 und folgende

- Kampf (ATTACKIEREN/BELAGERN)
- Monster, besondere Gegenstände wie Greifeneier
- Regeldatei für Magellan

## Ideen

- Befehle in der Vorlage wiederholen (LIEFERE bzw. @BEFEHL in Fantasya)
- Ereignisse
- Taktik ermöglicht Strategien im Kampf, STRATEGIE
- Seekampf/Piraterie
- SABOTIERE (Spion versenkt Schiff)
- VERSENKEN (Schiff vor Entern bewahren)
- Monsterrasse bestimmt maximalen Übermacht-Faktor
