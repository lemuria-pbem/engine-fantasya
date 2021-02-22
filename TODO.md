# To-Do

Hier werden Ideen gesammelt und zu behebende Fehler gelistet.

## Ideen

- Nahrung statt Silber
- Marktfunktionen
- Ereignisse
- Mikromanagement reduzieren
- Taktik ermöglicht Strategien im Kampf, STRATEGIE
- Monsterrasse bestimmt maximalen Übermacht-Faktor

## Verbesserungen

### 0.1.0

- Aufgelöste Einheiten entfernen
- Weltentwicklung (Wachstum, Unterhalt)

### 0.2.0 und folgende

- Straßen (Modell, Bau, Abriss, Reisen)
- BOTSCHAFT
- SPIONIEREN
- ROUTE
- FOLGE
- VERSENKEN (Schiff vor Entern bewahren)
- Handel (KAUFEN/VERKAUFEN)
- Kampf (ATTACKIEREN/BELAGERN)
- Magie (KAMPFZAUBER/ZAUBERN)
- Alchemie (Kräuter und Tränke, BENUTZEN)
- Seekampf/Piraterie
- Parteimeldungen für gelöschte Entities als Ereignis mit IDs der zugehörigen
  Meldungen
- Geschlossene Spielwelt

## Fehler

- Allocation deckt nur die eigene Partei ab, muss von Context nach LemuriaTurn verschoben werden
- Allocation muss in distribute() zuerst alle Checks der Consumer durchführen und deregistrieren
